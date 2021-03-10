<?php
/**
 * Discussion filters module
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

/**
 * Renders the discussion filter menu.
 */
class CommentFilterModule extends Gdn_Module {

    public function __construct($sort='desc', $verified=false) {
        parent::__construct();
        $this->Sort = $sort;
        $this->IsVerified = $verified;
    }

    public function assetTarget() {
        return 'Panel';
    }

    public function toString() {
        return parent::toString();
    }
}
