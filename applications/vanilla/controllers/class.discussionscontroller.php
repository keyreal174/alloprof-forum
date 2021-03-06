<?php
/**
 * Discussions controller
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

use Vanilla\Formatting\Formats\HtmlFormat;

/**
 * Handles displaying discussions in most contexts via /discussions endpoint.
 *
 * @todo Resolve inconsistency between use of $Page and $Offset as parameters.
 */
class DiscussionsController extends VanillaController {

    /** @var array Models to include. */
    public $Uses = ['Database', 'DiscussionModel', 'Form', 'UserModel', 'ActivityModel'];

    /** @var boolean Value indicating if discussion options should be displayed when rendering the discussion view.*/
    public $ShowOptions;

    /** @var object Category object. Used to limit which discussions are returned to a particular category. */
    public $Category;

    /** @var int Unique identifier for category. */
    public $CategoryID;

    /** @var array Limit the discussions to just this list of categories, checked for view permission. */
    protected $categoryIDs;

    /** @var boolean Value indicating whether to show the category following filter */
    public $enableFollowingFilter = false;

    /** @var Gdn_Form */
    public $Form;

    /** @var array An associative array of form types and their locations. */
    public $FormCollection;

    /** @var object User data to use in building profile. */
    public $User;

    /** @var bool Whether data has been stored in $this->User yet. */
    protected $_UserInfoRetrieved = false;

    /** @var array List of available tabs. */
    public $ProfileTabs;

    /** @var string UserRole 'Teacher' or 'Student' */
    public $UserRole;

    /**
     * Prep properties.
     *
     * @since 2.0.0
     * @access public
     */
    public function __construct() {
        $this->User = false;
        $this->ProfileTabs = [];
        parent::__construct();
        $this->UserRole = $this->getUserRole();
    }

    /**
     * "Table" layout for discussions. Mimics more traditional forum discussion layout.
     *
     * @param string $page Multiplied by PerPage option to determine offset.
     */
    public function table($page = '0') {
        if ($this->SyndicationMethod == SYNDICATION_NONE) {
            $this->View = 'table';
        }
        $this->index($page);
    }

    public function writeFilter() {
        $gradeFilterOption = (Gdn::request()->get('grade') || Gdn::request()->get('grade') == '0') ? strval((int)(Gdn::request()->get('grade'))) : -1;
        $this->GradeID = $gradeFilterOption;

        $explanation = Gdn::request()->get('explanation') ?? false;
        $this->IsExplanation = $explanation;

        $verified = Gdn::request()->get('verifiedBy') ?? false;
        $this->IsVerifiedBy = $verified;

        $sort = Gdn::request()->get('sort') ?? 'desc';
        $this->SortDirection = $sort;

        $discussionFilterModule = new DiscussionFilterModule($gradeFilterOption, $sort, $explanation, $verified);
        $this->addModule($discussionFilterModule);
        $this->addJsFile('filter.js');
        $wheres = [];

        if (($this->GradeID || $this->GradeID === '0') && $this->GradeID != -1) {
            $wheres['d.GradeID'] = $this->GradeID;
        } else {
            unset($wheres['d.GradeID']);
        }

        if ($this->IsExplanation == 'true') {
            $wheres['d.CountComments >'] = 0;
        } else {
            unset($wheres['d.CountComments >']);
        }

        if ($this->IsVerifiedBy == 'true') {
            $wheres['d.DateAccepted <>'] = '';
        } else {
            unset($wheres['d.DateAccepted <>']);
        }

        $this->WhereClause = $wheres;
    }

    /**
     * Check New Popup Notification
     */

    public function checkNewPopup() {
        $activities = $this->ActivityModel->getWhere(['NotifyUserID' => Gdn::session()->UserID, 'Notified' => ActivityModel::SENT_POPUP])->resultArray();
        $ids = [];

        for($i = 0; $i < count($activities); ++$i) {
            array_push($ids, $activities[$i]['ActivityID']);
            $data = $activities[$i]['Data'];
            $link = $activities[$i]['Route'];

            $this->informMessage(
                '<div class="toast-container"><div class="toast-title">'.t($data['Title']).'</div>'.
                '<div class="toast-text">'.t($data['Text']).'</div>'.
                '<a href="'.$link.'" class="btn-default">'.t('See').'</a></div>',
                'Dismissable'
            );
        }

        $this->ActivityModel->setReadPopup($ids);
    }

