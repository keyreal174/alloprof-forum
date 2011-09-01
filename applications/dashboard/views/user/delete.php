<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php printf(T('Delete User: %s'), UserAnchor($this->User)); ?></h1>
<?php 
   echo $this->Form->Errors(); 
   if ($this->Data("CanDelete")) {
?>
<table class="Label AltRows">
   <thead>
      <tr>
         <th><?php printf(T("Choose how to handle all of the content associated with the user account for %s (comments, messages, etc)."), Wrap($this->User->Name, 'em')); ?></th>
      </tr>
   </thead>
   <tbody>
      <tr class="Alt">
         <td>
            <h4><?php echo Anchor(T('Keep User Content'), 'user/delete/'.$this->User->UserID.'/keep'); ?></h4>
            <?php echo T("Delete the user but keep the user's content."); ?>
         </td>
      </tr>
      <tr>
         <td>
            <h4><?php echo Anchor(T('Blank User Content'), 'user/delete/'.$this->User->UserID.'/wipe'); ?></h4>
            <?php echo T("Delete the user and replace all of the user's content with a message stating the user has been deleted. This gives a visual cue that there is missing information."); ?>
         </td>
      </tr>
      <tr class="Alt">
         <td>
            <h4><?php echo Anchor(T('Remove User Content'), 'user/delete/'.$this->User->UserID.'/delete'); ?></h4>
            <?php echo T("Delete the user and completely remove all of the user's content. This may cause discussions to be disjointed. Best option for removing spammer content."); ?>
         </td>
      </tr>
   </tbody>
</table>
<?php } ?>