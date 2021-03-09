<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 */

if (!defined('APPLICATION')) {
    exit();
}
use Vanilla\Utility\HtmlUtils;


if (!function_exists('formatBody')) :
    /**
     * Format content of comment or discussion.
     *
     * Event argument for $object will be 'Comment' or 'Discussion'.
     *
     * @since 2.1
     * @param DataSet $object Comment or discussion.
     * @return string Parsed body.
     */
    function formatBody($object) {
        Gdn::controller()->fireEvent('BeforeCommentBody');
        $object->FormatBody = Gdn_Format::to($object->Body, $object->Format);
        Gdn::controller()->fireEvent('AfterCommentFormat');

        return $object->FormatBody;
    }
endif;

if (!function_exists('writeBookmarkLink')) :
    /**
     * Output link to (un)boomark a discussion.
     */
    function writeBookmarkLink() {
        if (!Gdn::session()->isValid()) {
            return '';
        }

        $discussion = Gdn::controller()->data('Discussion');
        $isBookmarked = $discussion->Bookmarked == '1';

        // Bookmark link
        $title = t($isBookmarked ? 'Unbookmark' : 'Bookmark');

        $accessibleLabel= HtmlUtils::accessibleLabel('%s for discussion: "%s"', [t($isBookmarked? 'Unbookmark' : 'Bookmark'), is_array($discussion) ? $discussion["Name"] : $discussion->Name]);

        echo anchor(
            $title,
            '/discussion/bookmark/'.$discussion->DiscussionID.'/'.Gdn::session()->transientKey().'?Target='.urlencode(Gdn::controller()->SelfUrl),
            'Hijack Bookmark'.($isBookmarked ? ' Bookmarked' : ''),
            ['title' => $title, 'aria-label' => $accessibleLabel, 'id' => 'followButton'.$discussion->DiscussionID]
        );
    }
endif;