    /**
     * Default all discussions view: chronological by most recent comment.
     *
     * @since 2.0.0
     * @access public
     *
     * @param string|false $Page Multiplied by PerPage option to determine offset.
     */
    public function index($Page = false) {
        $this->getUserInfo();
        $this->allowJSONP(true);
        // Figure out which discussions layout to choose (Defined on "Homepage" settings page).
        $Layout = c('Vanilla.Discussions.Layout');
        switch ($Layout) {
            case 'table':
                if ($this->SyndicationMethod == SYNDICATION_NONE) {
                    $this->View = 'table';
                }
                break;
            default:
                $this->View = 'index';
                break;
        }
        Gdn_Theme::section('DiscussionList');

        $this->checkNewPopup();

        $this->addJsFile('jquery.autosize.min.js');
        $this->addJsFile('autosave.js');
        $this->addJsFile('post.js');
        $this->addJsFile('askquestion.js');

        // Remove score sort
        DiscussionModel::removeSort('top');

        // Check for the feed keyword.
        if ($Page === 'feed' && $this->SyndicationMethod != SYNDICATION_NONE) {
            $Page = 'p1';
        }

        // Determine offset from $Page
        list($Offset, $Limit) = offsetLimit($Page, c('Vanilla.Discussions.PerPage', 30), true);
        $Page = pageNumber($Offset, $Limit);

        // Allow page manipulation
        $this->EventArguments['Page'] = &$Page;
        $this->EventArguments['Offset'] = &$Offset;
        $this->EventArguments['Limit'] = &$Limit;
        $this->fireEvent('AfterPageCalculation');

        // Set canonical URL
        $canonicalUrl = empty($this->Data['isHomepage']) ?
            url(concatSep('/', 'discussions', pageNumber($Offset, $Limit, true, false)), true) :
            url('/', true);
        $this->canonicalUrl($canonicalUrl);

        // We want to limit the number of pages on large databases because requesting a super-high page can kill the db.
        $MaxPages = c('Vanilla.Discussions.MaxPages');
        if ($MaxPages && $Page > $MaxPages) {
            throw notFoundException();
        }

        // Setup head.
        if (!$this->data('Title')) {
            $Title = Gdn::formatService()->renderPlainText(c('Garden.HomepageTitle'), HtmlFormat::FORMAT_KEY);
            $DefaultControllerRoute = val('Destination', Gdn::router()->getRoute('DefaultController'));
            if ($Title && ($DefaultControllerRoute == 'discussions')) {
                $this->title($Title, '');
            } else {
                if ($this->UserRole == "Teacher") {
                    $this->title(t('Recommended for you'));
                } else {
                    $this->title(t('Popular questions'));
                }
            }
        }
        if (!$this->description()) {
            $this->description(Gdn::formatService()->renderPlainText(c('Garden.Description', ''), HtmlFormat::FORMAT_KEY));
        }
        if ($this->Head) {
            $this->Head->addRss(url('/discussions/feed.rss', true), $this->Head->title());
        }

        // Add modules
        $this->addModule('AskQuestionModule');
        $this->addModule('CategoriesModule');
        // Filtering and Sorter Module
        $this->writeFilter();

        // Make sure the userphoto module gets added to the page
        $this->addModule('UserPhotoModule');

        // And add the filter menu module
        $this->fireEvent('AfterAddSideMenu');

        // Add discussion and question count on the profile block
        $this->fireEvent('AddProfileTabsInfo');

        if ($this->UserRole == "Teacher") {
            $bannerModule = new BannerModule('Home', 'Home', 'Welcome to', 'the Mutual Aid Zone', 'Want to help students? Explain away!', "", "/themes/alloprof/design/images/teacher-banner.svg", "#0C6B52");
        } else {
            $this->addModule('NewDiscussionModule');
            $bannerModule = new BannerModule('Home', 'Home', 'Welcome to', 'the Mutual Aid Zone', 'Do you have a question? Here are the explanations!');
            $this->addModule('ProfileFilterModule');
        }

        $this->addModule($bannerModule);

        // $this->addModule('BookmarkedModule');
        // $this->addModule('TagModule');

        // $this->setData('Breadcrumbs', [['Name' => t('Popular questions'), 'Url' => '/discussions']]);
        // $this->setData('QuestionSubMenus', [['Name' => t('Popular questions'), 'Url' => '/discussions']]);

        $categoryModel = new CategoryModel();
        $followingEnabled = $categoryModel->followingEnabled();
        if ($followingEnabled) {
            // some other controller has already set this value, so just take what's there
            if (array_key_exists('EnableFollowingFilter', $this->Data)) {
                $this->enableFollowingFilter = $this->data('EnableFollowingFilter');
            } else {
                $saveFollowing = Gdn::request()->get('save') && Gdn::session()->validateTransientKey(Gdn::request()->get('TransientKey', ''));
                $followed = paramPreference(
                    'followed',
                    'FollowedDiscussions',
                    'Vanilla.SaveFollowingPreference',
                    null,
                    $saveFollowing
                );
                $followedCategories = array_keys($categoryModel->getFollowed(Gdn::session()->UserID));
                $followed = count($followedCategories) > 0 ? true : false;
                if (strpos($this->SelfUrl, "discussions") !== false) {
                    $this->enableFollowingFilter = true;
                }
            }
        } else {
            $followed = false;
        }

        $this->setData('EnableFollowingFilter', $this->enableFollowingFilter);
        if ($this->enableFollowingFilter) {
            $this->setData('Followed', $followed);
        }

        // Set criteria & get discussions data
        $this->setData('Category', false, true);
        $DiscussionModel = new DiscussionModel();
        if ($this->data('ApplyRestrictions') === true) {
            $DiscussionModel->setOption('ApplyRestrictions', true);
        }
        $DiscussionModel->setSort($this->SortDirection);
        $DiscussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $DiscussionModel->getSort());
        $this->setData('Filters', $DiscussionModel->getFilters());

