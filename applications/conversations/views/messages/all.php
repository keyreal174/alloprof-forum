<?php if (!defined('APPLICATION')) exit();
include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));
?>
<div class="d-mobile">
    <div class="modal-header">
        <h3><?php echo t("Conversations"); ?></h3>
    </div>
</div>

<div class="d-desktop back-home">
    <a href="<?php echo url('/discussions'); ?>">
        <svg width="26" height="18" viewBox="0 0 26 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M24.25 8.88715L1.75 8.88715" stroke="black" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            <path d="M9.11842 16.2175L1.77539 8.87444L9.11842 1.53141" stroke="black" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span>
            <?php echo t('Home'); ?>
        </span>
    </a>
    <hr />
</div>

<h1 class="H d-desktop"><?php echo $this->data('Title'); ?></h1>
<div class="modal-body Section-ConversationList">

<a class="mobile-auto-popup d-mobile InboxPopup" href="<?php echo url('/messages/inbox'); ?>"></a>
<a class="mobile-auto-back-popup" href="<?php echo url('/discussions'); ?>"></a>

<?php if(userRoleCheck() == Gdn::config('Vanilla.ExtraRoles.Teacher')) { ?>
<a href="<?php echo url('/messages/add'); ?>" class="AddToConversationPopup">
    <div class="DataList">
        <div class="Item add-people">
            <h2>
                <?php echo t('New discussion');?>
            </h2>
            <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.763 1.502 6.117 14.537v4.478h4.417l12.3-13.442-4.071-4.071z" stroke="#000" />
                <path d="M22.206 12.978v8.52a2 2 0 0 1-2 2H3.166a2 2 0 0 1-2-2V4.458a2 2 0 0 1 2-2h8.52" stroke="#000"
                    stroke-linecap="round" />
            </svg>
        </div>
    </div>
</a>
<?php
    }
?>

<?php
// Pager setup
$PagerOptions = ['CurrentRecords' => count($this->data('Conversations'))];
if ($this->data('_PagerUrl'))
    $PagerOptions['Url'] = $this->data('_PagerUrl');

// Pre Pager
echo '<div class="PageControls Top">';
PagerModule::write($PagerOptions);
if (checkPermission('Conversations.Conversations.Add')) {
    echo '<div class="BoxButtons BoxNewConversation">';
    echo anchor(sprite('SpMessage').' '.t('New Message'), '/messages/add', 'Button NewConversation Primary');
    echo '</div>';
}
echo '</div>';
?>
<div class="DataListWrap">
    <?php
            if (count($this->data('Conversations')) > 0):
                echo '<ul class="Condensed DataList Conversations">';
                $ViewLocation = $this->fetchViewLocation('conversations');
                include $ViewLocation;
                echo '</ul>';
            else: ?>
    <div class="discussion-list-footer">
        <img src="<?= url('/themes/alloprof/design/images/full_of_questions.svg') ?>" />
        <p><?php echo t('There is no discussion at the moment.'); ?></p>
    </div>
    <?php
        endif;
    ?>
    </ul>
</div>
</div>
<?php
echo '<div class="conversation-mobile-back d-mobile"><span>×</span></div>';
// Post Pager
echo '<div class="PageControls Bottom">';
PagerModule::write($PagerOptions);

//   echo '<div class="BoxButtons BoxNewConversation">';
//   echo anchor(t('New Message'), '/messages/add', 'Button NewConversation Primary');
//   echo '</div>';
echo '</div>';