<?php

if (!$this->Empty) {
    ?>
    <div class="discussion-list-footer">
        <img src="/themes/alloprof/design/images/full_of_questions.svg" />
        <p><?php echo t('That\'s all for now!'); ?></p>
        <p><?php echo t('If you have other questions, don\'t hesitate to ask😉'); ?></p>
    </div>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
    <?php

} else {
    ?>
    <div class="Empty discussion-list-footer">
        <img src="/themes/alloprof/design/images/noquestions.svg" />
        <p><?php echo t('It seems there\'s nothing here at the moment!'); ?></p>
        <p><?php echo t('Don\'t hesitate ask if you have a question.'); ?></p>
    </div>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
<?php
}