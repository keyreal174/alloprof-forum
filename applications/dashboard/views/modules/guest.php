<?php if (!defined('APPLICATION')) exit();
    $dataDriven = \Gdn::themeFeatures()->useDataDrivenTheme();
?>
<div class="Box GuestBox">
    <h4 class="GuestBox-title">
        <img src="<?= url('/themes/alloprof/design/images/Anonymous_PP.svg') ?>"/>
    </h4>
    <p class="GuestBox-message">
        <?php echo t($this->MessageCode, $this->MessageDefault); ?>
    </p>

    <!-- <p class="GuestBox-beforeSignInButton">
        <?php $this->fireEvent('BeforeSignInButton'); ?>
    </p> -->

    <?php
    if ($this->data('signInUrl')) {
        echo '<div class="Buttons">';
        if ($this->data('registerUrl')) {
            //echo ' '.anchor(t('Register', t('Apply for Membership', 'Register')), 'https://www.alloprof.qc.ca/jsconnect/register', 'btn-default btn-shadow', ['rel' => 'nofollow', 'aria-label' => t("Register Now")]);
            echo ' '.anchor(t('Register', t('Apply for Membership', 'Register')), $this->data('registerUrl'), 'btn-default btn-shadow registerPopup', ['rel' => 'nofollow', 'aria-label' => t("Register Now")]);
        }
        echo '<p>'.t('Do you already have an account?');
        echo anchor(t('Sign In'), '/entry/signinstudent?Target='.$this->_Sender->SelfUrl, 'link SignInStudentPopupAgent', ['rel' => 'nofollow', 'aria-label' => t("Sign In Now")]);
        echo anchor(t('Sign In'), '/entry/signinstudent?Target='.$this->_Sender->SelfUrl, 'link SignInStudentPopup HiddenImportant', ['rel' => 'nofollow', 'aria-label' => t("Sign In Now")]);
        echo '</p>';
        echo '</div>';
    }
    ?>
    <?php $this->fireEvent('AfterSignInButton'); ?>
</div>