if (!function_exists('writeComment')) :
    /**
     * Outputs a formatted comment.
     *
     * Prior to 2.1, this also output the discussion ("FirstComment") to the browser.
     * That has moved to the discussion.php view.
     *
     * @param DataSet $comment .
     * @param Gdn_Controller $sender .
     * @param Gdn_Session $session .
     * @param int $CurrentOffet How many comments into the discussion we are (for anchors).
     */
    function writeComment($comment, $sender, $session, $currentOffset) {
        // Whether to order the name & photo with the latter first.
        static $userPhotoFirst = null;

        $comment = (is_array($comment)) ? (object)$comment: $comment;

        if ($userPhotoFirst === null) {
            $userPhotoFirst = c('Vanilla.Comment.UserPhotoFirst', true);
        }
        $author = Gdn::userModel()->getID($comment->InsertUserID); //UserBuilder($Comment, 'Insert');
        $permalink = val('Url', $comment, '/discussion/comment/'.$comment->CommentID.'/#Comment_'.$comment->CommentID);

        // Set CanEditComments (whether to show checkboxes)
        if (!property_exists($sender, 'CanEditComments')) {
            $sender->CanEditComments = $session->checkPermission('Vanilla.Comments.Edit', true, 'Category', 'any') && c('Vanilla.AdminCheckboxes.Use');
        }
        // Prep event args
        $cssClass = cssClass($comment, false);
        $sender->EventArguments['Comment'] = &$comment;
        $sender->EventArguments['Author'] = &$author;
        $sender->EventArguments['CssClass'] = &$cssClass;
        $sender->EventArguments['CurrentOffset'] = $currentOffset;
        $sender->EventArguments['Permalink'] = $permalink;

        // Needed in writeCommentOptions()
        if ($sender->data('Discussion', null) === null) {
            $discussionModel = new DiscussionModel();
            $discussion = $discussionModel->getID($comment->DiscussionID);
            $sender->setData('Discussion', $discussion);
        }

        if ($sender->data('Discussion.InsertUserID') === $comment->InsertUserID) {
            $cssClass .= ' isOriginalPoster';
        }

        if ($comment->DateAccepted) {
            $cssClass .= ' Accepted';
        }

        if ($sender->getUserRole($comment->InsertUserID) === 'Teacher') {
            $cssClass .= ' TeacherComment ';
        }

        // DEPRECATED ARGUMENTS (as of 2.1)
        $sender->EventArguments['Object'] = &$comment;
        $sender->EventArguments['Type'] = 'Comment';

        $userId = Gdn::session()->UserID;

        // First comment template event
        $sender->fireEvent('BeforeCommentDisplay');

        ?>
        <li class="<?php echo $cssClass; ?>" id="<?php echo 'Comment_'.$comment->CommentID; ?>">
            <?php
                if ($comment->DateAccepted) {
                    echo '<div class="verfied-info">
                            <img src="/themes/alloprof/design/images/icons/verifiedbadge.svg"/>
                            <span>'.t("Explanation verified by Alloprof").'</span>
                        </div>';
                }
            ?>
            <div class="Comment">
                <?php
                // Write a stub for the latest comment so it's easy to link to it from outside.
                if ($currentOffset == Gdn::controller()->data('_LatestItem') && Gdn::config('Vanilla.Comments.AutoOffset')) {
                    echo '<span id="latest"></span>';
                }
                ?>
                <div class="Options">
                    <?php writeCommentOptions($comment); ?>
                </div>
                <?php $sender->fireEvent('BeforeCommentMeta'); ?>
                <div class="Item-Header CommentHeader">
                    <div class="AuthorWrap">
                        <?php
                            if(!$comment->Published) {
                                echo '<div class="not-published-badge">';
                                echo '<img src="/themes/alloprof/design/images/icons/eyebreak.svg"/>';
                                echo t('Awaiting publication');
                                echo '</div>';
                            }
                        ?>
                        <span class="Author">
                        <?php
                        if ($userPhotoFirst) {
                            echo userPhoto($author);
                            if ($sender->getUserRole($comment->InsertUserID) == "Teacher") {
                                echo '<a href="/profile/'.$author->Name.'" class="Username js-userCard" data-userid="'.$author->UserID.'">'.$author->Name.'<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1.25 8.5C1.25 4.77208 4.27208 1.75 8 1.75C9.79021 1.75 11.5071 2.46116 12.773 3.72703C14.0388 4.9929 14.75 6.70979 14.75 8.5C14.75 12.2279 11.7279 15.25 8 15.25C4.27208 15.25 1.25 12.2279 1.25 8.5Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2.5"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.46978 7.96967C4.76268 7.67678 5.23755 7.67678 5.53044 7.96967L7.12143 9.56066L10.8337 5.84835C11.1266 5.55546 11.6015 5.55546 11.8944 5.84835C12.1873 6.14124 12.1873 6.61612 11.8944 6.90901L7.65176 11.1517C7.35887 11.4445 6.884 11.4445 6.5911 11.1517L4.46978 9.03033C4.17689 8.73744 4.17689 8.26256 4.46978 7.96967Z" fill="white"/>
                                </svg></a>';
                            } else {
                                echo userAnchor($author, 'Username');
                            }
                        } else {
                            echo userAnchor($author, 'Username');
                            echo userPhoto($author);
                        }
                        echo formatMeAction($comment);
                        $sender->fireEvent('AuthorPhoto');
                        ?>
                        </span>
                        <span class="AuthorInfo">
                        <?php
                        echo ' '.wrapIf(htmlspecialchars(val('Title', $author)), 'span', ['class' => 'MItem AuthorTitle']);
                        echo ' '.wrapIf(htmlspecialchars(val('Location', $author)), 'span', ['class' => 'MItem AuthorLocation']);
                        $sender->fireEvent('AuthorInfo');
                        ?>
                        </span>
                    </div>
                    <div class="Meta CommentMeta CommentInfo">
                        <span class="MItem TimeAgo">
                        <?php
                            $grade = getGrade($comment->GradeID);
                            if ($sender->getUserRole($comment->InsertUserID) === 'Teacher') {
                                echo t("Alloprof Teacher") . ' • ' . timeElapsedString($comment->DateInserted, false);
                            } else {
                                if ($grade) {
                                    echo $grade . ' • ' . timeElapsedString($comment->DateInserted, false);
                                } else {
                                    echo timeElapsedString($comment->DateInserted, false);
                                }
                            }
                        ?>
                        </span>
                        <?php
                        echo dateUpdated($comment, ['<span class="MItem">', '</span>']);
                        ?>
                        <?php
                        // Include source if one was set
                        if ($source = val('Source', $comment)) {
                            echo wrap(sprintf(t('via %s'), t($source.' Source', $source)), 'span', ['class' => 'MItem Source']);
                        }

                        // $sender->fireEvent('CommentInfo');
                        $sender->fireEvent('InsideCommentMeta'); // DEPRECATED
                        $sender->fireEvent('AfterCommentMeta'); // DEPRECATED
                        ?>
                    </div>
                </div>
                <div class="Item-BodyWrap">
                    <div class="Item-Body">
                        <div class="Message userContent">
                            <?php
                            echo formatBody($comment);
                            ?>
                        </div>
                        <?php
                        $sender->fireEvent('AfterCommentBody');
                        if (val('Attachments', $comment)) {
                            writeAttachments($comment->Attachments);
                        }
                        writeReactions($comment);
                        ?>
                    </div>
                </div>
            </div>
        </li>
        <?php
        $sender->fireEvent('AfterComment');
    }
endif;

if (!function_exists('discussionOptionsToDropdown')):
    /**
     * @param array $options
     * @param DropdownModule|null $dropdown
     * @return DropdownModule
     */
    function discussionOptionsToDropdown(array $options, $dropdown = null) {
        if (is_null($dropdown)) {
            $dropdown = new DropdownModule('dropdown', '', 'OptionsMenu');
        }

        if (!empty($options)) {
            foreach ($options as $option) {
                $dropdown->addLink(($option['Label'] ?? ''), ($option['Url'] ?? ''), NavModule::textToKey(($option['Label'] ?? '')), ($option['Class'] ?? false));
            }
        }

        return $dropdown;
    }
