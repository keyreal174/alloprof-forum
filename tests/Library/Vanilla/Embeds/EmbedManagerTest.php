<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace VanillaTests\Library\Vanilla\Embeds;

use Exception;
use Garden\Http\HttpRequest;
use PHPUnit\Framework\TestCase;
use Vanilla\Embeds\EmbedManager;
use Vanilla\Embeds\LinkEmbed;
use Vanilla\Embeds\ImageEmbed;
use Vanilla\Embeds\TwitterEmbed;
use Vanilla\Embeds\YouTubeEmbed;
use Vanilla\Embeds\VimeoEmbed;
use VanillaTests\Fixtures\PageScraper;
use VanillaTests\Fixtures\NullCache;

class EmbedManagerTest extends TestCase {

    /**
     * Create a new EmbedManager instance.
     *
     * @return EmbedManager
     */
    private function createEmbedManager(): EmbedManager {
        $embedManager = new EmbedManager(new NullCache(), new ImageEmbed);
        $embedManager->setDefaultEmbed(new LinkEmbed(new PageScraper(new HttpRequest())))
            ->addEmbed(new TwitterEmbed())
            ->addEmbed(new YouTubeEmbed())
            ->addEmbed(new VimeoEmbed())
            ->addEmbed(new ImageEmbed(), EmbedManager::PRIORITY_LOW)
            ->setNetworkEnabled(false);
        return $embedManager;
    }

