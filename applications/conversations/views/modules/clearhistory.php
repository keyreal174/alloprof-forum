<?php if (!defined('APPLICATION')) exit();
if ($this->ConversationID > 0)
    echo anchor(t('Leave the discussion'), '/messages/leave/'.$this->ConversationID, 'btn-default leave-conversation Popup');
