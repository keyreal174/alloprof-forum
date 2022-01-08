<?php if (!defined('APPLICATION')) exit(); ?>
<div class="leave-conversation-popup">
<h2><?php echo $this->data('Title'); ?></h2>

<?php
echo $this->Form->open();
echo $this->Form->errors();

echo '<div class="P">'.t('When you leave, you will no longer see the messages exchanged.<br/>
Do you want to leave the conversation? ').'</div>';

echo '<div class="Buttons Buttons-Confirm">';
echo $this->Form->button(t('Leave'), ['class' => 'btn-default btn-shadow']);
echo $this->Form->button('Cancel', ['type' => 'button', 'class' => 'Close btn-default']);
echo '</div>';
echo $this->Form->close();
echo '</div>';