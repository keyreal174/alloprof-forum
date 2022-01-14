<?php if (!defined('APPLICATION')) exit(); ?>
<div class="AddPeople">
    <?php
    echo '<h2>'.t('Manage the discussion').'</h2>';
    echo '<hr/>';
    echo $this->Form->open(['id' => 'Form_AddPeople']);
    echo wrap($this->Form->textBox('AddPeople', ['MultiLine' => true, 'class' => 'MultiComplete']), 'div', ['class' => 'TextBoxWrapper']);
    echo $this->Form->close(t('Add'), '', ['class' => 'btn-default Action']);
    ?>
    <div class="InThisConversation">
        <ul class="PanelInfo">
            <?php foreach ($this->data('Participants') as $User): ?>
                <li>
                    <?php
                    $UserMetaData = Gdn::userModel()->getMeta(val('UserID', $User), 'Profile.%', 'Profile.');
                    $Username = $UserMetaData['DisplayName'] ?? "";
                    $Photo = val('Photo', $User);
                    $userID = $user->UserID ?? $user['UserID'];


                    if (val('Deleted', $User)) {
                        echo anchor(
                            wrap(
                                ($Photo ? img($Photo, ['class' => 'ProfilePhoto']) : '').' '.
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
                        // echo anchor(
                        //     wrap(
                        //         ($Photo ? img($Photo, ['class' => 'ProfilePhoto']) : '').' '.
                        //         wrap($Username, 'span', ['class' => 'Username']),
                        //         'span', ['class' => 'Conversation-User']
                        //     ),
                        //     userUrl($User),
                        //     [
                        //         "data-userid"=> $userID
                        //     ]
                        // );
                        echo wrap(
                            ($Photo ? img($Photo, ['class' => 'ProfilePhoto']) : '').' '.
                            wrap($Username, 'span', ['class' => 'Username']),
                            'span', ['class' => 'Conversation-User']
                        );
                    }
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
