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
            <?php echo writeFilterToggle(null, null, null, true); ?>
        </div>
    </div>

    <div class="filter-block">
        <h4><?php echo t('Level') ?></h4>
        <div class="FilterMenu">
            <?php echo writeGradeFilter(null, true); ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn-default clean-filter"><?php echo t('Clean') ?></button>
    <button class="btn-default btn-shadow apply-filter"><?php echo t('Apply') ?></button>
</div>

<script>
    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
        return false;
    };

    var setPreFilter = function setPreFilter() {
        var grade = getUrlParameter('grade');
        var sort = getUrlParameter('sort');
        var explanation = getUrlParameter('explanation');
        var outexplanation = getUrlParameter('outexplanation');
        var verifiedBy = getUrlParameter('verifiedBy');
        var subject = getUrlParameter('subject');

        if(sort === 'asc')
            $(".FilterPopup input:radio").last().prop("checked", true);
        else $(".FilterPopup input:radio").first().prop("checked", true);

        if(explanation === 'true')
            $('.FilterMenu #Form_MobileExplanation').attr("checked", true);

        if(outexplanation === 'true')
            $('.FilterMenu #Form_MobileOutExplanation').attr("checked", true);

        if(verifiedBy === 'true')
            $('.FilterMenu #Form_MobileVerifiedBy').attr("checked", true);

        $('.FilterMenu .mobile-grade .item[value='+grade+']').addClass('selected');
    }

    setPreFilter();
</script>