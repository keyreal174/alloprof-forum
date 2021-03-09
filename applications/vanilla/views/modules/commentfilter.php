<?php
if (!defined('APPLICATION')) exit();
require_once $this->fetchViewLocation('helper_functions');

use Vanilla\Utility\HtmlUtils;

// if (\Gdn::themeFeatures()->useNewQuickLinks()) {
//     echo \Gdn_Theme::module(\Vanilla\Theme\VariableProviders\QuickLinksModule::class);
//     return;
// }

$Controller = Gdn::controller();
$Session = Gdn::session();

$titleClasses = HtmlUtils::classNames(
    !Gdn::themeFeatures()->useDataDrivenTheme() && "sr-only",
    "BoxFilter-HeadingWrap"
);
$titleID = "BoxFilterTitle";
?>
<div class="BoxDiscussionFilter Panel" role="navigation" aria-labelledby="<?php echo $titleID ?>">
    <h2 id="<?php echo $titleID ?>" class="BoxFilter-Heading">
        <?php echo t('Filter'); ?>
    </h2>
    <div class="FilterMenu">
        <?php
            echo writeCommentSort($this->Sort);
            echo writeCommentVerifiedToggle($this->IsVerified);
        ?>
    </div>
</div>
