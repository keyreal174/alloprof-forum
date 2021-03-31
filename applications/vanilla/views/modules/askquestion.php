<?php if (!defined('APPLICATION')) exit();

$Controller = Gdn::controller();
$Session = Gdn::session();
$SigninUrl = signInUrl($Controller->SelfUrl);

require Gdn::controller()->fetchViewLocation('helper_functions', 'Discussions', 'Vanilla');
if(userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
?>
<div class="scroll-top">
    <p><?php echo t("Can't find answers?") ?></p>
    <?php
        // if($Session->isValid()) {
            $Controller = Gdn::controller();
            if($Controller->data('Category')) {
                $category = $Controller->data('Category');
                $categoryID = val('CategoryID', $category);
            }
            echo '<a href="'.url('/post/newDiscussion/'. $categoryID) .'" class="btn-default btn-shadow scrollToAskQuestionForm scrollToAskQuestionFormPopup ">'.t("Ask a question").'</a>';
        // }
        // else echo anchor(t('Ask a question'), '/entry/jsconnect-redirect?client_id=alloprof', 'btn-default btn-shadow', ['rel' => 'nofollow', 'aria-label' => t("Sign In Now")]);
    ?>
</div>
<?php } ?>