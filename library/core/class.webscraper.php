<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0
 */

class WebScraper {

    /** Page info caching expiry (24 hours). */
    const CACHE_EXPIRY = 24 * 60 * 60;

    /** Valid image extensions. */
    const IMAGE_EXTENSIONS = ['bmp', 'gif', 'jpeg', 'jpg', 'png', 'svg', 'tif', 'tiff'];

    /** Default scrape type. */
    const TYPE_DEFAULT = 'site';

    /** Type for generic image URLs. */
    const TYPE_IMAGE = 'image';

    /** @var bool */
    private $disableFetch = false;

    /** @var array */
    private $types = [];

    /**
     * WebScraper constructor.
     */
    public function __construct() {
        // Add some default sites.
        $this->registerType('getty', ['gettyimages.com', 'gettyimages.ca'], [$this, 'lookupGetty']);
        $this->registerType('imgur', ['imgur.com'], [$this, 'lookupImgur']);
        $this->registerType('instagram', ['instagram.com', 'instagr.am'], [$this, 'lookupInstagram']);
        $this->registerType('pinterest', ['pinterest.com', 'pinterest.ca'], [$this, 'lookupPinterest']);
        $this->registerType('soundcloud', ['soundcloud.com'], [$this, 'lookupSoundcloud']);
        $this->registerType('twitch', ['twitch.tv'], [$this, 'lookupTwitch']);
        $this->registerType('twitter', ['twitter.com'], [$this, 'lookupTwitter']);
        $this->registerType('vimeo', ['vimeo.com'], [$this, 'lookupVimeo']);
        $this->registerType('vine', ['vine.co'], [$this, 'lookupVine']);
        $this->registerType('wistia', ['wistia.com', 'wi.st'], [$this, 'lookupWistia']);
        $this->registerType('youtube', ['youtube.com', 'youtube.ca', 'youtu.be'], [$this, 'lookupYouTube']);
    }

    /**
     * Fetch page info and normalize its keys.
     *
     * @param string $url Full target URL.
     * @return array
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function fetchPageInfo(string $url): array {
        $result = [
            'url' => $url,
            'name' => null,
            'body' => null,
            'photoUrl' => null,
            'media' => []
        ];

        if (!$this->disableFetch) {
            $pageInfo = fetchPageInfo($url, 3, false, true);

            if ($pageInfo['Exception']) {
                throw new Exception($pageInfo['Exception']);
            }

            $result['name'] = $pageInfo['Title'] ?: null;
            $result['body'] = $pageInfo['Description'] ?: null;
            $result['photoUrl'] = !empty($pageInfo['Images']) ? reset($pageInfo['Images']) : null;
            $result['media'] = $pageInfo['Media'];
        }

        return $result;
    }

    /**
     * Get the status of the "disable fetch" flag.
     *
     * @return bool
     */
    public function getDisableFetch(): bool {
        return $this->disableFetch;
    }

    /**
     * Get data about a page, including site-specific information, if available.
     *
     * @param string $url Full target URL.
     * @param bool $forceRefresh Should loading from the cache be skipped?
     * @return string|null
     * @throws Exception if errors encountered during processing.
     */
    public function getPageInfo(string $url, bool $forceRefresh = false) {
        $urlKey = md5($url);
        $cacheKey = "WebScraper.{$urlKey}";
        $info = null;

        if (!$forceRefresh) {
            $info = Gdn::cache()->get($cacheKey);

            if ($info === Gdn_Cache::CACHEOP_FAILURE) {
                unset($info);
            }
        }

        if (!isset($info)) {
            $type = $this->getTypeFromUrl($url);
            $info = $this->getInfoByType($type, $url);
        }

        if ($info) {
            Gdn::cache()->store($cacheKey, $info, [Gdn_Cache::FEATURE_EXPIRY => self::CACHE_EXPIRY]);
        }

        return $info;
    }

    /**
     * Given an array of info from fetchPageInfo, attempt to determine the size of the image in photoUrl.
     *
     * @param array $pageInfo
     * @return array
     * @throws Exception if the URL is invalid.
     */
    private function getSizeFromPhotoUrl(array $pageInfo): array {
        $width = null;
        $height = null;

        if (array_key_exists('photoUrl', $pageInfo) && !empty($pageInfo['photoUrl'])) {
            list($width, $height) = $this->getImageSize($pageInfo['photoUrl']);
        }

        return [$width, $height];
    }

