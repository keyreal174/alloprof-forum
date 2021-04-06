<?php if (!defined('APPLICATION')) exit();
    include_once $this->fetchViewLocation('helper_functions', 'discussions', 'vanilla');

    $Content = $this->Form->getFormValue('Body', false);

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
?>
<div class="modal-header">
    <h3><?php echo t("Ask a question"); ?></h3>
</div>
<div class="modal-body">
    <div class="BoxButtons BoxNewDiscussion">
        <div class="FormTitleWrapper DiscussionForm">
            <?php
                echo '<div class="FormWrapper">';
                echo $this->Form->open();
                echo $this->Form->errors();
            ?>

            <div class="content">
                <div class="avatar">
                    <?php
                        $UserMetaData = Gdn::userModel()->getMeta(Gdn::session()->UserID, 'Profile.%', 'Profile.');
                        $UserName = $UserMetaData['DisplayName'] ?? t('Unknown');

                        $photoClassName = 'user-avatar';
                        if (str_contains($Photo, 'avatars/0.svg')) {
                            $photoClassName = $photoClassName.' ProfilePhotoDefaultWrapper';
                        }
                        echo '<span class="'.$photoClassName.'" avatar--first-letter="'.$UserName[0].'">';
                        echo img($Photo, ['class' => 'user-avatar', 'alt' => $PhotoAlt]);
                        echo '</span>';
                    ?>
                </div>
                <div class="editor">
                    <?php
                        if(!$this->invalid) {
                            $this->fireEvent('BeforeFormInputs');

                            echo '<div class="P">';
                            echo wrap($this->Form->Hidden('Name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true', 'value' => 'Question']), 'div', ['class' => 'TextBoxWrapper']);
                            echo '</div>';

                            echo '<div class="P">';
                            echo wrap($this->Form->Hidden('CategoryID', ['maxlength' => 100, 'value' => $categoryID, 'class' => 'InputBox BigInput', 'spellcheck' => 'true']), 'div', ['class' => 'TextBoxWrapper']);
                            echo '</div>';

                            $this->fireEvent('BeforeBodyInput');

                            if(!$Content)
                                echo '<div class="clickToCreate">'.t('What is your question?').'</div>';

                            echo '<div class="P">';
                            echo $this->Form->bodyBox('Body', ['Table' => 'Discussion', 'FileUpload' => true, 'placeholder' => t('Type your question'), 'title' => t('Type your question')]);
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>
            <?php
                echo '<div class="bottom">';
                if(!$this->invalid) {
                    echo '<div class="selects">';
                    $Controller = Gdn::controller();

                    if($Controller->data('Category')) {
                        $category = $Controller->data('Category');
                        $options = ['Value' => val('CategoryID', $category), 'IncludeNull' => true, 'AdditionalPermissions' => ['PermsDiscussionsAdd']];
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
                            $GradeOption = array_filter($field['Options'], function($v) {
                                return preg_match('/(Primaire|Secondaire)/', $v);
                            });

                            if ($DefaultGrade && $DefaultGrade !== 0) {
                                $DefaultGrade = array_search($DefaultGrade, $GradeOption);
                            }
                        }
                    }


                    echo writeCategoryDropDown($this, 'CategoryID', $options, true, $this->Form);
                    echo '<span class="space"></span>';
                    echo '<div class="Category rich-select select2 select2-grade">';
                    echo '<div class="pre-icon"><img src="'.url("/themes/alloprof/design/images/icons/grade.svg").'"/></div>';
                    echo $this->Form->dropDown('GradeID', $GradeOption, array('IncludeNull' => true, 'Default' => $DefaultGrade));
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="Buttons">';
                    echo '<a href="'.url('/post/rules').'" class="RulesPopup">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="11.5" stroke="black"/>
                                    <path d="M11.985 6.75C11.1345 6.75 10.5 7.344 10.5 8.127C10.5 8.8965 11.1345 9.4905 11.985 9.4905C12.822 9.4905 13.443 8.8965 13.443 8.127C13.443 7.344 12.822 6.75 11.985 6.75ZM11.013 17.631H12.93V10.476H11.013V17.631Z" fill="black"/>
                                </svg>
                            </a>';
                    $this->fireEvent('BeforeFormButtons');
                    echo $this->Form->button((property_exists($this, 'Discussion')) ? t('Save') : t('Publish'), ['class' => 'btn-default btn-primary']);
                    $this->fireEvent('AfterFormButtons');
                    echo '</div>';
                }
                echo '</div>';
                echo $this->Form->close();
                echo '</div>';
            ?>
        </div>
    </div>
</div>

<script >
    $('.QuestionPopup .select2-grade select').select2({
        minimumResultsForSearch: -1,
        placeholder: "Niveau",
    });
</script>
