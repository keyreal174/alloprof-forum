<?php
/**
 * Guest module.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @since 2.0
 */

/**
 * Renders the "You should register or sign in" panel box.
 */
class GuestModule extends Gdn_Module {

    /** @var string  */
    public $MessageCode = 'GuestModule.Message';

    /** @var string  */
    public $MessageDefault = "Join the Alloprof community and find answers to your questions!";

    /**
     *
     *
     * @param string $sender
     * @param bool $applicationFolder
     */
    public function __construct($sender = '', $applicationFolder = false) {
        if (!$applicationFolder) {
            $applicationFolder = 'Dashboard';
        }
        parent::__construct($sender, $applicationFolder);

        $this->Visible = c('Garden.Modules.ShowGuestModule');
    }

    /**
     *
     *
     * @return string
     */
    public function assetTarget() {
        return 'LeftPanel';
    }

    /**
     * Get module data
     */
    public function getData() {
        $controller = Gdn::controller();
        $this->setData('signInUrl', signInUrl($controller->SelfUrl));
        $this->setData('registerUrl', registerUrl($controller->SelfUrl));
    }

    /**
     * Render.
     *
     * @return string
     */
    public function toString() {
        if (!Gdn::session()->isValid()) {
            $this->getData();
            return parent::toString();
        }

        return '';
    }
}
