<?php if (!defined('APPLICATION')) exit(); ?>
<div class="leave-conversation-popup">
<h2><?php echo $this->data('Title'); ?></h2>

<?php
echo $this->Form->open();
echo $this->Form->errors();

echo '<div class="P">'.t('When you leave, you will no longer see the messages exchanged.<br/>Do you want to leave the conversation?').'</div>';

echo '<div class="Buttons Buttons-Confirm">';
echo '<div class="d-desktop">'.$this->Form->button(t('Leave'), ['class' => 'btn-default btn-shadow']).'</div>';
echo '<div class="d-mobile">'.$this->Form->button(t('Leave'), ['class' => 'btn-default btn-shadow leave-conversation', 'type' => 'button']).'</div>';
echo '<div class="d-desktop">'.$this->Form->button('Cancel', ['type' => 'button', 'class' => 'Close btn-default']).'</div>';
echo '<div class="d-mobile">'.$this->Form->button('Cancel', ['type' => 'button', 'class' => 'mobile-close btn-default']).'</div>';

echo '</div>';
echo $this->Form->close();
echo '<div class="mobile-footer d-mobile"><span>×</span></div>';
echo '</div>';