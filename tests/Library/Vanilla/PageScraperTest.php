<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace VanillaTests\Library\Vanilla;

use Exception;
use Garden\Http\HttpRequest;
use VanillaTests\SharedBootstrapTestCase;
use VanillaTests\Fixtures\PageScraper;
use Vanilla\Metadata\Parser\OpenGraphParser;
use Vanilla\Metadata\Parser\JsonLDParser;

class PageScraperTest extends SharedBootstrapTestCase {

    /** @var string Directory of test HTML files. */
    const HTML_DIR = PATH_ROOT.'/tests/fixtures/html';

    /**
     * Grab a new testable instance of PageScraper.
     *
     * @return PageScraper
     */
    private function pageScraper() {
        // Create the test instance. Register the metadata handlers.
        $pageScraper = new PageScraper(new HttpRequest());
        $pageScraper->registerMetadataParser(new OpenGraphParser());
        $pageScraper->registerMetadataParser(new JsonLDParser());
        return $pageScraper;
    }

    /**
     * Provide data for testing the PageScraper::pageInfo method.
     *
     * @return array
     */
    public function provideInfoData(): array {
        $data = [
            [
                'jsonld.htm',
                [
                    'Title' => 'I am a standard title.',
                    'Description' => 'I am a standard description.',
                    'Images' => [],
                    'Attributes' => [
                        'subtype' => 'discussion',
                        'discussion' => [
                            'title' => 'Welcome to awesome!',
                            'body' => 'There\'s nothing sweeter than a fresh new forum, ready to welcome your community.',
                            'insertUser' => [
                                'name' => 'Vanilla Forums',
                                'photoUrl' => 'https://images.v-cdn.net/stubcontent/vanilla_avatar.jpg',
                                'url' => 'https://vanilla.localhost/profile/Vanilla%20Forums'
                            ],
                            'dateInserted' => '2018-04-20T21:06:41+00:00',
                        ]
                    ],
                ]
            ],
            [
                'no-description.htm',
                [
                    'Title' => 'I am a standard title.',
                    'Description' => 'I am a description. Instead of being part of the document head, I am inside the page contents. This is not ideal and is only a fallback for pages without proper meta descriptors.',
                    'Images' => []
                ]
            ],
            [
                'og.htm',
                [
                    'Title' => 'Online Community Software and Customer Forum Software by Vanilla Forums',
                    'Description' => 'Engage your customers with a vibrant and modern online customer community forum. A customer community helps to increases loyalty, reduce support costs and deliver feedback.',
                    'Images' => ['https://vanillaforums.com/images/metaIcons/vanillaForums.png']
                ]
            ],
            [
                'plain.htm',
                [
                    'Title' => 'I am a standard title.',
                    'Description' => 'I am a standard description.',
                    'Images' => []
                ]
            ]
        ];
        return $data;
    }

    /**
     * Test the PageInfo::pageInfo method.
     *
     * @param string $file
     * @param array $expected
     * @throws Exception if there was an error loading the file.
     * @dataProvider provideInfoData
     */
    public function testFetch(string $file, array $expected) {
        $pageScraper = $this->pageScraper();
        $url = 'file://'.self::HTML_DIR."/{$file}";
        $result = $pageScraper->pageInfo($url);
        $expected['Url'] = $url;
        $this->assertEquals($expected, $result);
    }

    /**
     * Scrape a file and return its result.
     *
     * @param string $file The file to scrape.
     * @return array Returns page info.
     * @throws Exception Throws an exception if there was a non-recoverable error scraping.
     */
    protected function scrapeFile(string $file) {
        $scraper = $this->pageScraper();
        $url = 'file://'.self::HTML_DIR."/{$file}";
        $result = $scraper->pageInfo($url);

        return $result;

    }

    /**
     * Test page fetching with unicode characters.
     */
    public function testUnicodeFetch() {
        $files = ['unicode.htm', 'unicode-xml.htm'];

        foreach ($files as $file) {
            $result = $this->scrapeFile($file);

            $this->assertEquals('Test · Hello World', $result['Title']);
            $this->assertEquals('😀😄😘<>', $result['Description']);
        }
    }
}
