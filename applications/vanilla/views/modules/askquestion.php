<?php if (!defined('APPLICATION')) exit();

$Controller = Gdn::controller();
$Session = Gdn::session();
$SigninUrl = signInUrl($Controller->SelfUrl);

require Gdn::controller()->fetchViewLocation('helper_functions', 'Discussions', 'Vanilla');
if(userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
?>
<div class="scroll-top">
    <h2><?php echo t("Can't find answers?") ?></h2>
    <?php
        if($Session->isValid())
            echo '<button class="btn-default btn-shadow scrollToAskQuestionForm">'.t("Ask a question").'</button>';
        else echo anchor(t('Ask a question'), $SigninUrl, 'btn-default btn-shadow'.(signInPopup() ? ' SignInPopup' : ''), ['rel' => 'nofollow', 'aria-label' => t("Sign In Now")]);
    ?>
</div>
<?php } ?>