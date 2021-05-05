<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$UcContext = htmlspecialchars(ucfirst($this->data('Plugin.Flagging.Data.Context')));
$ElementID = htmlspecialchars($this->data('Plugin.Flagging.Data.ElementID'));
$URL = $this->data('Plugin.Flagging.Data.URL');
$Title = sprintf("What's wrong?");
?>
    <div>
    <img src='<?= url("/themes/alloprof/design/images/Horreur.") ?>svg' alt='image' class='FlagAvatar' />
    </div>
    <div class="flag-content">
    <h2><?php echo t($Title); ?></h2>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
    <div class='FlagWarningList'>
        <div class="custom-radio">
            <label class="container">
                <input type="radio" name="Plugin-dot-Flagging-dot-Reason" id="Form_Plugin-dot-Flagging-dot-Reason" value="0">
                <span class="checkmark"></span>
                <span class="d-desktop title"><?php echo t('The words are offensive or inappropriate') ?></span>
                <span class="d-mobile title"><?php echo t('Offensive words') ?></span>
            </label>
        </div>
        <div class="custom-radio">
            <label class="container">
                <input type="radio" name="Plugin-dot-Flagging-dot-Reason" id="Form_Plugin-dot-Flagging-dot-Reason1" value="1">
                <span class="checkmark"></span>
                <span class="d-desktop title"><?php echo t('The author posts spam') ?></span>
                <span class="d-mobile title"><?php echo t('Undesirable content') ?></span>
            </label>
        </div>
        <div class="custom-radio">
            <label class="container">
                <input type="radio" name="Plugin-dot-Flagging-dot-Reason" id="Form_Plugin-dot-Flagging-dot-Reason2"  value="2">
                <span class="checkmark"></span>
                <span class="d-desktop title"><?php echo t('The post contains an unapproved photo') ?></span>
                <span class="d-mobile title"><?php echo t('Inappropriate image') ?></span>
            </label>
        </div>

        <?php
            $this->fireEvent('FlagContentAfter');
        ?>
    </div>
<?php
echo '<div class="FlatReportButton">',
        '<div class="d-mobile"><button class="btn btn-default close-btn" type="button">'.t('Cancel').'</button></div>',
        $this->Form->button(t('Report'), ['class' => 'btn btn-default btn-shadow']),
    '</div>';
echo $this->Form->close();
?>
</div>

<script>
    $(document).ready(function() {
        $('.flag-content .close-btn').click(function(e){
            e.preventDefault();
            $('.Overlay').remove();
        })
    })
</script>
