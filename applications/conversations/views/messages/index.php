<?php
if (!defined('APPLICATION')) exit();
include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));
?>
    <div class="d-desktop back-home">
        <a href="<?php echo url('/messages/inbox'); ?>">
            <svg width="26" height="18" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24.25 8.88715L1.75 8.88715" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9.11842 16.2175L1.77539 8.87444L9.11842 1.53141" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>
            <?php echo t('Back to my discussions'); ?>
            </span>
        </a>
        <hr/>
    </div>
    <div class="DataListWrap">
        <h2 class="H conversation-header">
            <?php
            $Names = ConversationModel::participantTitle($this->data('Conversation'), false);
            echo $Names;

            // if ($this->data('Conversation.Subject')) {
            //     echo
            //         bullet(' ').
            //         '<span class="Gloss">'.htmlspecialchars($this->data('Conversation.Subject')).'</span>';
            // }
            ?>
            <?php if(userRoleCheck() == Gdn::config('Vanilla.ExtraRoles.Teacher')) {
                ?>
                <a href="<?php echo url('/messages/addPeople/'.$this->data('Conversation.ConversationID')); ?>" class="AddToConversationPopup add-people">
                    <svg style="width: 24px;" viewBox="0 0 25 25" class="header__avatar ng-tns-c83-1 ng-star-inserted"><g transform="translate(18.000000, 18.000000)" class="ng-tns-c83-1"><path d="M2-3c2.8,0,5,2.2,5,5S4.8,7,2,7h-15c-2.8,0-5-2.2-5-5s2.2-5,5-5H2z M-5.5-17c3,0,5.5,2.5,5.5,5.5
                    S-2.5-6-5.5-6S-11-8.5-11-11.5S-8.5-17-5.5-17z" class="login__state ng-tns-c83-1" style="fill: transparent"></path><path d="M2-3c2.8,0,5,2.2,5,5S4.8,7,2,7h-15c-2.8,0-5-2.2-5-5s2.2-5,5-5H2z M2-2h-15c-2.2,0-4,1.8-4,4
                    s1.8,4,4,4H2c2.2,0,4-1.8,4-4S4.2-2,2-2z M-5.5-18c3.6,0,6.5,2.9,6.5,6.5S-1.9-5-5.5-5S-12-7.9-12-11.5S-9.1-18-5.5-18z M-5.5-17
                    c-3,0-5.5,2.5-5.5,5.5S-8.5-6-5.5-6S0-8.5,0-11.5S-2.5-17-5.5-17z" class="login__stroke ng-tns-c83-1"></path></g></svg>
                    <span>+</span>
                </a>
            <?php } ?>
        </h2>
        <hr/>
        <?php

        if ($this->data('Conversation.Type')) {
            $this->fireEvent('Conversation'.str_replace('_', '', $this->data('Conversation.Type')));
        }

        if ($this->data('_HasDeletedUsers')) {
            echo '<div class="Info">', t('One or more users have left this conversation.', 'One or more users have left this conversation. They won\'t receive any more messages unless you add them back in to the conversation.'), '</div>';
        }
        $this->fireEvent('BeforeConversation');
        echo $this->Pager->toString('less');
        ?>
        <ul class="DataList MessageList Conversation">
            <?php
                if(count($this->data('Messages', [])) > 0) {
                    $MessagesViewLocation = $this->fetchViewLocation('messages');
                    include($MessagesViewLocation);
                } else {
                    echo '<li class="empty">'.t('Write your first message to start the discussion!').'</li>';
                }
            ?>
        </ul>
        <?php
            echo Gdn::controller()->fetchView('addmessage');
        ?>
    </div>
<?php
echo $this->Pager->toString();