    /**
     * Provide parameters for verifying rendered data.
     *
     * @return array
     */
    public function provideRenderedData() {
        $data = [
            [
                [
                    "url" => "https://vanillaforums.com/images/metaIcons/vanillaForums.png",
                    "type" => "image",
                    "name" => null,
                    "body" => null,
                    "photoUrl" => "https://vanillaforums.com/images/metaIcons/vanillaForums.png",
                    "height" => 630,
                    "width" => 1200,
                    "attributes" => []
                ],
                '<div class="embed-image embed embedImage">
    <img class="embedImage-img" src="https://vanillaforums.com/images/metaIcons/vanillaForums.png">
</div>'
            ],
            [
                [
                    "url" => "https://vanillaforums.com",
                    "type" => "link",
                    "name" => "Online Community Software and Customer Forum Software by Vanilla Forums",
                    "body" => "Engage your customers with a vibrant and modern online customer community forum. A customer community helps to increases loyalty, reduce support costs and deliver feedback.",
                    "photoUrl" => "https://vanillaforums.com/images/metaIcons/vanillaForums.png",
                    "height" => null,
                    "width" => null,
                    "attributes" => []
                ],
                '<a class="embed-link embed embedLink" href="https://vanillaforums.com" target="_blank" rel="noopener noreferrer">
    <article class="embedLink-body">
        <div class="embedLink-image" aria-hidden="true" style="background-image: url(https://vanillaforums.com/images/metaIcons/vanillaForums.png);"></div>
        <div class="embedLink-main">
            <div class="embedLink-header">
                <h3 class="embedLink-title">Online Community Software and Customer Forum Software by Vanilla Forums</h3>
                <div class="embedLink-excerpt">Engage your customers with a vibrant and modern online customer community forum. A customer community helps to increases loyalty, reduce support costs and deliver feedback.</div>
            </div>
        </div>
    </article>
</a>'
            ],
            [
                [
                    "url" => "https://twitter.com/jack/status/20",
                    "type" => "twitter",
                    "name" => null,
                    "body" => null,
                    "photoUrl" => null,
                    "height" => null,
                    "width" => null,
                    "attributes" => [
                        "statusID" => "20"
                    ]
                ],
                '<div class="twitter-card" data-tweeturl="https://twitter.com/jack/status/20" data-tweetid="20"><a href="https://twitter.com/jack/status/20" class="tweet-url" rel="nofollow">https://twitter.com/jack/status/20</a></div>'
            ],
            [
                [
                    "url" => "https://www.youtube.com/watch?v=9bZkp7q19f0",
                    "type" => "youtube",
                    "name" => "YouTube",
                    "body" => null,
                    "photoUrl" => "https://i.ytimg.com/vi/9bZkp7q19f0/hqdefault.jpg",
                    "height" => 270,
                    "width" => 480,
                    "attributes" => [
                        "thumbnail_width" => 480,
                        "thumbnail_height" => 360,
                        "videoID" => "9bZkp7q19f0"
                    ]
                ],
                '<div class="embed-video embed embedVideo">
    <div class="embedVideo-ratio is16by9" style="">
        <button type="button" data-url="https://www.youtube.com/embed/9bZkp7q19f0?feature=oembed&amp;autoplay=1" aria-label="YouTube" class="embedVideo-playButton iconButton js-playVideo" style="background-image: url(https://img.youtube.com/vi/9bZkp7q19f0/0.jpg);">
            <svg class="embedVideo-playIcon" xmlns="http://www.w3.org/2000/svg" viewBox="-1 -1 24 24">
                <title>Play Video</title>
                <path class="embedVideo-playIconPath embedVideo-playIconPath-circle" style="fill: currentColor; stroke-width: .3;" d="M11,0A11,11,0,1,0,22,11,11,11,0,0,0,11,0Zm0,20.308A9.308,9.308,0,1,1,20.308,11,9.308,9.308,0,0,1,11,20.308Z"></path>
                <polygon class="embedVideo-playIconPath embedVideo-playIconPath-triangle" style="fill: currentColor; stroke-width: .3;" points="8.609 6.696 8.609 15.304 16.261 11 8.609 6.696"></polygon>
            </svg>
        </button>
    </div>
</div>'
            ],
            [
                [
                    "url" => "https://vimeo.com/264197456",
                    "type" => "vimeo",
                    "name" => "Vimeo",
                    "body" => null,
                    "photoUrl" => "https://i.vimeocdn.com/video/694532899_640.jpg",
                    "height" => 272,
                    "width" => 640,
                    "attributes" => [
                        "thumbnail_width" => 640,
                        "thumbnail_height" => 272,
                        "videoID" => "264197456",
                        "embedUrl" => "https://player.vimeo.com/video/264197456?autoplay=1",
                    ]
                ],
                '<div class="embed-video embed embedVideo">
    <div class="embedVideo-ratio" style="padding-top: 42.5%;">
        <button type="button" data-url="https://player.vimeo.com/video/264197456?autoplay=1" aria-label="Vimeo" class="embedVideo-playButton iconButton js-playVideo" style="background-image: url(https://i.vimeocdn.com/video/694532899_640.jpg);">
            <svg class="embedVideo-playIcon" xmlns="http://www.w3.org/2000/svg" viewBox="-1 -1 24 24">
                <title>Play Video</title>
                <path class="embedVideo-playIconPath embedVideo-playIconPath-circle" style="fill: currentColor; stroke-width: .3;" d="M11,0A11,11,0,1,0,22,11,11,11,0,0,0,11,0Zm0,20.308A9.308,9.308,0,1,1,20.308,11,9.308,9.308,0,0,1,11,20.308Z"></path>
                <polygon class="embedVideo-playIconPath embedVideo-playIconPath-triangle" style="fill: currentColor; stroke-width: .3;" points="8.609 6.696 8.609 15.304 16.261 11 8.609 6.696"></polygon>
            </svg>
        </button>
    </div>
</div>'
            ]
        ];
        return $data;
    }

    /**
     * Verify rendered data results.
     *
     * @param array $data
     * @param string $expected
     * @throws Exception if a default embed type is needed, but hasn't been configured.
     * @dataProvider provideRenderedData
     */
    public function testRenderData(array $data, string $expected) {
        $embedManager = $this->createEmbedManager();
        $actual = $embedManager->renderData($data);
        $this->assertEquals($expected, $actual);
    }
}
