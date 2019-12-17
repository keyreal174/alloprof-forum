<?php
/**
 * @author Richard Flynn <richard.flynn@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Library\Core;

use PHPUnit\Framework\TestCase;

/**
 * Tests for safeGlob().
 */

class SafeGlobTest extends TestCase {

    /**
     * Basic test for safeGlob().
     */
    public function testBasicGlob() {
        $actual = safeGlob(PATH_ROOT.'/tests/fixtures/glob/*');
        $expected = array (
            PATH_ROOT.'/tests/fixtures/glob/b.md',
            PATH_ROOT.'/tests/fixtures/glob/a.txt',
        );
        $this->assertSame($expected, $actual);
    }

    /**
     * Test with only '.md' extension.
     */
    public function testOnlyMdExtension() {
        $actual = safeGlob(PATH_ROOT.'/tests/fixtures/glob/*', ['md']);
        $expected = array (
            PATH_ROOT.'/tests/fixtures/glob/b.md',
        );
        $this->assertSame($expected, $actual);
    }
}
