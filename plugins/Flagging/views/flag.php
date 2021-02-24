<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$UcContext = htmlspecialchars(ucfirst($this->data('Plugin.Flagging.Data.Context')));
$ElementID = htmlspecialchars($this->data('Plugin.Flagging.Data.ElementID'));
$URL = $this->data('Plugin.Flagging.Data.URL');
$Title = sprintf("What's wrong?");
?>
    <div>
    <img src='/themes/alloprof/design/images/flagAvatar.svg' alt='image' class='FlagAvatar' />
    </div>
    <h2><?php echo t($Title); ?></h2>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
    <div class='FlagWarningList'>
        <?php
            echo $this->Form->radioList(
                'FlagWarning',
                [
                    0 => 'The words are offensive or inappropriate',
                    1 => 'The author posts spam',
                    2 => 'The post contains an unapproved photo',
                ]
                , ['list' => true]
            );
            $this->fireEvent('FlagContentAfter');
        ?>
    </div>
<?php
echo '<div class="FlatReportButton">',
    $this->Form->button(t('Report'), ['type' => 'button', 'class' => 'btn btn-default btn-shadow']),
    '</div>';
echo $this->Form->close();
?>
