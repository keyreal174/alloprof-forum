<?php if (!defined('APPLICATION')) exit();
$Methods = $this->data('Methods', []);
$SelectedMethod = $this->data('SelectedMethod', []);
$CssClass = count($Methods) > 0 ? ' MultipleEntryMethods' : ' SingleEntryMethod';

echo '<div class="FormTitleWrapper AjaxForm">';
?>
<div class='FormSummary'>
    <h1 class='Title'><?php echo $this->data('Title'); ?></h1>
    <img src='<?= url("/themes/alloprof/design/images/authAvatar.svg") ?>' alt='image' class='AuthAvatar' />
</div>
<?php
echo '<div class="FormWrapper">';
// Make sure to force this form to post to the correct place in case the view is
// rendered within another view (ie. /dashboard/entry/index/):
echo $this->Form->open(['Action' => $this->data('FormUrl', url('/entry/signin')), 'id' => 'Form_User_SignIn']);
echo $this->Form->errors();

echo '<div class="Entry'.$CssClass.'">';

// Render the main signin form.
echo '<div class="MainForm">';
?>
    <ul role="presentation">
        <li role="presentation">
            <?php
            echo $this->Form->textBox('Email', ['id' => 'Form_Email', 'class' => 'InputBox', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('Email address')]);
            ?>
        </li>
        <li role="presentation">
            <?php
            echo $this->Form->input('Password', 'password', ['class' => 'InputBox Password', 'placeholder' => t('Password')]);
            echo '<span class="EyeIcon EyeIconPassword"><img src="'.url("/themes/alloprof/design/images/icons/eye.svg").'" alt="image" /></span>';
            ?>
        </li>
    </ul>
<?php
echo '</div>';

echo '</div>';

?>
    <div class="Buttons">
        <?php
        $this->fireEvent('AfterPassword');
        echo $this->Form->button(t('Sign in'), ['class' => 'btn btn-default btn-shadow']);
        // echo $this->Form->checkBox('RememberMe', t('Keep me signed in'), ['value' => '1', 'id' => 'SignInRememberMe']);
        ?>
        <?php if (strcasecmp(c('Garden.Registration.Method'), 'Connect') != 0): ?>
            <div class="CreateAccount">
                <?php
                $Target = $this->target();
                if ($Target != '') {
                    $Target = '?Target='.urlencode($Target);
                }

                if (c('Garden.Registration.Method') != 'Invitation') {
                    // printf(t("Don't have an account yet? %s"), anchor(t('Sign up!'), '/entry/register'.$Target, '', ['title' => t('Create an Account')]));
                    printf(t("Don't have an account yet? %s"));
                }
                ?>
            </div>
        <?php endif; ?>
        <!-- <div>
            <img src='<?= url("/themes/alloprof/design/images/icons/help.svg") ?>' alt='image' class='HelpIcon' />
        </div> -->
    </div>

<?php
// Render the buttons to select other methods of signing in.
// if (count($Methods) > 0) {
//     echo '<div class="Methods">';

//     foreach ($Methods as $Key => $Method) {
//         $CssClass = 'Method Method_'.$Key;
//         echo '<div class="'.$CssClass.'">',
//         $Method['SignInHtml'],
//         '</div>';
//     }

//     echo '</div>';
// }
?>
<div class="Methods">
    <div class="Method Method_0">
        <a
            href=<?= url("/entry/googlesignin-redirect") ?>
            class="SocialIcon SocialIcon-Google HasText default"
            rel="nofollow"
            title="Connexion enseignant"
            tabindex="0"
        >
            <span class="Icon">
            </span>
            <span class="Text">
                <?php echo t("Sign In with Google"); ?>
            </span>
        </a>
    </div>
</div>
<?php
echo $this->Form->close();
echo '<div />';
echo '<div />';
