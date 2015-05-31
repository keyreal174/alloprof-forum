<?php
/**
 * SignedIn module.
 *
 * @copyright 2008-2015 Vanilla Forums, Inc
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Dashboard
 * @since 2.0
 */

/**
 * Info about a user ban.
 */
class UserBanModule extends GDN_Module {

    /** @var int The ban(s) to exclude from the reasons. */
    public $ExcludeBans = 0;

    /** @var string The translation code for the the summary. */
    public $Summary;

    /** @var int UserID The user ID we are looking at. Default to the current user. */
    public $UserID;

    /**
     * Initialize a new instance of the {@link UserBanModule} class.
     */
    public function __construct() {
        parent::__construct();
        $this->_ApplicationFolder = 'dashboard';
    }

    /**
     *
     *
     * @throws Exception
     */
    protected function getData() {
        $userID = $this->UserID ?: Gdn::Session()->UserID;
        $user = Gdn::UserModel()->GetID($userID);

        $banned = val('Banned', $user);
        $bits = BanModel::explodeBans($banned);
        $reasons = array();

        foreach ($bits as $bit) {
            if (($bit & $this->ExcludeBans) === 0) {
                $reasons[$bit] = T("BanReason.$bit");
            }
        }
        $this->SetData('Reasons', $reasons);

        if (!$this->Summary) {
            if ($this->ExcludeBans) {
                $summary = "Also banned for the following:";
            } else {
                $summary = "Banned for the following:";
            }
        }
        $this->SetData('Summary', $this->Summary ?: $summary);

        $this->EventArguments['User'] = $user;
        $this->FireEvent('GetData');
    }

    /**
     *
     *
     * @return string
     */
    public function ToString() {
        if (!Gdn::Session()->CheckPermission('Garden.Moderation.Manage')) {
            // Only moderators can view the reasons for being banned.
            return '';
        }

        $this->getData();

        if (empty($this->Data['Reasons'])) {
            return '';
        } else {
            return parent::ToString();
        }
    }
}
