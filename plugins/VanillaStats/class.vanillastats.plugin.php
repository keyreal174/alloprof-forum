<?php
/**
 * VanillaStats Plugin
 *
 * @author Tim Gunter <tim@vanillaforums.com>
 * @copyright 2009-2015 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package VanillaStats
 */

$PluginInfo['VanillaStats'] = array(
    'Name' => 'Vanilla Statistics',
    'Description' => 'Adds helpful graphs and information about activity on your forum over time (new users, discussions, comments, and pageviews).',
    'Version' => '2.0.6',
    'MobileFriendly' => false,
    'RequiredApplications' => array('Vanilla' => '2.0.18'),
    'Author' => "Vanilla Staff",
    'AuthorEmail' => 'support@vanillaforums.com',
    'AuthorUrl' => 'http://www.vanillaforums.com'
);

/**
 * This plugin tracks pageviews on the forum and reports them to the central Vanilla
 * Analytics System.
 *
 * Changes:
 *  1.0     Official release
 *  2.0.3   Fix http/https issue
 */
class VanillaStatsPlugin extends Gdn_Plugin {

    /**  */
    const RESOLUTION_DAY = 'day';

    /**  */
    const RESOLUTION_MONTH = 'month';

    /** @var mixed  */
    public $AnalyticsServer;

    /** @var string  */
    public $VanillaID;

    /**
     * Plugin setup.
     */
    public function __construct() {
        $this->AnalyticsServer = c('Garden.Analytics.Remote', 'analytics.vanillaforums.com');
        $this->VanillaID = Gdn::InstallationID();
    }

    /**
     * Override the default dashboard page with the new stats one.
     */
    public function Gdn_Dispatcher_BeforeDispatch_Handler($Sender) {
        $Enabled = c('Garden.Analytics.Enabled', true);

        if ($Enabled && !Gdn::pluginManager()->HasNewMethod('SettingsController', 'Index')) {
            Gdn::pluginManager()->RegisterNewMethod('VanillaStatsPlugin', 'StatsDashboard', 'SettingsController', 'Index');
        }
    }

    public function SecurityTokenCallback($JsonResponse, $RawResponse) {
        $SecurityToken = val('SecurityToken', $JsonResponse, null);
        if (!is_null($SecurityToken)) {
            $this->SecurityToken($SecurityToken);
        }
    }

    /**
     * Get the security token.
     *
     * @param null|string $SetSecurityToken
     * @return string
     */
    protected function SecurityToken($SetSecurityToken = null) {
        static $SecurityToken = null;

        if (!is_null($SetSecurityToken)) {
            $SecurityToken = $SetSecurityToken;
        }

        if (is_null($SecurityToken)) {
            $Request = array('VanillaID' => $this->VanillaID);
            Gdn::Statistics()->BasicParameters($Request);
            Gdn::Statistics()->Analytics('graph/getsecuritytoken.json', $Request, array(
                'Success' => array($this, 'SecurityTokenCallback')
            ));
        }
        return $SecurityToken;
    }

    /**
     * Override the default index method of the settings controller in the
     * dashboard application to render new statistics.
     */
    public function StatsDashboard($Sender) {
        $StatsUrl = $this->AnalyticsServer;
        if (!stringBeginsWith($StatsUrl, 'http:') && !stringBeginsWith($StatsUrl, 'https:')) {
            $StatsUrl = Gdn::request()->Scheme()."://{$StatsUrl}";
        }

        // Tell the page where to find the Vanilla Analytics provider
        $Sender->addDefinition('VanillaStatsUrl', $StatsUrl);
        $Sender->SetData('VanillaStatsUrl', $StatsUrl);

        // Load javascript & css, check permissions, and load side menu for this page.
        $Sender->addJsFile('settings.js');
        $Sender->Title(T('Dashboard'));
        $Sender->RequiredAdminPermissions[] = 'Garden.Settings.View';
        $Sender->RequiredAdminPermissions[] = 'Garden.Settings.Manage';
        $Sender->RequiredAdminPermissions[] = 'Garden.Community.Manage';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Add';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Edit';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Delete';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Approve';
        $Sender->fireEvent('DefineAdminPermissions');
        $Sender->Permission($Sender->RequiredAdminPermissions, '', false);
        $Sender->AddSideMenu('dashboard/settings');

        if (!Gdn_Statistics::CheckIsEnabled() && Gdn_Statistics::CheckIsLocalhost()) {
            $Sender->Render('dashboardlocalhost', '', 'plugins/VanillaStats');
        } else {
            $Sender->addJsFile('vanillastats.js', 'plugins/VanillaStats');
            $Sender->addJsFile('picker.js', 'plugins/VanillaStats');
            $Sender->AddCSSFile('picker.css', 'plugins/VanillaStats');

            $this->ConfigureRange($Sender);

            $VanillaID = Gdn::InstallationID();
            $Sender->SetData('VanillaID', $VanillaID);
            $Sender->SetData('VanillaVersion', APPLICATION_VERSION);
            $Sender->SetData('SecurityToken', $this->SecurityToken());

            // Render the custom dashboard view
            $Sender->Render('dashboard', '', 'plugins/VanillaStats');
        }
    }

