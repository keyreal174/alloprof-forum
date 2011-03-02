<?php if (!defined('APPLICATION')) exit();
PagerModule::Write(array('Sender' => $this, 'Limit' => 20));
?>
<table id="Log" class="AltColumns">
   <thead>
      <tr>
         <th><?php echo T('Ban Item', 'Item'); ?></th>
         <th><?php echo T('Ban Type', 'Type'); ?></th>
         <th><?php echo T('User Count', 'Users'); ?></th>
         <th class="UsernameCell"><?php echo T('Added By'); ?></th>
         <th><?php echo T('Notes'); ?></th>
         <th><?php echo T('Options'); ?></th>
      </tr>
   </thead>
   <tbody>
      <?php
      foreach ($this->Data('Bans') as $Row):
      ?>
      <tr id="<?php echo "BanID_{$Row['BanID']}"; ?>">
         <td><?php echo htmlspecialchars($Row['BanValue']); ?></td>
         <td><?php echo T($Row['BanType']); ?></td>
         <td>
            <?php
               echo Anchor($Row['CountUsers'], '/dashboard/user?Filter='.urlencode($this->_BanFilter($Row)));
            ?>
         </td>
         <td class="UsernameCell"><?php echo htmlspecialchars($Row['InsertName']); ?></td>
         <td><?php echo htmlspecialchars($Row['Notes']); ?></td>
         <td>
            <?php
            echo Anchor(T('Edit'), '/dashboard/settings/bans/edit?id='.$Row['BanID'], array('class' => 'SmallButton Edit'));
            echo ' ';
            echo Anchor(T('Delete'), '/dashboard/settings/bans/delete?id='.$Row['BanID'], array('class' => 'SmallButton Delete'));
            ?>
         </td>
      </tr>
      <?php
      endforeach;
      ?>
   </tbody>
</table>
<?php
PagerModule::Write();