        // Check for individual categories.
        $categoryIDs = $this->getCategoryIDs();
        // Fix to segregate announcement conditions until announcement caching has been reworked.
        // See https://github.com/vanilla/vanilla/issues/7241
        $where = $announcementsWhere = [];

        if ($this->data('Followed')) {
            $followedCategories = array_keys($categoryModel->getFollowed(Gdn::session()->UserID));
            $visibleCategoriesResult = CategoryModel::instance()->getVisibleCategoryIDs(['filterHideDiscussions' => true]);
            if ($visibleCategoriesResult === true) {
                $visibleFollowedCategories = $followedCategories;
            } else {
                $visibleFollowedCategories = array_intersect($followedCategories, $visibleCategoriesResult);
            }
            $where['d.CategoryID'] = $visibleFollowedCategories;
            $announcementsWhere['d.CategoryID'] = $visibleFollowedCategories;
        } elseif ($categoryIDs) {
            $where['d.CategoryID'] = $announcementsWhere['d.CategoryID'] = CategoryModel::filterCategoryPermissions($categoryIDs);
        } else {
            $visibleCategoriesResult = CategoryModel::instance()->getVisibleCategoryIDs(['filterHideDiscussions' => true]);
            if ($visibleCategoriesResult !== true) {
                $where['d.CategoryID'] = $visibleCategoriesResult;
            }
        }

        $where = array_merge($where, $this->WhereClause);

        // Get Discussion Count
        $CountDiscussions = $DiscussionModel->getCount($where);

        $this->checkPageRange($Offset, $CountDiscussions);

        if ($MaxPages) {
            $CountDiscussions = min($MaxPages * $Limit, $CountDiscussions);
        }

        $this->setData('CountDiscussions', $CountDiscussions);

        // Get Announcements
        $this->AnnounceData = $Offset == 0 ? $DiscussionModel->getAnnouncements($announcementsWhere) : false;
        $this->setData('Announcements', $this->AnnounceData !== false ? $this->AnnounceData : [], true);

        // Get Discussions
        $this->DiscussionData = $DiscussionModel->getWhereWithOrder($where, 'DateLastComment', $this->SortDirection, $Limit, $Offset);

        $this->setData('Discussions', $this->DiscussionData, true);
        $this->setJson('Loading', $Offset.' to '.$Limit);

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

    /**
     * @deprecated since 2.3
     */
    public function unread($page = '0') {
        deprecated(__METHOD__);

        if (!Gdn::session()->isValid()) {
            redirectTo('/discussions/index');
        }

        // Figure out which discussions layout to choose (Defined on "Homepage" settings page).
        $layout = c('Vanilla.Discussions.Layout');
        switch ($layout) {
            case 'table':
                if ($this->SyndicationMethod == SYNDICATION_NONE) {
                    $this->View = 'table';
                }
                break;
            default:
                // $this->View = 'index';
                break;
        }
        Gdn_Theme::section('DiscussionList');

        // Determine offset from $Page
        list($page, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));
        $this->canonicalUrl(url(concatSep('/', 'discussions', 'unread', pageNumber($page, $limit, true, false)), true));

        // Validate $Page
        if (!is_numeric($page) || $page < 0) {
            $page = 0;
        }

        // Setup head.
        if (!$this->data('Title')) {
            $title = Gdn::formatService()->renderPlainText(c('Garden.HomepageTitle'), HtmlFormat::FORMAT_KEY);
            if ($title) {
                $this->title($title, '');
            } else {
                $this->title(t('Unread Discussions'));
            }
        }
        if (!$this->description()) {
            $this->description(Gdn::formatService()->renderPlainText(c('Garden.Description', ''), HtmlFormat::FORMAT_KEY));
        }
        if ($this->Head) {
            $this->Head->addRss(url('/discussions/unread/feed.rss', true), $this->Head->title());
        }

        // Add modules
        $this->addModule('DiscussionFilterModule');
        // $this->addModule('NewDiscussionModule');
        $this->addModule('CategoriesModule');
        $this->addModule('BookmarkedModule');
        $this->addModule('TagModule');

        $this->setData('Breadcrumbs', [
            ['Name' => t('Discussions'), 'Url' => '/discussions'],
            ['Name' => t('Unread'), 'Url' => '/discussions/unread']
        ]);


