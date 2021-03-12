<?php if (!defined('APPLICATION')) exit();
require_once Gdn::controller()->fetchViewLocation('helper_functions', 'Discussions', 'Vanilla');
$User = val('User', Gdn::controller());
if (!$User && Gdn::session()->isValid()) {
    $User = Gdn::session()->User;
}
$Photo = $User->Photo;
if ($Photo) {
    $Photo = (isUrl($Photo)) ? $Photo : Gdn_Upload::url(changeBasename($Photo, 'p%s'));
    $PhotoAlt = t('Avatar');
} else {
    $Photo = UserModel::getDefaultAvatarUrl($User, 'profile');
    $PhotoAlt = t('Default Avatar');
}

if ($User->Banned) {
    $BannedPhoto = c('Garden.BannedPhoto', 'https://images.v-cdn.net/banned_large.png');
    if ($BannedPhoto) {
        $Photo = Gdn_Upload::url($BannedPhoto);
    }
}
$CancelUrl = $this->data('_CancelUrl');
if (!$CancelUrl) {
    $CancelUrl = '/discussions';
    if (c('Vanilla.Categories.Use') && is_object($this->Category)) {
        $CancelUrl = '/categories/'.urlencode($this->Category->UrlCode);
    }
}

?>
<div id="DiscussionForm" class="FormTitleWrapper DiscussionForm EditDiscussionDetail">

    <?php
    echo '<div class="FormWrapper">';
    echo $this->Form->open();
    echo $this->Form->errors();

    $this->fireEvent('BeforeFormInputs');

    if ($this->ShowCategorySelector === true) {
        $options = ['Value' => val('CategoryID', $this->Category), 'IncludeNull' => true, 'AdditionalPermissions' => ['PermsDiscussionsAdd']];
        if ($this->Context) {
            $options['Context'] = $this->Context;
        }
        $discussionType = property_exists($this, 'Type') ? $this->Type : $this->data('Type');
        if ($discussionType) {
            $options['DiscussionType'] = $discussionType;
        }
        if (property_exists($this, 'Draft') && is_object($this->Draft)) {
            $options['DraftID'] = $this->Draft->DraftID;
        }

        echo '<div class="P">';
        echo '<div class="Category">';
        echo $this->Form->label('Category', 'CategoryID'), ' ';
        echo $this->Form->categoryDropDown('CategoryID', $options);
        echo '</div>';
        echo '</div>';
    }

    // echo '<div class="P">';
    // echo $this->Form->label('Discussion Title', 'Name');
    // echo wrap($this->Form->textBox('Name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true']), 'div', ['class' => 'TextBoxWrapper']);
    // echo '</div>';
    echo '<div class="content">';
    echo '<div class="P">';
    echo wrap($this->Form->Hidden('Name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true', 'value' => 'Question']), 'div', ['class' => 'TextBoxWrapper']);
    echo '</div>';

    $this->fireEvent('BeforeBodyInput');

    echo '<div class="P">';
    echo $this->Form->bodyBox('Body', ['Table' => 'Discussion', 'FileUpload' => true, 'placeholder' => t('Type your message'), 'title' => t('Type your message')]);
    echo '</div>';
    echo '</div>';

    $Options = '';
    // If the user has any of the following permissions (regardless of junction), show the options.
    if (Gdn::session()->checkPermission('Vanilla.Discussions.Announce')) {
        $Options .= '<li>'.checkOrRadio('Announce', 'Announce', $this->data('_AnnounceOptions')).'</li>';
    }

    $this->EventArguments['Options'] = &$Options;
    $this->fireEvent('DiscussionFormOptions');

    if ($Options != '') {
        echo '<div class="P">';
        echo '<ul class="List Inline PostOptions">'.$Options.'</ul>';
        echo '</div>';
    }

    $this->fireEvent('AfterDiscussionFormOptions');

    // Category select and grade select
    // if ($this->ShowCategorySelector === true) {
        echo '<div class="selects">';
        $options = ['Value' => val('CategoryID', $this->Category), 'IncludeNull' => true, 'AdditionalPermissions' => ['PermsDiscussionsAdd']];
        if ($this->Context) {
            $options['Context'] = $this->Context;
        }
        $discussionType = property_exists($this, 'Type') ? $this->Type : $this->data('Type');
        if ($discussionType) {
            $options['DiscussionType'] = $discussionType;
        }
        if (property_exists($this, 'Draft') && is_object($this->Draft)) {
            $options['DraftID'] = $this->Draft->DraftID;
        }

        $Session = Gdn::session();
        $DefaultGrade = 0;
        if ($Session) {
            $UserID = $Session->UserID;
            $AuthorMetaData = Gdn::userModel()->getMeta($UserID, 'Profile.%', 'Profile.');
            if ($AuthorMetaData['Grade']) {
                $DefaultGrade = $AuthorMetaData['Grade'];
            }
        }

        $fields = c('ProfileExtender.Fields', []);
        if (!is_array($fields)) {
            $fields = [];
        }
        foreach ($fields as $k => $field) {
            if ($field['Label'] == "Grade") {
                $GradeOption = $field['Options'];

                if ($DefaultGrade && $DefaultGrade !== 0) {
                    $DefaultGrade = array_search($DefaultGrade, $GradeOption);
                }
            }
        }

        echo '<div class="Category rich-select">';
        echo '<img src="/themes/alloprof/design/images/icons/subject.svg"/>';
        echo $this->Form->categoryDropDown('CategoryID', $options);
        echo '</div>';
        echo '<span class="space"></span>';
        echo '<div class="Category rich-select">';
        echo '<img src="/themes/alloprof/design/images/icons/grade.svg"/>';
        echo $this->Form->dropDown('GradeID', $GradeOption, array('Default' => $DefaultGrade, 'IncludeNull' => 'Grade', 'IsDisabled' => TRUE));
        echo '</div>';
        echo '</div>';
    // }

    // Category select and grade select end
    echo '<div class="Buttons">';

    $this->fireEvent('BeforeFormButtons');
    // echo '<a class="close-icon CancelButton"> <img src="/themes/alloprof/design/images/icons/close.svg" /> </a>';
    echo $this->Form->button((property_exists($this, 'Discussion')) ? 'Save' : 'Publish', ['class' => 'btn-default btn-shadow']);
    $this->fireEvent('AfterFormButtons');
    echo '</div>';

    echo $this->Form->close();
    echo '</div>';
    ?>
</div>
