<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();

$CurrentDiscussionLayout = c('Vanilla.Discussions.Layout', '');
if ($CurrentDiscussionLayout == '')
    $CurrentDiscussionLayout = 'modern';

$CurrentCategoriesLayout = c('Vanilla.Categories.Layout', 'modern');
if ($CurrentCategoriesLayout == '')
    $CurrentCategoriesLayout = 'modern';

function writeHomepageOption($title, $url, $iconPath, $current, $description = '') {
    $cssClass = '';
    if ($current == $url) {
        $cssClass = 'active';
    }
    $cssClass .= ' Choice';

    echo wrap(
        '<div class="image-wrap">'
        .img($iconPath, ['alt' => $title, 'class' => 'label-selector-image'])
        .'<div class="overlay">'
        .'<div class="buttons">'
        .anchor(t('Select'), $url, 'btn btn-overlay', ['title' => $description, 'rel' => $url])
        .'</div>'
        .'<div class="selected">'
        .dashboardSymbol('checkmark')
        .'</div>'
        .'</div></div>'
        .'<div class="title">'
        .t($title)
        .'</div>',
        'div',
        ['class' => $cssClass.' label-selector-item']
    );
}

?>
    <h1><?php echo t('Layout'); ?></h1>
    <script>
        jQuery(document).ready(function($) {

            $('.HomeOptions a').click(function() {
                $('.HomeOptions .Choice').removeClass('active');
                $(this).parents('.Choice').addClass('active');
                var page = $(this).attr('rel');
                $('#Form_Target').val(page);
                return false;
            });

            $('.LayoutOptions a').click(function() {
                var parent = $(this).parents('.LayoutOptions');
                var layoutContainer = $(parent).hasClass('DiscussionsLayout') ? 'DiscussionsLayout' : 'CategoriesLayout';
                $(parent).find('.Choice').removeClass('active');
                $(this).parents('.Choice').addClass('active');
                var layout = $(this).attr('rel');
                $('#Form_' + layoutContainer).val(layout);
                return false;
            });

        });
    </script>
    <?php
    $links = '<ul>';
    $links .= wrap(anchor(t("Configuring Vanilla's Homepage"), 'https://docs.vanillaforums.com/developer/configuration/homepage/'), 'li');
    $links .= wrap(anchor(t("Video tutorial on managing appearance"), 'settings/tutorials/appearance'), 'li');
    $links .= '</ul>';
    helpAsset(t('Need More Help?'), $links);
    ?>

    <div class="Homepage">
        <?php echo subheading(
            t('Homepage'),
            sprintf(t('Use the content at this url as your homepage.', 'Choose the page people should see when they visit: <strong style="white-space: nowrap;">%s</strong>'), url('/', true))
        ); ?>
        <div class="HomeOptions label-selector">
            <?php
            $imgFolder = 'applications/dashboard/design/images/';
            // Only show the vanilla pages if Vanilla is enabled
            $CurrentTarget = $this->data('CurrentTarget');

            if (Gdn::addonManager()->isEnabled('Vanilla', \Vanilla\Addon::TYPE_ADDON)) {
                echo writeHomepageOption('Discussions', 'discussions',  $imgFolder.'disc-modern.png', $CurrentTarget);
                echo writeHomepageOption('Categories', 'categories',  $imgFolder.'cat-modern.png', $CurrentTarget);
            }
            $defaultRouteOptions = $this->data('defaultRouteOptions');
            foreach ($defaultRouteOptions as $option => $route) {
                echo writeHomepageOption($option, $route['Destination'], $route['ImageUrl'], $CurrentTarget);
            }

            if (Gdn::addonManager()->isEnabled('Reactions', \Vanilla\Addon::TYPE_ADDON)) {
                echo writeHomepageOption('Best Of', 'bestof', $imgFolder.'best-of.png', $CurrentTarget);
            }
            ?>
        </div>
        <?php if (Gdn::addonManager()->isEnabled('Vanilla', \Vanilla\Addon::TYPE_ADDON)): ?>

        <?php
        echo subheading(t('Discussions'), t('Choose the preferred layout for lists of discussions.'));
        echo $this->data('DiscussionsAlert', '');
        ?>
        <div class="LayoutOptions DiscussionsLayout label-selector">
            <?php
            echo writeHomepageOption('Modern Layout', 'modern', $imgFolder.'disc-modern.png', $CurrentDiscussionLayout, t('Modern non-table-based layout'));
            echo writeHomepageOption('Table Layout', 'table', $imgFolder.'disc-table.png', $CurrentDiscussionLayout, t('Classic table layout used by traditional forums'));
            ?>
        </div>
        <?php
        echo subheading(t('Categories'), t('Choose the preferred layout for lists of categories.'));
        echo $this->data('CategoriesAlert', '');
        ?>
        <div class="LayoutOptions CategoriesLayout label-selector">
            <?php
            echo writeHomepageOption('Modern Layout', 'modern', $imgFolder.'cat-modern.png', $CurrentCategoriesLayout, t('Modern non-table-based layout'));
            echo writeHomepageOption('Table Layout', 'table', $imgFolder.'cat-table.png', $CurrentCategoriesLayout, t('Classic table layout used by traditional forums'));
            echo writeHomepageOption('Mixed Layout', 'mixed', $imgFolder.'cat-mixed.png', $CurrentCategoriesLayout, t('All categories listed with a selection of 5 recent discussions under each'));
            ?>
        </div>

        <?php endif; ?>
    </div>

<?php
echo $this->Form->open();
echo $this->Form->errors();
echo $this->Form->hidden('Target');
echo $this->Form->hidden('DiscussionsLayout', ['value' => $CurrentDiscussionLayout]);
echo $this->Form->hidden('CategoriesLayout', ['value' => $CurrentCategoriesLayout]);
echo $this->Form->close('Save'); ?>
