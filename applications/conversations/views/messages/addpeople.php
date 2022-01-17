<?php if (!defined('APPLICATION')) exit(); 
include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));
?>
<div class="AddPeople">
    <?php
    echo '<h2>'.t('Manage the discussion').'</h2>';
    echo '<hr/>';
    echo $this->Form->open(['id' => 'Form_AddPeople']);
    echo wrap($this->Form->textBox('AddPeople', ['MultiLine' => true, 'class' => 'MultiComplete']), 'div', ['class' => 'TextBoxWrapper']);
    echo '<div class="Buttons">';
    echo '<div class="d-desktop">'.$this->Form->button(t('Add'), ['class' => 'btn-default Action add-people', 'type' => 'button']).'</div>';
    echo '<div class="d-mobile">'.$this->Form->button(t('Add'), ['class' => 'btn-default Action add-people mobile submit', 'type' => 'button']).'</div>';
    echo '</div>';
    echo $this->Form->close();
    ?>
    <hr class="d-mobile" />
    <div class="InThisConversation">
        <ul class="PanelInfo">
            <?php foreach ($this->data('Participants') as $User): ?>
                <li data-userid="<?php echo $User->UserID ?? $User['UserID']; ?>">
                    <?php
                    $UserMetaData = Gdn::userModel()->getMeta(val('UserID', $User), 'Profile.%', 'Profile.');
                    $Username = $UserMetaData['DisplayName'] ?? "";
                    $Photo = val('Photo', $User);
                    $userID = $User->UserID ?? $User['UserID'];
                    $badge = userExtraInfo($userID)['badge'];
                    $text = userExtraInfo($userID)['grade'];

                    if (val('Deleted', $User)) {
                        echo wrap(
                            ($Photo ? img($Photo, ['class' => 'ProfilePhoto']) : '').' '.
                            wrap('<span><span class="Name">'.$Username.$badge.'</span><span class="Grade">'.$text.'</span></span>', 'span', ['class' => 'Username']),
                            'del', ['class' => 'Conversation-User']
                        );
                    } else {
                        echo wrap(
                            ($Photo ? img($Photo, ['class' => 'ProfilePhoto']) : '').' '.
                            wrap('<span><span class="Name">'.$Username.$badge.'</span><span class="Grade">'.$text.'</span></span>', 'span', ['class' => 'Username']),
                            'span', ['class' => 'Conversation-User']
                        );
                    }
                    if($userID != Gdn::session()->UserID){
                        $deleteIcon = '<svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.613 3.937V16.09a1.736 1.736 0 0 1-1.736 1.736H5.196A1.736 1.736 0 0 1 3.46 16.09V3.937H1h17-2.387zM7.8 8.277v5.209M11.272 8.277v5.209" stroke="#000" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.298 4.068V2.622c0-.8-.648-1.447-1.447-1.447h-4.34c-.8 0-1.447.648-1.447 1.447v1.446" stroke="#000"/>
                        </svg>';
                        echo "<button class='delete-user d-desktop' data-conversation-id=".$this->data('Conversation')->ConversationID." data-user-id=".$userID.">".t('Remove')."</button>";
                        echo "<button class='delete-user d-mobile' data-conversation-id=".$this->data('Conversation')->ConversationID." data-user-id=".$userID.">".$deleteIcon."</button>";
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="mobile-footer d-mobile"><span>×</span></div>
</div>
