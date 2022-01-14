<?php if (!defined('APPLICATION')) exit();
/** @var InThisConversationModule $this */
?>
<div class="InThisConversation">
    <?php echo '<h2 style="margin-bottom: 24px;">'.(t('In this discussion')).'</h2>'; ?>
    <ul class="PanelInfo">
        <?php foreach ($this->data('Participants') as $User): ?>
            <li data-userid="<?php echo $User->UserID ?? $User['UserID']; ?>">
                <?php
                $UserMetaData = Gdn::userModel()->getMeta(val('UserID', $User), 'Profile.%', 'Profile.');
                $UserDisplayName = $UserMetaData['DisplayName'] ?? "";
                $Username = htmlspecialchars($UserDisplayName);
                $Photo = val('Photo', $User);
                $userID = $user->UserID ?? $user['UserID'];


                if (val('Deleted', $User)) {
                    echo anchor(
                        wrap(
                            ($Photo ? img($Photo, ['class' => 'ProfilePhoto ProfilePhotoSmall']) : '').' '.
                            wrap($Username, 'del', ['class' => 'Username']),
                            'span', ['class' => 'Conversation-User',]
                        ),
                        userUrl($User),
                        [
                            'title' => sprintf(t('%s has left this conversation.'), $Username),
                            "data-userid"=> $userID
                        ]
                    );
                } else {
                    echo anchor(
                        wrap(
                            ($Photo ? img($Photo, ['class' => 'ProfilePhoto ProfilePhotoSmall']) : '').' '.
                            wrap($Username, 'span', ['class' => 'Username']),
                            'span', ['class' => 'Conversation-User']
                        ),
                        userUrl($User),
                        [
                            "data-userid"=> $userID
                        ]
                    );
                }
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