endif;

if (!function_exists('getDiscussionOptions')) :
    /**
     * Get options for the current discussion.
     *
     * @since 2.1
     * @param DataSet $discussion .
     * @return array $options Each element must include keys 'Label' and 'Url'.
     */
    function getDiscussionOptions($discussion = null) {
        $options = [];

        $sender = Gdn::controller();
        $session = Gdn::session();

        if ($discussion == null) {
            $discussion = $sender->data('Discussion');
        }
        $categoryID = val('CategoryID', $discussion);
        if (!$categoryID && property_exists($sender, 'Discussion')) {
            $categoryID = val('CategoryID', $sender->Discussion);
        }

        // Build the $Options array based on current user's permission.
        // Can the user edit the discussion?
        $canEdit = DiscussionModel::canEdit($discussion, $timeLeft);
        if ($canEdit) {
            if ($timeLeft) {
                $timeLeft = ' ('.Gdn_Format::seconds($timeLeft).')';
            }
            $options['EditDiscussion'] = ['Label' => t('Edit').$timeLeft, 'Url' => '/post/editdiscussion/'.$discussion->DiscussionID];
        }

        // Can the user announce?
        if (CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Announce')) {
            $options['AnnounceDiscussion'] = [
                'Label' => t('Announce'),
                'Url' => '/discussion/announce?discussionid='.$discussion->DiscussionID.'&Target='.urlencode($sender->SelfUrl.'#Head'),
                'Class' => 'AnnounceDiscussion Popup'
            ];
        }

        // Can the user sink?
        if (CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Sink')) {
            $newSink = (int)!$discussion->Sink;
            $options['SinkDiscussion'] = [
                'Label' => t($discussion->Sink ? 'Unsink' : 'Sink'),
                'Url' => "/discussion/sink?discussionid={$discussion->DiscussionID}&sink=$newSink",
                'Class' => 'SinkDiscussion Hijack'
            ];
        }

        // Can the user close?
        if (CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Close')) {
            $newClosed = (int)!$discussion->Closed;
            $options['CloseDiscussion'] = [
                'Label' => t($discussion->Closed ? 'Reopen' : 'Close'),
                'Url' => "/discussion/close?discussionid={$discussion->DiscussionID}&close=$newClosed",
                'Class' => 'CloseDiscussion Hijack'
            ];
        }

        if ($canEdit && valr('Attributes.ForeignUrl', $discussion)) {
            $options['RefetchPage'] = [
                'Label' => t('Refetch Page'),
                'Url' => '/discussion/refetchpageinfo.json?discussionid='.$discussion->DiscussionID,
                'Class' => 'RefetchPage Hijack'
            ];
        }

        // Can the user move?
        if ($canEdit && $session->checkPermission('Garden.Moderation.Manage')) {
            $options['MoveDiscussion'] = [
                'Label' => t('Move'),
                'Url' => '/moderation/confirmdiscussionmoves?discussionid='.$discussion->DiscussionID,
                'Class' => 'MoveDiscussion Popup'
            ];
        }

        // Can the user delete?
        if (CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Delete')) {
            $category = CategoryModel::categories($categoryID);
            $options['DeleteDiscussion'] = [
                'Label' => t('Delete Discussion'),
                'Url' => '/discussion/delete?discussionid='.$discussion->DiscussionID.'&target='.urlencode(categoryUrl($category)),
                'Class' => 'DeleteDiscussion DeleteDiscussionPopup'
            ];
        }

        // DEPRECATED (as of 2.1)
        $sender->EventArguments['Type'] = 'Discussion';

        // Allow plugins to add options.
        $sender->EventArguments['DiscussionOptions'] = &$options;
        $sender->EventArguments['Discussion'] = $discussion;
        $sender->fireEvent('DiscussionOptions');

        return $options;
    }
endif;

if (!function_exists('addFlagButtonToDropdown')):
    function addFlagButtonToDropdown($data, $context = 'comment') {
        if (!in_array($context, ["comment", "discussion"])) {
            return;
        }

        $elementID = ($context == 'comment') ? $data->CommentID : $data->DiscussionID;

        if (!isset(Gdn::session()->UserID)) {
            $elementAuthorID = 0;
            $elementAuthor = 'Unknown';
            $isAllowed = false;
        } else {
            $elementAuthorID = $data->InsertUserID;
            $User = Gdn::userModel()->getID($elementAuthorID);
            $elementAuthor = $User->Name;
            $isAllowed = true;
        }

        $flagLink = [
            isAllowed => $isAllowed,
            name => t('Report post'),
            url => "discussion/flag/{$context}/{$elementID}/{$elementAuthorID}/".Gdn_Format::url($elementAuthor),
            type => 'FlagContent FlagContentPopup'
        ];
        // echo wrap($flagLink, 'span', ['class' => 'MItem CommentFlag']);
        return $flagLink;
    }
