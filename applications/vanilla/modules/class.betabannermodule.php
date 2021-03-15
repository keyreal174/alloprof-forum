<?php defined('APPLICATION') or die;

class BetaBannerModule extends Gdn_Module {
    public $BetaBannerHidden;

    public function __construct() {
        parent::__construct();
        $this->_ApplicationFolder = 'vanilla';
    }

    public function toString() {
        $session = Gdn::session();

        if (!$session->isValid()) {
            return parent::toString();
        }

        $UserMetaData = Gdn::userModel()->getMeta(Gdn::session()->UserID, 'Profile.%', 'Profile.');
        $this->$BetaBannerHidden = $UserMetaData["BetaBannerHidden0"];

        if ($UserMetaData["BetaBannerHidden0"] == "1") {
            return '';
        }
        return parent::toString();
    }
}
