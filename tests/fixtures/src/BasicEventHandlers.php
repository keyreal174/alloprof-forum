<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace VanillaTests\Fixtures;


class BasicEventHandlers {
    private $foo = 'foo';

    public function filter_handler($val = '') {
        return $val.'filter';
    }

    public function someController_someMethod_create($val = '') {
        return $val.'someController_someMethod';
    }

    public function someController_someEndpoint_endpoint($val = '') {
        return $val.'someController_someEndpoint';
    }

    public function setFoo($value) {
        $this->foo = $value;
        return $this->foo;
    }

    public function getFoo() {
        return $this->foo;
    }

    public function event_before() {
        return 'event_before';
    }

    public function event_after() {
        return 'event_after';
    }

    public function foo_handler() {
        return $this->foo;
    }
}
