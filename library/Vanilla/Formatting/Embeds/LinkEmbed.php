<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0
 */

namespace Vanilla\Formatting\Embeds;

use Gdn_Format;
use Vanilla\PageScraper;

/**
 * Generic link embed.
 */
class LinkEmbed extends Embed {

    /** @var PageScraper */
    private $pageScraper;

    /**
     * LinkEmbed constructor.
     *
     * @param PageScraper $pageScraper
     */
    public function __construct(PageScraper $pageScraper) {
        $this->pageScraper = $pageScraper;
        parent::__construct('link', 'link');
    }

    /**
     * @inheritdoc
     */
    public function matchUrl(string $url) {
        $result = [
            'url' => $url,
            'name' => null,
            'body' => null,
            'photoUrl' => null,
            'media' => [],
            'attributes' => [],
        ];

        if ($this->isNetworkEnabled()) {
            $pageInfo = $this->pageScraper->pageInfo($url);
            $images = $pageInfo['Images'] ?? [];

            $result['name'] = $pageInfo['Title'] ?: null;
            $result['body'] = $pageInfo['Description'] ?: null;
            $result['photoUrl'] = !empty($images) ? reset($images) : null;
            $result['media'] = !empty($images) ? ['image' => $images] : [];
            $result['attributes'] = $pageInfo['Attributes'] ?? [];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function renderData(array $data): string {
        $url = $data['url'] ?? null;
        $name = $data['name'] ?? null;
        $body = $data['body'] ?? null;
        $photoUrl = $data['photoUrl'] ?? null;
        $userPhoto = $data['userPhoto'] ?? null;
        $userName = $data['userName'] ?? null;
        $timestamp = $data['timestamp'] ?? null;
        $humanTime = $data['humanTime'] ?? null;

        if ($photoUrl) {
            $photoUrlEncoded = htmlspecialchars($photoUrl);
            $image = "<img src='$photoUrlEncoded' class='embedLink-image' aria-hidden='true'>";
        } else {
            $image = "";
        }



        if ($userPhoto && $userName) {
            $userPhotoEncoded = htmlspecialchars($userPhoto);
            $userPhotoAsMeta = "<span class=\"embedLink-userPhoto PhotoWrap\"><img src=\"$userPhotoEncoded\" alt=\"$userName\" class=\"ProfilePhoto ProfilePhotoMedium\" /></span>";
        } else {
            $userPhotoAsMeta = "";
        }

        if ($userName) {
            $userName = "<span class=\"embedLink-userName\">$userName</span>";
        } else {
            $userName = "";
        }

        if ($timestamp && $humanTime) {
            $timestampAsMeta = "<time class=\"embedLink-dateTime meta\" dateTime=\"$timestamp\">$humanTime</time>";
        } else {
            $timestampAsMeta = "";
        }

        $urlEncoded = htmlspecialchars(\Gdn_Format::sanitizeUrl($url));
        $urlAsMeta = "<span class=\"embedLink-source meta\">$urlEncoded</span>";
        $nameEncoded = htmlspecialchars($name);
        $bodyEncoded = htmlspecialchars($body);

        $result = <<<HTML
<a class="embedExternal embedLink" href="{$urlEncoded}" rel="noopener noreferrer">
    <div class="embedExternal-content">
        <article class="embedLink-body">
            {$image}
            <div class="embedLink-main">
                <div class="embedLink-header">
                    <h3 class="embedLink-title">{$nameEncoded}</h3>
                    {$userPhotoAsMeta}
                    {$userName}
                    {$timestampAsMeta}
                    {$urlAsMeta}
                </div>
                <div class="embedLink-excerpt">{$bodyEncoded}</div>
            </div>
        </article>
    </div>
</a>
HTML;

        return $result;
    }
}
