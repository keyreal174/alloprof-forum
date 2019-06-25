<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests;

use Garden\Container\Container;
use PHPUnit\Framework\TestCase;

/**
 * A very minimal PHPUnit test case using Garden\Container.
 */
class ContainerTestCase extends TestCase {

    /**
     * Setup the container.
     */
    public static function setUpBeforeClass() {
        \Gdn::setContainer(new Container());
    }

    /**
     * Reset the container.
     */
    public static function tearDownAfterClass() {
        \Gdn::setContainer(new NullContainer());
    }
}
