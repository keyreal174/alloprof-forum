<?php if (!defined('APPLICATION')) exit(); ?>
<div class="FormTitleWrapper AjaxForm SignUpForm">
    <div class='FormSummary'>
        <h1 class='Title'><?php echo t('To send in your question, sign up!'); ?></h1>
        <img src='<?= url("/themes/alloprof/design/images/authAvatar.svg") ?>' alt='image' class='AuthAvatar' />
    </div>

    <div class="FormWrapper">
        <?php
        $TermsOfServiceUrl = Gdn::config('Garden.TermsOfService', '#');
        $TermsOfServiceText = sprintf(t('I accept the <a href="https://www.alloprof.qc.ca/fr/pages/conditions-d-utilisation-et-vie-privee">Terms of Service and the Privacy Policy</a>, as well as the creation of a personal file in which notes can be added by the Alloprof teachers with whom I communicate.'), url($TermsOfServiceUrl));
        $AgeServiceText = sprintf(t('I confirm that I am 14 years of age or older. If I am 13 years of age or under, my parent or legal guardian confirms that they authorize the creation of my account and my use of Alloprof services.'));

        // Make sure to force this form to post to the correct place in case the view is
        // rendered within another view (ie. /dashboard/entry/index/):
        echo $this->Form->open(['Action' => url('/entry/register'), 'id' => 'Form_User_Register']);
        echo $this->Form->errors();
        ?>
        <ul role="presentation">
            <li role="presentation">
                <?php
                echo $this->Form->textBox('Name', ['autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('Choose a nickname to stay anonymous')]);
                echo '<span id="NameUnavailable" class="Incorrect" style="display: none;">'.t('Name Unavailable').'</span>';
                ?>
            </li>
            <?php if (!$this->data('NoEmail')): ?>
                <li role="presentation">
                    <?php
                    echo $this->Form->textBox('Email', ['type' => 'email', 'Wrap' => TRUE, 'placeholder' => t('Email address')]);
                    echo '<span id="EmailUnavailable" class="Incorrect" style="display: none;">'.t('Email Unavailable').'</span>';
                    ?>
                </li>
            <?php endif; ?>
            <li role="presentation">
                <?php
                $passwordDescID = \Vanilla\Utility\HtmlUtils::uniqueElementID('Password');
                // echo wrap(sprintf(t('Your password must be at least %d characters long.'), c('Garden.Password.MinLength')).' '.t('For a stronger password, increase its length or combine upper and lowercase letters, digits, and symbols.'), 'div', ['class' => 'Gloss', 'id' => $passwordDescID]);
                echo $this->Form->input('Password', 'password', ['Wrap' => true, 'Strength' => TRUE, 'aria-describedby' => $passwordDescID, 'placeholder' => t('Password')]);
                echo '<span class="EyeIcon EyeIconPassword"><img src="'.url("/themes/alloprof/design/images/icons/eye.svg").'" alt="image" /></span>';
                ?>
            </li>
            <li role="presentation">
                <?php
                echo $this->Form->input('PasswordMatch', 'password', ['Wrap' => TRUE, 'placeholder' => t('Confirm your password')]);
                echo '<span class="EyeIcon EyeIconConfirmPassword"><img src="'.url("/themes/alloprof/design/images/icons/eye.svg").'" alt="image" /></span>';
                echo '<span id="PasswordsDontMatch" class="Incorrect" style="display: none;">'.t("Passwords don't match").'</span>';
                ?>
            </li>
            <?php $this->fireEvent('RegisterBeforePassword'); ?>
            <?php $this->fireEvent('ExtendedRegistrationFields'); ?>
            <?php if ($this->Form->getValue('DiscoveryText') || val('DiscoveryText', $this->Form->validationResults())): ?>
                <li role="presentation">
                    <?php
                    echo $this->Form->label('Why do you want to join?', 'DiscoveryText');
                    echo $this->Form->textBox('DiscoveryText', ['MultiLine' => true, 'Wrap' => TRUE]);
                    ?>
                </li>
            <?php endif; ?>

            <?php Captcha::render($this); ?>

            <?php $this->fireEvent('RegisterFormBeforeTerms'); ?>

            <li role="presentation" class='validators'>
                <?php
                echo $this->Form->checkBox('TermsOfService', '@'.$TermsOfServiceText, ['value' => '1']);
                echo $this->Form->checkBox('RememberMe', t($AgeServiceText), ['value' => '1']);
                ?>
            </li>
            <li class="Buttons SignUpButtons"  role="presentation">
                <?php echo $this->Form->button(t('Sign up'), ['class' => 'btn btn-default btn-shadow']); ?>
                <?php printf(anchor(t('Sign in'), '/entry/signin'.$Target, '')); ?>
            </li>
        </ul>
        <?php echo $this->Form->close(); ?>
    </div>
</div>
