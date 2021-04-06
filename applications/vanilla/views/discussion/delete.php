<?php if (!defined('APPLICATION')) exit(); ?>
<div class="DeleteDiscussionPopup">
<h1><?php echo $this->data('Title'); ?></h1>

<?php
echo $this->Form->open();
echo $this->Form->errors();

echo '<div class="P">'.t('You are about to delete the post. It will no longer be visible to users of the Mutual Aid Zone. <br/><br/>You can leave a comment on the reason for the deletion to the author.').'</div>';
echo '<div class="DeleteTextBox">';
echo $this->Form->textBox('DeleteMessage', ['MultiLine' => TRUE]);
echo '</div>';
echo '<div class="Buttons Buttons-Confirm">';
echo $this->Form->button(t('Remove'), ['class' => 'btn-default btn-shadow']);
echo $this->Form->button(t('Cancel'), ['type' => 'button', 'class' => 'Close btn-default']);
echo '</div>';
echo $this->Form->close();
?>
</div>
