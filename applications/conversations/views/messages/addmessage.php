<?php if (!defined('APPLICATION')) exit();
echo $this->Form->Open();
?>
<h1><?php echo T("Add a Message") ?></h1>
<?php echo $this->Form->Errors(); ?>
<ul>
   <li>
      <?php
         echo $this->Form->Label('Message', 'Body');
         echo Wrap($this->Form->TextBox('Body', array('MultiLine' => TRUE, 'Class' => 'TextBox')), 'div', array('class' => 'TextBoxWrapper'));
      ?>
   </li>
</ul>
<?php echo $this->Form->Close('Send');