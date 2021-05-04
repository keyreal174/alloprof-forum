<?php
/**
 * Manages basic searching.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Dashboard
 * @since 2.0
 */

use Vanilla\Search\LegacySearchAdapter;
use Vanilla\Search\SearchResults;

/**
 * Handles /search endpoint.
 */
class SearchController extends Gdn_Controller {

    /** @var array Models to automatically instantiate. */
    public $Uses = ['Database', 'UserModel', 'DiscussionModel', 'CommentModel'];

    /**  @var Gdn_Form */
    public $Form;

    /** @var LegacySearchAdapter */
    private $searchAdapter;

    public $ShowOptions;

    /**
     * Object instantiation & form prep.
     *
     * @param LegacySearchAdapter $searchAdapter
     */
    public function __construct(LegacySearchAdapter $searchAdapter,\DiscussionsApiController $discussionApi) {
        parent::__construct();
        $this->searchAdapter = $searchAdapter;
        $this->discussionApi = $discussionApi;
        $form = Gdn::factory('Form');

        // Form prep
        $form->Method = 'get';
        $this->Form = $form;
    }

    /**
     * Add JS, CSS, modules. Automatically run on every use.
     *
     * @since 2.0.0
     * @access public
     */
    public function initialize() {
        $this->Head = new HeadModule($this);
        $this->addJsFile('jquery.js');
        $this->addJsFile('jquery.form.js');
        $this->addJsFile('jquery.popup.js');
        $this->addJsFile('jquery.gardenhandleajaxform.js');
        $this->addJsFile('jquery.expander.js');
        $this->addJsFile('global.js');

        $this->addCssFile('style.css');
        $this->addCssFile('vanillicon.css', 'static');
        $this->addModule('GuestModule');
        parent::initialize();
        $this->setData('Breadcrumbs', [['Name' => t('Search'), 'Url' => '/search']]);
    }

    /**
     * Filter Functionality.
     *
     */

    public function writeFilter() {
        $gradeFilterOption = (Gdn::request()->get('grade') || Gdn::request()->get('grade') == '0') ? strval((int)(Gdn::request()->get('grade'))) : -1;
        $this->GradeID = $gradeFilterOption;

        $subject = (Gdn::request()->get('subject') || Gdn::request()->get('subject') == '0') ? strval((int)(Gdn::request()->get('subject'))) : -1;
        $this->SubjectID = $subject;

        $explanation = Gdn::request()->get('explanation') ?? false;
        $this->IsExplanation = $explanation;

        $outexplanation = Gdn::request()->get('outexplanation') ?? false;
        $this->IsOutExplanation = $outexplanation;

        $verified = Gdn::request()->get('verifiedBy') ?? false;
        $this->IsVerifiedBy = $verified;

        $sort = Gdn::request()->get('sort') ?? 'desc';
        $this->SortDirection = $sort;

        $dashboardDiscussionFilterModule = new DashboardDiscussionFilterModule($gradeFilterOption, $sort, $explanation, $verified, $subject, $outexplanation);
        $this->addModule($dashboardDiscussionFilterModule);
        $this->addJsFile('filter.js');
        $wheres = [];

        if (($this->GradeID || $this->GradeID === '0') && $this->GradeID != -1) {
            $wheres['d.GradeID'] = $this->GradeID;
        } else {
            unset($wheres['d.GradeID']);
        }

        if (($this->SubjectID || $this->SubjectID === '0') && $this->SubjectID != -1) {
            $wheres['d.CategoryID'] = $this->SubjectID;
        } else {
            unset($wheres['d.CategoryID']);
        }

        $role = $this->getUserRole(Gdn::session()->UserID);
        if ($role === 'Teacher') {
            if ($this->IsExplanation == 'true') {
                $wheres['d.CountComments'] = 0;
            } else {
                unset($wheres[$role_where]);
            }
        } else {
            if ($this->IsExplanation == 'true' && $this->IsOutExplanation == 'false') {
                $wheres['d.CountComments >'] = 0;
                if ($wheres['d.CountComments']) { unset($wheres['d.CountComments']); }
            } else if ($this->IsExplanation == 'false' && $this->IsOutExplanation == 'true') {
                $wheres['d.CountComments'] = 0;
                if ($wheres['d.CountComments >']) { unset($wheres['d.CountComments >']); }
            } else if ($this->IsExplanation != 'true' && $this->IsOutExplanation != 'true') {
                if ($wheres['d.CountComments']) { unset($wheres['d.CountComments']); }
                if ($wheres['d.CountComments >']) { unset($wheres['d.CountComments >']); }
            }
        }

        $verify_where = $role === 'Teacher' ? 'd.DateAccepted =' : 'd.DateAccepted <>';
        $verify_value = $role === 'Teacher' ? NULL : '';
        if ($this->IsVerifiedBy == 'true') {
            $wheres[$verify_where] = $verify_value;
        } else {
            unset($wheres[$verify_where]);
        }

        $this->WhereClause = $wheres;
    }

