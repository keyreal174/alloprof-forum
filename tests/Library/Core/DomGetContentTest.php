<?php
/**
 * @author Richard Flynn <richard.flynn@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Library\Core;

use PHPUnit\Framework\TestCase;

/**
 * Tests for domGetContent().
 */
class DomGetContentTest extends TestCase {

    /**
     * Test {@link domGetContent()}.
     *
     * @param string $testHtml The DOM to search.
     * @param string $testSelector The CSS style selector for the content to find.
     * @param string $testDefault The default content to return if the node isn't found.
     * @param string $expected The expected result.
     * @dataProvider provideDomGetContentArrays
     */
    public function testDomGetContent($testHtml, string $testSelector, string $testDefault, $expected) {
        $testDom = \pQuery::parseStr($testHtml);
        $actual = domGetContent($testDom, $testSelector, $testDefault);
        $this->assertSame($expected, $actual);
    }

    /**
     * Provide test data for {@link domGetContent()}.
     *
     * @return array Returns an array of test data
     */
    public function provideDomGetContentArrays() {
        $r = [
            'test1' => [
                '<h1><a id="user-content-pquery" class="anchor" aria-hidden="true" href="#pquery"><svg 
                class="octicon octicon-link" viewBox="0 0 16 16" version="1.1" width="16" height="16" 
                aria-hidden="true"><path fill-rule="evenodd" d="M4 9h1v1H4c-1.5 0-3-1.69-3-3.5S2.55 3 4 
                3h4c1.45 0 3 1.69 3 3.5 0 1.41-.91 2.72-2 3.25V8.59c.58-.45 1-1.27 1-2.09C10 5.22 8.98 4 
                8 4H4c-.98 0-2 1.22-2 2.5S3 9 4 9zm9-3h-1v1h1c1 0 2 1.22 2 2.5S13.98 12 13 12H9c-.98 
                0-2-1.22-2-2.5 0-.83.42-1.64 1-2.09V6.25c-1.09.53-2 1.84-2 3.25C6 11.31 7.55 13 9 13h4c1.45 
                0 3-1.69 3-3.5S14.5 6 13 6z"></path></svg></a>pQuery</h1>',
                'h1',
                'burryQuery',
                'burryQuery',
            ],
        ];

        return $r;
    }
}
