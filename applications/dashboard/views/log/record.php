<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>
<div class="Info"><?php echo T('Edits and deletions are recorded here. Use &lsquo;Restore&rsquo; to undo any change.');
   echo '<br>'.sprintf(T('We start logging edits on a post %s minutes after it is first created.'),  C('Garden.Log.FloodControl', 20)); ?>
</div>
<?php
echo '<noscript><div class="Errors"><ul><li>', T('This page requires Javascript.'), '</li></ul></div></noscript>';
echo $this->Form->Open();
?>
<div class="Info">
   <?php
   echo Anchor(T('Restore'), '#', array('class' => 'RestoreButton SmallButton'));
   echo Anchor(T('Delete Forever'), '#', array('class' => 'DeleteButton SmallButton'));
   ?>
</div>
<?php

echo '<div id="LogTable">';
include dirname(__FILE__).'/table.php';
echo '</div id="LogTable">';
?>
<div class="Info">
   <?php
   echo Anchor(T('Restore'), '#', array('class' => 'RestoreButton SmallButton'));
   echo Anchor(T('Delete Forever'), '#', array('class' => 'DeleteButton SmallButton'));
   ?>
</div>
<?php

$this->AddDefinition('ExpandText', T('(more)'));
$this->AddDefinition('CollapseText', T('(less)'));
echo $this->Form->Close();