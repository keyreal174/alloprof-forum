<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace Vanilla\Embeds;

use Exception;

/**
 * Generic link embed.
 */
class LinkEmbed extends AbstractEmbed {

    protected $type = 'link';

    /**
     * @inheritdoc
     */
    public function matchUrl(string $url) {
        $result = [
            'url' => $url,
            'name' => null,
            'body' => null,
            'photoUrl' => null,
            'media' => []
        ];

        if ($this->isNetworkEnabled()) {
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
     * @inheritdoc
     */
    public function renderData(array $data): string {
        $url = $data['url'] ?? null;
        $name = $data['name'] ?? null;
        $body = $data['body'] ?? null;
        $photoUrl = $data['photoUrl'] ?? null;

        if ($photoUrl) {
            $photoUrlEncoded = htmlspecialchars($photoUrl);
        $image = <<<HTML
<div class="embedLink-image" aria-hidden="true" style="background-image: url({$photoUrlEncoded});"></div>
HTML;
        } else {
            $image = '';
        }

        $urlEncoded = htmlspecialchars($url);
        $nameEncoded = htmlentities($name);
        $bodyEncoded = htmlentities($body);

        $result = <<<HTML
<a class="embed-link embed embedLink" href="{$urlEncoded}" target="_blank" rel="noopener noreferrer">
    <article class="embedLink-body">
        {$image}
        <div class="embedLink-main">
            <div class="embedLink-header">
                <h3 class="embedLink-title">{$nameEncoded}</h3>
                <div class="embedLink-excerpt">{$bodyEncoded}</div>
            </div>
        </div>
    </article>
</a>
HTML;

        return $result;
    }
}
