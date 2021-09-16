<?php
/**
 * Categories controller
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

use Vanilla\Contracts\Site\SiteSectionInterface;
use Vanilla\Formatting\Formats\HtmlFormat;
use Vanilla\Formatting\Html\HtmlSanitizer;
use Vanilla\Site\DefaultSiteSection;
use Vanilla\Site\SiteSectionModel;

/**
 * Handles displaying categories via /categoris endpoint.
 */
class CategoriesController extends VanillaController {

    /** @var array Models to include.*/
    public $Uses = ['Database', 'Form', 'CategoryModel', 'UserModel', 'CommentModel', 'DiscussionModel'];

    /** @var CategoryModel */
    public $CategoryModel;

    /**  @var bool Should the discussions have their options available. */
    public $ShowOptions = true;

    /** @var int Unique identifier. */
    public $CategoryID;

    /** @var object Category object. */
    public $Category;

    /** @var bool Value indicating if the category-following filter should be displayed when rendering a view */
    public $enableFollowingFilter = false;

    /** @var array List of available tabs. */
    public $ProfileTabs;

    /**
     * @var \Closure $categoriesCompatibilityCallback A backwards-compatible callback to get `$this->data('Categories')`.
     */
    private $categoriesCompatibilityCallback;

    /**
     *
     *
     * @param $category
     * @param $month
     * @param bool $page
     * @throws Exception
     * @throws Gdn_UserException
     */
    public function archives($category, $month, $page = false) {
        $category = CategoryModel::categories($category);
        if (!$category) {
            throw notFoundException('Category');
        }

        if (!$category['PermsDiscussionsView']) {
            throw permissionException();
        }

        $timestamp = strtotime($month);
        if (!$timestamp) {
            throw new Gdn_UserException("The archive month is not a valid date.");
        }

        $this->setData('Category', $category);

        // Round the month to the first day.
        $from = gmdate('Y-m-01', $timestamp);
        $to = gmdate('Y-m-01', strtotime('+1 month', strtotime($from)));

        // Grab the discussions.
        list($offset, $limit) = offsetLimit($page, c('Vanilla.Discussions.PerPage', 30));
        $where = [
            'CategoryID' => $category['CategoryID'],
            'Announce' => 'all',
            'DateInserted >=' => $from,
            'DateInserted <' => $to];

        saveToConfig('Vanilla.Discussions.SortField', 'd.DateInserted', false);
        $discussionModel = new DiscussionModel();
        $discussionModel->setSort(Gdn::request()->get());
        $discussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $discussionModel->getSort());
        $this->setData('Filters', $discussionModel->getFilters());
        $discussions = $discussionModel->getWhereRecent($where, $limit, $offset);
        $this->DiscussionData = $this->setData('Discussions', $discussions);
        $this->setData('_CurrentRecords', count($discussions));
        $this->setData('_Limit', $limit);

        $canonical = '/categories/archives/'.rawurlencode($category['UrlCode']).'/'.gmdate('Y-m', $timestamp);
        $page = pageNumber($offset, $limit, true, false);
        $this->canonicalUrl(url($canonical.($page ? '?page='.$page : ''), true));

        PagerModule::current()->configure($offset, $limit, false, $canonical.'?page={Page}');

//      PagerModule::current()->Offset = $Offset;
//      PagerModule::current()->Url = '/categories/archives'.rawurlencode($Category['UrlCode']).'?page={Page}';

        Gdn_Theme::section(val('CssClass', $category));
        Gdn_Theme::section('DiscussionList');

        $this->title(Gdn::formatService()->renderPlainText(val('Name', $category, ''), HtmlFormat::FORMAT_KEY));
        $this->description(sprintf(t("Archives for %s"), gmdate('F Y', strtotime($from))), true);
        $this->addJsFile('discussions.js');
        $this->Head->addTag('meta', ['name' => 'robots', 'content' => 'noindex']);

        $this->ControllerName = 'DiscussionsController';
        $this->CssClass = 'Discussions';

