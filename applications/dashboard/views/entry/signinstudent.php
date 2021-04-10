<?php if (!defined('APPLICATION')) exit();
$Methods = $this->data('Methods', []);
$SelectedMethod = $this->data('SelectedMethod', []);
$CssClass = count($Methods) > 0 ? ' MultipleEntryMethods' : ' SingleEntryMethod';

echo '<div class="logo"><a href="/">
<svg width="115" height="25" viewBox="0 0 147 32" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M58.4417 28.5418C52.6308 28.5418 47.9027 23.7727 47.9027 17.9115C47.9027 12.0503 52.6308 7.28125 58.4417 7.28125C64.2526 7.28125 68.9808 12.0503 68.9808 17.9115C68.9808 23.7727 64.2526 28.5418 58.4417 28.5418ZM58.4417 10.8125C54.5798 10.8125 51.4398 13.9798 51.4398 17.8751C51.4398 21.7704 54.5798 24.9377 58.4417 24.9377C62.3036 24.9377 65.4437 21.7704 65.4437 17.8751C65.4437 13.9798 62.3036 10.8125 58.4417 10.8125Z" fill="#1A1919"/>
    <path d="M123.517 28.5418C117.706 28.5418 112.978 23.7727 112.978 17.9115C112.978 12.0503 117.706 7.28125 123.517 7.28125C129.328 7.28125 134.056 12.0503 134.056 17.9115C134.056 23.7727 129.328 28.5418 123.517 28.5418ZM123.517 10.8125C119.655 10.8125 116.515 13.9798 116.515 17.8751C116.515 21.7704 119.655 24.9377 123.517 24.9377C127.379 24.9377 130.519 21.7704 130.519 17.8751C130.519 13.9798 127.379 10.8125 123.517 10.8125Z" fill="#1A1919"/>
    <path d="M31.228 0.328125H27.4383V28.5784H31.228V0.328125Z" fill="#1A1919"/>
    <path d="M42.0197 0.328125H38.23V28.5784H42.0197V0.328125Z" fill="#1A1919"/>
    <path d="M92.0079 10.3758C90.0228 8.3007 87.352 7.13574 84.5007 7.13574C82.6239 7.13574 80.8192 7.64541 79.2312 8.59194C79.1229 8.66475 79.0146 8.70116 78.9063 8.77397C76.0189 10.667 74.2865 13.9071 74.2865 17.4748V19.2586V27.5954V30.7626V31.964L77.8957 29.8525L81.6854 27.6318C82.7321 27.923 83.8149 28.0686 84.9338 28.0322C90.2755 27.8138 94.6427 23.4452 94.8953 18.0572C95.0036 15.1812 93.993 12.4509 92.0079 10.3758ZM84.7894 24.5009C83.4179 24.5373 82.1547 24.2097 81.0719 23.5544L77.8596 25.4111V19.2586V18.9674V17.584C77.8596 15.3269 78.9785 13.179 80.8553 11.9412C80.9275 11.9048 80.9997 11.8684 81.0719 11.832C82.0825 11.2131 83.2374 10.8855 84.5007 10.8855C88.3265 10.8855 91.4304 14.1255 91.25 18.0208C91.1056 21.5157 88.2543 24.3917 84.7894 24.5009Z" fill="#1A1919"/>
    <path d="M0.0078753 18.0937C0.260523 23.4816 4.62773 27.8502 9.96943 28.0686C11.0883 28.105 12.1711 27.9594 13.2178 27.6682L17.0075 29.8889L20.6167 32.0004V30.799V27.6318V19.2586V17.4748C20.6167 13.9071 18.8843 10.667 15.9969 8.77397C15.8886 8.70116 15.7803 8.62835 15.6721 8.59194C14.084 7.64541 12.2433 7.13574 10.4025 7.13574C7.55123 7.13574 4.88038 8.3007 2.89528 10.3758C0.91019 12.4509 -0.100402 15.1812 0.0078753 18.0937ZM3.61713 18.0208C3.43667 14.1255 6.54064 10.8855 10.3665 10.8855C11.6297 10.8855 12.7847 11.2495 13.7952 11.832C13.8674 11.8684 13.9396 11.9048 14.0118 11.9412C15.8886 13.179 17.0075 15.3269 17.0075 17.584V18.9674V19.2586V25.4111L13.7952 23.5544C12.7125 24.2097 11.4492 24.5373 10.0777 24.5009C6.61282 24.3917 3.76151 21.5157 3.61713 18.0208Z" fill="#1A1919"/>
    <path d="M146.363 0C141.924 0 139.47 2.40273 139.47 6.80774V28.5779H143.259V12.2685H146.363V8.44596H143.259V6.80774C143.259 5.75199 143.44 5.02389 143.801 4.58703C144.234 4.07736 145.1 3.82253 146.327 3.82253H146.472V0H146.363Z" fill="#1A1919"/>
    <path d="M107.889 6.77148C101.717 6.77148 100.778 11.759 100.778 14.7078V28.5781H104.568V14.7442C104.568 12.0502 105.109 10.6304 107.889 10.6304H108.033V6.77148H107.889Z" fill="#1A1919"/>
</svg>
</a></div>';

echo '<div class="FormTitleWrapper AjaxForm">';
?>

<div class='FormSummary d-desktop'>
    <h1 class='Title'><?php echo $this->data('Title'); ?></h1>
    <img src='<?= url("/themes/alloprof/design/images/authAvatar.svg") ?>' alt='image' class='AuthAvatar' />
</div>
<?php
echo '<div class="FormWrapper">';
// Make sure to force this form to post to the correct place in case the view is
// rendered within another view (ie. /dashboard/entry/index/):
// echo $this->Form->open(['Action' => $this->data('FormUrl', url('/entry/signin')), 'id' => 'Form_User_SignIn']);
// echo $this->Form->open(['id' => 'Form_Student_SignIn']);
// echo $this->Form->errors();
?>
<h1 class='Title d-mobile'><?php echo $this->data('Title'); ?></h1>
<div class='ErrorMessage'></div>
<?php
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
            echo anchor(t('Forgot?'), 'https://www.alloprof.qc.ca/fr/auth/mot-de-passe-oublie', 'ForgotPassword', ['title' => t('Forgot your password?'), 'target' => '_blank']);
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
                    printf(t("Don't have an account yet? %s"), anchor(t('Sign up!'), '/entry/register'.$Target, 'registerPopup registerLink', ['title' => t('Create an Account')]));
                    printf(t("Don't have an account yet? %s"));
                }
                ?>
            </div>
        <?php endif; ?>
        <div>
            <a href='https://alloprof-ti.atlassian.net/servicedesk/customer/portal/6/group/13/create/52' target='_blank'>
                <img src='<?= url("/themes/alloprof/design/images/icons/help.svg") ?>' alt='image' class='HelpIcon' />
            </a>
        </div>
    </div>
<?php
// echo $this->Form->close();
echo '<div />';
echo '<div />';
