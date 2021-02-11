<?php if (!defined('APPLICATION')) exit();

$Controller = Gdn::controller();
$Session = Gdn::session();
$SigninUrl = signInUrl($Controller->SelfUrl);
?>

<div class="scroll-top">
    <h2>Can't find answers?</h2>
    <?php
        if($Session->isValid())
            echo '<button class="btn-default btn-shadow scrollToAskQuestionForm">'.t("Ask a question").'</button>';
        else echo anchor(t('Ask a question'), $SigninUrl, 'btn-default btn-shadow'.(signInPopup() ? ' SignInPopup' : ''), ['rel' => 'nofollow', 'aria-label' => t("Sign In Now")]);
    ?>
</div>