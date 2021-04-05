<?php if (!defined('APPLICATION')) exit();
    include_once $this->fetchViewLocation('helper_functions', 'modules', 'vanilla');

    echo $this->data('Sort');
?>
<div class="modal-header">
    <h3><?php echo t("Filters"); ?></h3>
</div>
<div class="modal-body">
    <div class="filter-block">
        <h4><?php echo t('Sort By') ?></h4>
        <div class="sort">
            <!-- <div class="custom-radio">
                <label class="container">
                    <span><?php echo t('Most popular questions') ?></span>
                    <input type="radio" checked="checked" value="popular" name="sortRadio">
                    <span class="checkmark"></span>
                </label>
            </div> -->
            <div class="custom-radio">
                <label class="container">
                    <span><?php echo t('Most recent first') ?></span>
                    <input type="radio" name="sortRadio" checked="checked" value="desc">
                    <span class="checkmark"></span>
                </label>
            </div>
            <div class="custom-radio">
                <label class="container">
                    <span><?php echo t('The oldest first') ?></span>
                    <input type="radio" name="sortRadio" value="asc">
                    <span class="checkmark"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="filter-block">
        <h4><?php echo t('Status') ?></h4>
        <div class="FilterMenu">
            <?php echo writeFilterToggle(null, null, true); ?>
        </div>
    </div>

    <div class="filter-block">
        <h4><?php echo t('Grade') ?></h4>
        <div class="FilterMenu">
            <?php echo writeGradeFilter(null, true); ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn-default clean-filter"><?php echo t('Clean') ?></button>
    <button class="btn-default btn-shadow apply-filter"><?php echo t('Apply') ?></button>
</div>