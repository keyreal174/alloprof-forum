<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
$this->EventArguments['FormCssClass'] = 'MessageForm CommentForm';
$this->fireEvent('BeforeMessageForm');
?>
<div id="MessageForm" class="<?php echo $this->EventArguments['FormCssClass']; ?>">
    <!-- <h2 class="H"><?php // echo t("Add a Message"); ?></h2> -->

    <div class="MessageFormWrap">
        <div class="Form-HeaderWrap">
            <div class="Form-Header">
            <span class="Author">
               <?php
               if (c('Vanilla.Comment.UserPhotoFirst', true)) {
                   echo userPhoto($Session->User);
                   echo userAnchor($Session->User, 'Username');
               } else {
                   echo userAnchor($Session->User, 'Username');
                   echo userPhoto($Session->User);
               }
               ?>
            </span>
            </div>
        </div>
        <div class="Form-BodyWrap">
            <div class="Form-Body">
                <div class="FormWrapper FormWrapper-Condensed">
                    <?php
                    echo $this->Form->open(['id' => 'Form_ConversationMessage', 'action' => url('/messages/addmessage/')]);
                    echo $this->Form->errors();
                    echo '<div class="message-input-wrapper">';
                    // echo wrap($this->Form->textBox('Body', array('MultiLine' => true, 'class' => 'TextBox')), 'div', array('class' => 'TextBoxWrapper'));
                    echo $this->Form->bodyBox('Body', ['Table' => 'ConversationMessage', 'FileUpload' => true, 'placeholder' => t('Write your message'), 'title' => t('Write your message')]);
                    echo '<button class="message-send" type="submit"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="m13.887 21.348-3.68-5.586L16.51 7.99c.18-.224-.047-.428-.25-.249l-7.776 6.323-5.59-3.7c-.724-.478-.57-1.56.217-1.826l16.94-5.65c.789-.267 1.56.504 1.292 1.292l-5.653 16.93c-.267.81-1.349.94-1.803.24z" fill="#fff"/>
                </svg></button>',
                    '</div>';
                    echo $this->Form->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
