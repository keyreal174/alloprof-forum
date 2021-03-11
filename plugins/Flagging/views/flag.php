<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$UcContext = htmlspecialchars(ucfirst($this->data('Plugin.Flagging.Data.Context')));
$ElementID = htmlspecialchars($this->data('Plugin.Flagging.Data.ElementID'));
$URL = $this->data('Plugin.Flagging.Data.URL');
$Title = sprintf("What's wrong?");
?>
    <div>
    <img src='<?= url("/themes/alloprof/design/images/flagAvatar.") ?>svg' alt='image' class='FlagAvatar' />
    </div>
    <h2><?php echo t($Title); ?></h2>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
    <div class='FlagWarningList'>
        <?php
            echo $this->Form->radioList(
                'Plugin.Flagging.Reason',
                Array(
                    t('The words are offensive or inappropriate'),
                    t('The author posts spam'),
                    t('The post contains an unapproved photo'),
                )
                , ['list' => true]
            );
            $this->fireEvent('FlagContentAfter');
        ?>
    </div>
<?php
echo '<div class="FlatReportButton">',
    $this->Form->button(t('Report'), ['class' => 'btn btn-default btn-shadow']),
    '</div>';
echo $this->Form->close();
?>
