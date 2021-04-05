<?php
/**
 * Discussion controller
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0
 */

use Garden\Schema\Schema;
use Vanilla\Formatting\DateTimeFormatter;
use Vanilla\Message;
use Vanilla\SchemaFactory;

/**
 * Handles accessing & displaying a single discussion via /discussion endpoint.
 */
class DiscussionController extends VanillaController {

    /** @var array Models to include. */
    public $Uses = ['DiscussionModel', 'CategoryModel', 'CommentModel', 'Form', 'UserModel', 'ActivityModel'];

    /** @var array Unique identifier. */
    public $CategoryID;

    /** @var array Unique identifier. */
    public $Category;

    /**  @var CommentModel */
    public $CommentModel;

    /** @var DiscussionModel */
    public $DiscussionModel;

    /** @var Message[] */
    private $messages = [];

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
     *
     *
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name) {
        switch ($name) {
            case 'CommentData':
                deprecated('DiscussionController->CommentData', "DiscussionController->data('Comments')");
                return $this->data('Comments');
                break;
        }
        return null;
    }

    /**
     * Add a message to be displayed on the discussion.
     *
     * @param Message $message
     */
    public function addMessage(Message $message) {
        $this->messages[] = $message;
    }

    /**
     * Get the most recent date inserted from an array of comments.
     *
     * @param iterable|null $comments The comments from a page.
     * @return string|null $maxDateInserted.
     * @throws Exception Some exception.
     */
    public function maxDateInserted(?iterable $comments): ?string {
        if (is_null($comments)) {
            return null;
        }

        $maxDate = null;
        foreach ($comments as $comment) {
            $maxDate = DiscussionModel::maxDate($maxDate, val('DateInserted', $comment));
        }
        return $maxDate;
    }

    public function writeCommentFilter() {
        $verified = Gdn::request()->get('commentverifiedby') ?? false;
        $this->IsVerifiedBy = $verified;

        $sort = Gdn::request()->get('sort') ?? 'desc';
        $this->SortDirection = $sort;

        $commentFilterModule = new CommentFilterModule($sort, $verified);
        $this->addModule($commentFilterModule);
        $this->addJsFile('filter.js');
        $wheres = [];

        if ($this->IsVerifiedBy == 'true') {
            $wheres['DateAccepted <>'] = '';
        } else {
            unset($wheres['DateAccepted <>']);
        }

        $this->WhereClause = $wheres;
    }

    /**
     * Default single discussion display.
     *
     * @param int $DiscussionID Unique discussion ID
     * @param string $DiscussionStub URL-safe title slug
     * @param string $Page The current page of comments
     */
    public function index($DiscussionID = 0, $DiscussionStub = '', $Page = 0, $Order = '') {
        // Setup head
        $Session = Gdn::session();
        $this->addJsFile('jquery.autosize.min.js');
        $this->addJsFile('autosave.js');
        $this->addJsFile('discussion.js');
        $this->addJsFile('replyQuestion.js');
        $this->addJsFile('feedback.js');

        // write comment filter
        $this->writeCommentFilter();
        $Order = $this->SortDirection;

        Gdn_Theme::section('Discussion');

        // Load the discussion record
        $DiscussionID = (is_numeric($DiscussionID) && $DiscussionID > 0) ? $DiscussionID : 0;
        if (!array_key_exists('Discussion', $this->Data)) {
            $this->setData('Discussion', $this->DiscussionModel->getID($DiscussionID), true);
        }

        if (!is_object($this->Discussion)) {
            $this->EventArguments['DiscussionID'] = $DiscussionID;
            $this->fireEvent('DiscussionNotFound');
            throw notFoundException('Discussion');
        }

        // Define the query offset & limit.
        $Limit = c('Vanilla.Comments.PerPage', 30);

        $OffsetProvided = $Page != '';
        list($Offset, $Limit) = offsetLimit($Page, $Limit);

        // set GradeID
        $this->GradeID = $this->Discussion->GradeID;
        $this->setData('GradeID', $this->GradeID);

        // Check permissions.
        $Category = CategoryModel::categories($this->Discussion->CategoryID);
        $this->categoryPermission($Category, 'Vanilla.Discussions.View');

        if (c('Vanilla.Categories.Use', true)) {
            $this->CategoryID = $this->Discussion->CategoryID;
        } else {
            $this->CategoryID = null;
        }
        $this->setData('CategoryID', $this->CategoryID);

        if (strcasecmp(val('Type', $this->Discussion), 'redirect') === 0) {
            $this->redirectDiscussion($this->Discussion);
        }

        $this->setData('Category', $Category);
        $this->setData('Editor.BackLink', anchor(htmlspecialchars($Category['Name']), categoryUrl($Category)));

        if ($CategoryCssClass = val('CssClass', $Category)) {
            Gdn_Theme::section($CategoryCssClass);
        }

        $this->setData('Breadcrumbs', CategoryModel::getAncestors($this->CategoryID));

        // Setup
        $this->title($this->Discussion->Name);

        // Actual number of comments, excluding the discussion itself.
        $ActualResponses = $this->Discussion->CountComments;

        $this->Offset = $Offset;
        if (c('Vanilla.Comments.AutoOffset')) {
//         if ($this->Discussion->CountCommentWatch > 1 && $OffsetProvided == '')
//            $this->addDefinition('ScrollTo', 'a[name=Item_'.$this->Discussion->CountCommentWatch.']');
            if (!is_numeric($this->Offset) || $this->Offset < 0 || !$OffsetProvided) {
                // Round down to the appropriate offset based on the user's read comments & comments per page
                $CountCommentWatch = $this->Discussion->CountCommentWatch > 0 ? $this->Discussion->CountCommentWatch : 0;
                if ($CountCommentWatch > $ActualResponses) {
                    $CountCommentWatch = $ActualResponses;
                }

                // (((67 comments / 10 perpage) = 6.7) rounded down = 6) * 10 perpage = offset 60;
                $this->Offset = floor($CountCommentWatch / $Limit) * $Limit;

                if ($this->Offset >= $ActualResponses) {
                    $this->Offset = $ActualResponses - $Limit;
                }
                if ($ActualResponses <= $Limit) {
                    $this->Offset = 0;
                }
            }
        } else {
            if ($this->Offset == '') {
                $this->Offset = 0;
            }
        }

        if ($this->Offset < 0) {
            $this->Offset = 0;
        }

        //set language meta
        $this->Head->addTag('meta', ['property' => 'language', 'content' => $this->getContentLocale()]);

        //set last-modified-date meta (Must be in the format YYYY-MM-DD)
        try {
            $lastDate = date_create($this->data('Discussion.LastDate'));
            $formattedLastDate = date_format($lastDate, 'Y-m-d');
            $this->Head->addTag('meta', ['http-equiv' => 'last-modified', 'property' => 'last-modified-date', 'content' => $formattedLastDate]);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_NOTICE);
        }

        // Set the canonical url to have the proper page title.
        $canonicalUrl = ($this->Discussion->Attributes['CanonicalUrl'] ?? '');
        if (empty($canonicalUrl)) {
            $this->canonicalUrl(discussionUrl($this->Discussion, pageNumber($this->Offset, $Limit, 0, false)));
        } else {
            $canonicalMessage = new Message(
                str_replace(
                    ["<0>", "</0>"],
                    ['<a href="' . htmlspecialchars(\Gdn::request()->url($canonicalUrl)) . '">', "</a>"],
                    \Gdn::translate('This discussion has a more <0>recent version</0>.')
                ),
                Message::TYPE_WARNING
            );
            $this->addMessage($canonicalMessage);
            $this->canonicalUrl($canonicalUrl);
        }
        $this->checkPageRange($this->Offset, $ActualResponses);

        // Load the comments
        if ($Order == 'asc') {
            $this->CommentModel->orderBy('c.DateInserted asc');
            $this->setData('SortComments', 'asc');
            $this->setData('Comments', $this->CommentModel->getByDiscussion($DiscussionID, $Limit, $this->Offset, $this->WhereClause), true);
        } else {
            $this->CommentModel->orderBy('c.DateInserted desc');
            $this->setData('SortComments', 'desc');
            $this->setData('Comments', $this->CommentModel->getByDiscussion($DiscussionID, $Limit, $this->Offset, $this->WhereClause), true);
        }

        $where['DiscussionID'] = $DiscussionID;
        $where = array_merge($where, $this->WhereClause);
        $this->setData('CommentCount', $this->CommentModel->getCount($where));

        $LatestItem = $this->Discussion->CountCommentWatch;
        if ($LatestItem === null) {
            $LatestItem = 0;
        } elseif ($LatestItem < $this->Discussion->CountComments) {
            $LatestItem += 1;
        } elseif ($LatestItem > $this->Discussion->CountComments) {
            // If ever the CountCommentWatch is greater than the actual number of comments.
            $LatestItem = $this->Discussion->CountComments;
        }

        $this->setData('_LatestItem', $LatestItem);

        $PageNumber = pageNumber($this->Offset, $Limit);
        $this->setData('Page', $PageNumber);
        $this->_SetOpenGraph();
        if ($PageNumber == 1) {
            $this->description(sliceParagraph(Gdn_Format::plainText($this->Discussion->Body, $this->Discussion->Format), 160));
            // Add images to head for open graph
            $Dom = pQuery::parseStr(Gdn_Format::to($this->Discussion->Body, $this->Discussion->Format));
        } else {
            $this->Data['Title'] .= sprintf(t(' - Page %s'), pageNumber($this->Offset, $Limit));

            $FirstComment = $this->data('Comments')->firstRow();
            $FirstBody = val('Body', $FirstComment);
            $FirstFormat = val('Format', $FirstComment);
            $this->description(sliceParagraph(Gdn_Format::plainText($FirstBody, $FirstFormat), 160));
            // Add images to head for open graph
            $Dom = pQuery::parseStr(Gdn_Format::to($FirstBody, $FirstFormat));
        }

        if ($Dom) {
            foreach ($Dom->query('img') as $img) {
                if ($img->attr('src')) {
                    $this->image($img->attr('src'));
                }
            }
        }

        // Save the insert date of the last comment viewed to set in the user's discussion watch table.

        // Make sure to set the user's discussion watch records if this is not an API request.
        if ($this->deliveryType() !== DELIVERY_TYPE_DATA) {
            $maxDateInserted = $this->maxDateInserted($this->data('Comments'));
            $this->DiscussionModel->setWatch($this->Discussion, $Limit, $this->Offset, $this->Discussion->CountComments, $maxDateInserted);
        }

        // Build a pager
        $PagerFactory = new Gdn_PagerFactory();
        $this->EventArguments['PagerType'] = 'Pager';
        $this->fireEvent('BeforeBuildPager');
        $this->Pager = $PagerFactory->getPager($this->EventArguments['PagerType'], $this);
        $this->Pager->ClientID = 'Pager';

        $this->Pager->configure(
            $this->Offset,
            $Limit,
            $ActualResponses,
            ['DiscussionUrl']
        );
        $this->Pager->Record = $this->Discussion;
        PagerModule::current($this->Pager);
        $this->fireEvent('AfterBuildPager');

        // Define the form for the comment input
        $this->Form = Gdn::factory('Form', 'Comment');
        $this->Form->Action = url('/post/comment/');
        $this->DiscussionID = $this->Discussion->DiscussionID;
        $this->Form->addHidden('DiscussionID', $this->DiscussionID);
        $UserMetaData = Gdn::userModel()->getMeta($Session->UserID, 'Profile.%', 'Profile.');
        $fields = c('ProfileExtender.Fields', []);
        if (!is_array($fields)) {
            $fields = [];
        }
        $GradeOption = [];
        foreach ($fields as $k => $field) {
            if ($field['Label'] == "Grade") {
                $GradeOption = $field['Options'];
            }
        }
        if ($UserMetaData && $UserMetaData['Grade']) {
            $this->Form->addHidden('GradeID', array_search($UserMetaData['Grade'],  $GradeOption));
        }
        if ($this->UserRole === TEACHER_ROLE) {
            $this->Form->addHidden('GradeID', array_search('Enseignant',  $GradeOption));
        }

        $this->Form->addHidden('CommentID', '');

        // Look in the session stash for a comment
        $StashComment = $Session->getPublicStash('CommentForDiscussionID_'.$this->Discussion->DiscussionID);
        if ($StashComment) {
            $this->Form->setValue('Body', $StashComment);
            $this->Form->setFormValue('Body', $StashComment);
        }

        // Retrieve & apply the draft if there is one:
        if (Gdn::session()->UserID) {
            $DraftModel = new DraftModel();
            $Draft = $DraftModel->getByUser($Session->UserID, 0, 1, $this->Discussion->DiscussionID)->firstRow();
            $this->Form->addHidden('DraftID', $Draft ? $Draft->DraftID : '');
            if ($Draft && !$this->Form->isPostBack()) {
                $this->Form->setValue('Body', $Draft->Body);
                $this->Form->setValue('Format', $Draft->Format);
            }
        }

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            $this->setJson('LessRow', $this->Pager->toString('less'));
            $this->setJson('MoreRow', $this->Pager->toString('more'));
            $this->View = 'comments';
        }

        // Inform moderator of checked comments in this discussion
        $CheckedComments = $Session->getAttribute('CheckedComments', []);
        if (count($CheckedComments) > 0) {
            ModerationController::informCheckedComments($this);
        }

        // Add modules
        // $this->addModule('NewDiscussionModule');
        $this->addModule('CategoriesModule');
        $this->addModule('BookmarkedModule');

        $this->CanEditComments = Gdn::session()->checkPermission('Vanilla.Comments.Edit', true, 'Category', 'any') && c('Vanilla.AdminCheckboxes.Use');

        // Report the discussion id so js can use it.
        $this->addDefinition('DiscussionID', $DiscussionID);
        $this->addDefinition('Category', $this->data('Category.Name'));

        $mobileHeader = new MobileHeaderModule('Student Questions', true);
        $this->addModule($mobileHeader);

        $this->fireEvent('BeforeDiscussionRender');

        $AttachmentModel = AttachmentModel::instance();
        if (AttachmentModel::enabled()) {
            $AttachmentModel->joinAttachments($this->Data['Discussion'], $this->Data['Comments']);

            $this->fireEvent('FetchAttachmentViews');
            if ($this->deliveryMethod() === DELIVERY_METHOD_XHTML) {
                require_once $this->fetchViewLocation('attachment', 'attachments', 'dashboard');
            }
        }

        $this->Head->setJsonLD(
            'DiscussionForumPosting',
            $this->DiscussionModel->structuredData((array)$this->Discussion)
        );
        $this->setData('IsAnswer', true);

        $this->getUserInfo();

        $this->addModule('UserPhotoModule');
        $this->fireEvent('AddProfileInfo');

        $DiscussionMeta = Gdn::userModel()->getMeta($this->Discussion->InsertUserID, 'Profile.%', 'Profile.');

        if ($this->UserRole == TEACHER_ROLE) {
            // $bannerModule = new BannerModule('Home', 'Home', '', 'Mutual Aid Zone', "Welcome to the Mutual Aid Zone! <br/> Want to help the students? It's this way!", "", "Teacher");
            $discussionsFooterModule = new DiscussionsFooterModule(false, "You have the same problem and the explanations don't help?");
            $this->addModule($discussionsFooterModule);
        } else {
            // $bannerModule = new BannerModule('Home', 'Home', '', 'Mutual Aid Zone', 'Welcome to the Mutual Aid Zone! <br/> Do you have a question? Here are the explanations!', '');
            $this->addModule('ProfileFilterModule');
        }

        // $this->addModule($bannerModule);

        $this->render();
    }

    /**
     * Get current messages.
     *
     * @return array
     */
    public function getMessages(): array {
        return $this->messages;
    }

    /**
     * Display comments in a discussion since a particular CommentID.
     *
     * @param int $discussionID Unique discussion ID
     * @param int $lastCommentID Only shows comments posted after this one
     */
    public function getNew($discussionID, $lastCommentID = 0) {
        $this->setData('Discussion', $this->DiscussionModel->getID($discussionID), true);

        // Check permissions.
        $this->categoryPermission($this->Discussion->CategoryID, 'Vanilla.Discussions.View');
        $this->setData('CategoryID', $this->CategoryID = $this->Discussion->CategoryID, true);

        // Get the comments.
        $comments = $this->CommentModel->getNew($discussionID, $lastCommentID);
        $this->setData('Comments', $comments, true);
        $comments = $comments->result();

        // Set the data.
        if (count($comments) > 0) {
            $lastComment = $comments[count($comments) - 1];
            // Mark the comment read.
            $this->setData('Offset', $this->Discussion->CountComments, true);
            $this->DiscussionModel->setWatch(
                $this->Discussion,
                $this->Discussion->CountComments,
                $this->Discussion->CountComments,
                $this->Discussion->CountComments
            );

            $lastCommentID = $this->json('LastCommentID');
            if (is_null($lastCommentID) || $lastComment->CommentID > $lastCommentID) {
                $this->json('LastCommentID', $lastComment->CommentID);
            }
        } else {
            $this->setData('Offset', $this->CommentModel->getOffset($lastCommentID), true);
        }

        $this->View = 'comments';
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
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Teacher') ?? TEACHER_ROLE;
            else $UserRole = RoleModel::TYPE_MEMBER ?? 'Student';

            return $UserRole;
        } else return null;
    }

    /**
     * Highlight route & add common JS definitions.
     *
     * Always called by dispatcher before controller's requested method.
     */
    public function initialize() {
        parent::initialize();
        $this->addDefinition('ConfirmDeleteCommentHeading', t('ConfirmDeleteCommentHeading', 'Delete Comment'));
        $this->addDefinition('ConfirmDeleteCommentText', t('ConfirmDeleteCommentText', 'Are you sure you want to delete this comment?'));
        $this->Menu->highlightRoute('/discussions');
    }

    /**
     * Display discussion page starting with a particular comment.
     *
     * @param int $commentID Unique comment ID
     */
    public function comment($commentID) {
        // Get the discussionID
        $comment = $this->CommentModel->getID($commentID);
        if (!$comment) {
            throw notFoundException('Comment');
        }

        $discussionID = $comment->DiscussionID;

        // Figure out how many comments are before this one
        $offset = $this->CommentModel->getOffset($comment);
        $limit = Gdn::config('Vanilla.Comments.PerPage', 30);

        $pageNumber = pageNumber($offset, $limit, true);
        $this->setData('Page', $pageNumber);

        $this->View = 'index';
        $this->index($discussionID, 'x', $pageNumber);
    }

    /**
     * Allows user to remove announcement.
     *
     * Users may remove announcements from being displayed for themselves only.
     * Does not affect what announcements are shown for other users.
     *
     * @param int $discussionID Unique discussion ID.
     */
    public function dismissAnnouncement($discussionID = 0) {
        // Make sure we are posting back.
        if (!Gdn::request()->isAuthenticatedPostBack(true)) {
            throw new Exception('Requires POST', 405);
        }

        // Confirm announcements may be dismissed
        if (!c('Vanilla.Discussions.Dismiss', 1)) {
            throw permissionException('Vanilla.Discussions.Dismiss');
        }

        $session = Gdn::session();
        if (is_numeric($discussionID)
            && $discussionID > 0
            && $session->UserID > 0
        ) {
            $this->DiscussionModel->dismissAnnouncement($discussionID, $session->UserID);
        }

        // Redirect back where the user came from if necessary
        if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
            redirectTo('discussions');
        }

        $this->jsonTarget("#Discussion_$discussionID", null, 'Highlight');

        $this->render('Blank', 'Utility', 'Dashboard');
    }

    /**
     * Allows user to bookmark or unbookmark a discussion.
     *
     * If the discussion isn't bookmarked by the user, this bookmarks it.
     * If it is already bookmarked, this unbookmarks it.
     *
     * @param int|null $DiscussionID Unique discussion ID.
     */
    public function bookmark($DiscussionID = null) {
        // Make sure we are posting back.
        // if (!$this->Request->isAuthenticatedPostBack()) {
        //     throw permissionException('Javascript');
        // }

        $Session = Gdn::session();

        if (!$Session->UserID) {
            throw permissionException('SignedIn');
        }

        // Check the form to see if the data was posted.
        $Form = new Gdn_Form();
        $DiscussionID = $Form->getFormValue('DiscussionID', $DiscussionID);
        $Bookmark = $Form->getFormValue('Bookmark', null);
        $UserID = $Form->getFormValue('UserID', $Session->UserID);

        // Check the permission on the user.
        if ($UserID != $Session->UserID) {
            $this->permission('Garden.Moderation.Manage');
        }

        $Discussion = $this->DiscussionModel->getID($DiscussionID);
        if (!$Discussion) {
            throw notFoundException('Discussion');
        }

        // Make sure that the user has access to the discussion.
        $categoryID = val('CategoryID', $Discussion);
        $this->DiscussionModel->categoryPermission('Vanilla.Discussions.View', $categoryID);

        $Bookmark = $this->DiscussionModel->bookmark($DiscussionID, $UserID, $Bookmark);

        // Set the new value for api calls and json targets.
        $this->setData([
            'UserID' => $UserID,
            'DiscussionID' => $DiscussionID,
            'Bookmarked' => (bool)$Bookmark
        ]);
        setValue('Bookmarked', $Discussion, (int)$Bookmark);

        // Update the user's bookmark count
        $CountBookmarks = $this->DiscussionModel->setUserBookmarkCount($UserID);
        $this->jsonTarget('.User-CountBookmarks', (string)$CountBookmarks);

        //  Short circuit if this is an api call.
        if ($this->deliveryType() === DELIVERY_TYPE_DATA) {
            $this->render('Blank', 'Utility', 'Dashboard');
            return;
        }

        // Return the appropriate bookmark.
        require_once $this->fetchViewLocation('helper_functions', 'Discussions');
        $Html = bookmarkButton($Discussion);
//      $this->jsonTarget(".Section-DiscussionList #Discussion_$DiscussionID .Bookmark,.Section-Discussion .PageTitle .Bookmark", $Html, 'ReplaceWith');
        $this->jsonTarget("!element", $Html, 'ReplaceWith');

        // Add the bookmark to the bookmarks module.
        if ($Bookmark) {
            // Grab the individual bookmark and send it to the client.
            $Bookmarks = new BookmarkedModule($this);
            if ($CountBookmarks == 1) {
                // When there is only one bookmark we have to get the whole module.
                $Target = '#Panel';
                $Type = 'Append';
                $Bookmarks->getData();
                $Data = $Bookmarks->toString();
            } else {
                $Target = '#Bookmark_List';
                $Type = 'Prepend';
                $Loc = $Bookmarks->fetchViewLocation('discussion');

                ob_start();
                include($Loc);
                $Data = ob_get_clean();
            }

            $this->jsonTarget($Target, $Data, $Type);
        } else {
            // Send command to remove bookmark html.
            if ($CountBookmarks == 0) {
                $this->jsonTarget('#Bookmarks', null, 'Remove');
            } else {
                $this->jsonTarget('#Bookmark_'.$DiscussionID, null, 'Remove');
            }
        }

        $this->render('Blank', 'Utility', 'Dashboard');
    }

    /**
     * Allows user to announce or unannounce a discussion.
     *
     * If the discussion isn't announced, this announces it.
     * If it is already announced, this unannounces it.
     * Announced discussions stay at the top of the discussions
     * list regardless of how long ago the last comment was.
     *
     * @param int $discussionID Unique discussion ID.
     * @param string $target Redirect here after announcing.
     */
    public function announce($discussionID = 0, $target = '') {
        $discussion = $this->DiscussionModel->getID($discussionID);
        if (!$discussion) {
            throw notFoundException('Discussion');
        }
        $this->categoryPermission($discussion->CategoryID, 'Vanilla.Discussions.Announce');

        if ($this->Form->authenticatedPostBack()) {
            // Save the property.
            $cacheKeys = [
                $this->DiscussionModel->getAnnouncementCacheKey(),
                $this->DiscussionModel->getAnnouncementCacheKey(val('CategoryID', $discussion))
            ];
            $this->DiscussionModel->SQL->cache($cacheKeys);
            $this->DiscussionModel->setProperty($discussionID, 'Announce', (int)$this->Form->getFormValue('Announce', 0));

            if ($target) {
                $this->setRedirectTo($target);
            }

            $this->jsonTarget('', '', 'Refresh');
        } else {
            if (!$discussion->Announce) {
                $discussion->Announce = 2;
            }
            $this->Form->setData($discussion);
        }

        $discussion = (array)$discussion;
        $category = CategoryModel::categories($discussion['CategoryID']);

        $this->setData('Discussion', $discussion);
        $this->setData('Category', $category);

        $this->title(t('Announce'));
        $this->render();
    }

    /**
     *
     *
     * @param $discussion
     * @throws Exception
     */
    public function sendOptions($discussion) {
        require_once $this->fetchViewLocation('helper_functions', 'Discussion');
        $this->jsonTarget("#Discussion_{$discussion->DiscussionID} .OptionsMenu,.Section-Discussion .Discussion .OptionsMenu", getDiscussionOptionsDropdown($discussion)->toString(), 'ReplaceWith');
    }

    /**
     * Allows user to sink or unsink a discussion.
     *
     * If the discussion isn't sunk, this sinks it. If it is already sunk,
     * this unsinks it. Sunk discussions do not move to the top of the
     * discussion list when a new comment is added.
     *
     * @param int $discussionID Unique discussion ID.
     * @param bool $sink Whether or not to unsink the discussion.
     */
    public function sink($discussionID = 0, $sink = true) {
        // Make sure we are posting back.
        if (!$this->Request->isAuthenticatedPostBack()) {
            throw permissionException('Javascript');
        }

        $discussion = $this->DiscussionModel->getID($discussionID);

        if (!$discussion) {
            throw notFoundException('Discussion');
        }

        $this->categoryPermission($discussion->CategoryID, 'Vanilla.Discussions.Sink');

        // Sink the discussion.
        $this->DiscussionModel->setField($discussionID, 'Sink', $sink);
        $discussion->Sink = $sink;

        // Redirect to the front page
        if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
            $target = getIncomingValue('Target', 'discussions');
            $this->setRedirectTo($target);
        }

        $this->sendOptions($discussion);

        $this->jsonTarget("#Discussion_$discussionID", null, 'Highlight');
        $this->jsonTarget(".Discussion #Item_0", null, 'Highlight');

        $this->render('Blank', 'Utility', 'Dashboard');
    }

    /**
     * Allows user to close or re-open a discussion.
     *
     * If the discussion isn't closed, this closes it. If it is already
     * closed, this re-opens it. Closed discussions may not have new
     * comments added to them.
     *
     * @param int $discussionID Unique discussion ID.
     * @param bool $close Whether or not to close the discussion.
     */
    public function close($discussionID = 0, $close = true) {
        // Make sure we are posting back.
        if (!$this->Request->isAuthenticatedPostBack()) {
            throw permissionException('Javascript');
        }

        $Discussion = $this->DiscussionModel->getID($discussionID);

        if (!$Discussion) {
            throw notFoundException('Discussion');
        }

        if (!DiscussionModel::canClose($Discussion)) {
            $this->permission('Vanilla.Discussions.Close', true, 'Category', $Discussion->CategoryID);
        }

        // Close the discussion.
        $this->DiscussionModel->setField($discussionID, 'Closed', $close);

        $attributes = $Discussion->Attributes;
        unset($Discussion->Attributes[DiscussionModel::CLOSED_BY_USER_ID]);

        // Check if the discussion is getting closed and check if the author is closing it.
        if ($close) {
            $Discussion->Attributes[DiscussionModel::CLOSED_BY_USER_ID] = Gdn::session()->UserID;
        }

        // Update the attributes if they changed.
        if ($attributes !== $Discussion->Attributes) {
            $this->DiscussionModel->setProperty($discussionID, 'Attributes', dbencode($Discussion->Attributes));
        }

        $Discussion->Closed = $close;

        // Redirect to the front page
        if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
            $Target = getIncomingValue('Target', 'discussions');
            redirectTo($Target);
        }

        $this->sendOptions($Discussion);

        if ($close) {
            require_once $this->fetchViewLocation('helper_functions', 'Discussions');
            $this->jsonTarget(".Section-DiscussionList #Discussion_$discussionID .Meta-Discussion", tag($Discussion, 'Closed', 'Closed'), 'Prepend');
            $this->jsonTarget(".Section-DiscussionList #Discussion_$discussionID", 'Closed', 'AddClass');
        } else {
            $this->jsonTarget(".Section-DiscussionList #Discussion_$discussionID .Tag-Closed", null, 'Remove');
            $this->jsonTarget(".Section-DiscussionList #Discussion_$discussionID", 'Closed', 'RemoveClass');
        }

        $this->jsonTarget("#Discussion_$discussionID", null, 'Highlight');
        $this->jsonTarget(".Discussion #Item_0", null, 'Highlight');

        $this->render('Blank', 'Utility', 'Dashboard');
    }

    /**
     * Allows user to delete a discussion.
     *
     * This is a "hard" delete - it is removed from the database.
     *
     * @param int $discussionID Unique discussion ID.
     */
    public function delete(int $discussionID, $target = '') {
        $discussion = $this->DiscussionModel->getID($discussionID);

        if (!$discussion) {
            throw notFoundException('Discussion');
        }

        $this->categoryPermission($discussion->CategoryID, 'Vanilla.Discussions.Delete');
        if ($this->Form->authenticatedPostBack()) {
            if (!$this->DiscussionModel->deleteID($discussionID)) {
                $this->Form->addError('Failed to delete discussion');
            }

            if ($this->Form->errorCount() == 0) {
                \Gdn::config()->touch([
                    'Preferences.Email.Delete' => 2,
                    'Preferences.Popup.Delete' => 2,
                ]);
                $text = sprintf(t('Your question has been deleted by a moderator %s.',
                    'Your question has been deleted by a moderator <b>"%s"</b>.'), $this->Form->getFormValue('DeleteMessage'));
                $data = [
                    "ActivityType" => "Delete",
                    "NotifyUserID" => $discussion->InsertUserID,
                    "HeadlineFormat" => 'Question deleted!',
                    "RecordType" => "Delete",
                    "RecordID" => $discussionID,
                    "Route" => discussionUrl($discussion, "", "/"),
                    "Data" => [
                        "Name" => $discussion->Name,
                        "Category" => $discussion->Category,
                        "Text" => $text
                    ],
                ];

                $ActivityModel = new ActivityModel();
                $ActivityModel->queue($data, 'Delete');
                $ActivityModel->saveQueue();

                if ($this->_DeliveryType === DELIVERY_TYPE_ALL) {
                    redirectTo($target);
                }

                if ($target) {
                    $this->setRedirectTo($target);
                }

                $this->jsonTarget(".Section-DiscussionList #Discussion_$discussionID", null, 'SlideUp');
            }
        }

        $this->setData('Title', t('Delete Post'));
        $this->render();
    }

    /**
     * Allows user to delete a comment.
     *
     * If the comment is the only one in the discussion, the discussion will
     * be deleted as well. Users without administrative delete abilities
     * should not be able to delete a comment unless it is a draft. This is
     * a "hard" delete - it is removed from the database.
     *
     * @param int $commentID Unique comment ID.
     * @param string $transientKey Single-use hash to prove intent.
     */
    public function deleteComment(int $commentID, $transientKey = '') {
        $session = Gdn::session();
        $defaultTarget = '/discussions/';
        $validCommentID = is_numeric($commentID) && $commentID > 0;
        $validUser = $session->UserID > 0 && $session->validateTransientKey($transientKey);

        if ($validCommentID && $validUser) {
            // Get comment and discussion data
            $comment = $this->CommentModel->getID($commentID);
            $discussionID = val('DiscussionID', $comment);
            $discussion = $this->DiscussionModel->getID($discussionID);

            if ($comment && $discussion) {
                $defaultTarget = discussionUrl($discussion);

                // Make sure comment is this user's or they have Delete permission.
                $groupDelete = false;
                if ($comment->InsertUserID != $session->UserID || !c('Vanilla.Comments.AllowSelfDelete')) {
                    if (!is_null($discussion->GroupID)) {
                        $groupModel = new GroupModel;
                        $groupDelete = $groupModel->canModerate($discussion->GroupID, $session->UserID);
                    }
                    if (!$groupDelete) {
                        $this->categoryPermission($discussion->CategoryID, 'Vanilla.Comments.Delete');
                    }
                }

                // Make sure that content can (still) be edited.
                $editTimeout = 0;
                if (!CommentModel::canEdit($comment, $editTimeout, $discussion) && !$groupDelete) {
                    $this->categoryPermission($discussion->CategoryID, 'Vanilla.Comments.Delete');
                }

                // Delete the comment.
                if (!$this->CommentModel->deleteID($commentID)) {
                    $this->Form->addError('Failed to delete comment');
                }
            } else {
                $this->Form->addError('Invalid comment');
            }
        } else {
            $this->Form->addError('ErrPermission');
        }

        // Redirect
        if ($this->_DeliveryType == DELIVERY_TYPE_ALL) {
            $target = getIncomingValue('Target', $defaultTarget);
            redirectTo($target);
        }

        if ($this->Form->errorCount() > 0) {
            $this->setJson('ErrorMessage', $this->Form->errors());
        } else {
            \Gdn::config()->touch([
                'Preferences.Email.Delete' => 2,
                'Preferences.Popup.Delete' => 2,
            ]);
            $headlineFormat = sprintf(t('Your content has been deleted by a moderator %s', 'Your content has been deleted by a moderator <b>%s</b>'), $this->Form->getFormValue('DeleteMessage'));
            $data = [
                "ActivityType" => "Delete",
                "NotifyUserID" => $comment->InsertUserID,
                "HeadlineFormat" => $headlineFormat,
                "RecordType" => "Delete",
                "RecordID" => $commentID,
                "Route" => commentUrl($comment, "", "/"),
                "Data" => [
                    "Name" => 'comment',
                ],
            ];

            $ActivityModel = new ActivityModel();
            $ActivityModel->queue($data, 'Delete');
            $ActivityModel->saveQueue();

            $this->jsonTarget("#Comment_$commentID", '', 'SlideUp');
        }

        $this->render();
    }

    /**
     * Alternate version of Index that uses the embed master view.
     *
     * @param int $discussionID Unique identifier, if discussion has been created.
     * @param string $discussionStub Deprecated.
     * @param int $offset
     * @param int|false $limit
     */
    public function embed($discussionID = 0, $discussionStub = '', $offset = 0, $limit = false) {
        $this->title(t('Comments'));

        // Add theme data
        $this->Theme = c('Garden.CommentsTheme', $this->Theme);
        Gdn_Theme::section('Comments');

        // Force view options
        $this->MasterView = 'empty';
        $this->CanEditComments = false; // Don't show the comment checkboxes on the embed comments page

        // Add some css to help with the transparent bg on embedded comments
        if ($this->Head) {
            $this->Head->addString('<style>
body { background: transparent !important; }
</style>');
        }

        // Javascript files & options
        $this->addJsFile('jquery.gardenmorepager.js');
        $this->addJsFile('jquery.autosize.min.js');
        $this->addJsFile('discussion.js');
        $this->removeJsFile('autosave.js');
        $this->addDefinition('DoInform', '0'); // Suppress inform messages on embedded page.
        $this->addDefinition('SelfUrl', Gdn::request()->pathAndQuery());
        $this->addDefinition('Embedded', true);

        // Define incoming variables (prefer querystring parameters over method parameters)
        $discussionID = (is_numeric($discussionID) && $discussionID > 0) ? $discussionID : 0;
        $discussionID = getIncomingValue('vanilla_discussion_id', $discussionID);
        $offset = getIncomingValue('Offset', $offset);
        $limit = getIncomingValue('Limit', $limit);
        $vanilla_identifier = getIncomingValue('vanilla_identifier', '');

        // Only allow vanilla identifiers of 32 chars or less - md5 if larger
        if (strlen($vanilla_identifier) > 32) {
            $vanilla_identifier = md5($vanilla_identifier);
        }
        $vanilla_type = getIncomingValue('vanilla_type', 'page');
        $vanilla_url = getIncomingValue('vanilla_url', '');
        $vanilla_category_id = getIncomingValue('vanilla_category_id', '');
        $foreignSource = [
            'vanilla_identifier' => $vanilla_identifier,
            'vanilla_type' => $vanilla_type,
            'vanilla_url' => $vanilla_url,
            'vanilla_category_id' => $vanilla_category_id
        ];
        $this->setData('ForeignSource', $foreignSource);

        // Set comment sorting
        $sortComments = c('Garden.Embed.SortComments') == 'desc' ? 'desc' : 'asc';
        $this->setData('SortComments', $sortComments);

        // Retrieve the discussion record
        $discussion = false;
        if ($discussionID > 0) {
            $discussion = $this->DiscussionModel->getID($discussionID);
        } elseif ($vanilla_identifier != '' && $vanilla_type != '') {
            $discussion = $this->DiscussionModel->getForeignID($vanilla_identifier, $vanilla_type);
        }

        // Set discussion data if we have one for this page
        if ($discussion) {
            // Allow Vanilla.Comments.View to be defined to limit access to embedded comments only.
            // Otherwise, go with normal discussion view permissions. Either will do.
            $this->categoryPermission($discussion->CategoryID, ['Vanilla.Discussions.View', 'Vanilla.Comments.View'], false);

            $this->setData('Discussion', $discussion, true);
            $this->setData('DiscussionID', $discussion->DiscussionID, true);
            $this->title($discussion->Name);

            // Actual number of comments, excluding the discussion itself
            $actualResponses = $discussion->CountComments;

            // Define the query offset & limit
            if (!is_numeric($limit) || $limit < 0) {
                $limit = c('Garden.Embed.CommentsPerPage', 30);
            }

            $offsetProvided = $offset != '';
            list($offset, $limit) = offsetLimit($offset, $limit);
            $this->Offset = $offset;
            if (c('Vanilla.Comments.AutoOffset')) {
                if ($actualResponses <= $limit) {
                    $this->Offset = 0;
                }

                if ($this->Offset == $actualResponses) {
                    $this->Offset -= $limit;
                }
            } elseif ($this->Offset == '') {
                $this->Offset = 0;
            }

            if ($this->Offset < 0) {
                $this->Offset = 0;
            }

            // Set the canonical url to have the proper page title.
            $canonicalUrl = ($discussion->Attributes['CanonicalUrl'] ?? '');
            if (empty($canonicalUrl)) {
                $this->canonicalUrl(discussionUrl($discussion, pageNumber($this->Offset, $limit)));
            } else {
                $this->canonicalUrl($canonicalUrl);
            }

            // Load the comments.
            $currentOrderBy = $this->CommentModel->orderBy();
            if (stringBeginsWith(getValueR('0.0', $currentOrderBy), 'c.DateInserted')) {
                $this->CommentModel->orderBy('c.DateInserted '.$sortComments); // allow custom sort
            }
            $this->setData('Comments', $this->CommentModel->getByDiscussion($discussion->DiscussionID, $limit, $this->Offset), true);

            if (count($this->CommentModel->where()) > 0) {
                $actualResponses = false;
            }

            $this->setData('_Count', $actualResponses);

            // Build a pager
            $pagerFactory = new Gdn_PagerFactory();
            $this->EventArguments['PagerType'] = 'MorePager';
            $this->fireEvent('BeforeBuildPager');
            $this->Pager = $pagerFactory->getPager($this->EventArguments['PagerType'], $this);
            $this->Pager->ClientID = 'Pager';
            $this->Pager->MoreCode = 'More Comments';
            $this->Pager->configure(
                $this->Offset,
                $limit,
                $actualResponses,
                'discussion/embed/'.$discussion->DiscussionID.'/'.Gdn_Format::url($discussion->Name).'/%1$s'
            );
            $this->Pager->CurrentRecords = $this->Comments->numRows();
            $this->fireEvent('AfterBuildPager');
        }

        // Define the form for the comment input
        $this->Form = Gdn::factory('Form', 'Comment');
        $this->Form->Action = url('/post/comment/');
        $this->Form->addHidden('CommentID', '');
        $this->Form->addHidden('Embedded', 'true'); // Tell the post controller that this is an embedded page (in case there are custom views it needs to pick up from a theme).
        $this->Form->addHidden('DisplayNewCommentOnly', 'true'); // Only load/display the new comment after posting (don't load all new comments since the page last loaded).

        // Grab the page title
        if ($this->Request->get('title')) {
            $this->Form->setValue('Name', $this->Request->get('title'));
        }

        // Set existing DiscussionID for comment form
        if ($discussion) {
            $this->Form->addHidden('DiscussionID', $discussion->DiscussionID);
        }

        foreach ($foreignSource as $key => $val) {
            // Drop the foreign source information into the form so it can be used if creating a discussion
            $this->Form->addHidden($key, $val);

            // Also drop it into the definitions so it can be picked up for stashing comments
            $this->addDefinition($key, $val);
        }

        // Retrieve & apply the draft if there is one:
        $draft = false;
        if (Gdn::session()->UserID && $discussion) {
            $draftModel = new DraftModel();
            $draft = $draftModel->getByUser(Gdn::session()->UserID, 0, 1, $discussion->DiscussionID)->firstRow();
            $this->Form->addHidden('DraftID', $draft ? $draft->DraftID : '');
        }

        if ($draft) {
            $this->Form->setFormValue('Body', $draft->Body);
        } else {
            // Look in the session stash for a comment
            $stashComment = Gdn::session()->getPublicStash('CommentForForeignID_'.$foreignSource['vanilla_identifier']);
            if ($stashComment) {
                $this->Form->setValue('Body', $stashComment);
                $this->Form->setFormValue('Body', $stashComment);
            }
        }

        // Deliver JSON data if necessary
        if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
            if ($this->Discussion) {
                $this->setJson('LessRow', $this->Pager->toString('less'));
                $this->setJson('MoreRow', $this->Pager->toString('more'));
            }
            $this->View = 'comments';
        }

        // Ordering note for JS
        if ($sortComments == 'desc') {
            $this->addDefinition('PrependNewComments', '1');
        }

        // Report the discussion id so js can use it.
        if ($discussion) {
            $this->addDefinition('DiscussionID', $discussion->DiscussionID);
        }

        $this->fireEvent('BeforeDiscussionRender');
        $this->render();
    }

    /**
     * Redirect to the url specified by the discussion.
     * @param array|object $discussion
     */
    protected function redirectDiscussion($discussion) {
        $body = Gdn_Format::to(val('Body', $discussion), val('Format', $discussion));
        if (preg_match('`href="([^"]+)"`i', $body, $matches)) {
            $url = $matches[1];
            redirectTo($url, 301);
        }
    }

    /**
     * Re-fetch a discussion's content based on its foreign url.
     *
     * @param int $discussionID
     */
    public function refetchPageInfo($discussionID) {
        // Make sure we are posting back.
        if (!$this->Request->isAuthenticatedPostBack(true)) {
            throw permissionException('Javascript');
        }

        // Grab the discussion.
        $discussion = $this->DiscussionModel->getID($discussionID);

        if (!$discussion) {
            throw notFoundException('Discussion');
        }

        // Make sure the user has permission to edit this discussion.
        $this->categoryPermission($discussion->CategoryID, 'Vanilla.Discussions.Edit');

        $foreignUrl = valr('Attributes.ForeignUrl', $discussion);
        if (!$foreignUrl) {
            throw new Gdn_UserException(t("This discussion isn't associated with a url."));
        }

        $stub = $this->DiscussionModel->fetchPageInfo($foreignUrl, true);

        // Save the stub.
        $this->DiscussionModel->setField($discussionID, (array)$stub);

        // Send some of the stuff back.
        if (isset($stub['Name'])) {
            $this->jsonTarget('.PageTitle h1', Gdn_Format::text($stub['Name']));
        }
        if (isset($stub['Body'])) {
            $this->jsonTarget("#Discussion_$discussionID .Message", Gdn_Format::to($stub['Body'], $stub['Format']));
        }

        $this->informMessage('The page was successfully fetched.');

        $this->render('Blank', 'Utility', 'Dashboard');
    }

    protected function _setOpenGraph() {
        if (!$this->Head) {
            return;
        }
        $this->Head->addTag('meta', ['property' => 'og:type', 'content' => 'article']);
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
     * Display modal
     *
     */
    public function bad() {
        $this->View = 'bad';
        $this->render();
    }

    /**
     * Display modal
     *
     */
    public function confirmFollow($DiscussionID) {
        $this->setData('Discussion', $this->DiscussionModel->getID($DiscussionID), true);
        $this->View = 'confirmfollow';
        $this->addJsFile('confirmfollow.js');
        $this->render();
    }

    /**
     * Verify a comment.
     *
     * @throws Exception If the user cannot view the discussion.
     * @throws ServerException If the comment could not be created.
     */
    public function verify($commentID) {
        $this->permission('Garden.SignIn.Allow');

        $comment = $this->CommentModel->setVerified($commentID, Gdn::session()->UserID);
        if ($comment) {
            $this->View = 'verified';
        } else {

        }

        $this->render();
    }

    /**
     * Remove verification
     *
     * @throws Exception If the user cannot view the discussion.
     * @throws ServerException If the comment could not be created.
     */
    public function unverify($commentID) {
        $this->permission('Garden.SignIn.Allow');

        $comment = $this->CommentModel->removeVerification($commentID, Gdn::session()->UserID);
        $this->View = '';
        $this->render();
    }

    /**
     * Get a comment by its numeric ID.
     *
     * @param int $id The comment ID.
     * @throws NotFoundException if the comment could not be found.
     * @return array
     */
    public function commentByID($id) {
        $row = $this->CommentModel->getID($id, DATASET_TYPE_ARRAY);
        if (!$row) {
            throw new NotFoundException('Comment');
        }
        return $row;
    }

    /**
     * Get a discussion by its numeric ID.
     *
     * @param int $id The discussion ID.
     * @throws NotFoundException if the discussion could not be found.
     * @return array
     */
    public function discussionByID($id) {
        $row = $this->DiscussionModel->getID($id, DATASET_TYPE_ARRAY);
        if (!$row) {
            throw new NotFoundException('Discussion');
        }
        return $row;
    }

    // Social Sharing
    public function social($DiscussionID) {
        $this->setData('Discussion', $this->discussionByID($DiscussionID));
        $this->setData('DesktopTitle', 'Share your question!');
        $this->setData('MobileTitle', 'Share');
        $this->setData('SubTitle', 'Share it with your friends, they may have the explanation!');
        $this->setData('CopySubTitle', 'Asking yourself the same question? Share it with your friends, they may have the explanation!');

        $this->render($this->fetchViewLocation('socialsharing', 'discussion', 'vanilla'));
    }

    // discussion resolved
    public function resolved($DiscussionID) {
        $this->DiscussionModel->resolved($DiscussionID);
    }
}
