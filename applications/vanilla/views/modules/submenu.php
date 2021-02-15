<?php
    require_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussions', 'Vanilla');
?>
<nav class="Question-submenu">
    <?php if(userRoleCheck() == Gdn::config('Vanilla.ExtraRoles.Teacher')) {
    ?>
        <div class='Navigation-linkContainer'>
            <?php echo Gdn_Theme::link('discussions', sprite('Home').' '.t('Home'), '<a href="%url" class="%class Navigation-link">%text</a>'); ?>
        </div>
        <div class='Navigation-linkContainer'>
            <?php
                $discussionModel = new DiscussionModel();
                $wheres = ['d.CountComments' => 0];
                if ($discussionModel->getCount($wheres)) {
                    $text = t('Waiting for a response').' <span class="Count">'.htmlspecialchars($discussionModel->getCount($wheres)).'</span>';
                }
                echo Gdn_Theme::link('discussions/waiting', sprite('Home').' '.$text, '<a href="%url" class="%class Navigation-link HasCount">%text</a>'); ?>
        </div>
    <?php } else { ?>
        <div class='Navigation-linkContainer'>
            <?php echo Gdn_Theme::link('discussions', sprite('Home').' '.t('Home'), '<a href="%url" class="%class Navigation-link">%text</a>'); ?>
        </div>
        <div class='Navigation-linkContainer'>
            <?php echo Gdn_Theme::link('discussions/mine', sprite('Home').' '.t('My Questions'), '<a href="%url" class="%class Navigation-link">%text</a>'); ?>
        </div>
        <div class='Navigation-linkContainer'>
            <?php echo Gdn_Theme::link('discussions/bookmarked', sprite('Home').' '.t('Question followed'), '<a href="%url" class="%class Navigation-link">%text</a>'); ?>
        </div>
        <div class='Navigation-linkContainer'>
            <?php echo Gdn_Theme::link('profile', sprite('Home').' '.t('Resources'), '<a href="%url" class="%class Navigation-link">%text</a>'); ?>
        </div>
    <?php } ?>
</nav>