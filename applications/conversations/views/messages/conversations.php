<?php if (!defined('APPLICATION')) exit();
include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));
$Session = Gdn::session();
$Alt = false;
$SubjectsVisible = c('Conversations.Subjects.Visible');

foreach ($this->data('Conversations') as $Conversation) {
    $Conversation = (object)$Conversation;
    $Alt = !$Alt;


    // Figure out the last photo.
    $LastPhoto = '';
    $LastUser = '';
    if (empty($Conversation->Participants)) {
        $User = Gdn::userModel()->getID($Conversation->LastInsertUserID);
        $LastPhoto = userPhoto($User);
    } else {
        foreach ($Conversation->Participants as $User) {
            if ($User['UserID'] == $Conversation->LastInsertUserID) {
                $LastPhoto = userPhoto($User);
                $LastUser = $User;
                if ($LastPhoto)
                    break;
            } elseif (!$LastPhoto) {
                $LastPhoto = userPhoto($User);
            }
        }
    }

    $UserMetaData = Gdn::userModel()->getMeta($LastUser['UserID'], 'Profile.%', 'Profile.');
    $lastusername = $UserMetaData['DisplayName'] ?? "";

    $CssClass = 'Item';
    $CssClass .= $Alt ? ' Alt' : '';
    $CssClass .= $Conversation->CountNewMessages > 0 ? ' New' : '';
    $CssClass .= $LastPhoto != '' ? ' HasPhoto' : '';
    $CssClass .= ' '.($Conversation->CountNewMessages <= 0 ? 'Read' : 'Unread');

    $JumpToItem = $Conversation->CountMessages - $Conversation->CountNewMessages;

    if (stringIsNullOrEmpty(trim($Conversation->LastBody))) {
        $Message = t('Blank Message');
    } else {
        $Message = (sliceString(Gdn::formatService()->renderExcerpt($Conversation->LastBody, $Conversation->LastFormat), 100));
    }

    $this->EventArguments['Conversation'] = $Conversation;
    ?>
    <li class="<?php echo $CssClass; ?>" id="Conversation_<?php echo $Conversation->ConversationID; ?>">
        <?php
        $Names = ConversationModel::participantTitle($Conversation, false);
        ?>
        <div class="ItemContent Conversation">
            <?php
            $Url = '/messages/'.$Conversation->ConversationID.'/#Item_'.$JumpToItem;
            $session = Gdn::session();
            echo '<div class="Header d-desktop">';
            echo '<h2>'.htmlspecialchars($Names).'</h2>';
            echo anchor('<svg width="26" height="18" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24.25 8.88715L1.75 8.88715" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9.11842 16.2175L1.77539 8.87444L9.11842 1.53141" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>', $Url);

            echo '</div>';

            echo '<a class="mobile-detail-link d-mobile InboxMessagePopup" href="'.url($Url).'"></a>';
            
            echo '<div class="d-desktop"><hr/></div>';

            if ($Names) {
                if ($LastPhoto) {
                    echo '<div class="Author Photo">'.$LastPhoto.'</div>';
                }
            }
            if ($Subject = val('Subject', $Conversation)) {
                if ($Names) {
                    echo bullet(' ');
                }
                echo '<span class="Subject">'.anchor(htmlspecialchars($Subject), $Url).'</span>';
            }

            ?>
            <div class="Content">
                <!-- <div class="User"><?php echo anchor(htmlspecialchars($Names), $Url); ?></div> -->
                <div class="User">
                    <?php 
                        $badge = userExtraInfo($LastUser['UserID'])['badge'];
                        echo $lastusername.$badge;
                    ?>
                    <div class="Meta">
                        <?php
                        $this->fireEvent('BeforeConversationMeta');

                        // echo ' <span class="MItem CountMessages">'.sprintf(plural($Conversation->CountMessages, '%s message', '%s messages'), $Conversation->CountMessages).'</span> ';

                        if ($Conversation->CountNewMessages > 0) {
                            echo ' <strong class="HasNew"> '.plural($Conversation->CountNewMessages, '%s new', '%s new').'</strong> ';
                        }

                        echo ' <span class="MItem LastDateInserted">'.Gdn_Format::date($Conversation->LastDateInserted).'</span> ';
                        ?>
                    </div>
                </div>
                <div class="Excerpt"><?php echo htmlspecialchars($Message); ?></div>
            </div>
        </div>
    </li>
<?php
}