    /**
     * Given a URL, return its type. Use default if no specific type can be determined..
     *
     * @param string $url
     * @return string
     * @throws InvalidArgumentException if the URL is invalid.
     * @throws Exception if the type does not have a valid "domains" value.
     */
    private function getTypeFromUrl(string $url): string {
        $urlDomain = parse_url($url, PHP_URL_HOST);
        if ($urlDomain === false) {
            throw new InvalidArgumentException('Invalid URL.');
        }

        $types = $this->getTypes();
        $result = null;
        $testDomains = [];
        $urlDomainParts = explode('.', $urlDomain);
        while ($urlDomainParts) {
            $testDomains[] = implode('.', $urlDomainParts);
            array_shift($urlDomainParts);
        }

        foreach ($types as $type => $config) {
            if (!array_key_exists('domains', $config) || !is_array($config['domains'])) {
                throw new Exception('Invalid domains for type.');
            }
            foreach ($config['domains'] as $typeDomain) {
                foreach ($testDomains as $testDomain) {
                    if ($typeDomain == $testDomain) {
                        $result = $type;
                        break 3;
                    }
                }
            }
        }

        // No site-specific matches? Test if we're dealing with an image URL.
        if ($result === null) {
            $path = parse_url($url, PHP_URL_PATH);
            if ($path !== false) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if ($extension && in_array(strtolower($extension), self::IMAGE_EXTENSIONS)) {
                    $result = self::TYPE_IMAGE;
                }
            }
        }

        // Still nothing? Default type.
        if ($result === null) {
            $result = self::TYPE_DEFAULT;
        }

