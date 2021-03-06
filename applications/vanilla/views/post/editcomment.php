<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
$this->fireEvent('BeforeCommentForm');
$formCssClass = 'MessageForm EditCommentForm';
if ($this->UserRole == TEACHER_ROLE) {
    $formCssClass = $formCssClass . " InlineFormatterEnabled";
}
?>
<div class="<?php echo $formCssClass; ?>">

    <div class="Form-BodyWrap">
        <div class="Form-Body">
            <div class="FormWrapper FormWrapper-Condensed">
                <?php
                echo $this->Form->open();
                echo $this->Form->errors();
                $this->fireEvent('BeforeBodyField');
                echo $this->Form->bodyBox('Body', ['Table' => 'Comment', 'FileUpload' => true, 'placeholder' => t('Type your comment'), 'title' => t('Type your comment')]);
                $this->fireEvent('AfterBodyField');

                $ButtonOptions = ['class' => 'btn-default btn-shadow btn-m-l-auto'];
                echo "<a href='".$this->data("PreviousURL")."' class='close-icon Cancel'><img src='".url("/themes/alloprof/design/images/icons/close.svg")."' /></a>";

                echo "<div class=\"Buttons\">\n";
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