endif;


if (!function_exists('getDiscussionOptionsDropdown')):
    /**
     * Constructs an options dropdown menu for a discussion.
     *
     * @param object|array|null $discussion The discussion to get the dropdown options for.
     * @return DropdownModule A dropdown consisting of discussion options.
     * @throws Exception
     */
    function getDiscussionOptionsDropdown($discussion = null) {
        $dropdown = new DropdownModule('dropdown', '', 'OptionsMenu');
        $sender = Gdn::controller();
        $session = Gdn::session();

        if ($discussion == null) {
            $discussion = $sender->data('Discussion');
        }

        $categoryID = val('CategoryID', $discussion);

        if (!$categoryID && property_exists($sender, 'Discussion')) {
            trace('Getting category ID from controller Discussion property.');
            $categoryID = val('CategoryID', $sender->Discussion);
        }

        $discussionID = $discussion->DiscussionID;
        $categoryUrl = urlencode(categoryUrl(CategoryModel::categories($categoryID)));

        // Permissions
        $canEdit = DiscussionModel::canEdit($discussion, $timeLeft);
        $canAnnounce = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Announce');
        $canSink = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Sink');
        $canClose = DiscussionModel::canClose($discussion);
        $canDelete = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Delete');
        $canMove = $canEdit && $session->checkPermission('Garden.Moderation.Manage');
        $canRefetch = $canEdit && valr('Attributes.ForeignUrl', $discussion);
        $canDismiss = c('Vanilla.Discussions.Dismiss', 1)
            && $discussion->Announce
            && !$discussion->Dismissed
            && $session->isValid();
        $canTag = c('Tagging.Discussions.Enabled') && checkPermission('Vanilla.Tagging.Add') && in_array(strtolower($sender->ControllerName), ['discussionscontroller', 'categoriescontroller']) ;

        if ($canEdit && $timeLeft) {
            $timeLeft = ' ('.Gdn_Format::seconds($timeLeft).')';
        }

        $flagLink = addFlagButtonToDropdown($discussion, 'discussion');

        $dropdown->addLInkIf($flagLink['isAllowed'], $flagLink['name'], $flagLink['url'], 'FlagMenuItem', $flagLink['type'])
            ->addLinkIf($canDismiss, t('Dismiss'), "vanilla/discussion/dismissannouncement?discussionid={$discussionID}", 'dismiss', 'DismissAnnouncement Hijack')
            ->addLinkIf($canEdit, t('Edit').$timeLeft, '/post/editdiscussion/'.$discussionID, 'edit')
            ->addLinkIf($canTag, t('Tag'), '/discussion/tag?discussionid='.$discussionID, 'tag', 'TagDiscussion Popup');

        if ($canEdit && $canAnnounce) {
            $dropdown->addDivider();
        }

        $dropdown
            ->addLinkIf($canAnnounce, t('Announce'), '/discussion/announce?discussionid='.$discussionID, 'announce', 'AnnounceDiscussion Popup')
            ->addLinkIf($canSink, t($discussion->Sink ? 'Unsink' : 'Sink'), '/discussion/sink?discussionid='.$discussionID.'&sink='.(int)!$discussion->Sink, 'sink', 'SinkDiscussion Hijack')
            ->addLinkIf($canClose, t($discussion->Closed ? 'Reopen' : 'Close'), '/discussion/close?discussionid='.$discussionID.'&close='.(int)!$discussion->Closed, 'close', 'CloseDiscussion Hijack')
            ->addLinkIf($canRefetch, t('Refetch Page'), '/discussion/refetchpageinfo.json?discussionid='.$discussionID, 'refetch', 'RefetchPage Hijack')
            ->addLinkIf($canMove, t('Move'), '/moderation/confirmdiscussionmoves?discussionid='.$discussionID, 'move', 'MoveDiscussion Popup');

        $hasDiv = false;
        if ($session->checkPermission('Garden.Moderation.Manage')) {
            if (!empty(val('DateUpdated', $discussion))) {
                $hasDiv = true;
                $dropdown
                    ->addDivider()
                    ->addLink(
                        t('Revision History'),
                        '/log/filter?' . http_build_query(['recordType' => 'discussion', 'recordID' => $discussionID]),
                        'discussionRevisionHistory',
                        'RevisionHistory'
                    );
            }
            $dropdown
                ->addDividerIf(!$hasDiv)
                ->addLink(
                    t('Deleted Comments'),
                    '/log/filter?'.http_build_query(['parentRecordID' => $discussionID, 'recordType' => 'comment', 'operation' => 'delete']),
                    'deletedComments',
                    'DeletedComments'
                );
        }

        if ($canDelete) {
            $dropdown
                ->addDivider()
                ->addLink(t('Delete Discussion'), '/discussion/delete?discussionid='.$discussionID.'&target='.$categoryUrl, 'delete', 'DeleteDiscussion Popup');
        }

        // DEPRECATED
        $options = [];
        $sender->EventArguments['DiscussionOptions'] = &$options;
        $sender->EventArguments['Discussion'] = $discussion;
        $sender->fireEvent('DiscussionOptions');

        // Backwards compatibility
        $dropdown = discussionOptionsToDropdown($options, $dropdown);

        // Allow plugins to edit the dropdown.
        $sender->EventArguments['DiscussionOptionsDropdown'] = &$dropdown;
        $sender->EventArguments['Discussion'] = $discussion;
        $sender->fireEvent('DiscussionOptionsDropdown');

        return $dropdown;
    }