        return $result;
    }

    /**
     * Get all supported sites and their configurations.
     *
     * @return array
     */
    private function getTypes(): array {
        return $this->types;
    }

    /**
     * Attempt to get the width and height of an image.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     */
    private function getImageSize(string $url): array {
        $size = [null, null];

        if (!$this->disableFetch) {
            // Make sure the URL is valid.
            $urlParts = parse_url($url);
            if ($urlParts === false || !in_array(val('scheme', $urlParts), ['http', 'https'])) {
                throw new Exception('Invalid URL.', 400);
            }

            $result = getimagesize($url);
            if (is_array($result) && count($result) >= 2) {
                $size = [$result[0], $result[1]];
            }
        }

        return $size;
    }

    /**
     * Get site-specific data from a page.
     *
     * @param string $type A supported site type.
     * @param string $url The full URL to the page.
     * @return array
     * @throws InvalidArgumentException if the type is invalid.
     * @throws Exception if unable to find a lookup function for the type.
     */
    private function getInfoByType(string $type, string $url): array {
        if ($type === self::TYPE_DEFAULT) {
            $data = $this->lookupDefault($url);
        } elseif ($type === self::TYPE_IMAGE) {
            $data = $this->lookupImage($url);
        } else {
            $types = $this->getTypes();
            if (!array_key_exists($type, $types)) {
                throw new InvalidArgumentException("Invalid type: {$type}");
            }

            $config = $types[$type];
            $data = call_user_func($config['callback'], $url);
        }

        $defaults = [
            'name' => null,
            'body' => null,
            'photoUrl' => null,
            'height' => null,
            'width' => null,
            'attributes' => []
        ];
        $result = array_merge($defaults, $data);
        $result['url'] = $url;
        $result['type'] = $type;

        return $result;
    }

    /**
     * Get width and height of first item of the media type from OpenGraph info in fetchPageInfo result.
     *
     * @param array $pageInfo Array result of a call to fetchPageInfo.
     * @param string $type Media type: image or video.
     * @param int|null $defaultWidth Default width.
     * @param int|null $defaultHeight Default height.
     * @return array
     * @throws InvalidArgumentException if an invalid type is specified.
     */
    private function getMediaSize(array $pageInfo, string $type, int $defaultWidth = null, int $defaultHeight = null): array {
        $validTypes = ['image', 'video'];
        if (!in_array($type, $validTypes)) {
            throw new InvalidArgumentException("Invalid type: {$type}");
        }

        $width = $defaultWidth;
        $height = $defaultHeight;

        if (array_key_exists('media', $pageInfo)
            && array_key_exists($type, $pageInfo['media'])
            && !empty($pageInfo['media'][$type])) {

            $media = reset($pageInfo['media'][$type]);
            $width = val('width', $media, $defaultWidth);
            $height = val('height', $media, $defaultHeight);
        }

        return [$width, $height];
    }

    /**
     * Get oEmbed data from a URL.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if the URL failed to load.
     */
    private function getOembed(string $url): array {
        $result = [];

        if (!$this->disableFetch) {
            // Make sure the URL is valid.
            $urlParts = parse_url($url);
            if ($urlParts === false || !in_array(val('scheme', $urlParts), ['http', 'https'])) {
                throw new Exception('Invalid URL.', 400);
            }

            $request = new ProxyRequest();
            $rawResponse = $request->request([
                'URL' => $url,
                'Redirects' => true,
            ]);
            if ($request->status() !== 200) {
                throw new Exception("Failed to load URL: {$url}");
            }

            $response = json_decode($rawResponse, true);
            if (is_array($response)) {
                $validAttributes = ['type', 'version', 'title', 'author_name', 'author_url', 'provider_name', 'provider_url',
                    'cache_age', 'thumbnail_url', 'thumbnail_width', 'thumbnail_height'];

                $type = $response['type'] ?? null;
                switch ($type) {
                    case 'photo':
                    case 'video':
                    case 'rich':
                        $validAttributes = array_merge(['url', 'width', 'height'], $validAttributes);
                        break;
                }

                // Make it easier to compare by key.
                $validAttributes = array_combine($validAttributes, $validAttributes);
                $result = array_intersect_key($response, $validAttributes);
            }
        }

        return $result;
    }

    /**
     * Gather general information about a document.
     *
     * @param string $url
     * @return array
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupDefault(string $url): array {
        $result = $this->fetchPageInfo($url);
        return $result;
    }

    /**
     * Grab info from Getty Images.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if the URL failed to load.
     */
    private function lookupGetty(string $url): array {
        preg_match(
            '/https?:\/\/(?:(?:www|embed).)?gettyimages\.c(?:a|om)\/(?:embed|detail)\/(?<mediaID>[\d]+)/i',
            $url,
            $matches
        );
        $mediaID = $matches['mediaID'] ?: null;

        $data = [];
        if ($mediaID) {
            $oembed = $this->getOembed("http://embed.gettyimages.com/oembed?url=http%3a%2f%2fgty.im%2f{$mediaID}");
            $data = array_merge($data, $this->normalizeOembed($oembed));
        }

        $attributes = $data['attributes'] ?? [];
        $attributes['mediaID'] = $mediaID;
        $data['attributes'] = $attributes;

        return $data;
    }

    /**
     * Grab info for a standard image.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     */
    private function lookupImage(string $url): array {
        list($width, $height) = $this->getImageSize($url);
        $data = [
            'photoUrl' => $url,
            'width' => $width,
            'height' => $height
        ];
        return $data;
    }

    /**
     * Grab info from an Imgur embed.
     *
     * @param string $url
     * @return array
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupImgur(string $url): array {
        preg_match(
            '/https?:\/\/([im]\.)?imgur\.com\/(?<mediaID>[a-z0-9]+)(\.(?<ext>.{1,3}))?/i',
            $url,
            $matches
        );
        $mediaID = $matches['mediaID'] ?: null;
        $data = [];

        if ($mediaID) {
            // Get basic info from the page markup.
            $data = $this->fetchPageInfo("https://imgur.com/{$mediaID}");
            list($width, $height) = $this->getMediaSize($data, 'image');
            $data['url'] = $url;
            $data['width'] = $width;
            $data['height'] = $height;
        }

        $data['attributes'] = ['mediaID' => $mediaID];

        return $data;
    }

    /**
     * Grab info from Instagram.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupInstagram(string $url): array {
        preg_match(
            '/https?:\/\/(?:www\.)?instagr(?:\.am|am\.com)\/p\/(?<mediaID>[\w-]+)/i',
            $url,
            $matches
        );
        $mediaID = $matches['mediaID'] ?: null;

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = ['mediaID' => $mediaID];

        return $data;
    }

    /**
     * Grab info from Pinterest.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupPinterest($url): array {
        preg_match(
            '/https?:\/\/(?:www\.)?pinterest\.(ca|com)\/pin\/(?<pinID>[\d]+)/i',
            $url,
            $matches
        );
        $pinID = $matches['pinID'] ?: null;

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = ['pinID' => $pinID];

        return $data;
    }

    /**
     * Grab info from SoundCloud.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupSoundCloud(string $url): array {
        preg_match(
            '/https?:(?:www\.)?\/\/soundcloud\.com\/(?<user>[\w=?&;+-_]*)\/(?<trackID>[\w=?&;+-_]*)/i',
            $url,
            $matches
        );
        $user = $matches['user'] ?: null;
        $track = $matches['trackID'] ?: null;

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = [
            'user' => $user,
            'track' => $track
        ];

        return $data;
    }

    /**
     * Grab info from Twitch.tv.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupTwitch(string $url): array {
        preg_match(
            '/https?:\/\/(?:www\.)?twitch\.tv\/(?<channel>[\w]+)/i',
            $url,
            $matches
        );
        $channel = $matches['channel'] ?: null;

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = ['channel' => $channel];

        return $data;
    }


    /**
     * Grab info from Twitter.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupTwitter(string $url): array {
        preg_match(
            '/https?:\/\/(?:www\.)?twitter\.com\/(?:#!\/)?(?:[^\/]+)\/status(?:es)?\/(?<statusID>[\d]+)/i',
            $url,
            $matches
        );

        $statusID = $matches['statusID'] ?: null;

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = ['statusID' => $statusID];

        return $data;
    }

    /**
     * Grab info from Vimeo.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupVimeo(string $url): array {
        preg_match(
            '/https?:\/\/(?:www\.)?vimeo\.com\/(?:channels\/[a-z0-9]+\/)?(?<videoID>\d+)/i',
            $url,
            $matches
        );

        $videoID = $matches['videoID'] ?: null;

        // Try another way to get the video ID.
        if (!$videoID && !$this->disableFetch) {
            $request = new ProxyRequest();
            $rawResponse = $request->request([
                'URL' => "https://vimeo.com/api/oembed.json?url=".urlencode($url),
                'Redirects' => true
            ]);
            $response = json_decode($rawResponse, true);
            if (is_array($response)) {
                $videoID = val('video_id', $response, null);
            }
        }

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = [
            'videoID' => $videoID
        ];

        return $data;
    }

    /**
     * Grab info from Vine.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if the URL failed to load.
     */
    private function lookupVine(string $url): array {
        preg_match(
            '/https?:\/\/(?:www\.)?vine\.co\/(?:v\/)?(?<videoID>[\w]+)/i',
            $url,
            $matches
        );

        $videoID = $matches['videoID'] ?: null;

        $data = [];

        $oembed = $this->getOembed("https://vine.co/oembed.json?url=https%3A%2F%2Fvine.co%2Fv%2F{$videoID}");
        $data = array_merge($data, $this->normalizeOembed($oembed));

        $attributes = $data['attributes'] ?? [];
        $attributes['videoID'] = $videoID;
        $data['attributes'] = $attributes;

        return $data;
    }

    /**
     * Grab info from Wistia.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if there was an error encountered while getting the page's info.
     */
    private function lookupWistia(string $url): array {
        // Try the wvideo-style URL.
        $wvideo = preg_match(
            '/https?:\/\/(?:[A-za-z0-9\-]+\.)?(?:wistia\.com|wi\.st)\/.*?\?wvideo=(?<videoID>[A-za-z0-9]+)([\?&]wtime=(?<time>((\d)+m)?((\d)+s)?))?/i',
            $url,
            $matches
        );
        if (!$wvideo) {
            // Fallback to the medias-style URL.
            preg_match(
                '/https?:\/\/([A-za-z0-9\-]+\.)?(wistia\.com|wi\.st)\/medias\/(?<videoID>[A-za-z0-9]+)(\?wtime=(?<time>((\d)+m)?((\d)+s)?))?/i',
                $url,
                $matches
            );
        }

        $videoID = array_key_exists('videoID', $matches) ? $matches['videoID'] : null;
        $time = array_key_exists('time', $matches) ? $matches['time'] : null;

        // Get basic info from the page markup.
        $data = $this->fetchPageInfo($url);
        list($width, $height) = $this->getSizeFromPhotoUrl($data);

        $data['width'] = $width;
        $data['height'] = $height;
        $data['attributes'] = [
            'videoID' => $videoID,
            'time' => $time
        ];

        return $data;
    }

    /**
     * Grab info about a YouTube video.
     *
     * @param string $url
     * @return array
     * @throws Exception if the URL is invalid.
     * @throws Exception if the URL failed to load.
     */
    private function lookupYouTube(string $url): array {
        // Get info from the URL.
        preg_match(
            '/https?:\/\/(?:(?:www.)|(?:m.))?(?:(?:youtube.(ca|com))|(?:youtu.be))\/(?:(?:playlist?)|(?:(?:watch\?v=)?(?P<videoId>[\w-]{11})))(?:\?|\&)?(?:list=(?P<listId>[\w-]*))?(?:t=(?:(?P<minutes>\d*)m)?(?P<seconds>\d*)s)?(?:#t=(?P<start>\d*))?/i',
            $url,
            $urlParts
        );

        $videoID = array_key_exists('videoId', $urlParts) ? $urlParts['videoId'] : null;

        // Figure out the start time.
        $start = null;
        if (array_key_exists('start', $urlParts)) {
            $start = $urlParts['start'];
        } elseif (array_key_exists('minutes', $urlParts) || array_key_exists('seconds', $urlParts)) {
            $minutes = $urlParts['minutes'] ? intval($urlParts['minutes']) : 0;
            $seconds = $urlParts['seconds'] ? intval($urlParts['seconds']) : 0;
            $start = ($minutes * 60) + $seconds;
        }

        $data = [];

        $oembed = $this->getOembed("https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D{$videoID}");
        $data = array_merge($data, $this->normalizeOembed($oembed));

        $listID = $urlParts['listId'] ?? null;
        $attributes = $data['attributes'] ?? [];
        $attributes['videoID'] = $videoID;
        $attributes['listID'] = $listID ?: null;
        $attributes['start'] = $start;
        $data['attributes'] = $attributes;

        return $data;
    }

    /**
     * Normalize oEmbed fields.
     *
     * @param array $oembed
     * @return array
     */
    private function normalizeOembed(array $oembed): array {
        // Simple renaming.
        $fields = ['name' => 'title', 'photoUrl' => 'thumbnail_url'];
        foreach ($fields as $new => $original) {
            $val = $oembed[$original] ?? null;
            $oembed[$new] = $val;
            unset($oembed[$original]);
        }

        $attributes = [];

        foreach (['width', 'height'] as $sizeAttribute) {
            $thumbField = "thumbnail_{$sizeAttribute}";
            $primary = $oembed[$sizeAttribute] ?? null;
            $thumb = $oembed[$thumbField] ?? null;
            if ($primary && $thumb) {
                $attributes[$thumbField] = $thumb;
            } elseif ($thumb) {
                $primary = $thumb;
            }
            $oembed[$sizeAttribute] = $primary;
            unset($oembed[$thumbField]);
        }

        if (!empty($attributes)) {
            $oembed['attributes'] = $attributes;
        }
        return $oembed;
    }

    /**
     * Register a site.
     *
     * @param string $type
     * @param array $domains
     * @param callable $callback
     * @return self
     */
    public function registerType(string $type, array $domains, callable $callback): self {
        $this->types[$type] = [
            'domains' => $domains,
            'callback' => $callback
        ];

        return $this;
    }

    /**
     * Set value of the "disable fetch" flag. Disabling fetch will avoid downloading page contents.
     *
     * @param bool $disableFetch
     * @return self
     */
    public function setDisableFetch(bool $disableFetch): self {
        $this->disableFetch = boolval($disableFetch);
        return $this;
    }
}