    public function getUserRole($UserID = null) {
        $userModel = new UserModel();
        if ($UserID) {
            $User = $userModel->getID($UserID);
        } else {
            $User = $userModel->getID(Gdn::session()->UserID);
        }

        if($User) {
            $RoleData = $userModel->getRoles($User->UserID);
            if ($RoleData !== false) {
                $Roles = array_column($RoleData->resultArray(), 'Name');
            }

            // Hide personal info roles
            if (!checkPermission('Garden.PersonalInfo.View')) {
                $Roles = array_filter($Roles, 'RoleModel::FilterPersonalInfo');
            }

            if(in_array(Gdn::config('Vanilla.ExtraRoles.Teacher'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Teacher') ?? 'Teacher';
            else $UserRole = RoleModel::TYPE_MEMBER ?? 'Student';

            return $UserRole;
        } else return null;
    }

    /**
     * Default search functionality.
     *
     * @param string $search The search string.
     * @param string $page Page number.
     */
    public function index($Page = false, $search = '') {
        $this->getUserInfo();
        $this->allowJSONP(true);
        $this->ShowOptions = true;
        $this->addJsFile('search.js');
        $this->addCssFile('search.css');
        $this->addJsFile('askquestion.js', 'vanilla');
        $this->addJsFile('select2.js', 'vanilla');
        $this->addJsFile('basic.js', 'vanilla');
        $this->addJsFile('discussionstyle.js', 'vanilla');
        $this->title(t('Search'));

        // Add New Modules
        $this->addModule('AskQuestionModule');

        // Make sure the userphoto module gets added to the page
        $this->addModule('UserPhotoModule');

        $mobileHeader = new MobileHeaderModule(null);
        $this->addModule($mobileHeader);

        // And add the filter menu module
        $this->fireEvent('AfterAddSideMenu');

        // Add discussion and question count on the profile block
        $this->fireEvent('AddProfileTabsInfo');
        $this->addModule('ProfileFilterModule');

        // $bannerModule = new BannerModule(
        //     'Search',
        //     'Home',
        //     t('Search for'),
        //     t(Gdn_Format::text($search).',')
        // );
        // $this->addModule($bannerModule);

        saveToConfig('Garden.Format.EmbedSize', '160x90', false);
        Gdn_Theme::section('SearchResults');

        // Determine offset from $Page
        list($Offset, $Limit) = offsetLimit($Page, c('Vanilla.Discussions.PerPage', 30));
        $Page = pageNumber($Offset, $Limit);

        // We want to limit the number of pages on large databases because requesting a super-high page can kill the db.
        $MaxPages = c('Vanilla.Discussions.MaxPages');
        if ($MaxPages && $Page > $MaxPages) {
            throw notFoundException();
        }

        $this->writeFilter();
        // $where['d.Body like'] = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
        $where['CommentJoin'] = true;
        $where['SearchKey'] = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';

        $where = array_merge($where, $this->WhereClause);
        $DiscussionModel = new DiscussionModel();

        // Get Discussion Count
        $CountDiscussions = $DiscussionModel->getCountForSearch($where);

        $this->checkPageRange($Offset, $CountDiscussions);

        if ($MaxPages) {
            $CountDiscussions = min($MaxPages * $Limit, $CountDiscussions);
        }

        $this->DiscussionData = $DiscussionModel->getWhereWithOrder($where, 'DateInserted', $this->SortDirection, $Limit, $Offset);

        $this->setData('SearchResults', $this->DiscussionData, true);
        $this->setData('SearchTerm', Gdn_Format::text($search), true);
        $this->setData('searchResultCount', $CountDiscussions);
        $this->setData('_CurrentRecords', count($this->DiscussionData));
        $this->setData('CountDiscussions', $CountDiscussions);
        $this->setData('Discussions', $this->DiscussionData, true);
        $this->canonicalUrl(url('search', true));

        // Build a pager
        $PagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'Pager';
        $this->fireEvent('BeforeBuildPager');
        if (!$this->data('_PagerUrl')) {
            $this->setData('_PagerUrl', 'discussions/{Page}');
        }
        $queryString = DiscussionModel::getSortFilterQueryString($DiscussionModel->getSort(), $DiscussionModel->getFilters());
        $this->setData('_PagerUrl', $this->data('_PagerUrl').$queryString);
        $this->Pager = $PagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $Offset,
            $Limit,
            $this->data('CountDiscussions'),
            $this->data('_PagerUrl')
        );

        PagerModule::current($this->Pager);

        $this->setData('_Page', $Page);
        $this->setData('_Limit', $Limit);
        $this->fireEvent('AfterBuildPager');

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'discussions';
        }

        $this->render();
    }

    public function getDiscusson($id) {
        $discussion = $this->discussionApi->discussionByID($id);
        return $discussion;
    }

    /**
     * Adds a tab (or array of tabs) to the profile tab collection ($this->ProfileTabs).
     *
     * @since 2.0.0
     * @access public
     * @param mixed $tabName Tab name (or array of tab names) to add to the profile tab collection.
     * @param string $tabUrl URL the tab should point to.
     * @param string $cssClass Class property to apply to tab.
     * @param string $tabHtml Overrides tab's HTML.
     */
    public function addProfileTab($tabName, $tabUrl = '', $cssClass = '', $tabHtml = '') {
        if (!is_array($tabName)) {
            if ($tabHtml == '') {
                $tabHtml = $tabName;
            }

            $tabName = [$tabName => ['TabUrl' => $tabUrl, 'CssClass' => $cssClass, 'TabHtml' => $tabHtml]];
        }

        foreach ($tabName as $name => $tabInfo) {
            $url = val('TabUrl', $tabInfo, '');
            if ($url == '') {
                $tabInfo['TabUrl'] = userUrl($this->User, '', strtolower($name));
            }

            $this->ProfileTabs[$name] = $tabInfo;
            $this->_ProfileTabs[$name] = $tabInfo; // Backwards Compatibility
        }
    }

    /**
     * Retrieve the user to be manipulated. Defaults to current user.
     *
     * @since 2.0.0
     * @access public
     * @param mixed $User Unique identifier, possibly username or ID.
     * @param string $username .
     * @param int $userID Unique ID.
     * @param bool $checkPermissions Whether or not to check user permissions.
     * @return bool Always true.
     */
    public function getUserInfo($userReference = '', $username = '', $userID = '', $checkPermissions = false) {
        if ($this->_UserInfoRetrieved) {
            return;
        }

        // If a UserID was provided as a querystring parameter, use it over anything else:
        if ($userID) {
            $userReference = $userID;
            $username = 'Unknown'; // Fill this with a value so the $UserReference is assumed to be an integer/userid.
        }

        $this->Roles = [];
        if ($userReference == '') {
            if ($username) {
                $this->User = $this->UserModel->getByUsername($username);
            } else {
                $this->User = $this->UserModel->getID(Gdn::session()->UserID);
            }
        } elseif (is_numeric($userReference) && $username != '') {
            $this->User = $this->UserModel->getID($userReference);
        } else {
            $this->User = $this->UserModel->getByUsername($userReference);
        }

        $this->fireEvent('UserLoaded');

        if ($this->User === false) {
            // throw notFoundException('User');
        } elseif ($this->User->Deleted == 1) {
            redirectTo('dashboard/home/deleted');
        } else {
            $this->RoleData = $this->UserModel->getRoles($this->User->UserID);
            if ($this->RoleData !== false && $this->RoleData->numRows(DATASET_TYPE_ARRAY) > 0) {
                $this->Roles = array_column($this->RoleData->resultArray(), 'Name');
            }

            // Hide personal info roles
            if (!checkPermission('Garden.PersonalInfo.View')) {
                $this->Roles = array_filter($this->Roles, 'RoleModel::FilterPersonalInfo');
            }

            $this->setData('Profile', $this->User);
            $this->setData('UserRoles', $this->Roles);
            if ($cssClass = val('_CssClass', $this->User)) {
                $this->CssClass .= ' '.$cssClass;
            }
        }

        if ($checkPermissions && Gdn::session()->UserID != $this->User->UserID) {
            $this->permission(['Garden.Users.Edit', 'Moderation.Profiles.Edit'], false);
        }

        // $this->addSideMenu();
        $this->_UserInfoRetrieved = true;
        return true;
    }

    public function mobile() {
        $this->View = 'mobile_search';
        $this->addJsFile('search.js');
        $this->render();
    }
}
