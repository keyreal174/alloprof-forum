<?php
/**
 * User Photo module.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @since 2.0
 */

/**
 * Renders a user's photo (if they've uploaded one).
 */
class UserPhotoModule extends Gdn_Module {

    /** @var array  */
    public $User;

    /**
     * @var bool Can the current user edit this user's photo?
     */
    public $CanEditPhotos;

    public function __construct() {
        $this->User = false;
        parent::__construct();
        $this->_ApplicationFolder = 'dashboard';
        $this->CanEditPhotos = Gdn::session()->checkRankedPermission(c('Garden.Profile.EditPhotos', true)) || Gdn::session()->checkPermission('Garden.Users.Edit');
    }

    public function loadData() {
        $userID = Gdn::controller()->data('Profile.UserID', Gdn::session()->UserID);
        $this->User = Gdn::userModel()->getID($userID);
        $this->Roles = Gdn::userModel()->getRoles($userID)->resultArray();
        // Hide personal info roles
        if (!checkPermission('Garden.PersonalInfo.View')) {
            $this->Roles = array_filter($this->Roles, 'RoleModel::FilterPersonalInfo');
        }
        $this->setData('_canViewPersonalInfo', Gdn::session()->UserID === $this->User->UserID || gdn::session()->checkPermission('Garden.PersonalInfo.View'));
    }

    public function assetTarget() {
        return 'LeftPanel';
    }

    public function toString() {
        if (!$this->User) {
            $this->loadData();
        }

        if (is_object($this->User)) {
            return parent::toString();
        }

        return '';
        // return parent::toString();
    }
}
