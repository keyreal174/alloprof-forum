<?php
require_once $this->fetchViewLocation('helper_functions');
$role = getUserRole();
$button = '';

if($role != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
    $button = '<a href="/post/newQuestionPopup" class="btn-default btn-shadow QuestionPopup d-mobile">'.t('Ask a question').'</a>';
}

if (!$this->Empty) {
    ?>
    <div class="discussion-list-footer">
        <img src="<?= url('/themes/alloprof/design/images/full_of_questions.svg') ?>" />
        <p><?php echo t($this->Text1); ?></p>
        <p><?php echo t($this->Text2); ?></p>
    </div>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
    <?php

} else {
    ?>
    <div class="Empty discussion-list-footer">
        <img src="<?= url('/themes/alloprof/design/images/noquestions.svg') ?>" />
        <p><?php echo t($this->Text1); ?></p>
        <p><?php echo t($this->Text2); ?></p>
        <?php echo $button; ?>
    </div>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
<?php
}