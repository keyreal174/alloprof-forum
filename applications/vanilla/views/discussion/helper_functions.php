<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 */

if (!defined('APPLICATION')) {
    exit();
}
use Vanilla\Utility\HtmlUtils;
if (!function_exists('timeElapsedString'))
    include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));


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
                            <img src="'.url("/themes/alloprof/design/images/icons/verifiedbadge.svg").'"/>
                            <span>'.t("Answer verified by Alloprof").'</span>
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
        <?php
                if (Gdn::session()->isValid()) {
                ?>
        <div class="Options">
            <?php writeCommentOptions($comment); ?>
        </div>
        <?php } ?>
        <?php $sender->fireEvent('BeforeCommentMeta'); ?>
        <div class="Item-Header CommentHeader">
            <div class="AuthorWrap">
                <?php
                            if(!$comment->Published) {
                                echo '<div class="not-published-badge">';
                                echo '<img src="'.url("/themes/alloprof/design/images/icons/eyebreak.svg").'"/>';
                                echo t('Awaiting publication');
                                echo '</div>';
                            }
                        ?>
                <span class="Author">
                    <?php
                        if ($userPhotoFirst) {
                            echo userPhoto($author);
                            if ($sender->getUserRole($comment->InsertUserID) == "Teacher") {
                                $UserMetaData = Gdn::userModel()->getMeta($author->UserID, 'Profile.%', 'Profile.');
                                $name = $UserMetaData["DisplayName"] ?? "";
                                echo '<a class="Username js-userCard" style="display: flex;" data-userid="'.$author->UserID.'">'.$name.'<img class="TeacherCheckIcon" src="'.url("/themes/alloprof/design/images/icons/teacherCheck.svg").'" alt="teacher check"></a>';
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
                            if ($sender->getUserRole($comment->InsertUserID) === TEACHER_ROLE) {
                                echo '<span class="ItemGrade">'.t("Alloprof Teacher") . ' • </span>'. timeElapsedString($comment->DateInserted, false);
                            } else {
                                if ($grade) {
                                    echo '<span class="ItemGrade">'.$grade . ' • </span>' . timeElapsedString($comment->DateInserted, false);
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

        if ($session && $session->UserID == $discussion->InsertUserID && $sender->getUserRole() == 'member' && val('CountComments', $discussion) != 0)
            return [];

        if ($sender->getUserRole() === 'Teacher') {
            if (!$discussion->Published) {
                $logID = Gdn::sql()
                    ->select('LogID')
                    ->from('Log')
                    ->where('RecordID', $discussion->DiscussionID)
                    ->where('RecordType', 'Discussion')
                    ->get()
                    ->value('LogID', null);
                $options['ApprovePublication'] = ['Label' => '<svg width="24" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.29492 12C1.29492 5.92487 6.21979 1 12.2949 1C15.2123 1 18.0102 2.15893 20.0731 4.22183C22.136 6.28473 23.2949 9.08262 23.2949 12C23.2949 18.0751 18.3701 23 12.2949 23C6.21979 23 1.29492 18.0751 1.29492 12Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2"/>
                <path d="M7.79492 12L10.9769 15.182L17.3409 8.81802" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg><span>'.t('Approve the publication').'</span>', 'Url' => '#', 'Class' => 'RestoreButton', 'Id' => $logID];
            }
        }

        $flagLink = addFlagButtonToDropdown($discussion, 'discussion');
        if ($session && $session->UserID != $discussion->InsertUserID) {
            $options['FlagDiscussion'] = [
                'Label' => '<svg width="24" height="24" viewBox="0 0 15 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2979 3.175L5.41992 2.52175L2.49492 2.278V1.225C2.49492 0.686522 2.0584 0.25 1.51992 0.25C0.981444 0.25 0.544922 0.686522 0.544922 1.225V18.775C0.544922 19.3135 0.981444 19.75 1.51992 19.75C2.0584 19.75 2.49492 19.3135 2.49492 18.775V11.872L5.41992 11.6283L13.2979 10.975C13.8057 10.9342 14.1966 10.5094 14.1949 10V4.15C14.1966 3.64057 13.8057 3.21575 13.2979 3.175ZM4.44489 9.75602L2.49489 9.91201V4.23752L4.44489 4.39352V9.75602ZM8.34493 9.42452L6.39493 9.59027V4.55926L8.34493 4.72501V9.42452ZM12.245 9.10276L10.2949 9.26851V4.88101L12.245 5.04676V9.10276Z" fill="#EB5757"/>
                </svg><span>'.t($flagLink['name']).'</span>',
                'Url' => $flagLink['url'],
                'Class' => 'FlagContent SocialPopup FlagPopup'
            ];
        }

        // Build the $Options array based on current user's permission.
        // Can the user edit the discussion?
        $canEdit = DiscussionModel::canEdit($discussion, $timeLeft);
        if ($canEdit) {
            if ($timeLeft) {
                $timeLeft = ' ('.Gdn_Format::seconds($timeLeft).')';
            }
            $options['EditDiscussion'] = ['Label' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M23.6488 3.2648L20.7329 0.348935C20.2649 -0.116312 19.509 -0.116312 19.041 0.348935L12.3453 7.04462C12.1207 7.27107 11.9955 7.57763 11.9973 7.89658V10.8004C11.9973 11.4631 12.5345 12.0004 13.1973 12.0004H16.1011C16.4201 12.0022 16.7266 11.877 16.9531 11.6524L23.6488 4.95672C24.114 4.48869 24.114 3.73282 23.6488 3.2648ZM15.597 9.60073H14.3971V8.40079L19.8928 2.90505L21.0928 4.10499L15.597 9.60073ZM20.3969 12.0006C19.7342 12.0006 19.1969 12.5378 19.1969 13.2006C19.1969 17.8395 15.4363 21.6002 10.7973 21.6002H4.08966L4.85763 20.8442C5.08479 20.6189 5.21257 20.3122 5.21257 19.9922C5.21257 19.6723 5.08479 19.3656 4.85763 19.1403C2.4551 16.738 1.73635 13.125 3.03655 9.98608C4.33676 6.84721 7.39983 4.80071 10.7973 4.80095C11.4601 4.80095 11.9973 4.26372 11.9973 3.60101C11.9973 2.9383 11.4601 2.40107 10.7973 2.40107C6.65301 2.41469 2.88202 4.79877 1.09229 8.53675C-0.697448 12.2747 -0.190202 16.7072 2.39774 19.9442L0.34584 21.9481C0.00536261 22.2932 -0.0940553 22.8092 0.0938519 23.2561C0.277927 23.7042 0.71338 23.9976 1.1978 24H10.7973C16.7617 24 21.5968 19.1649 21.5968 13.2006C21.5968 12.5378 21.0596 12.0006 20.3969 12.0006Z" fill="black"/>
            </svg><span>'.t('Edit the publication').$timeLeft.'</span>', 'Url' => '/post/editdiscussion/'.$discussion->DiscussionID, 'Class' => 'EditDiscussion'];
            // $options['EditDiscussionPopup'] = ['Label' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            // <path fill-rule="evenodd" clip-rule="evenodd" d="M23.6488 3.2648L20.7329 0.348935C20.2649 -0.116312 19.509 -0.116312 19.041 0.348935L12.3453 7.04462C12.1207 7.27107 11.9955 7.57763 11.9973 7.89658V10.8004C11.9973 11.4631 12.5345 12.0004 13.1973 12.0004H16.1011C16.4201 12.0022 16.7266 11.877 16.9531 11.6524L23.6488 4.95672C24.114 4.48869 24.114 3.73282 23.6488 3.2648ZM15.597 9.60073H14.3971V8.40079L19.8928 2.90505L21.0928 4.10499L15.597 9.60073ZM20.3969 12.0006C19.7342 12.0006 19.1969 12.5378 19.1969 13.2006C19.1969 17.8395 15.4363 21.6002 10.7973 21.6002H4.08966L4.85763 20.8442C5.08479 20.6189 5.21257 20.3122 5.21257 19.9922C5.21257 19.6723 5.08479 19.3656 4.85763 19.1403C2.4551 16.738 1.73635 13.125 3.03655 9.98608C4.33676 6.84721 7.39983 4.80071 10.7973 4.80095C11.4601 4.80095 11.9973 4.26372 11.9973 3.60101C11.9973 2.9383 11.4601 2.40107 10.7973 2.40107C6.65301 2.41469 2.88202 4.79877 1.09229 8.53675C-0.697448 12.2747 -0.190202 16.7072 2.39774 19.9442L0.34584 21.9481C0.00536261 22.2932 -0.0940553 22.8092 0.0938519 23.2561C0.277927 23.7042 0.71338 23.9976 1.1978 24H10.7973C16.7617 24 21.5968 19.1649 21.5968 13.2006C21.5968 12.5378 21.0596 12.0006 20.3969 12.0006Z" fill="black"/>
            // </svg><span>'.t('Edit the publication').$timeLeft.'</span>', 'Url' => '/post/editQuestionPopup/'.$discussion->DiscussionID, 'Class' => 'EditDiscussion d-mobile QuestionPopup'];
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
        if (FALSE && $canEdit && $session->checkPermission('Garden.Moderation.Manage')) {
            $options['MoveDiscussion'] = [
                'Label' => t('Move'),
                'Url' => '/moderation/confirmdiscussionmoves?discussionid='.$discussion->DiscussionID,
                'Class' => 'MoveDiscussion Popup'
            ];
        }

        $canSelfDelete = ($session->UserID == $discussion->InsertUserID);
        // Can the user delete?
        if (CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Delete') || $canSelfDelete) {
            $category = CategoryModel::categories($categoryID);

            $targetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            if (strpos($_SERVER[REQUEST_URI], 'question') !== false || strpos($_SERVER[REQUEST_URI], 'discussion/') !== false) {
                $targetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]".url('/discussions');
            }

            $options['DeleteDiscussion'] = [
                'Label' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M21.4 4.8H16.6V3.6C16.6 1.61177 14.9882 0 13 0H10.6C8.61177 0 7 1.61177 7 3.6V4.8H2.2C1.53726 4.8 1 5.33726 1 6C1 6.66274 1.53726 7.2 2.2 7.2H3.4V20.4C3.4 22.3882 5.01178 24 7 24H16.6C18.5882 24 20.2 22.3882 20.2 20.4V7.2H21.4C22.0627 7.2 22.6 6.66274 22.6 6C22.6 5.33726 22.0627 4.8 21.4 4.8ZM9.40007 3.60031C9.40007 2.93757 9.93732 2.40031 10.6001 2.40031H13.0001C13.6628 2.40031 14.2001 2.93757 14.2001 3.60031V4.80031H9.40007V3.60031ZM17.8001 20.4C17.8001 21.0627 17.2629 21.6 16.6001 21.6H7.00013C6.33739 21.6 5.80013 21.0627 5.80013 20.4V7.19995H17.8001V20.4Z" fill="#333333"/>
                </svg><span>'.t('Delete the publication').'</span>',
                'Url' => '/discussion/delete?discussionid='.$discussion->DiscussionID.'&target='.$targetUrl,
                'Class' => 'DeleteDiscussion SocialPopup'
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

        if ($session && $session->UserID == $discussion->InsertUserID && $sender->getUserRole() == 'member' && val('CountComments', $discussion) != 0)
            return '';

        $discussionID = $discussion->DiscussionID;
        $categoryUrl = urlencode(categoryUrl(CategoryModel::categories($categoryID)));

        if ($sender->getUserRole() === 'Teacher') {
            if (!$discussion->Published) {
                $logID = Gdn::sql()
                    ->select('LogID')
                    ->from('Log')
                    ->where('RecordID', $discussion->DiscussionID)
                    ->where('RecordType', 'Discussion')
                    ->get()
                    ->value('LogID', null);
                $dropdown->addLInk('<span>'.t('Approve the publication').'</span>', $logID, '', 'RestoreButton', ['Id' => $logID]);
            }
        }

        // Permissions
        $canEdit = DiscussionModel::canEdit($discussion, $timeLeft);
        $canAnnounce = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Announce');
        $canSink = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Sink');
        $canClose = DiscussionModel::canClose($discussion);
        $canDelete = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Delete');
        $canMove = FALSE && $canEdit && $session->checkPermission('Garden.Moderation.Manage');
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
            ->addLinkIf($canEdit, t('Edit the publication').$timeLeft, '/post/editdiscussion/'.$discussionID, 'edit', 'EditDiscussion')
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
            if (FALSE && !empty(val('DateUpdated', $discussion))) {
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
                ->addLink(t('Delete the publication'), '/discussion/delete?discussionid='.$discussionID.'&target='.$categoryUrl, 'delete', 'DeleteDiscussion Popup');
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
        // deprecated('writeDiscussionOptions', 'getDiscussionOptionsDropdown', 'March 2016');

        $options = getDiscussionOptions($discussion);

        if (empty($options)) {
            return;
        }

        echo ' <span class="ToggleFlyout OptionsMenu">';
        echo '<span class="OptionsTitle" title="'.t('Options').'">'.t('Options').'</span>';
        echo sprite('SpFlyoutHandle', 'Arrow');
        echo '<span class="mobileFlyoutOverlay">';
        echo '<ul class="Flyout MenuItems" style="display: none;">';
        foreach ($options as $code => $option) {
            echo wrap("<a href='". url($option['Url']) . "' class='dd " . val('Class', $option, $code) . "' id='". val('Id', $option, $code) ."'>" . $option['Label'] ."</a>", 'li');
            // echo wrap(anchor($option['Label'], $option['Url'], val('Class', $option, $code)), 'li');
        }
        echo '</ul>';
        echo '</span>';
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
            if ($comment->Published) {
                if ($comment->DateAccepted) {
                    $options['QnA'] = ['Label' => '<svg width="24" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.29492 12C1.29492 5.92487 6.21979 1 12.2949 1C15.2123 1 18.0102 2.15893 20.0731 4.22183C22.136 6.28473 23.2949 9.08262 23.2949 12C23.2949 18.0751 18.3701 23 12.2949 23C6.21979 23 1.29492 18.0751 1.29492 12Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2"/>
                <path d="M7.79492 12L10.9769 15.182L17.3409 8.81802" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg><span>'.t('Remove verification').'</span>', 'Url' => 'javascript:;', 'Class' => 'mark-verify', 'Id' => url('/discussion/unverify?commentid='.$comment->CommentID)];
                } else {
                    $options['QnA'] = ['Label' => '<svg width="24" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.29492 12C1.29492 5.92487 6.21979 1 12.2949 1C15.2123 1 18.0102 2.15893 20.0731 4.22183C22.136 6.28473 23.2949 9.08262 23.2949 12C23.2949 18.0751 18.3701 23 12.2949 23C6.21979 23 1.29492 18.0751 1.29492 12Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2"/>
                <path d="M7.79492 12L10.9769 15.182L17.3409 8.81802" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg><span>'.t('Mark as verified').'</span>', 'Url' => 'javascript:;', 'Class' => 'mark-verify', 'Id' => url('/discussion/verify?commentid='.$comment->CommentID)];
                }
            } else {
                $logID = Gdn::sql()
                    ->select('LogID')
                    ->from('Log')
                    ->where('RecordID', $comment->CommentID)
                    ->where('RecordType', 'Comment')
                    ->get()
                    ->value('LogID', null);
                $options['ApprovePublication'] = ['Label' => '<svg width="24" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1.29492 12C1.29492 5.92487 6.21979 1 12.2949 1C15.2123 1 18.0102 2.15893 20.0731 4.22183C22.136 6.28473 23.2949 9.08262 23.2949 12C23.2949 18.0751 18.3701 23 12.2949 23C6.21979 23 1.29492 18.0751 1.29492 12Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2"/>
                <path d="M7.79492 12L10.9769 15.182L17.3409 8.81802" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg><span>'.t('Approve the publication').'</span>', 'Url' => '#', 'Class' => 'RestoreButton', 'Id' => $logID];
            }
        }

        // Can the user edit the comment?
        $canEdit = CommentModel::canEdit($comment, $timeLeft, $discussion);
        if ($canEdit) {
            if ($timeLeft) {
                $timeLeft = ' ('.Gdn_Format::seconds($timeLeft).')';
            }
            $options['EditComment'] = [
                'Label' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M23.6488 3.2648L20.7329 0.348935C20.2649 -0.116312 19.509 -0.116312 19.041 0.348935L12.3453 7.04462C12.1207 7.27107 11.9955 7.57763 11.9973 7.89658V10.8004C11.9973 11.4631 12.5345 12.0004 13.1973 12.0004H16.1011C16.4201 12.0022 16.7266 11.877 16.9531 11.6524L23.6488 4.95672C24.114 4.48869 24.114 3.73282 23.6488 3.2648ZM15.597 9.60073H14.3971V8.40079L19.8928 2.90505L21.0928 4.10499L15.597 9.60073ZM20.3969 12.0006C19.7342 12.0006 19.1969 12.5378 19.1969 13.2006C19.1969 17.8395 15.4363 21.6002 10.7973 21.6002H4.08966L4.85763 20.8442C5.08479 20.6189 5.21257 20.3122 5.21257 19.9922C5.21257 19.6723 5.08479 19.3656 4.85763 19.1403C2.4551 16.738 1.73635 13.125 3.03655 9.98608C4.33676 6.84721 7.39983 4.80071 10.7973 4.80095C11.4601 4.80095 11.9973 4.26372 11.9973 3.60101C11.9973 2.9383 11.4601 2.40107 10.7973 2.40107C6.65301 2.41469 2.88202 4.79877 1.09229 8.53675C-0.697448 12.2747 -0.190202 16.7072 2.39774 19.9442L0.34584 21.9481C0.00536261 22.2932 -0.0940553 22.8092 0.0938519 23.2561C0.277927 23.7042 0.71338 23.9976 1.1978 24H10.7973C16.7617 24 21.5968 19.1649 21.5968 13.2006C21.5968 12.5378 21.0596 12.0006 20.3969 12.0006Z" fill="black"/>
                </svg><span>'.t('Edit the comment').$timeLeft.'</span>',
                'Url' => '/post/editcomment/'.$comment->CommentID,
                'EditComment'
            ];
        }

        if (FALSE && $session->checkPermission('Garden.Moderation.Manage') && !empty(val('DateUpdated', $comment))) {
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
        $canSelfDelete = ($session->UserID == $comment->InsertUserID);
        if ($canDelete || $canSelfDelete) {
            $options['DeleteComment'] = [
                'Label' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M21.4 4.8H16.6V3.6C16.6 1.61177 14.9882 0 13 0H10.6C8.61177 0 7 1.61177 7 3.6V4.8H2.2C1.53726 4.8 1 5.33726 1 6C1 6.66274 1.53726 7.2 2.2 7.2H3.4V20.4C3.4 22.3882 5.01178 24 7 24H16.6C18.5882 24 20.2 22.3882 20.2 20.4V7.2H21.4C22.0627 7.2 22.6 6.66274 22.6 6C22.6 5.33726 22.0627 4.8 21.4 4.8ZM9.40007 3.60031C9.40007 2.93757 9.93732 2.40031 10.6001 2.40031H13.0001C13.6628 2.40031 14.2001 2.93757 14.2001 3.60031V4.80031H9.40007V3.60031ZM17.8001 20.4C17.8001 21.0627 17.2629 21.6 16.6001 21.6H7.00013C6.33739 21.6 5.80013 21.0627 5.80013 20.4V7.19995H17.8001V20.4Z" fill="#333333"/>
                </svg><span>'.t('Delete the publication').'</span>',
                'Url' => '/discussion/deletecomment/'.$comment->CommentID.'/'.$session->transientKey(),
                // '/discussion/deletecomment/'.$comment->CommentID.'/'.$session->transientKey().'/?Target='.urlencode("/discussion/{$comment->DiscussionID}/x"
                'Class' => 'DeleteComment SocialPopup'
            ];
        }

        $flagLink = addFlagButtonToDropdown($comment, 'comment');

        if ($session && $session->UserID != $comment->InsertUserID) {
            $options['FlagComment'] = [
                'Label' => '<svg width="15" height="20" viewBox="0 0 15 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2979 3.175L5.41992 2.52175L2.49492 2.278V1.225C2.49492 0.686522 2.0584 0.25 1.51992 0.25C0.981444 0.25 0.544922 0.686522 0.544922 1.225V18.775C0.544922 19.3135 0.981444 19.75 1.51992 19.75C2.0584 19.75 2.49492 19.3135 2.49492 18.775V11.872L5.41992 11.6283L13.2979 10.975C13.8057 10.9342 14.1966 10.5094 14.1949 10V4.15C14.1966 3.64057 13.8057 3.21575 13.2979 3.175ZM4.44489 9.75602L2.49489 9.91201V4.23752L4.44489 4.39352V9.75602ZM8.34493 9.42452L6.39493 9.59027V4.55926L8.34493 4.72501V9.42452ZM12.245 9.10276L10.2949 9.26851V4.88101L12.245 5.04676V9.10276Z" fill="#EB5757"/>
                </svg><span>'.t($flagLink['name']).'</span>',
                'Url' => $flagLink['url'],
                'Class' => $flagLink['type']
            ];
        }

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
                echo wrap("<a href='". url($option['Url']) . "' class='" . val('Class', $option, $code) . "' id='". val('Id', $option, $code) ."'>" . $option['Label'] ."</a>", 'li');
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
<!-- <div class="Foot Closed">
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
                </div> -->
<?php
            }
        }

        if (($discussion->Closed == '1') || ($discussion->Closed == '0')) {
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
            '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.4024 4.77036L14.257 17.1938C13.5127 19.4245 10.3904 19.5002 9.5386 17.3156L8.22255 13.9405C7.83589 12.9488 7.05163 12.1646 6.05998 11.7779L2.68482 10.4618C0.500266 9.61003 0.575902 6.48771 2.80661 5.74339L15.2301 1.59803C17.192 0.94339 19.057 2.80843 18.4024 4.77036Z" stroke="black" stroke-width="2"/>
                <path d="M7.7959 12.2044L12.8952 7.10511" stroke="black" stroke-width="2" stroke-linecap="round"/>
            </svg>',
            'discussion/social/'.$Discussion->DiscussionID,
            'SocialIcon SocialPopup',
            ['rel' => 'nofollow', 'title' => t("Share")]
        );

        echo wrap($socialLink, 'span', ['class' => 'MItem SocialLink']);
    }
endif;

if (!function_exists('writeDiscussionFooter')) :
    function writeDiscussionFooter($Discussion, $sender ,$page='') {
        $discussionUrl = $Discussion->Url;
        $Session = Gdn::session();
        $isUser = $Discussion->InsertUserID === Gdn::session()->UserID;
        $commentsCount = CommentModel::getPublishedCommentsCount($Discussion->DiscussionID);
        $userCanComment = CategoryModel::checkPermission($categoryID, 'Vanilla.Comments.Add');
        ?>
<div class="Item-Footer">
    <div class="Item-Footer-Icons DisableClick">
        <?php
                include($sender->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

                if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                        echo '<span class="Options">';
                        if (!$isUser) {
                            echo bookmarkButton($Discussion);
                        }
                        writeReactions($Discussion);
                        writeSocialSharing($Discussion);
                        echo '</span>';
                    }
                ?>
    </div>
    <div class="Item-Footer-Buttons">
        <?php
                    $commentsLabel = $commentsCount < 2 ? $commentsCount . ' ' . t('answer') : $commentsCount . ' ' . t('answers');
                    if ($commentsLabel == '0 answer') {
                        $commentsLabel = '0 answers';
                    }
                    if (!$sender->data('IsAnswer')) {
                        echo '<a class="btn-default" href="'.$discussionUrl.'">'.$commentsLabel.'</a>';
                    } else {
                        if ($Discussion->InsertUserID == Gdn::session()->UserID) {
                            echo '<a class="btn-default not-clickable">'.$commentsLabel.'</a>';
                        } else {
                            if (!function_exists('userRoleCheck'))
                                include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));
                            $teacherClass = "";
                            if(userRoleCheck() == Gdn::config('Vanilla.ExtraRoles.Teacher')) {
                                $teacherClass = " teacher ";
                            }

                            echo $Session->isValid()?'<div class="ReplyQuestionButton '.$teacherClass.' d-desktop">':'<a class="ReplyQuestionButton d-desktop'.$teacherClass.'">';

                            $sender->fireEvent('BeforeFormButtons');
                            $answerButton = '';
                            $answerButtonForMobile = null;

                            // if($userCanComment){
                                $answerButton = $sender->Form->button(t('Provide an answer'), ['class' => 'btn-default btn-shadow '.($Session->isValid()?'ReplyQuestionSubmitButton':'SignInStudentPopupAgent')]);
                                $answerButtonForMobile = anchor(t('Provide an answer'), '/post/answerPopup/'.$Discussion->DiscussionID, 'btn-default btn-shadow AnswerPopup QuestionPopup '.($Session->isValid()?'':'SignInStudentPopupAgent'));
                            // } else {
                            //     $answerButton = anchor(t('Provide an answer'), '/entry/signinstudent', 'btn-default btn-shadow');
                            //     $answerButtonForMobile = anchor(t('Provide an answer'), '/entry/signinstudent', 'btn-default btn-shadow');
                            // }

                            echo $answerButton;

                            $sender->fireEvent('AfterFormButtons');
                            echo $Session->isValid()?'</div>':'</a>';
                        }
                    }
                ?>
    </div>
</div>
<div class="Answer-Button d-mobile ReplyQuestionButton" . $teacherClass>
    <?php
                if($answerButtonForMobile) {
                    echo '<div class="answer-button-mobile">';
                    echo $answerButtonForMobile;
                    echo '</div>';
                }
            ?>
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
                $GradeOption = array_filter($field['Options'], function($v) {
                    return preg_match('/(Primaire|Secondaire|Post-secondaire)/', $v);
                });
                $GradeOption = array_map(function($val) {
                    return t($val);
                }, $GradeOption);
            }
        }
        return ($GradeID || $GradeID === 0) ? $GradeOption[$GradeID] : "";
    }
endif;

if (!function_exists('checkAnswer')) :

    function checkAnswer($sender) {
        $answered = $sender->Resolved !== 1 ? 'Hidden' : '';
        $noanswered = $sender->Resolved === 1 ? 'Hidden' : '';
        ?>
<div class="BoxCheckAnswer NotAnswered <?php echo $noanswered; ?>">
    <h2><?php echo t("Did you find the answer to your question?") ?></h2>
    <div class="BoxCheckAnswer-answers">
        <a class="FeedbackPerfect">
            <img src="<?= url('/themes/alloprof/design/images/peace.svg') ?>" width="80px" height="80px" />
            <span> <?php echo t("I sure did!") ?> </span>
        </a>
        <a href="<?= url('/discussion/bad') ?>" class="FeedbackHelp">
            <img src="<?= url('/themes/alloprof/design/images/neutre.svg') ?>" width="80px" height="80px" />
            <span> <?php echo t("I still need help") ?> </span>
        </a>
    </div>
</div>
<div class="BoxCheckAnswer Answered <?php echo $answered; ?>">
    <h2><?php echo t("The explanations have helped you!") ?></h2>
    <div class="BoxCheckAnswer-answers">
        <a class="">
            <img src="<?= url('/themes/alloprof/design/images/peace.svg') ?>" width="80px" height="80px" />
        </a>
    </div>
</div>
<?php
    }
endif;