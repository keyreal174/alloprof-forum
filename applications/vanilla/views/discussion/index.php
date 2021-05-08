<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
if (!function_exists('WriteComment'))
    include $this->fetchViewLocation('helper_functions', 'discussion');

if (!function_exists('checkAnswer'))
    include $this->fetchViewLocation('helper_functions', 'discussion');

if (!function_exists('userRoleCheck'))
    include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

// Wrap the discussion related content in a div.
echo '<div class="MessageList Discussion">';
echo '<div class="d-desktop goback-btn"><a href="'.url('/discussions').'">
<svg width="26" height="18" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M24.25 8.88715L1.75 8.88715" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M9.11842 16.2175L1.77539 8.87444L9.11842 1.53141" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg><span>'.t('Back').'</span></a></div>';
$this->fireEvent('AfterDiscussionTitle');
$this->fireEvent('AfterPageTitle');

// Write the initial discussion.
if ($this->data('Page') == 1) {
    include $this->fetchViewLocation('discussion', 'discussion');
    echo '</div>'; // close discussion wrap

    $this->fireEvent('AfterDiscussion');
} else {
    echo '</div>'; // close discussion wrap
}

writeCommentForm();

echo '<div class="CommentsWrap">';

// Write the comments.
// $this->Pager->Wrapper = '<span %1$s>%2$s</span>';
// echo '<span class="BeforeCommentHeading">';
// $this->fireEvent('CommentHeading');
// echo $this->Pager->toString('less');
// echo '</span>';

echo '<div class="DataBox DataBox-Comments">';
if ($this->data('Comments')->numRows() > 0) {
    echo $this->Form->open();
    $discussionUrl = $this->data('Discussion')->Url;
    echo '<div class="CommentHeadingWrapper">';
    echo '<h2 class="CommentHeading">'.$this->data('_CommentsHeader', t('Explanations')).' ('.CommentModel::getPublishedCommentsCount($this->Data['Discussion']->DiscussionID).')</h2>';
    echo '</div>';
    echo $this->Form->close();
}
?>
    <ul class="MessageList DataList Comments">
        <?php include $this->fetchViewLocation('comments'); ?>
    </ul>
<?php
$this->fireEvent('AfterComments');
if ($this->Pager->lastPage()) {
    $LastCommentID = $this->addDefinition('LastCommentID');
    if (!$LastCommentID || $this->Data['Discussion']->LastCommentID > $LastCommentID)
        $this->addDefinition('LastCommentID', (int)$this->Data['Discussion']->LastCommentID);
    $this->addDefinition('Vanilla_Comments_AutoRefresh', Gdn::config('Vanilla.Comments.AutoRefresh', 0));
}
echo '</div>';

echo '<div class="P PagerWrap">';
$this->Pager->Wrapper = '<div %1$s>%2$s</div>';
echo $this->Pager->toString('more');
echo '</div>';
echo '</div>';
echo $this->data['Published'];

if(userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')
    && $this->Data['Discussion']->Published && $this->Data['Discussion']->InsertUserID == Gdn::session()->UserID
    && CommentModel::getPublishedCommentsCount($this->Data['Discussion']->DiscussionID) > 0) {
    echo checkAnswer($this->Data['Discussion']);
}

if (!$this->Data['Discussion']->Published && userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
    echo '<div class="question-not-published">';
    echo '<img src="'.url("/themes/alloprof/design/images/question_not_approved.svg").'"/>';
    echo '<p>'.t('Your question will be reviewed by a moderator.').'</p>';
    echo '<p>'.t('You will be notified as soon as it is published!').'</p>';
    echo '</div>';
}
