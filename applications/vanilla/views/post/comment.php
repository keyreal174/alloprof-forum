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
            if ($this->UserRole == "Teacher") {
                echo '<span class="UserAuthorName">'.$User->Name.'<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.64495 0.776516C9.40048 0.0744949 10.5995 0.0744944 11.3551 0.776516L12.0133 1.38817C12.4454 1.78962 13.0461 1.9771 13.6413 1.89624L14.5482 1.77303C15.589 1.63163 16.5591 2.30857 16.7407 3.30306L16.8989 4.16953C17.0028 4.73822 17.3741 5.22906 17.9051 5.49967L18.7142 5.91197C19.6428 6.3852 20.0133 7.4805 19.5516 8.3876L19.1494 9.17793C18.8854 9.69665 18.8854 10.3034 19.1494 10.8221L19.5516 11.6124C20.0133 12.5195 19.6428 13.6148 18.7142 14.088L17.9051 14.5003C17.3741 14.7709 17.0028 15.2618 16.8989 15.8305L16.7407 16.6969C16.5591 17.6914 15.589 18.3684 14.5482 18.227L13.6413 18.1038C13.0461 18.0229 12.4454 18.2104 12.0133 18.6118L11.3551 19.2235C10.5995 19.9255 9.40048 19.9255 8.64495 19.2235L7.98668 18.6118C7.55463 18.2104 6.95389 18.0229 6.35868 18.1038L5.45182 18.227C4.41097 18.3684 3.44092 17.6914 3.2593 16.6969L3.10106 15.8305C2.9972 15.2618 2.62591 14.7709 2.0949 14.5003L1.28584 14.088C0.357241 13.6148 -0.0132854 12.5195 0.44837 11.6124L0.850598 10.8221C1.11459 10.3034 1.11459 9.69665 0.850598 9.17793L0.448371 8.3876C-0.0132849 7.4805 0.357241 6.3852 1.28584 5.91197L2.0949 5.49967C2.62591 5.22906 2.9972 4.73822 3.10106 4.16953L3.2593 3.30306C3.44092 2.30857 4.41097 1.63163 5.45182 1.77303L6.35868 1.89624C6.95388 1.9771 7.55463 1.78962 7.98668 1.38817L8.64495 0.776516Z" fill="#05BF8E"/>
                <path d="M6.25 10L8.75 12.25L13.75 7.75" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                </span>';
            } else {
                echo '<span class="UserAuthorName">'.$User->Name.'</span>';
            }
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
                        echo $this->Form->button($Editing ? 'Save Comment' : t('Publish my explanation'), $ButtonOptions);
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