    /**
     * A view containing most active discussions & users during a specific time
     * period. This gets ajaxed into the dashboard homepage as date ranges are
     * defined.
     */
    public function SettingsController_DashboardSummaries_Create($Sender) {
        // Load javascript & css, check permissions, and load side menu for this page.
        $Sender->addJsFile('settings.js');
        $Sender->Title(T('Dashboard Summaries'));

        $Sender->RequiredAdminPermissions[] = 'Garden.Settings.Manage';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Add';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Edit';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Delete';
        $Sender->RequiredAdminPermissions[] = 'Garden.Users.Approve';

        $Sender->fireEvent('DefineAdminPermissions');
        $Sender->Permission($Sender->RequiredAdminPermissions, '', false);
        $Sender->AddSideMenu('dashboard/settings');

        $this->ConfigureRange($Sender);

        // Load the most active discussions during this date range
        $UserModel = new UserModel();
        $Sender->SetData('DiscussionData', $UserModel->SQL
            ->select('d.DiscussionID, d.Name, d.CountBookmarks, d.CountViews, d.CountComments, d.CategoryID')
            ->from('Discussion d')
            ->where('d.DateLastComment >=', $Sender->DateStart)
            ->where('d.DateLastComment <=', $Sender->DateEnd)
            ->orderBy('d.CountViews', 'desc')
            ->orderBy('d.CountComments', 'desc')
            ->orderBy('d.CountBookmarks', 'desc')
            ->limit(10, 0)
            ->get());

        // Load the most active users during this date range
        $Sender->SetData('UserData', $UserModel->SQL
            ->select('u.UserID, u.Name')
            ->select('c.CommentID', 'count', 'CountComments')
            ->from('User u')
            ->join('Comment c', 'u.UserID = c.InsertUserID', 'inner')
            ->groupBy('u.UserID, u.Name')
            ->where('c.DateInserted >=', $Sender->DateStart)
            ->where('c.DateInserted <=', $Sender->DateEnd)
            ->orderBy('CountComments', 'desc')
            ->limit(10, 0)
            ->get());

        // Render the custom dashboard view
        $Sender->Render('dashboardsummaries', '', 'plugins/VanillaStats');
    }

    /**
     * Set the date range.
     *
     * @param $Sender
     * @throws Exception
     */
    private function ConfigureRange($Sender) {
        // Grab the range resolution from the url or form. Default to "day" range.
        $Sender->Range = GetIncomingValue('Range');
        if (!in_array($Sender->Range, array(VanillaStatsPlugin::RESOLUTION_DAY, VanillaStatsPlugin::RESOLUTION_MONTH))) {
            $Sender->Range = VanillaStatsPlugin::RESOLUTION_DAY;
        }

        // Define default values for start & end dates
        $Sender->DayStampStart = strtotime('1 month ago'); // Default to 1 month ago
        $Sender->MonthStampStart = strtotime('12 months ago'); // Default to 24 months ago

        $Sender->DayDateStart = Gdn_Format::ToDate($Sender->DayStampStart);
        $Sender->MonthDateStart = Gdn_Format::ToDate($Sender->MonthStampStart);

        // Validate that any values coming from the url or form are valid
        $Sender->DateRange = GetIncomingValue('DateRange');
        $DateRangeParts = explode('-', $Sender->DateRange);
        $Sender->StampStart = strtotime(val(0, $DateRangeParts));
        $Sender->StampEnd = strtotime(val(1, $DateRangeParts));
        if (!$Sender->StampEnd) {
            $Sender->StampEnd = strtotime('yesterday');
        }

        // If no date was provided, or the provided values were invalid, use defaults
        if (!$Sender->StampStart) {
            $Sender->StampEnd = time();
            if ($Sender->Range == 'day') {
                $Sender->StampStart = $Sender->DayStampStart;
            }
            if ($Sender->Range == 'month') {
                $Sender->StampStart = $Sender->MonthStampStart;
            }
        }

        // Assign the variables used in the page with the validated values.
        $Sender->DateStart = Gdn_Format::ToDate($Sender->StampStart);
        $Sender->DateEnd = Gdn_Format::ToDate($Sender->StampEnd);
        $Sender->DateRange = $Sender->DateStart.' - '.$Sender->DateEnd;

        // Define the range boundaries.
        $Database = Gdn::database();

        // We use the User table as the boundary start b/c users are always inserted before discussions or comments.
        // We have to put a little kludge in here b/c an older version of Vanilla hard-inserted the admin user with an insert date of Sept 16, 1975.
        $Data = $Database->sql()
            ->select('DateInserted')
            ->from('User')
            ->where('DateInserted >', '1975-09-17')
            ->orderBy('DateInserted', 'asc')
            ->limit(1)
            ->get()->firstRow();

        $Sender->BoundaryStart = Gdn_Format::date($Data ? $Data->DateInserted : $Sender->DateStart, '%Y-%m-%d');
        $Sender->BoundaryEnd = Gdn_Format::date($Sender->DateEnd, '%Y-%m-%d');
    }
}
