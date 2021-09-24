<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$UcContext = htmlspecialchars(ucfirst($this->data('Plugin.Flagging.Data.Context')));
$ElementID = htmlspecialchars($this->data('Plugin.Flagging.Data.ElementID'));
$URL = $this->data('Plugin.Flagging.Data.URL');
$Title = sprintf("What happened?");
?>
    <div>
    <img src='<?= url("/themes/alloprof/design/images/Horreur.") ?>svg' alt='image' class='FlagAvatar' />
    </div>
<?php if ($this->data('Flagged')) { ?>
    <div class="flag-content">
        <h2 style="margin-bottom: 16px"><?php echo t('Thank you !'); ?></h2>
        <p style="font-size: 18px; line-height: 32px; color: black; margin: 0; padding: 0;"><?php echo t('The moderator team will review the post.'); ?></p>
    </div>
<?php } else { ?>
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
                <span class="d-desktop title"><?php echo t('This post is offensive or inappropriate') ?></span>
                <span class="d-mobile title"><?php echo t('Offensive words') ?></span>
            </label>
        </div>
        <div class="custom-radio">
            <label class="container">
                <input type="radio" name="Plugin-dot-Flagging-dot-Reason" id="Form_Plugin-dot-Flagging-dot-Reason1" value="1">
                <span class="checkmark"></span>
                <span class="d-desktop title"><?php echo t('This post is spam') ?></span>
                <span class="d-mobile title"><?php echo t('Undesirable content') ?></span>
            </label>
        </div>
        <div class="custom-radio">
            <label class="container">
                <input type="radio" name="Plugin-dot-Flagging-dot-Reason" id="Form_Plugin-dot-Flagging-dot-Reason2"  value="2">
                <span class="checkmark"></span>
                <span class="d-desktop title"><?php echo t('This post contains an inappropriate photo') ?></span>
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
<?php } ?>

<script>
    $(document).ready(function() {
        $('.flag-content .close-btn').click(function(e){
            e.preventDefault();
            $('.Overlay').remove();
        })
    })
</script>
