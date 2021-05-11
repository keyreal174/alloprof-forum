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
$Title = property_exists($Controller, 'Category') ? val('Name', $Controller->Category, '') : '';
if ($Title == '')
    $Title = t('All Discussions');

$Bookmarked = t('My Bookmarks');
$MyDiscussions = t('My Discussions');
$MyDrafts = t('My Drafts');
$CountBookmarks = 0;
$CountDiscussions = 0;
$CountDrafts = 0;

if ($Session->isValid()) {
    $CountBookmarks = $Session->User->CountBookmarks ?? 0;
    $CountDiscussions = $Session->User->CountDiscussions ?? 0;
    $CountDrafts = $Session->User->CountDrafts ?? 0;
}

if (!function_exists('FilterCountString')) {
    function filterCountString($count, $url = '') {
        $count = countString($count, $url);
        return $count != '' ? '<span class="Aside">'.$count.'</span>' : '';
    }
}
if (c('Vanilla.Discussions.ShowCounts', true)) {
    $Bookmarked .= filterCountString($CountBookmarks, '/discussions/UserBookmarkCount');
    $MyDiscussions .= filterCountString($CountDiscussions);
    $MyDrafts .= filterCountString($CountDrafts);
}
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
            if($this->SubjectID != -1)
                $options = ['Value' => $this->SubjectID];
            else $options = [];

            if (!$this->IsCategory) {
                echo writeCategoryDropDown($this, 'Form_SubjectDropdown', $options);
            }
            echo writeGradeFilter($this->GradeID);
            echo writeDiscussionSort($this->Sort);
            echo writeFilterToggle($this->IsExplanation, $this->IsVerified, $this->IsOutExplanation);
        ?>
    </div>
</div>