        $this->render();
    }

    /**
     * Build a structured tree of children for the specified category.
     *
     * @param int|string|object|array|null $category Category or code/ID to build the tree for. Null for all.
     * @param string|null $displayAs What display should the tree be configured for?
     * @param bool $recent Join in recent record info?
     * @param bool $watching Filter categories by "watching" status?
     * @return array
     */
    private function getCategoryTree($category = null, $displayAs = null, $recent = false, $watching = false) {
        $categoryIdentifier = null;

        if (is_string($category) || is_numeric($category)) {
            $category = CategoryModel::categories($category);
        }

        if ($category) {
            if ($displayAs === null) {
                $displayAs = val('DisplayAs', $category, 'Discussions');
            }
            $categoryIdentifier = val('CategoryID', $category, null);
        }

        switch ($displayAs) {
            case 'Flat':
                $perPage = c('Vanilla.Categories.PerPage', 30);
                $page = Gdn::request()->get('Page', Gdn::request()->get('page', null));
                list($offset, $limit) = offsetLimit($page, $perPage);
                $categoryTree = $this->CategoryModel->getTreeAsFlat($categoryIdentifier, $offset, $limit);
                $this->setData('_Limit', $perPage);
                $this->setData('_CurrentRecords', count($categoryTree));
                break;
            case 'Categories':
            case 'Discussions':
            case 'Default':
            case 'Nested':
            default:
            $categoryTree = $this->CategoryModel
                    ->setJoinUserCategory(true)
                    ->getChildTree(
                        $categoryIdentifier ?: null,
                        ['depth' => CategoryModel::instance()->getMaxDisplayDepth() ?: 10]
                    );
        }

        if ($recent) {
            $this->CategoryModel->joinRecent($categoryTree);
        }

        return $categoryTree;
    }

    /**
     * Get a flattened tree representing the current user's followed categories.
     *
     * @param bool $recent Include recent post information?
     * @param array|null $filterIDs An array of category IDs. Filter result to a subset of these categories.
     * @return array
     */
    private function getFollowed($recent = false, $filterIDs = null) {
        if ($filterIDs !== null && !is_array($filterIDs)) {
            throw new InvalidArgumentException('Filter IDs must be in an array.');
        }

        $perPage = c('Vanilla.Categories.PerPage', 30);
        $page = Gdn::request()->get(
            'Page',
            Gdn::request()->get('page', null)
        );
        list($offset, $limit) = offsetLimit($page, $perPage);

        $where = ['Followed' => true, 'Language' => Gdn::config('Garden.Locale')];

        if (!empty($filterIDs)) {
            $where['CategoryID'] = $filterIDs;
        }

        $result = $this->CategoryModel
            ->getWhere($where, '', 'asc', $limit, $offset)
            ->resultArray();
        $result = $this->CategoryModel->flattenCategories($result);

        if ($recent) {
            $this->CategoryModel->joinRecent($result);
        }

        $this->setData('_Limit', $perPage);
        $this->setData('_CurrentRecords', count($result));

        return $result;
    }

    /**
     * "Table" layout for categories. Mimics more traditional forum category layout.
     *
     * @param string $category
     * @param string $displayAs
     */
    public function table($category = '', $displayAs = '') {
        if ($this->SyndicationMethod == SYNDICATION_NONE) {
            $this->View = $displayAs === 'Flat' ? 'flat_table' : 'table';
        } else {
            $this->View = $displayAs === 'Flat' ? 'flat_all' : 'all';
        }
        $this->all($category, $displayAs);
    }

    /**
     * Endpoint that returns a flattened list of children categories in JSON format. Collapses the categories,
     * so we only retrieve the child categories that are not nested under a nested or flat category.
     * Includes the category options that appear in the category settings dropdown in the response.
     *
     * @param int $parentID The ID of the parent to retrieve categories under.
     */
    public function getFlattenedChildren($parentID = -1) {
        $options = ['maxdepth' => 10, 'collapsecategories' => true];
        $categories = $this->CategoryModel->getChildTree($parentID, $options);
        $categories = $this->CategoryModel->flattenTree($categories);

        foreach ($categories as &$category) {
            $category['Options'] = $this->getOptions($category);
        }

        $this->setData('Categories', $categories);
        $this->deliveryType(DELIVERY_TYPE_DATA);
        $this->deliveryMethod(DELIVERY_METHOD_JSON);
        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * Returns an array representation of the dropdown object, ready to add to a data array.
     *
     * @param array|object $category The category to retrieve the dropdown options for.
     * @return array
     */
    private function getOptions($category) {
        $cdd = CategoryModel::getCategoryDropdown($category);
        return $cdd->toArray();
    }

    /**
     * Switch params if page is provided as category slug
     *
     * @param string $categoryIdentifier
     * @param string $page
     * @return array
     */
    private function validatePagination(string $categoryIdentifier, string $page) {
        if ($page === '0' && preg_match('/^p\d+$/', $categoryIdentifier)) {
            // Just double check that it is not a category slug
            $category = CategoryModel::categories($categoryIdentifier);
            if (empty($category)) {
                $page = $categoryIdentifier;
                $categoryIdentifier = '';
            }
        }
        return [$categoryIdentifier, $page];
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

        if (!c('Garden.Profile.Public') && !Gdn::session()->isValid()) {
            // throw permissionException();
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

    public function writeFilter($isLinkedCategory) {
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

        $language = Gdn::request()->get('language') ?? false;
        $this->IsLanguage = $language;

        $sort = Gdn::request()->get('sort') ?? 'desc';
        $this->SortDirection = $sort;

        $discussionFilterModule = new DiscussionFilterModule($gradeFilterOption, $sort, $explanation, $verified, $subject, $outexplanation, $language, $isLinkedCategory, true);
        $this->addModule($discussionFilterModule);
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

        if ($this->IsLanguage == 'true') {
            unset($wheres['d.Language']);
        } else {
            $wheres['d.Language'] = Gdn::config('Garden.Locale') == 'fr_CA' ? 'fr' : 'en';
        }

        $this->WhereClause = $wheres;
    }

    /**
     * Show all discussions in a particular category.
     *
     * @since 2.0.0
     * @access public
     *
     * @param string $categoryIdentifier Unique category slug or ID.
     * @param int $offset Number of discussions to skip.
     */
    public function index($categoryIdentifier = '', $page = '0') {
        $this->getUserInfo();
        [$categoryIdentifier, $page] = $this->validatePagination($categoryIdentifier, $page);
        if (!$categoryIdentifier) {
            /** @var SiteSectionInterface $siteSection */
            $siteSection = Gdn::getContainer()->get(SiteSectionModel::class)->getCurrentSiteSection();
            if (!($siteSection instanceof DefaultSiteSection)) {
                $categoryIdentifier = $siteSection->getAttributes()['categoryID'] ?? '';
            }
        }

        $this->addJsFile('discussionstyle.js');
        $this->addJsFile('askquestion.js');

        // Figure out which category layout to choose (Defined on "Homepage" settings page).
        $layout = c('Vanilla.Categories.Layout');

        if ($this->CategoryModel->followingEnabled()) {
            // Only use the following filter on the root category level.
            $this->enableFollowingFilter = $categoryIdentifier === '';
            $saveFollowing = Gdn::request()->get('save') && Gdn::session()->validateTransientKey(Gdn::request()->get('TransientKey', ''));
            // Only filter categories by "following" on the root category level.
            $this->followed = $categoryIdentifier !== '' ? false : paramPreference(
                'followed',
                'FollowedCategories',
                'Vanilla.SaveFollowingPreference',
                null,
                $saveFollowing
            );
            $this->fireEvent('EnableFollowingFilter', [
                'CategoryIdentifier' => $categoryIdentifier,
                'EnableFollowingFilter' => &$this->enableFollowingFilter,
                'Followed' => &$this->followed,
                'SaveFollowing' => $saveFollowing
            ]);
        } else {
            $this->enableFollowingFilter = $this->followed = false;
        }
        $this->setData('EnableFollowingFilter', $this->enableFollowingFilter);
        $this->setData('Followed', $this->followed);

        if ($categoryIdentifier == '') {
            switch ($layout) {
                case 'mixed':
                    $this->View = 'discussions';
                    $this->discussions();
                    break;
                case 'table':
                    $this->table();
                    break;
                default:
                    $this->View = 'all';
                    $this->all('', CategoryModel::getRootDisplayAs());
                    break;
            }
            return;
        } else {
            CategoryModel::instance()->setJoinUserCategory(true);
            $category = CategoryModel::categories($categoryIdentifier);

            if (empty($category)) {
                throw notFoundException();
            }
            $category = (object)$category;

            $currentLanguage = Gdn::config('Garden.Locale') == 'fr_CA' ? 'fr' : 'en';
            if (!$category->LinkedCategoryID && $currentLanguage != $category->Language) {
                redirectTo("/");
            } elseif ($category->LinkedCategoryID && $currentLanguage != $category->Language) {
                redirectTo(CategoryModel::categories($category->LinkedCategoryID)['Url']);
            }

            // Check permission
            $this->permission('Vanilla.Discussions.View', true, 'Category', val('PermissionCategoryID', $category));

            Gdn_Theme::section($category->CssClass);

            // Load the breadcrumbs.
            $this->setData('Breadcrumbs', CategoryModel::getAncestors(val('CategoryID', $category)));

            $this->setData('Category', $category, true);

            $this->setData('BannerImage', val('BannerImage', $category));
            $this->writeFilter($category->LinkedCategoryID);

            $discussionModel = new DiscussionModel();

            $dWheres = [];
            $cWheres = [];
            $cID = val('CategoryID', $category);

            $cID = $this->IsLanguage == 'true' ? [$cID, val('LinkedCategoryID', $category)] : $cID;

            if ($this->getUserRole() == 'member') {
                $cWheres = ['d.Published' => 1, 'cm.Published' => 1, 'd.CategoryID' => $cID];
                $dWheres = ['d.CategoryID' => $cID, 'd.Published' => 1];
            } else {
                $cWheres = ['d.CategoryID' => $cID];
                $dWheres = ['d.CategoryID' => $cID];
            }

            $cCount = Gdn::sql()
                ->select('cm.CommentID', 'count', 'CountComments')
                ->from('Comment cm')
                ->join('Discussion d', 'd.DiscussionID = cm.DiscussionID')
                ->where($cWheres)
                ->orWhere(['d.CategoryID' => $cID, 'd.InsertUserID' => Gdn::session()->UserID])
                ->get()
                ->firstRow()
                ->CountComments;

            $DCount = Gdn::sql()
                ->select('d.DiscussionID', 'count', 'CountDiscussions')
                ->from('Discussion d')
                ->where($dWheres)
                ->orWhere(['d.CategoryID' => $cID, 'd.InsertUserID' => Gdn::session()->UserID])
                ->get()
                ->firstRow()
                ->CountDiscussions;

            $this->setData('CountAllDiscussions', $DCount);
            $this->setData('CountAllComments', $cCount);

            // $this->title(Gdn::formatService()->renderPlainText(val('Name', $category, ''), HtmlFormat::FORMAT_KEY));
            $this->title('Zone d’entraide '.val('Name', $category, '').' | Alloprof');
            $this->description(val('Description', $category), false);

            switch ($category->DisplayAs) {
                case 'Flat':
                case 'Heading':
                case 'Categories':
                    $stopHeadings = val('Depth', $category) > CategoryModel::instance()->getNavDepth();
                    CategoryModel::instance()->setStopHeadingsCalculation($stopHeadings);
                    if ($this->SyndicationMethod != SYNDICATION_NONE) {
                        // RSS can't show a category list so just tell it to expand all categories.
                        saveToConfig('Vanilla.ExpandCategories', true, false);
                    } else {
                        // This category is an overview style category and displays as a category list.
                        switch ($layout) {
                            case 'mixed':
                                $this->View = 'discussions';
                                $this->discussions($categoryIdentifier);
                                break;
                            case 'table':
                                $this->table($categoryIdentifier, $category->DisplayAs);
                                break;
                            default:
                                $this->View = 'all';
                                $this->all($categoryIdentifier, $category->DisplayAs);
                                break;
                        }
                        return;
                    }
                    break;
            }

            Gdn_Theme::section('DiscussionList');
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

            $this->setData('CategoryTree', $this->getCategoryTree(
                $categoryIdentifier, val('DisplayAs', $category)
            ));

            // Add a backwards-compatibility shim for the old categories.
            $this->categoriesCompatibilityCallback = function () use ($categoryIdentifier) {
                $categories = CategoryModel::getSubtree($categoryIdentifier, false);
                return $categories;
            };

            // Setup head
            $this->Menu->highlightRoute('/discussions');
            if ($this->Head) {
                $this->addJsFile('discussions.js');
                $this->Head->addRss(categoryUrl($category) . '/feed.rss', $this->Head->title());
            }

            // Set CategoryID
            $categoryID = val('CategoryID', $category);
            $LinkedCategoryID = val('LinkedCategoryID', $category);
            $this->setData('CategoryID', $categoryID, true);

            // Add modules
            // $this->addModule('DiscussionFilterModule');
            $this->addModule('AskQuestionModule');

            // $this->addModule('BookmarkedModule');
            // $this->addModule('TagModule');

            // Make sure the userphoto module gets added to the page
            $this->addModule('UserPhotoModule');

            // Add discussion and question count on the profile block
            $this->fireEvent('AddProfileTabsInfo');
            $this->addModule('ProfileFilterModule');

            $bannerModule = new BannerModule('Categories', val('Name', $category, ''), '', val('BannerImage', $category), val('Color', $category));
            $this->addModule($bannerModule);

            $mobileHeader = new MobileHeaderModule(val('Name', $category, ''), true);
            $this->addModule($mobileHeader);


            // Get a DiscussionModel
            $discussionModel = new DiscussionModel();
            $discussionModel->setSort($this->SortDirection);
            $discussionModel->setFilters(Gdn::request()->get());
            $this->setData('Filters', $discussionModel->getFilters());

            $categoryIDs = $this->IsLanguage == 'true' ? [$categoryID, $LinkedCategoryID] : [$categoryID];
            if (c('Vanilla.ExpandCategories')) {
                $categoryIDs = array_merge($categoryIDs, array_column($this->data('Categories'), 'CategoryID'));
            }
            $wheres = ['d.CategoryID' => $categoryIDs];
            $wheres = array_merge($wheres, $this->WhereClause);
            $this->setData('_ShowCategoryLink', count($categoryIDs) > 1);

            // Check permission.
            $this->categoryPermission($category, 'Vanilla.Discussions.View');

            // Set discussion meta data.
            $this->EventArguments['PerPage'] = c('Vanilla.Discussions.PerPage', 30);
            $this->fireEvent('BeforeGetDiscussions');
            list($offset, $limit) = offsetLimit($page, $this->EventArguments['PerPage']);
            if (!is_numeric($offset) || $offset < 0) {
                $offset = 0;
            }

            $page = pageNumber($offset, $limit);

            // Allow page manipulation
            $this->EventArguments['Page'] = &$page;
            $this->EventArguments['Offset'] = &$offset;
            $this->EventArguments['Limit'] = &$limit;
            $this->fireEvent('AfterPageCalculation');

            // We want to limit the number of pages on large databases because requesting a super-high page can kill the db.
            $maxPages = c('Vanilla.Categories.MaxPages');
            if ($maxPages && $page > $maxPages) {
                throw notFoundException();
            }

            $countDiscussions = $discussionModel->getCount($wheres);
            $this->checkPageRange($offset, $countDiscussions);

            if ($maxPages && $maxPages * $limit < $countDiscussions) {
                $countDiscussions = $maxPages * $limit;
            }

            $this->setData('CountDiscussions', $countDiscussions);
            $this->setData('_Limit', $limit);

            // We don't wan't child categories in announcements.
            $wheres['d.CategoryID'] = $categoryID;
            $announceData = $offset == 0 ? $discussionModel->getAnnouncements($wheres) : false;
            $this->AnnounceData = $this->setData('Announcements', $announceData !== false ? $announceData : [], true);
            $wheres['d.CategoryID'] = $categoryIDs;

            // RSS should include announcements.
            if ($this->SyndicationMethod !== SYNDICATION_NONE) {
                $wheres['Announce'] = 'all';
            }

            $this->DiscussionData = $this->setData('Discussions', $discussionModel->getWhereWithOrder($wheres, 'DateLastComment', $this->SortDirection, $limit, $offset));

            // Build a pager
            $pagerFactory = new Gdn_PagerFactory();
            $url = categoryUrl($categoryIdentifier);

            $this->EventArguments['PagerType'] = 'Pager';
            $this->fireEvent('BeforeBuildPager');
            if (!$this->data('_PagerUrl')) {
                $this->setData('_PagerUrl', $url.'/{Page}');
            }
            $queryString = DiscussionModel::getSortFilterQueryString($discussionModel->getSort(), $discussionModel->getFilters());
            $this->setData('_PagerUrl', $this->data('_PagerUrl').$queryString);

            $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
            $this->Pager->ClientID = 'Pager';
            $this->Pager->configure(
                $offset,
                $limit,
                $countDiscussions,
                $this->data('_PagerUrl')
            );

            $this->Pager->Record = $category;
            PagerModule::current($this->Pager);
            $this->setData('_Page', $page);
            $this->setData('_Limit', $limit);
            $this->fireEvent('AfterBuildPager');

            // Set the canonical Url.
            $this->canonicalUrl(categoryUrl($category, pageNumber($offset, $limit)));

            // Change the controller name so that it knows to grab the discussion views
            $this->ControllerName = 'DiscussionsController';
            // Pick up the discussions class
            $this->CssClass = 'Discussions Category-'.val('UrlCode', $category);

            // Deliver JSON data if necessary
            if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
                $this->setJson('LessRow', $this->Pager->toString('less'));
                $this->setJson('MoreRow', $this->Pager->toString('more'));
                $this->View = 'discussions';
            }
            // Render default view.
            $this->fireEvent('BeforeCategoriesRender');
            $this->render();
        }
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

    /**
     * Show all (nested) categories.
     *
     * @param string $Category The url code of the parent category.
     * @param string $displayAs
     * @since 2.0.17
     * @access public
     */
    public function all($Category = '', $displayAs = '') {
        // Setup head.
        $this->Menu->highlightRoute('/discussions');
        if (!$this->title()) {
            $Title = Gdn::formatService()->renderPlainText(c('Garden.HomepageTitle'), HtmlFormat::FORMAT_KEY);
            if ($Title) {
                $this->title($Title, '');
            } else {
                $this->title(t('All Categories'));
            }
        }
        Gdn_Theme::section('CategoryList');

        if (!$Category) {
            $this->description(Gdn::formatService()->renderPlainText(c('Garden.Description', ''), HtmlFormat::FORMAT_KEY));
        }

        $this->setData('Breadcrumbs', CategoryModel::getAncestors(val('CategoryID', $this->data('Category'))));

        // Set the category follow toggle before we load category data so that it affects the category query appropriately.
        $CategoryFollowToggleModule = new CategoryFollowToggleModule($this);
        $CategoryFollowToggleModule->setToggle();

        // Get category data
        $this->CategoryModel->Watching = !Gdn::session()->getPreference('ShowAllCategories');

        if ($Category) {
            $this->setData('Category', CategoryModel::categories($Category));

            $this->categoriesCompatibilityCallback = function () use ($Category) {
                $Subtree = CategoryModel::getSubtree($Category, false);
                $CategoryIDs = array_column($Subtree, 'CategoryID');
                return $this->CategoryModel->getFull($CategoryIDs)->resultArray();
            };
        } else {
            $this->categoriesCompatibilityCallback = function () {
                return $this->CategoryModel->getFull()->resultArray();
            };
        }

        if ($this->data('Followed')) {
            if ($Category) {
                $ancestor = CategoryModel::categories($Category);
                if (empty($ancestor)) {
                    throw new Gdn_UserException("Invalid category ID: {$Category}");
                }
                $tree = $this->CategoryModel->getTree($ancestor['CategoryID']);
                $flatTree = CategoryModel::flattenTree($tree);
                $filterIDs = array_column($flatTree, 'CategoryID');
            } else {
                $filterIDs = null;
            }
            $categoryTree = $this->getFollowed(true, $filterIDs);
        } else {
            $categoryTree = $this->getCategoryTree(
                $Category ?: -1,
                $Category ? null : CategoryModel::getRootDisplayAs(),
                true,
                true
            );
        }

        $this->setData('CategoryTree', $categoryTree);

        // Add modules
        // $this->addModule('NewDiscussionModule');
        // $this->addModule('DiscussionFilterModule');
        // $this->addModule('BookmarkedModule');

        $this->addModule($CategoryFollowToggleModule);
        // $this->addModule('TagModule');

        // Make sure the userphoto module gets added to the page
        $this->addModule('UserPhotoModule');

        // Add discussion and question count on the profile block
        $this->fireEvent('AddProfileTabsInfo');
        $this->addModule('ProfileFilterModule');

        $canonicalUrl = $this->calculateCanonicalUrl($this->Data);
        $this->canonicalUrl($canonicalUrl);

        if ($this->View === 'all' && $displayAs === 'Flat') {
            $this->View = 'flat_all';
        }

        $Location = $this->fetchViewLocation('helper_functions', 'categories', false, false);
        if ($Location) {
            include_once $Location;
        }
        $this->render();
    }

    /**
     * Show all categories and few discussions from each.
     *
     * @param string $Category The url code of the parent category.
     * @since 2.0.0
     * @access public
     */
    public function discussions($Category = '') {
        // Setup head
        $this->addJsFile('discussions.js');
        $this->Menu->highlightRoute('/discussions');

        if (!$this->title()) {
            $Title = Gdn::formatService()->renderPlainText(c('Garden.HomepageTitle'), HtmlFormat::FORMAT_KEY);
            if ($Title) {
                $this->title($Title, '');
            } else {
                $this->title(t('All Categories'));
            }
        }

        if (!$Category) {
            $this->description(Gdn::formatService()->renderPlainText(c('Garden.Description', ''), HtmlFormat::FORMAT_KEY));
        }

        Gdn_Theme::section('CategoryDiscussionList');

        // Set the category follow toggle before we load category data so that it affects the category query appropriately.
        $CategoryFollowToggleModule = new CategoryFollowToggleModule($this);
        $CategoryFollowToggleModule->setToggle();

        //$this->CategoryModel->Watching = !Gdn::session()->getPreference('ShowAllCategories');

        if ($Category) {
            $Subtree = CategoryModel::getSubtree($Category, false);
            $CategoryIDs = array_column($Subtree, 'CategoryID');
            $Categories = $this->CategoryModel->getFull($CategoryIDs)->resultArray();
        } elseif ($this->data('Followed')) {
            $Categories = $this->CategoryModel->getWhere(['Followed' => true])->resultArray();
            $Categories = array_column($Categories, null, 'CategoryID');
            $Categories = $this->CategoryModel->flattenCategories($Categories);
        } else {
            $Categories = $this->CategoryModel->getFull()->resultArray();
        }

        $this->setData('Categories', $Categories);

        // Get category data and discussions
        $this->DiscussionsPerCategory = c('Vanilla.Discussions.PerCategory', 5);
        $DiscussionModel = new DiscussionModel();
        $DiscussionModel->setSort(Gdn::request()->get());
        $DiscussionModel->setFilters(Gdn::request()->get());
        $this->setData('Sort', $DiscussionModel->getSort());
        $this->setData('Filters', $DiscussionModel->getFilters());

        $this->CategoryDiscussionData = [];
        $this->CategoryAnnounceData = [];
        $Discussions = [];

        foreach ($this->CategoryData->result() as $Category) {
            $iD = $Category->CategoryID;
            if ($iD > 0 && $Category->CountDiscussions > 0) {
                $announcements = $DiscussionModel->getAnnouncements(
                    ['d.CategoryID' => $iD],
                    0,
                    $this->DiscussionsPerCategory
                );
                $this->CategoryAnnounceData[$iD] = $announcements;
                $newLimit = $this->DiscussionsPerCategory - count($announcements->result());

                $this->CategoryDiscussionData[$iD] = $newLimit > 0 ?
                    $DiscussionModel->getWhereRecent(['d.CategoryID' => $iD, 'd.Announce' => 0], $newLimit) :
                    new Gdn_DataSet();

                $categoryDiscussions = $announcements->resultObject();
                $discussionsToAdd = $this->CategoryDiscussionData[$iD]->resultObject();
                $categoryDiscussions = array_merge($categoryDiscussions, $discussionsToAdd);

                $Discussions = array_merge(
                    $Discussions,
                    $categoryDiscussions
                );
            }
        }
        $this->setData('Discussions', $Discussions);

        // Add modules
        // $this->addModule('NewDiscussionModule');
        $this->addModule('DiscussionFilterModule');

        $this->addModule('BookmarkedModule');
        $this->addModule($CategoryFollowToggleModule);

        // Set view and render
        $this->View = 'discussions';

        $canonicalUrl = $this->calculateCanonicalUrl($this->Data);
        $this->canonicalUrl($canonicalUrl);

        $Path = $this->fetchViewLocation('helper_functions', 'discussions', false, false);
        if ($Path) {
            include_once $Path;
        }

        // For GetOptions function
        $Path2 = $this->fetchViewLocation('helper_functions', 'categories', false, false);
        if ($Path2) {
            include_once $Path2;
        }
        $this->render();
    }

    public function __get($name) {
        switch ($name) {
            case 'CategoryData':
//            deprecated('CategoriesController->CategoryData', "CategoriesController->data('Categories')");
                $this->CategoryData = new Gdn_DataSet($this->data('Categories'), DATASET_TYPE_ARRAY);
                $this->CategoryData->datasetType(DATASET_TYPE_OBJECT);
                return $this->CategoryData;
        }
    }

    /**
     * Highlight route.
     *
     * Always called by dispatcher before controller's requested method.
     *
     * @since 2.0.0
     * @access public
     */
    public function initialize() {
        parent::initialize();
        if (!c('Vanilla.Categories.Use', true)) {
            redirectTo('/discussions');
        }
        if ($this->Menu) {
            $this->Menu->highlightRoute('/categories');
        }

        $this->CountCommentsPerPage = c('Vanilla.Comments.PerPage', 30);

        /**
         * The default Cache-Control header does not include no-store, which can cause issues with outdated category
         * information (e.g. counts).  The same check is performed here as in Gdn_Controller before the Cache-Control
         * header is added, but this value includes the no-store specifier.
         */
        if (Gdn::session()->isValid()) {
            $this->setHeader('Cache-Control', 'private, no-cache, no-store, max-age=0, must-revalidate');
        }
    }

    public function tree($category = '') {
        $tree = CategoryModel::instance()->getChildTree($category);
        $this->setData('Categories', $tree);
        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * Returns the full list of categories for the APIv1.
     */
    public function apiV1List() {
        $categories = CategoryModel::categories();

        // Purge the root category, if present.
        if (val(-1, $categories)) {
            unset($categories[-1]);
        }

        $this->setData('Categories', $categories);
        $this->render('blank', 'utility', 'dashboard');
    }

    /**
     * {@inheritdoc}
     */
    public function data($path, $default = '') {
        if (isset($this->Data[$path])) {
            return $this->Data[$path];
        }

        switch ($path) {
            case 'Categories':
                if ($this->categoriesCompatibilityCallback instanceof \Closure) {
                    deprecated('Categories', 'CategoryTree');
                    $this->Data['Categories'] = $categories = call_user_func($this->categoriesCompatibilityCallback);
                    return $categories;
                }
                return $default;
            default:
                return parent::data($path, $default);
        }
    }

    /**
     * Return URL based on 'isHomepage'
     *
     * @param array $data
     * @return string
     */
    private function calculateCanonicalUrl($data) {
        return empty($data['isHomepage']) ? url(Gdn::request()->path(), true) : url('/', true);
    }

    public function filterDiscussion() {
        $parameter = $_POST['parameter'];

        echo $this->_PagerUrl.'?'.$parameter;
    }

    public function subjectdropdown() {
        $this->render('SubjectDropdown', 'Categories', 'Vanilla');
    }
}
