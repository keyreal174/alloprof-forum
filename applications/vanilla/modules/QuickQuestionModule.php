<?php
/**
 * Profile filter module.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @since 2.0
 */

/**
 * Renders the profile filter menu.
 */
class QuickQuestionModule extends Gdn_Module {

    public function assetTarget() {
        return 'LeftPanel';
    }

    public function toString() {
        return parent::toString();
    }
}
