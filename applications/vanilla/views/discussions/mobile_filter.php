<?php if (!defined('APPLICATION')) exit();
    include_once $this->fetchViewLocation('helper_functions', 'modules', 'vanilla');
?>
<div class="modal-header">
    <h3><?php echo t("Filters"); ?></h3>
</div>
<div class="modal-body">
    <div class="filter-block">
        <h4><?php echo t('Sort By') ?></h4>
        <div class="sort">
            <div class="custom-radio">
                <label class="container">
                    <span><?php echo t('Most popular questions') ?></span>
                    <input type="radio" checked="checked" name="radio">
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="custom-radio">
                <label class="container">
                    <span><?php echo t('Most recent first') ?></span>
                    <input type="radio" name="radio">
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="custom-radio">
                <label class="container">
                    <span><?php echo t('The oldest first') ?></span>
                    <input type="radio" name="radio">
                    <span class="checkmark"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="filter-block">
        <h4><?php echo t('Status') ?></h4>
        <div class="FilterMenu">
            <?php
                $explanation = false;
                $verified = false;

                $role = getUserRole(Gdn::session()->User->UserID);
                echo '<ul>';
                echo '<li class="form-group">';
                $text = $role === 'Teacher' ? t('Without explanations only') : t('With explanations only');
                $verifiedText = $role === 'Teacher' ? t('Not Verified by Alloprof only') : t('Verified by Alloprof only');
                if ($explanation == 'true') {
                    echo Gdn::controller()->Form->toggle('MobileExplanation', $text, [ 'checked' => $explanation ]);
                } else {
                    echo Gdn::controller()->Form->toggle('MobileExplanation', $text);
                }
                echo '</li>';
                echo '<li class="form-group">';
                if ($verified == 'true') {
                    echo Gdn::controller()->Form->toggle('MobileVerifiedBy', $verifiedText, [ 'checked' => $verified ]);
                } else {
                    echo Gdn::controller()->Form->toggle('MobileVerifiedBy', $verifiedText);
                }
                echo '</li>';
                echo '</ul>';
            ?>
        </div>
    </div>

    <div class="filter-block">
        <h4><?php echo t('Grade') ?></h4>
        <div class="FilterMenu">

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn-default"><?php echo t('Clean') ?></button>
    <button class="btn-default btn-shadow"><?php echo t('Apply') ?></button>
</div>