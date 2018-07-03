<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0
 */

namespace Vanilla\Embeds;

use Exception;

/**
 * Vimeo embed.
 */
class VimeoEmbed extends VideoEmbed {

    const DEFAULT_HEIGHT = 270;

    const DEFAULT_WIDTH = 640;

    /** @inheritdoc */
    protected $type = 'vimeo';

    /** @inheritdoc */
    protected $domains = ['vimeo.com'];

    /**
     * VimeoEmbed constructor.
     */
    public function __construct() {
        parent::__construct('vimeo', 'video');
    }

    /**
     * @inheritdoc
     */
    public function matchUrl(string $url) {

        $data = null;

        if ($this->isNetworkEnabled()) {
            $oembed = $this->oembed("https://vimeo.com/api/oembed.json?url=" . urlencode($url));
            if ($oembed) {
                $oembed = $this->normalizeOembed($oembed);
                $data = $oembed;
            }
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            preg_match(
                '/(\/[a-zA-Z0-9]+\/)?([a-zA-Z0-9]+\/)?(?<videoID>[0-9]+)/i',
                $path,
                $matches
            );
            // urls without a numeric video will fail for the moment, until embed fall back modified.
            // ie. https://vimeo.com/ondemand/yappie will fail.
            if (!$matches['videoID']) {
                throw new Exception('Unable to get video ID.', 400);
            }


            if (array_key_exists('videoID', $matches)) {
                $data = $data ?: [];
                if (!array_key_exists('attributes', $data)) {
                    $data['attributes'] = [];
                }
                $data['attributes']['videoID'] = $matches['videoID'];
                $data['attributes']['embedUrl'] = "https://player.vimeo.com/video/{$matches['videoID']}?autoplay=1";
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function renderData(array $data): string {
        $attributes = $data['attributes'] ?? [];
        $embedUrl = $attributes['embedUrl'] ?? '';
        $height = $data['height'] ?? self::DEFAULT_HEIGHT;
        $width = $data['width'] ?? self::DEFAULT_WIDTH;
        $name = $data['name'] ?? '';
        $photoURL = $data['photoUrl'] ?? '';

        $result = $this->videoCode($embedUrl, $name, $photoURL, $width, $height);
        return $result;
    }
}
