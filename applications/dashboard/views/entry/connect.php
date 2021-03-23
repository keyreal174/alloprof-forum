<?php if (!defined('APPLICATION')) exit();

/** @var \EntryController $this */

// Get the connection information.
print_r($this->Form->getFormValue('ProviderName'));
if (!($ConnectName = $this->Form->getFormValue('FullName'))) {
    $ConnectName = $this->Form->getFormValue('Name');
}
$ConnectPhoto = $this->Form->getFormValue('Photo');
if (!$ConnectPhoto) {
    $ConnectPhoto = '/applications/dashboard/design/images/usericon.gif';
}
// Teacher default photo;
$ConnectPhoto = "https://www.alloprof.qc.ca/zonedentraide/uploads/Avatar_Enseignant.svg";
$ConnectSource = $this->Form->getFormValue('ProviderName');

// By default, clients will try to connect existing users.
// Turning this off forces connecting clients to choose unique usernames.
$allowConnect = $this->data('AllowConnect');

$hasUserID = $this->Form->getFormValue('UserID');

if (!$hasUserID) {
    // Determine whether to show ConnectName field.
    $ExistingUsers = (array)$this->data('ExistingUsers', []);
    $NoConnectName = $this->data('NoConnectName');

    // You just landed on this page.
    $firstTimeHere = !($this->Form->isPostBack() && $this->Form->getFormValue('Connect', null) !== null);
    $connectNameProvided = (bool)$this->Form->getFormValue('ConnectName');

    $validationResults = $this->Form->validationResults();
    $usernameNotValid = array_key_exists('Name', $validationResults) || array_key_exists('ConnectName', $validationResults);

    // Buckle up, deciding whether to show this field is intense.
    // Any of these 3 scenarios will do it:
    $displayConnectName =
        // 1) If you arrived with NO ConnectName OR you've clicked Submit WITH a ConnectName (not not both!)
        //    we need to display the field again so that you can add/edit it.
        ($firstTimeHere xor $connectNameProvided)
        // 2) If you clicked submit and we found matches (but validation failed and you need to try again).
        || (!$firstTimeHere && count($ExistingUsers))
        // 3) We're forcing a manual username selection.
        || !$allowConnect
        // 4) There was an error with the submitted name.
        || $usernameNotValid;
}
?>
<div class="Connect FormTitleWrapper AjaxForm">
    <h1><?php echo stringIsNullOrEmpty($ConnectSource) ? t("Sign In") : sprintf(t('%s Connect'), htmlentities($ConnectSource)); ?></h1>

    <div class="FormWrapper">
        <?php
        echo $this->Form->open();
        echo $this->Form->errors();

        /**
         *  HideName can be passed by any plugin that hooks into
         *  the EntryController that has rules that require this form to be
         *  shown but not the Name Field.
         */
        if ($ConnectName || $ConnectPhoto && !$this->data('HideName')) : ?>
            <div class="MeBox">
                <?php
                if ($ConnectPhoto) {
                    echo '<span class="PhotoWrap">',
                    img($ConnectPhoto, ['alt' => t('Profile Picture'), 'class' => 'ProfilePhoto']),
                    '</span>';
                }

                echo '<div class="WhoIs">';
                if ($ConnectName && $ConnectSource) {
                    $NameFormat = t('You are connected as %s through %s.');
                } elseif ($ConnectName) {
                    $NameFormat = t('You are connected as %s.');
                } elseif ($ConnectSource) {
                    $NameFormat = t('You are connected through %2$s.');
                } else {
                    $NameFormat = '';
                }

                $NameFormat = '%1$s';
                echo sprintf(
                    $NameFormat,
                    '<span class="Name">'.htmlspecialchars($ConnectName).'</span>',
                    '<span class="Source">'.htmlspecialchars($ConnectSource).'</span>');

                echo wrap(t('ConnectCreateAccount', 'Add Info &amp; Create Account'), 'h3', ["aria-level" => 2]);

                echo '</div>';
                ?>
            </div>
        <?php endif; ?>

        <?php if ($hasUserID) : ?>
            <div class="SignedIn">
                <?php echo '<div class="Info">', t('You are now signed in.'), '</div>'; ?>
            </div>
        <?php else : ?>
            <ul role="presentation">
                <li role="presentation">
                    <?php
                    echo $this->Form->label('Email', 'Email');
                    echo $this->Form->textBox('Email', ['value' => $this->Form->getFormValue('Email'), 'id' => 'Form_Email', 'class' => 'InputBox', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('Email address')]);
                    ?>
                </li>
                <li role="presentation">
                    <?php
                    echo $this->Form->label('UserName', 'UserName');
                    echo $this->Form->textBox('UserName', ['value' => $this->Form->getFormValue('Email'), 'id' => 'Form_Email', 'class' => 'InputBox', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('User name')]);
                    ?>
                </li>
                <li role="presentation">
                    <?php
                    echo $this->Form->label('DisplayName', 'DisplayName');
                    echo $this->Form->textBox('DisplayName', ['value' => $this->Form->getFormValue('DisplayName'), 'id' => 'Form_Email', 'class' => 'InputBox', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('Display name')]);
                    ?>
                </li>
                <li role="presentation">
                    <?php
                    echo $this->Form->label('Grade', 'Grade');
                    echo $this->Form->textBox('Grade', ['value' => $this->Form->getFormValue('Grade'), 'id' => 'Form_Email', 'class' => 'InputBox', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('Grade')]);
                    ?>
                </li>
                <li role="presentation">
                    <?php
                    echo $this->Form->label('Role', 'Role');
                    echo $this->Form->textBox('Role', ['value' => $this->Form->getFormValue('Role'), 'id' => 'Form_Email', 'class' => 'InputBox', 'autocorrect' => 'off', 'autocapitalize' => 'off', 'Wrap' => TRUE, 'placeholder' => t('Role')]);
                    ?>
                </li>
            </ul>

            <?php
            echo '<div class="Buttons">', wrap($this->Form->button('Connect', ['class' => 'Button Primary']), 'div', ['class' => 'ButtonContainer']), '</div>';

        endif;

        echo $this->Form->close();
        ?>
    </div>
</div>
