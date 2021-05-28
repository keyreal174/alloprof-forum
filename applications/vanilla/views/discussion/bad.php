<?php
    use Vanilla\Utility\HtmlUtils;
?>

<div class="needmore-modal">
    <div class="d-mobile">
        <h3 class="NeedMoreHelp"><?php echo t('Need more help?') ?></h3>
    </div>
    <img src="<?= url('/themes/alloprof/design/images/noquestion.svg') ?>" />
    <div class="d-desktop">
    <h3 class="NeedMoreHelp"><?php echo t('Need more help?') ?></h3>
    </div>
    <h4 class="NeedMoreCommunicate"><?php echo t('With Alloprof 100% solutions communicate freely with teachers by phone, SMS or chat!') ?></h4>
    <a href="https://www.alloprof.qc.ca/fr/solutions" target="_blank" class="btn-default btn-shadow">
        <span class="d-desktop">
            <?php echo t('Access Alloprof 100% solutions') ?>
        </span>
        <span class="d-mobile">
            <?php echo t('Access') ?>
        </span>
    </a>
</div>