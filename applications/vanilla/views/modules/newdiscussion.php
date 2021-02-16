<?php
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
    if(userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
?>

<div class="BoxButtons BoxNewDiscussion AskQuestionForm">
    <div class="BoxNewDiscussion-header">
        <?php
            echo img($Photo, ['class' => 'user-avatar', 'alt' => $PhotoAlt]);
        ?>
        <h1>
            <?php
                echo t('Ask a question');
            ?>
        </h1>
    </div>
    <div class="close-icon">
        <img src="/themes/alloprof/design/images/icons/close.svg" />
    </div>

    <div id="DiscussionForm" class="FormTitleWrapper DiscussionForm">
        <?php
            echo '<div class="FormWrapper">';
            echo $this->Form->open();
            echo $this->Form->errors();
        ?>

        <div class="content">
            <?php
                if($this->invalid) {
                    $Controller = Gdn::controller();
                    $Session = Gdn::session();
                    $SigninUrl = signInUrl($Controller->SelfUrl);

                    echo '<a href="'.$SigninUrl.'" class="SignInPopup" rel="nofollow">';
                    echo '<div class="placeholder">';
                } else echo '<div class="placeholder OpenAskQuestionForm">';
            ?>
                <div class="icon">
                    <img src="/themes/alloprof/design/images/icons/ask_question.svg" />
                </div>
                <?php echo t('What is your question?'); ?>
            </div>
            <?php if($this->invalid) echo '</a>';?>
            <?php
                if(!$this->invalid) {
                    $this->fireEvent('BeforeFormInputs');

                    echo '<div class="P">';
                    echo wrap($this->Form->Hidden('Name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true', 'value' => 'Question']), 'div', ['class' => 'TextBoxWrapper']);
                    echo '</div>';

                    $this->fireEvent('BeforeBodyInput');

                    echo '<div class="P">';
                    echo $this->Form->bodyBox('Body', ['Table' => 'Discussion', 'FileUpload' => true, 'placeholder' => t('Type your question'), 'title' => t('Type your question')]);
                    echo '</div>';
                }
            ?>
        </div>
        <?php
            if(!$this->invalid) {
                echo '<div class="selects">';
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

                    echo '<div>';
                    echo '<div class="Category rich-select">';
                    echo '<img src="/themes/alloprof/design/images/icons/subject.svg"/>';
                    echo $this->Form->categoryDropDown('CategoryID', $options);
                    echo '</div>';
                    echo '</div>';
                    echo '<span class="space"></span>';
                    echo '<div><div class="Category rich-select"><select></select></div></div>';
                }
                echo '</div>';
                echo '<div class="Buttons">';

                $this->fireEvent('BeforeFormButtons');
                echo $this->Form->button((property_exists($this, 'Discussion')) ? 'Save' : 'Publish', ['class' => 'btn-default btn-shadow']);
                $this->fireEvent('AfterFormButtons');
                echo '</div>';
            }
                echo $this->Form->close();
                echo '</div>';

        ?>
    </div>

</div>
<?php } ?>
