<?php if (!defined('APPLICATION')) exit();
include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));
$Session = Gdn::session();

$Alt = false;
$CurrentOffset = $this->Offset;
$Messages = $this->data('Messages', []);
foreach ($Messages as $Message) {
    $CurrentOffset++;
    $Alt = !$Alt;
    $Class = 'Item';
    $Class .= $Alt ? ' Alt' : '';
    if ($this->Conversation->DateLastViewed < $Message->DateInserted)
        $Class .= ' New';

    if ($Message->InsertUserID == $Session->UserID)
        $Class .= ' Mine';

    if ($Message->InsertPhoto != '')
        $Class .= ' HasPhoto';

    $Format = empty($Message->Format) ? 'Display' : $Message->Format;
    $Author = userBuilder($Message, 'Insert');

    $this->EventArguments['Message'] = &$Message;
    $this->EventArguments['Class'] = &$Class;
    $this->fireEvent('BeforeConversationMessageItem');
    $Class = trim($Class);

    $UserMetaData = Gdn::userModel()->getMeta(val('UserID', $Author), 'Profile.%', 'Profile.');
    $UserDisplayName = $UserMetaData['DisplayName'] ?? "";
    $badge = userExtraInfo(val('UserID', $Author))['badge'];
    ?>
    <li id="Message_<?php echo $Message->MessageID; ?>"<?php echo $Class == '' ? '' : ' class="'.$Class.'"'; ?>>
        <div id="Item_<?php echo $CurrentOffset ?>" class="ConversationMessage">
            <span class="Author">
                <?php
                echo userPhoto($Author, 'Photo');
                ?>
            </span>
            <div class="Message userContent">
                <div class="Meta">
                    <h3><?php echo $UserDisplayName.$badge; ?></h3>
                    <span class="MItem DateCreated"><?php echo Gdn_Format::date($Message->DateInserted, 'html'); ?></span>
                    <?php
                        $this->fireEvent('AfterConversationMessageDate');
                    ?>
                </div>
                <?php
                $this->fireEvent('BeforeConversationMessageBody');
                echo Gdn_Format::to($Message->Body, $Format);
                $this->EventArguments['Message'] = &$Message;
                $this->fireEvent('AfterConversationMessageBody');
                ?>
            </div>
        </div>
    </li>
<?php } ?>

<script>
    if (window.location.hash) {
        window.location.href = window.location.href.split('#')[0];
    }
</script>
