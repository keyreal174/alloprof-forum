<div class="BoxButtons BoxNewDiscussion AskQuestionForm">
<h1>
    <?php echo t('Ask a question'); ?>
</h1>
<div class="close-icon">
    <img src="/themes/alloprof/design/images/icons/close.svg" />
</div>
<?php if (!defined('APPLICATION')) exit();
// $Css = 'Button Primary Action NewDiscussion';
// $Css .= strpos($this->CssClass, 'Big') !== FALSE ? ' BigButton' : '';

// foreach ($this->getButtonGroups() as $buttonGroup) {
//     echo buttonGroup($buttonGroup, $Css, $this->DefaultButton, $this->reorder)."\n";
// }

// Gdn::controller()->fireEvent('AfterNewDiscussionButton');
?>


<div id="DiscussionForm" class="FormTitleWrapper DiscussionForm">
    <?php

    echo '<div class="FormWrapper">';
    echo $this->Form->open();
    echo $this->Form->errors();
    ?>

    <div class="content">
        <div class="placeholder">
            <div class="icon">
                <img src="/images/icons/ask_question.svg" />
            </div>
            <?php echo t('What is your question?'); ?>
        </div>

        <?php
        $this->fireEvent('BeforeFormInputs');

        echo '<div class="P">';
        echo wrap($this->Form->Hidden('Name', ['maxlength' => 100, 'class' => 'InputBox BigInput', 'spellcheck' => 'true', 'value' => 'Question']), 'div', ['class' => 'TextBoxWrapper']);
        echo '</div>';

        $this->fireEvent('BeforeBodyInput');

        echo '<div class="P">';
        echo $this->Form->bodyBox('Body', ['Table' => 'Discussion', 'FileUpload' => true, 'placeholder' => t('Type your question'), 'title' => t('Type your question')]);
        echo '</div>';

    ?>
    </div>
    <?php
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

        echo $this->Form->close();
        echo '</div>';
    ?>
</div>

</div>