endif;

/**
 * Output moderation checkbox.
 *
 * @since 2.1
 */
if (!function_exists('WriteAdminCheck')):
    function writeAdminCheck($object = null) {
        if (!Gdn::controller()->CanEditComments || !c('Vanilla.AdminCheckboxes.Use')) {
            return;
        }
        echo '<span class="AdminCheck"><input type="checkbox" aria-label="'.t("Select Discussion").'" name="Toggle"></span>';
    }
endif;

/**
 * Output discussion options.
 *
 * @since 2.1
 */
if (!function_exists('writeDiscussionOptions')):
    function writeDiscussionOptions($discussion = null) {
        deprecated('writeDiscussionOptions', 'getDiscussionOptionsDropdown', 'March 2016');

        $options = getDiscussionOptions($discussion);

        if (empty($options)) {
            return;
        }

        echo ' <span class="ToggleFlyout OptionsMenu">';
        echo '<span class="OptionsTitle" title="'.t('Options').'">'.t('Options').'</span>';
        echo sprite('SpFlyoutHandle', 'Arrow');
        echo '<ul class="Flyout MenuItems" style="display: none;">';
        foreach ($options as $code => $option) {
            echo wrap(anchor($option['Label'], $option['Url'], val('Class', $option, $code)), 'li');
        }
        echo '</ul>';
        echo '</span>';
    }
endif;

