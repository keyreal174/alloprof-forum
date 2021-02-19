<?php
/**
 * Profile edit module.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @since 2.0
 */

/**
 * Renders the profile edit block.
 */
class ProfileEditModule extends Gdn_Module {

    public function assetTarget() {
        return 'LeftPanel';
    }

    public function toString() {
        return parent::toString();
    }

}
