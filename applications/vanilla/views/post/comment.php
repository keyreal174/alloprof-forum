<?php if (!defined('APPLICATION')) exit();
$dataDriven = \Gdn::themeFeatures()->useDataDrivenTheme();
if (Gdn::session()->isValid()) {
    $User = Gdn::session()->User;
}
if (!$User) {
    return;
}

$IsProfilePage = val('IsProfilePage', Gdn::controller());
$UserMetaData = Gdn::userModel()->getMeta($User->UserID, 'Profile.%', 'Profile.');

$Session = Gdn::session();
$NewOrDraft = !isset($this->Comment) || property_exists($this->Comment, 'DraftID') ? true : false;
$Editing = isset($this->Comment);
$formCssClass = 'BoxButtons BoxNewDiscussion MessageForm CommentPostForm FormTitleWrapper';
$this->EventArguments['FormCssClass'] = &$formCssClass;
$this->fireEvent('BeforeCommentForm');
?>
<div class="<?php echo $formCssClass; ?>">
    <?php
        if ($Editing) {
            ?>
            <h2 class="H"><?php echo t('Edit Comment'); ?></h2>
            <?php
        } else {
            echo '<div class="UserInfo">';
            echo "<a class='UserPhoto' href='/profile/picture?userid=".$User->UserID."'><img src='".$User->PhotoUrl."' class='PhotoWrap' alt='Photo'/></a>";
            echo '<div class="UserAuthor">';
            echo '<span class="UserAuthorName">'.$User->Name.'</span>';
            echo '<span class="UserAuthorGrade">'.$UserMetaData["Grade"].'</span>';
            echo '</div>';
            echo '</div>';
        }
    ?>
    <div class="close-icon">
        <img src="/themes/alloprof/design/images/icons/close.svg" />
    </div>
    <div class="CommentFormWrap">
        <?php if (Gdn::session()->isValid()) : ?>
            <div class="Form-HeaderWrap">
                <div class="Form-Header">
            <span class="Author">
                <?php writeCommentFormHeader(); ?>
            </span>
                </div>
            </div>
        <?php endif; ?>
        <div class="Form-BodyWrap">
            <div class="Form-Body">
                <div class="FormWrapper FormWrapper-Condensed">
                    <?php
                    echo $this->Form->open(['id' => 'Form_Comment']);
                    echo $this->Form->errors();
                    $this->fireEvent('BeforeBodyField');

                    echo $this->Form->bodyBox('Body', ['Table' => 'Comment', 'FileUpload' => true, 'placeholder' => t('Type your comment'), 'title' => t('Type your comment')]);
                    echo '<div class="CommentOptions List Inline">';
                    $this->fireEvent('AfterBodyField');
                    echo '</div>';

                    echo "<div class=\"Buttons\">\n";
                    $this->fireEvent('BeforeFormButtons');

                    $ButtonOptions = ['class' => 'btn-default btn-shadow btn-m-l-auto'];

                    if ($Session->isValid()) {
                        echo $this->Form->button($Editing ? 'Save Comment' : t('Reply'), $ButtonOptions);
                    } else {
                        $AllowSigninPopup = c('Garden.SignIn.Popup');
                        $Attributes = ['tabindex' => '-1'];
                        if (!$AllowSigninPopup) {
                            $Attributes['target'] = '_parent';
                        }
                        $AuthenticationUrl = signInUrl($this->SelfUrl);
                        $CssClass = 'Button Primary Stash';
                        if ($AllowSigninPopup) {
                            $CssClass .= ' SignInPopup';
                        }
                        echo anchor(t('Comment As ...'), $AuthenticationUrl, $CssClass, $Attributes);
                    }

                    $this->fireEvent('AfterFormButtons');
                    echo "</div>\n";
                    echo $this->Form->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
