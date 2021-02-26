<?php

if (!$this->Empty) {
    ?>
    <div class="discussion-list-footer">
        <img src="/themes/alloprof/design/images/full_of_questions.svg" />
        <p><?php echo t($this->Text1); ?></p>
        <p><?php echo t($this->Text2); ?></p>
    </div>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
    <?php

} else {
    ?>
    <div class="Empty discussion-list-footer">
        <img src="/themes/alloprof/design/images/noquestions.svg" />
        <p><?php echo t($this->Text1); ?></p>
        <p><?php echo t($this->Text2); ?></p>
    </div>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
<?php
}