if (!function_exists('getCommentOptions')) :
    /**
     * Get comment options.
     *
     * @since 2.1
     * @param object $comment The comment to get the options for.
     * @return array $options Each element must include keys 'Label' and 'Url'.
     */
    function getCommentOptions($comment) {
        $options = [];

        if (!is_numeric(val('CommentID', $comment))) {
            return $options;
        }

        $sender = Gdn::controller();
        $session = Gdn::session();
        $discussion = Gdn::controller()->data('Discussion');

        $categoryID = val('CategoryID', $discussion);

        if ($sender->getUserRole() === 'Teacher') {
            if ($comment->DateAccepted) {
                $options['QnA'] = ['Label' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.29492 12C1.29492 5.92487 6.21979 1 12.2949 1C15.2123 1 18.0102 2.15893 20.0731 4.22183C22.136 6.28473 23.2949 9.08262 23.2949 12C23.2949 18.0751 18.3701 23 12.2949 23C6.21979 23 1.29492 18.0751 1.29492 12Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2"/>
            <path d="M7.79492 12L10.9769 15.182L17.3409 8.81802" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg><span>'.t('Remove verification').'</span>', 'Url' => 'javascript:;', 'Class' => 'mark-verify', 'Id' => '/discussion/unverify?commentid='.$comment->CommentID];
            } else {
                $options['QnA'] = ['Label' => '<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.29492 12C1.29492 5.92487 6.21979 1 12.2949 1C15.2123 1 18.0102 2.15893 20.0731 4.22183C22.136 6.28473 23.2949 9.08262 23.2949 12C23.2949 18.0751 18.3701 23 12.2949 23C6.21979 23 1.29492 18.0751 1.29492 12Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2"/>
            <path d="M7.79492 12L10.9769 15.182L17.3409 8.81802" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg><span>'.t('Mark as verified').'</span>', 'Url' => 'javascript:;', 'Class' => 'mark-verify', 'Id' => '/discussion/verify?commentid='.$comment->CommentID];
            }

        }

        // Can the user edit the comment?
        $canEdit = CommentModel::canEdit($comment, $timeLeft, $discussion);
        if ($canEdit) {
            if ($timeLeft) {
                $timeLeft = ' ('.Gdn_Format::seconds($timeLeft).')';
            }
            $options['EditComment'] = [
                'Label' => '<svg width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5853 3.61017C15.1032 3.90423 15.7614 3.72517 16.0589 3.20926C16.3022 2.77995 16.8048 2.56923 17.2815 2.69669C17.7582 2.82415 18.0886 3.2576 18.0851 3.75103C18.0851 4.34945 17.6 4.83457 17.0016 4.83457C16.4032 4.83457 15.9181 5.31969 15.9181 5.91811C15.9181 6.51653 16.4032 7.00165 17.0016 7.00165C18.5788 7.00063 19.9278 5.86752 20.201 4.31413C20.4742 2.76073 19.5929 1.23535 18.1106 0.696217C16.6284 0.157085 14.973 0.759795 14.1844 2.12572C14.0397 2.37551 14.0006 2.6727 14.0759 2.95138C14.1512 3.23007 14.3345 3.4672 14.5853 3.61017ZM19.2446 11.3356C18.6524 11.2588 18.1096 11.6755 18.031 12.2675C17.5605 16.0683 14.3303 18.9221 10.5004 18.9204H4.44342L5.14772 18.2161C5.56783 17.7935 5.56783 17.1109 5.14772 16.6883C2.98737 14.5195 2.34256 11.2646 3.51288 8.43589C4.6832 5.60722 7.4392 3.75936 10.5004 3.75086C11.0988 3.75086 11.5839 3.26574 11.5839 2.66732C11.5839 2.0689 11.0988 1.58378 10.5004 1.58378C6.76216 1.59937 3.36183 3.75059 1.74656 7.12188C0.131296 10.4932 0.585408 14.4911 2.91563 17.4143L1.06278 19.2346C0.755331 19.5462 0.665557 20.0122 0.835236 20.4157C1.00145 20.8203 1.39466 21.0853 1.83209 21.0875H10.5004C15.4133 21.0881 19.5596 17.434 20.1764 12.56C20.2164 12.2739 20.1405 11.9836 19.9655 11.7537C19.7906 11.5238 19.531 11.3733 19.2446 11.3356ZM17.4133 8.16115C17.216 8.07388 16.9972 8.04747 16.7849 8.08531L16.5898 8.15032L16.3948 8.24784L16.2322 8.3887C16.1348 8.48854 16.0575 8.60628 16.0047 8.73543C15.9406 8.87058 15.9109 9.01945 15.918 9.16885C15.9149 9.31335 15.9407 9.45703 15.9939 9.59143C16.0499 9.72147 16.1309 9.83927 16.2322 9.93816C16.4367 10.141 16.7136 10.2541 17.0016 10.2524C17.6 10.2524 18.0851 9.76727 18.0851 9.16885C18.0888 9.02671 18.0591 8.88569 17.9984 8.7571C17.882 8.49673 17.6737 8.28842 17.4133 8.17199V8.16115Z" fill="black"/>
                </svg><span>'.t('Edit the post').$timeLeft.'</span>',
                'Url' => '/post/editcomment/'.$comment->CommentID,
                'EditComment'
            ];
        }

        if ($session->checkPermission('Garden.Moderation.Manage') && !empty(val('DateUpdated', $comment))) {
            $options['RevisionHistory'] = [
                'Label' => t('Revision History'),
                'Url' => '/log/filter?' . http_build_query(['recordType' => 'comment', 'recordID' => $comment->CommentID]),
                'RevisionHistory',
            ];
        }

        // Can the user delete the comment?
        $canDelete = CategoryModel::checkPermission(
            $categoryID,
            'Vanilla.Comments.Delete'
        );
        $canSelfDelete = ($canEdit && $session->UserID == $comment->InsertUserID && c('Vanilla.Comments.AllowSelfDelete'));
        if ($canDelete || $canSelfDelete) {
            $options['DeleteComment'] = [
                'Label' => t('Delete'),
                'Url' => '/discussion/deletecomment/'.$comment->CommentID.'/'.$session->transientKey().'/?Target='.urlencode("/discussion/{$comment->DiscussionID}/x"),
                'Class' => 'DeleteComment'
            ];
        }

        $flagLink = addFlagButtonToDropdown($comment, 'comment');
        $options['FlagComment'] = [
            'Label' => '<svg width="15" height="20" viewBox="0 0 15 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2979 3.175L5.41992 2.52175L2.49492 2.278V1.225C2.49492 0.686522 2.0584 0.25 1.51992 0.25C0.981444 0.25 0.544922 0.686522 0.544922 1.225V18.775C0.544922 19.3135 0.981444 19.75 1.51992 19.75C2.0584 19.75 2.49492 19.3135 2.49492 18.775V11.872L5.41992 11.6283L13.2979 10.975C13.8057 10.9342 14.1966 10.5094 14.1949 10V4.15C14.1966 3.64057 13.8057 3.21575 13.2979 3.175ZM4.44489 9.75602L2.49489 9.91201V4.23752L4.44489 4.39352V9.75602ZM8.34493 9.42452L6.39493 9.59027V4.55926L8.34493 4.72501V9.42452ZM12.245 9.10276L10.2949 9.26851V4.88101L12.245 5.04676V9.10276Z" fill="#EB5757"/>
            </svg><span>'.t($flagLink['name']).'</span>',
            'Url' => $flagLink['url'],
            'Class' => $flagLink['type']
        ];

        // DEPRECATED (as of 2.1)
        $sender->EventArguments['Type'] = 'Comment';

        // Allow plugins to add options
        $sender->EventArguments['CommentOptions'] = &$options;
        $sender->EventArguments['Comment'] = $comment;
        $sender->fireEvent('CommentOptions');

        return $options;
    }
endif;

if (!function_exists('writeCommentOptions')) :
    /**
     * Output comment options.
     *
     * @since 2.1
     * @param DataSet $comment
     */
    function writeCommentOptions($comment) {
        $controller = Gdn::controller();
        $session = Gdn::session();

        $id = $comment->CommentID;
        $options = getCommentOptions($comment);

        echo '<span class="ToggleFlyout OptionsMenu">';
        echo '<span class="OptionsTitle" title="'.t('Options').'">'.t('Options').'</span>';
        echo sprite('SpFlyoutHandle', 'Arrow');
        echo '<ul class="Flyout MenuItems CommentOptions">';

        if (!empty($options)) {
            foreach ($options as $code => $option) {
                echo wrap("<a href='". $option['Url'] . "' class='" . val('Class', $option, $code) . "' id='". val('Id', $option, $code) ."'>" . $option['Label'] ."</a>", 'li');
                // anchor($option['Label'], $option['Url'], val('Class', $option, $code), val('Id', $option, $code))
            }
        }
        echo '</ul>';
        echo '</span>';
        if (c('Vanilla.AdminCheckboxes.Use')) {
            // Only show the checkbox if the user has permission to affect multiple items
            $discussion = Gdn::controller()->data('Discussion');
            if (CategoryModel::checkPermission(val('CategoryID', $discussion), 'Vanilla.Comments.Delete')) {
                if (!property_exists($controller, 'CheckedComments')) {
                    $controller->CheckedComments = $session->getAttribute('CheckedComments', []);
                }
                $itemSelected = inSubArray($id, $controller->CheckedComments);
                echo '<span class="AdminCheck"><input type="checkbox" aria-label="'.t("Select Discussion").'" name="'.'Comment'.'ID[]" value="'.$id.'"'.($itemSelected ? ' checked="checked"' : '').' /></span>';
            }
        }

    }
endif;

if (!function_exists('writeCommentForm')) :
    /**
     * Output comment form.
     *
     * @since 2.1
     */
    function writeCommentForm() {
        $session = Gdn::session();
        $controller = Gdn::controller();

        $discussion = $controller->data('Discussion');
        $categoryID = val('CategoryID', $discussion);
        $userCanClose = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Close');
        $userCanComment = CategoryModel::checkPermission($categoryID, 'Vanilla.Comments.Add');

        // Closed notification
        if ($discussion->Closed == '1') {
            ?>
            <div class="Foot Closed">
                <div class="Note Closed"><?php echo t('This discussion has been closed.'); ?></div>
            </div>
        <?php
        } elseif (!$userCanComment) {
            if (!Gdn::session()->isValid()) {
                ?>
                <div class="Foot Closed">
                    <div class="Note Closed SignInOrRegister"><?php
                        $popup = (c('Garden.SignIn.Popup')) ? ' class="Popup"' : '';
                        $returnUrl = Gdn::request()->pathAndQuery();
                        echo formatString(
                            t('Sign In or Register to Comment.', '<a href="{SignInUrl,html}"{Popup}>Sign In</a> or <a href="{RegisterUrl,html}">Register</a> to comment.'),
                            [
                                'SignInUrl' => url(signInUrl($returnUrl)),
                                'RegisterUrl' => url(registerUrl($returnUrl)),
                                'Popup' => $popup
                            ]
                        ); ?>
                    </div>
                    <?php //echo anchor(t('All Discussions'), 'discussions', 'TabLink'); ?>
                </div>
            <?php
            }
        }

        if (($discussion->Closed == '1' && $userCanClose) || ($discussion->Closed == '0' && $userCanComment)) {
            echo $controller->fetchView('comment', 'post', 'vanilla');
        }
    }
endif;

if (!function_exists('writeCommentFormHeader')) :
    /**
     *
     */
    function writeCommentFormHeader() {
        $session = Gdn::session();
        if (c('Vanilla.Comment.UserPhotoFirst', true)) {
            echo userPhoto($session->User);
            echo userAnchor($session->User, 'Username');
        } else {
            echo userAnchor($session->User, 'Username');
            echo userPhoto($session->User);
        }
    }
endif;

if (!function_exists('writeEmbedCommentForm')) :
    /**
     *
     */
    function writeEmbedCommentForm() {
        $session = Gdn::session();
        $controller = Gdn::controller();
        $discussion = $controller->data('Discussion');

        if ($discussion && $discussion->Closed == '1') {
            ?>
            <div class="Foot Closed">
                <div class="Note Closed"><?php echo t('This discussion has been closed.'); ?></div>
            </div>
        <?php } else { ?>
            <h2><?php echo t('Leave a comment'); ?></h2>
            <div class="MessageForm CommentForm EmbedCommentForm">
                <?php
                echo '<div class="FormWrapper">';
                echo $controller->Form->open(['id' => 'Form_Comment']);
                echo $controller->Form->errors();
                echo $controller->Form->hidden('Name');
                echo wrap($controller->Form->bodyBox('Body'));
                echo "<div class=\"Buttons\">\n";

                $allowSigninPopup = c('Garden.SignIn.Popup');
                $attributes = ['target' => '_top'];

                // If we aren't ajaxing this call then we need to target the url of the parent frame.
                $returnUrl = $controller->data('ForeignSource.vanilla_url', Gdn::request()->pathAndQuery());
                $returnUrl = trim($returnUrl, '/').'#vanilla-comments';

                if ($session->isValid()) {
                    $authenticationUrl = url(signOutUrl($returnUrl), true);
                    echo wrap(
                        sprintf(
                            t('Commenting as %1$s (%2$s)', 'Commenting as %1$s <span class="SignOutWrap">(%2$s)</span>'),
                            Gdn_Format::text($session->User->Name),
                            anchor(t('Sign Out'), $authenticationUrl, 'SignOut', $attributes)
                        ),
                        'div',
                        ['class' => 'Author']
                    );
                    echo $controller->Form->button('Post Comment', ['class' => 'Button CommentButton']);
                } else {
                    $authenticationUrl = url(signInUrl($returnUrl), true);
                    if ($allowSigninPopup) {
                        $cssClass = 'SignInPopup Button Stash';
                    } else {
                        $cssClass = 'Button Stash';
                    }

                    echo anchor(t('Comment As ...'), $authenticationUrl, $cssClass, $attributes);
                }
                echo "</div>\n";
                echo $controller->Form->close();
                echo '</div> ';
                ?>
            </div>
        <?php
        }
    }
endif;

if (!function_exists('isMeAction')) :
    /**
     *
     *
     * @param $row
     * @return bool|void
     */
    function isMeAction($row) {
        if (!c('Garden.Format.MeActions')) {
            return;
        }
        $row = (array)$row;
        if (!array_key_exists('Body', $row)) {
            return false;
        }

        return strpos(trim($row['Body']), '/me ') === 0;
    }
endif;

if (!function_exists('formatMeAction')) :
    /**
     *
     *
     * @param $comment
     * @return string|void
     */
    function formatMeAction($comment) {
        if (!isMeAction($comment) || !c('Garden.Format.MeActions')) {
            return;
        }

        // Maxlength (don't let people blow up the forum)
        $comment->Body = substr($comment->Body, 4);
        $maxlength = c('Vanilla.MeAction.MaxLength', 100);
        $body = formatBody($comment);
        if (strlen($body) > $maxlength) {
            $body = substr($body, 0, $maxlength).'...';
        }

        return '<div class="AuthorAction">'.$body.'</div>';
    }
endif;

if (!function_exists('writeSocialSharing')) :
    function writeSocialSharing($Discussion) {
        $socialLink = anchor(
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.212 1.72792C20.7227 0.890167 23.1096 3.27703 22.2718 5.78774L17.2974 20.6959C16.3451 23.5498 12.3495 23.6477 11.2592 20.8514L9.67995 16.8012C9.23627 15.6634 8.33639 14.7635 7.19853 14.3198L3.14834 12.7405C0.352028 11.6502 0.449971 7.6546 3.30385 6.70235L18.212 1.72792Z" stroke="black" stroke-width="2"/>
                <path d="M9.35449 14.6455L15.4737 8.52631" stroke="black" stroke-width="2" stroke-linecap="round"/>
            </svg>',
            'discussion/social/'.$Discussion->DiscussionID,
            'SocialIcon SocialPopup'
        );

        echo wrap($socialLink, 'span', ['class' => 'MItem SocialLink']);
    }
endif;

if (!function_exists('writeDiscussionFooter')) :
    function writeDiscussionFooter($Discussion, $sender ,$page='') {
        $discussionUrl = $Discussion->Url;
        ?>
        <div class="Item-Footer">
            <div class="Item-Footer-Icons">
                <?php
                include($sender->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

                if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                        echo '<span class="Options">';
                        echo bookmarkButton($Discussion);
                        writeReactions($Discussion);
                        writeSocialSharing($Discussion);
                        echo '</span>';
                    }
                ?>
                <?php
                    if ($page !== 'search') {
                ?>
                <div class="Separator"></div>
                <span class="Response">
                    <?php
                        echo $Discussion->CountComments . ' ' . 'réponses';
                    ?>
                </span>
                <?php } ?>
            </div>
            <div>
                <?php
                    if (!$sender->data('IsAnswer')) {
                        echo '<a class="btn-default" href="'.$discussionUrl.'">'.t('See').'</a>';
                    } else {
                        echo '<div class="ReplyQuestionButton">';

                        $sender->fireEvent('BeforeFormButtons');
                        echo $sender->Form->button('Reply', ['class' => 'btn-default btn-shadow']);
                        $sender->fireEvent('AfterFormButtons');
                        echo '</div>';
                    }
                ?>
            </div>
        </div>
        <?php
    }
endif;

if (!function_exists('getGrade')) :

    function getGrade($GradeID) {
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
        return ($GradeID || $GradeID === 0) ? $GradeOption[$GradeID] : "";
    }
endif;