        // Set criteria & get discussions data
        $this->setData('Category', false, true);
        $discussionModel = new DiscussionModel();
        $discussionModel->setSort(Gdn::request()->get());
        $discussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $discussionModel->getSort());
        $this->setData('Filters', $discussionModel->getFilters());

        // Get Discussion Count
        $countDiscussions = $discussionModel->getUnreadCount();
        $this->setData('CountDiscussions', $countDiscussions);

        // Get Discussions
        $this->DiscussionData = $discussionModel->getUnread($page, $limit, [
            'd.CategoryID' => CategoryModel::instance()->getVisibleCategoryIDs(['filterHideDiscussions' => true])
        ]);

        $this->setData('Discussions', $this->DiscussionData, true);
        $this->setJson('Loading', $page.' to '.$limit);

        // Build a pager
        $pagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'Pager';
        $this->fireEvent('BeforeBuildPager');
        $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $page,
            $limit,
            $countDiscussions,
            'discussions/unread/%1$s'
        );
        if (!$this->data('_PagerUrl')) {
            $this->setData('_PagerUrl', 'discussions/unread/{Page}');
        }
        $this->setData('_Page', $page);
        $this->setData('_Limit', $limit);
        $this->fireEvent('AfterBuildPager');

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'discussions';
        }

        $this->render();
    }

    /**
     * Highlight route and include JS, CSS, and modules used by all methods.
     *
     * Always called by dispatcher before controller's requested method.
     *
     * @since 2.0.0
     * @access public
     */
    public function initialize() {
        parent::initialize();
        $this->ShowOptions = true;
        // $this->Menu->highlightRoute('/discussions');
        $this->addJsFile('discussions.js');

        // Inform moderator of checked comments in this discussion
        $checkedDiscussions = Gdn::session()->getAttribute('CheckedDiscussions', []);
        if (count($checkedDiscussions) > 0) {
            ModerationController::informCheckedDiscussions($this);
        }

        $this->CountCommentsPerPage = c('Vanilla.Comments.PerPage', 30);

        /**
         * The default Cache-Control header does not include no-store, which can cause issues (e.g. inaccurate unread
         * status or new comment counts) when users visit the discussion list via the browser's back button.  The same
         * check is performed here as in Gdn_Controller before the Cache-Control header is added, but this value
         * includes the no-store specifier.
         */
        if (Gdn::session()->isValid()) {
            $this->setHeader('Cache-Control', 'private, no-cache, no-store, max-age=0, must-revalidate');
        }

        $this->fireEvent('AfterInitialize');
    }

    /**
     * Display discussions the user has bookmarked.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $Offset Number of discussions to skip.
     */
    public function bookmarked($page = '0') {
        $this->getUserInfo();
        $this->permission('Garden.SignIn.Allow');
        Gdn_Theme::section('DiscussionList');

        $this->checkNewPopup();

        // Add js
        $this->addJsFile('jquery.autosize.min.js');
        $this->addJsFile('autosave.js');
        $this->addJsFile('post.js');
        $this->addJsFile('askquestion.js');

        // Figure out which discussions layout to choose (Defined on "Homepage" settings page).
        $layout = c('Vanilla.Discussions.Layout');
        switch ($layout) {
            case 'table':
                if ($this->SyndicationMethod == SYNDICATION_NONE) {
                    $this->View = 'table';
                }
                break;
            default:
                $this->View = 'index';
                break;
        }

        // Determine offset from $Page
        list($page, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));
        $this->canonicalUrl(url(concatSep('/', 'discussions', 'bookmarked', pageNumber($page, $limit, true, false)), true));

        // Validate $Page
        if (!is_numeric($page) || $page < 0) {
            $page = 0;
        }

        // Filter Discussion Module
        $this->writeFilter();

        $discussionModel = new DiscussionModel();
        $discussionModel->setSort($this->SortDirection);
        $discussionModel->setFilters(Gdn::request()->get());
        $this->setData('Filters', $discussionModel->getFilters());

        $wheres = [
            'w.Bookmarked' => '1',
            'w.UserID' => Gdn::session()->UserID
        ];

        $wheres = array_merge($wheres, $this->WhereClause);

        $this->DiscussionData = $discussionModel->get($offset, $limit, $wheres, [$this->SortDirection => 'DateLastComment']);
        $this->setData('Discussions', $this->DiscussionData);
        $countDiscussions = $discussionModel->getCount($wheres);
        $this->setData('CountDiscussions', $countDiscussions);
        $this->Category = false;

        $this->setJson('Loading', $page.' to '.$limit);

        // Build a pager
        $pagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'Pager';
        $this->fireEvent('BeforeBuildBookmarkedPager');
        $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $page,
            $limit,
            $countDiscussions,
            'discussions/bookmarked/%1$s'
        );

        if (!$this->data('_PagerUrl')) {
            $this->setData('_PagerUrl', 'discussions/bookmarked/{Page}');
        }
        $this->setData('_Page', $page);
        $this->setData('_Limit', $limit);
        $this->fireEvent('AfterBuildBookmarkedPager');

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'discussions';
        }

        // Add modules
        // $this->addModule('NewDiscussionModule');
        $this->addModule('AskQuestionModule');
        $this->addModule('CategoriesModule');
        $this->addModule('UserPhotoModule');
        $this->fireEvent('AfterAddSideMenu');
        $this->fireEvent('AddProfileTabsInfo');

        if ($this->UserRole == "Teacher") {
            $bannerModule = new BannerModule('Question followed', 'Home / Question followed', 'They are waiting', 'for you!', 'Here you\'ll find questions from your subjects awaiting explanation.', "", "/themes/alloprof/design/images/teacher-banner.svg", "#0C6B52");
        } else {
            $bannerModule = new BannerModule('Question followed', 'Home / Question followed', 'You ask yourself the same', 'questions,', 'Find here all the questions you\'re following!');
            $this->addModule('ProfileFilterModule');
        }

        $this->addModule($bannerModule);

        $DiscussionEmpty = true;
        if ($this->DiscussionData->numRows() > 0 || (isset($this->AnnounceData) && is_object($this->AnnounceData) && $this->AnnounceData->numRows() > 0)) {
            $DiscussionEmpty = false;
            if ($this->UserRole == "Teacher") {
                $discussionsFooterModule = new DiscussionsFooterModule($DiscussionEmpty, "That's it for now!", "Follow new questions and you'll find them here!");
            } else {
                $discussionsFooterModule = new DiscussionsFooterModule($DiscussionEmpty, "That's all for now!", "If you have other questions, don't hesitate to ask😉");
            }
        } else {
            $discussionsFooterModule = new DiscussionsFooterModule($DiscussionEmpty, "It seems there's nothing here at the moment!", "Don't hesitate ask if you have a question.");
        }

        $this->addModule($discussionsFooterModule);

        // Render default view (discussions/bookmarked.php)
        // $this->setData('Title', t('My Bookmarks'));
        $this->setData('Breadcrumbs', [['Name' => t('My Bookmarks'), 'Url' => '/discussions/bookmarked']]);
        $this->render();
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

    public function bookmarkedPopin() {
        $this->permission('Garden.SignIn.Allow');

        $discussionModel = new DiscussionModel();
        $wheres = [
            'w.Bookmarked' => '1',
            'w.UserID' => Gdn::session()->UserID
        ];

        $discussions = $discussionModel->get(0, 5, $wheres)->result();
        $this->setData('Title', t('Bookmarks'));
        $this->setData('Discussions', $discussions);
        $this->render('Popin');
    }

    /**
     * @return array
     */
    public function getCategoryIDs() {
        return $this->categoryIDs;
    }

    /**
     * @param array $categoryIDs
     */
    public function setCategoryIDs($categoryIDs) {
        $this->categoryIDs = $categoryIDs;
    }

    /**
     * Display discussions started by the user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $offset Number of discussions to skip.
     */
    public function mine($page = 'p1') {
        $this->getUserInfo();
        $this->permission('Garden.SignIn.Allow');
        Gdn_Theme::section('DiscussionList');

        $this->checkNewPopup();

        // Add js
        $this->addJsFile('jquery.autosize.min.js');
        $this->addJsFile('autosave.js');
        $this->addJsFile('post.js');
        $this->addJsFile('askquestion.js');

        // Set criteria & get discussions data
        list($offset, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));
        $session = Gdn::session();
        $wheres = ['d.InsertUserID' => $session->UserID];

        $this->View = 'index';
        if (c('Vanilla.Discussions.Layout') === 'table') {
            $this->View = 'table';
        }

        // Filter Discussion Module
        $this->writeFilter();
        $wheres = array_merge($wheres, $this->WhereClause);

        $discussionModel = new DiscussionModel();
        $discussionModel->setSort($this->SortDirection);
        $discussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $discussionModel->getSort());
        $this->setData('Filters', $discussionModel->getFilters());

        $this->DiscussionData = $discussionModel->get($offset, $limit, $wheres, [$this->SortDirection => 'DateLastComment']);
        $this->setData('Discussions', $this->DiscussionData);
        $countDiscussions = $this->setData('CountDiscussions', $discussionModel->getCount($wheres));
        // Build a pager
        $pagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'MorePager';
        $this->fireEvent('BeforeBuildMinePager');
        $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->MoreCode = 'More Discussions';
        $this->Pager->LessCode = 'Newer Discussions';
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $offset,
            $limit,
            $countDiscussions,
            'discussions/mine/%1$s'
        );

        $this->setData('_PagerUrl', 'discussions/mine/{Page}');
        $this->setData('_Page', $page);
        $this->setData('_Limit', $limit);

        $this->fireEvent('AfterBuildMinePager');

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'discussions';
        }

        // Add modules
        $this->addModule('NewDiscussionModule');
        $this->addModule('AskQuestionModule');
        $this->addModule('CategoriesModule');
        $this->addModule('UserPhotoModule');
        $this->addModule('ProfileFilterModule');
        $this->fireEvent('AfterAddSideMenu');
        $this->fireEvent('AddProfileTabsInfo');

        $DiscussionEmpty = true;
        if ($this->DiscussionData->numRows() > 0 || (isset($this->AnnounceData) && is_object($this->AnnounceData) && $this->AnnounceData->numRows() > 0)) {
            $DiscussionEmpty = false;
            $discussionsFooterModule = new DiscussionsFooterModule($DiscussionEmpty, "That's all for now!", "If you have other questions, don't hesitate to ask😉");
        } else {
            $discussionsFooterModule = new DiscussionsFooterModule($DiscussionEmpty, "It seems there's nothing here at the moment!", "Don't hesitate ask if you have a question.");
        }

        $this->addModule($discussionsFooterModule);

        $bannerModule = new BannerModule('My Questions', 'Home / My Questions', 'All my <b>questions,</b>', '', 'Find here all the questions you have asked to the community!');
        $this->addModule($bannerModule);

        // Render view
        $this->setData('Title', t('My Questions'));
        $this->setData('Breadcrumbs', [['Name' => t('My Questions'), 'Url' => '/discussions/mine']]);
        $this->render();
    }


    /**
     * Display discussions started by the user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $offset Number of discussions to skip.
     */
    public function waiting($page = 'p1') {
        $this->getUserInfo();
        $this->permission('Garden.SignIn.Allow');
        Gdn_Theme::section('DiscussionList');

        // add profile filter and photo
        $this->addModule('UserPhotoModule');
        $this->fireEvent('AddProfileTabsInfo');
        $this->addModule('ProfileFilterModule');

        // Set criteria & get discussions data
        list($offset, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));
        $session = Gdn::session();
        $wheres = ['d.CountComments' => 0];

        $discussionModel = new DiscussionModel();
        $discussionModel->setSort(Gdn::request()->get());
        $discussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $discussionModel->getSort());
        $this->setData('Filters', $discussionModel->getFilters());

        $this->DiscussionData = $discussionModel->get($offset, $limit, $wheres);
        $this->setData('Discussions', $this->DiscussionData);
        $countDiscussions = $this->setData('CountDiscussions', $discussionModel->getCount($wheres));

        $this->View = 'index';
        if (c('Vanilla.Discussions.Layout') === 'table') {
            $this->View = 'table';
        }

        // Build a pager
        $pagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'MorePager';
        $this->fireEvent('BeforeBuildMinePager');
        $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->MoreCode = 'More Discussions';
        $this->Pager->LessCode = 'Newer Discussions';
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $offset,
            $limit,
            $countDiscussions,
            'discussions/waiting/%1$s'
        );

        $this->setData('_PagerUrl', 'discussions/waiting/{Page}');
        $this->setData('_Page', $page);
        $this->setData('_Limit', $limit);

        $this->fireEvent('AfterBuildMinePager');

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'discussions';
        }

        // Add modules
        $this->addModule('CategoriesModule');
        $this->addModule('BookmarkedModule');
        $this->addModule('TagModule');

        // Render view
        $this->setData('Breadcrumbs', [['Name' => t('My Discussions'), 'Url' => '/discussions/waiting']]);
        $this->render();
    }

    public function userBookmarkCount($userID = false) {
        if ($userID === false) {
            $userID = Gdn::session()->UserID;
        }

        if ($userID !== Gdn::session()->UserID) {
            $this->permission('Garden.Settings.Manage');
        }

        if (!$userID) {
            $countBookmarks = null;
        } else {
            if ($userID == Gdn::session() && isset(Gdn::session()->User->CountBookmarks)) {
                $countBookmarks = Gdn::session()->User->CountBookmarks;
            } else {
                $userModel = new UserModel();
                $user = $userModel->getID($userID, DATASET_TYPE_ARRAY);
                $countBookmarks = $user['CountBookmarks'];
            }

            if ($countBookmarks === null) {
                $countBookmarks = Gdn::sql()
                    ->select('DiscussionID', 'count', 'CountBookmarks')
                    ->from('UserDiscussion')
                    ->where('Bookmarked', '1')
                    ->where('UserID', $userID)
                    ->get()->value('CountBookmarks', 0);

                Gdn::userModel()->setField($userID, 'CountBookmarks', $countBookmarks);
            }
        }
        $this->setData('CountBookmarks', $countBookmarks);
        $this->setData('_Value', $countBookmarks);
        $this->xRender('Value', 'utility', 'dashboard');
    }

    /**
     * Takes a set of discussion identifiers and returns their comment counts in the same order.
     */
    public function getCommentCounts() {
        $this->allowJSONP(true);

        $vanilla_identifier = val('vanilla_identifier', $_GET);
        if (!is_array($vanilla_identifier)) {
            $vanilla_identifier = [$vanilla_identifier];
        }

        $vanilla_identifier = array_unique($vanilla_identifier);

        $finalData = array_fill_keys($vanilla_identifier, 0);
        $misses = [];
        $cacheKey = 'embed.comments.count.%s';
        $originalIDs = [];
        foreach ($vanilla_identifier as $foreignID) {
            $hashedForeignID = foreignIDHash($foreignID);

            // Keep record of non-hashed identifiers for the reply
            $originalIDs[$hashedForeignID] = $foreignID;

            $realCacheKey = sprintf($cacheKey, $hashedForeignID);
            $comments = Gdn::cache()->get($realCacheKey);
            if ($comments !== Gdn_Cache::CACHEOP_FAILURE) {
                $finalData[$foreignID] = $comments;
            } else {
                $misses[] = $hashedForeignID;
            }
        }

        if (sizeof($misses)) {
            $countData = Gdn::sql()
                ->select('ForeignID, CountComments')
                ->from('Discussion')
                ->where('Type', 'page')
                ->whereIn('ForeignID', $misses)
                ->get()->resultArray();

            foreach ($countData as $row) {
                // Get original identifier to send back
                $foreignID = $originalIDs[$row['ForeignID']];
                $finalData[$foreignID] = $row['CountComments'];

                // Cache using the hashed identifier
                $realCacheKey = sprintf($cacheKey, $row['ForeignID']);
                Gdn::cache()->store($realCacheKey, $row['CountComments'], [
                    Gdn_Cache::FEATURE_EXPIRY => 60
                ]);
            }
        }

        $this->setData('CountData', $finalData);
        $this->DeliveryMethod = DELIVERY_METHOD_JSON;
        $this->DeliveryType = DELIVERY_TYPE_DATA;
        $this->render();
    }

    /**
     * Set user preference for sorting discussions.
     *
     * @param string $target The target to redirect to.
     */
    public function sort($target = '') {
        deprecated("sort");

        if (!Gdn::session()->isValid()) {
            throw permissionException();
        }

        if (!$this->Request->isAuthenticatedPostBack()) {
            throw forbiddenException('GET');
        }

        if ($target) {
            redirectTo($target);
        }

        // Send sorted discussions.
        $this->setData('Deprecated', true);
        $this->deliveryMethod(DELIVERY_METHOD_JSON);
        $this->render();
    }

    /**
     * Endpoint for the PromotedContentModule's data.
     *
     * Parameters & values must be lowercase and via GET.
     *
     * @see PromotedContentModule
     */
    public function promoted() {
        // Create module & set data.
        $promotedModule = new PromotedContentModule();
        $status = $promotedModule->load(Gdn::request()->get());
        if ($status === true) {
            // Good parameters.
            $promotedModule->getData();
            $this->setData('Content', $promotedModule->data('Content'));
            $this->setData('Title', t('Promoted Content'));
            $this->setData('View', c('Vanilla.Discussions.Layout'));
            $this->setData('EmptyMessage', t('No discussions were found.'));

            // Pass display properties to the view.
            $this->Group = $promotedModule->Group;
            $this->TitleLimit = $promotedModule->TitleLimit;
            $this->BodyLimit = $promotedModule->BodyLimit;
        } else {
            $this->setData('Errors', $status);
        }

        $this->deliveryMethod();
        Gdn_Theme::section('PromotedContent');
        $this->render('promoted', 'modules', 'vanilla');
    }

    /**
     * Add the discussions/tagged/{TAG} endpoint.
     */
    public function tagged() {
        if (!c('Tagging.Discussions.Enabled')) {
            throw new Exception('Not found', 404);
        }

        Gdn_Theme::section('DiscussionList');

        $args = $this->RequestArgs;
        $get = array_change_key_case($this->Request->get());

        if ($useCategories = c('Vanilla.Tagging.UseCategories')) {
            // The url is in the form /category/tag/p1
            $categoryCode = val(0, $args);
            $tag = val(1, $args);
            $page = val(2, $args);
        } else {
            // The url is in the form /tag/p1
            $categoryCode = '';
            $tag = val(0, $args);
            $page = val(1, $args);
        }

        // Look for explcit values.
        $categoryCode = val('category', $get, $categoryCode);
        $tag = val('tag', $get, $tag);
        $page = val('page', $get, $page);
        $category = CategoryModel::categories($categoryCode);

        $tag = stringEndsWith($tag, '.rss', true, true);
        list($offset, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));

        $multipleTags = strpos($tag, ',') !== false;

        $this->setData('Tag', $tag, true);

        $tagModel = TagModel::instance();
        $recordCount = false;
        if (!$multipleTags) {
            $tags = $tagModel->getWhere(['Name' => $tag])->resultArray();

            if (count($tags) == 0) {
                throw notFoundException('Page');
            }

            if (count($tags) > 1) {
                foreach ($tags as $tagRow) {
                    if ($tagRow['CategoryID'] == val('CategoryID', $category)) {
                        break;
                    }
                }
            } else {
                $tagRow = array_pop($tags);
            }
            $tags = $tagModel->getRelatedTags($tagRow);

            $recordCount = $tagRow['CountDiscussions'];
            $this->setData('CountDiscussions', $recordCount);
            $this->setData('Tags', $tags);
            $this->setData('Tag', $tagRow);

            $childTags = $tagModel->getChildTags($tagRow['TagID']);
            $this->setData('ChildTags', $childTags);
        }

        $this->title(htmlspecialchars($tagRow['FullName']));
        $urlTag = empty($categoryCode) ? rawurlencode($tag) : rawurlencode($categoryCode).'/'.rawurlencode($tag);
        if (urlencode($tag) == $tag) {
            $this->canonicalUrl(url(concatSep('/', "/discussions/tagged/$urlTag", pageNumber($offset, $limit, true)), true));
            $feedUrl = url(concatSep('/', "/discussions/tagged/$urlTag/feed.rss", pageNumber($offset, $limit, true, false)), '//');
        } else {
            $this->canonicalUrl(url(concatSep('/', 'discussions/tagged', pageNumber($offset, $limit, true)).'?Tag='.$urlTag, true));
            $feedUrl = url(concatSep('/', 'discussions/tagged', pageNumber($offset, $limit, true, false), 'feed.rss').'?Tag='.$urlTag, '//');
        }

        if ($this->Head) {
            $this->addJsFile('discussions.js');
            $this->Head->addRss($feedUrl, $this->Head->title());
        }

        if (!is_numeric($offset) || $offset < 0) {
            $offset = 0;
        }

        // Add Modules
        // $this->addModule('NewDiscussionModule');
        $this->addModule('DiscussionFilterModule');
        $this->addModule('BookmarkedModule');

        $this->setData('Category', false, true);

        $this->AnnounceData = false;
        $this->setData('Announcements', [], true);

        $this->DiscussionData = $tagModel->getDiscussions($tag, $limit, $offset);

        $this->setData('Discussions', $this->DiscussionData, true);
        $this->setJson('Loading', $offset.' to '.$limit);

        // Build a pager.
        $pagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'Pager';
        $this->fireEvent('BeforeBuildPager');
        if (!$this->data('_PagerUrl')) {
            $this->setData('_PagerUrl', "/discussions/tagged/$urlTag/{Page}");
        }
        $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $offset,
            $limit,
            $recordCount,
            $this->data('_PagerUrl')
        );
        $this->setData('_Page', $page);
        $this->setData('_Limit', $limit);
        $this->fireEvent('AfterBuildPager');

        $this->View = c('Vanilla.Discussions.Layout') == 'table' && $this->SyndicationMethod == SYNDICATION_NONE ? 'table' : 'index';
        $this->render($this->View, 'discussions', 'vanilla');
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

    /**
     * Display discussions followed by the user.
     *
     * @since 2.0.0
     * @access public
     *
     * @param int $offset Number of discussions to skip.
     */
    public function followed($page = 'p1') {
        $this->getUserInfo();
        $this->permission('Garden.SignIn.Allow');
        Gdn_Theme::section('DiscussionList');

        // Add js
        $this->addJsFile('jquery.autosize.min.js');
        $this->addJsFile('autosave.js');
        $this->addJsFile('post.js');
        $this->addJsFile('askquestion.js');

        // add profile filter and photo
        $this->addModule('UserPhotoModule');
        $this->fireEvent('AddProfileTabsInfo');
        $this->addModule('ProfileFilterModule');

        // Set criteria & get discussions data
        list($offset, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));
        $session = Gdn::session();
        $wheres = ['d.InsertUserID' => $session->UserID];

        $discussionModel = new DiscussionModel();
        $discussionModel->setSort(Gdn::request()->get());
        $discussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $discussionModel->getSort());
        $this->setData('Filters', $discussionModel->getFilters());

        $this->DiscussionData = $discussionModel->get($offset, $limit, $wheres);
        $this->setData('Discussions', $this->DiscussionData);
        $countDiscussions = $this->setData('CountDiscussions', $discussionModel->getCount($wheres));

        $this->View = 'index';
        if (c('Vanilla.Discussions.Layout') === 'table') {
            $this->View = 'table';
        }

        // Build a pager
        $pagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'MorePager';
        $this->fireEvent('BeforeBuildMinePager');
        $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->MoreCode = 'More Discussions';
        $this->Pager->LessCode = 'Newer Discussions';
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $offset,
            $limit,
            $countDiscussions,
            'discussions/mine/%1$s'
        );

        $this->setData('_PagerUrl', 'discussions/followed/{Page}');
        $this->setData('_Page', $page);
        $this->setData('_Limit', $limit);

        $this->fireEvent('AfterBuildMinePager');

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'discussions';
        }

        // Add modules
        $this->addModule('DiscussionFilterModule');
        $this->addModule('NewDiscussionModule');
        $this->addModule('CategoriesModule');
        $this->addModule('BookmarkedModule');
        $this->addModule('TagModule');

        // Render view
        // $this->setData('Title', t('My Discussions'));
        $this->setData('Breadcrumbs', [['Name' => t('Questions followed'), 'Url' => '/discussions/followed']]);
        $this->render();
    }

    // Filter Discussion Function
    public function filterDiscussion() {
        $parameter = $_POST['parameter'];

        echo $this->_PagerUrl.'?'.$parameter;
    }
}
