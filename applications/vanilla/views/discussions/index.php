<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
$isDataDrivenTheme = Gdn::themeFeatures()->useDataDrivenTheme();
include_once $this->fetchViewLocation('helper_functions', 'discussions', 'vanilla');
include_once $this->fetchViewLocation('helper_functions', 'categories', 'vanilla');

$checkMark = !$isDataDrivenTheme ? adminCheck(NULL, ['', ' ']) : '';
echo '<div class="FilterBanner">';
if($this->data('Title') && !$this->data('Category'))
echo '<h1 class="H HomepageTitle">'.
    $checkMark.
    $this->data('Title').
    // followButton($this->data('Category.CategoryID')).
    '</h1>';
echo '</div>';

if($this->data('Category')) {
    echo '<div class="CategoryDetail">';
    echo '<div class="Card">';
    echo '<div class="category-info">';
    echo '<div class="item"><h1>'.$this->data('CountAllDiscussions').'</h1><h2>'.t('Questions').'</h2></div>';
    echo '<div class="item"><h1>'.$this->data('CountAllComments').'</h1><h2>'.t('Explanations').'</h2></div>';
    echo '</div>';
    echo '<div class="buttons">';
    if(followButton($this->data('Category.CategoryID')) != '' && $this->getUserRole() == 'Teacher')
        echo '<button class="btn-default btn-shadow">'.followButton($this->data('Category.CategoryID')).'</button>';
    // echo '<button class="btn-default btn-shadow scrollToAskQuestionForm">'.t('Ask a question').'</button>';
    echo '</div>';
    echo '</div>';
}

if($this->data('Category') && $this->getUserRole() != 'Teacher') {
    echo Gdn_Theme::module('NewDiscussionModule', $this->data('_NewDiscussionProperties', ['CssClass' => 'Button Action Primary']));
}


/** @var $htmlSanitizer */
$htmlSanitizer = Gdn::getContainer()->get(\Vanilla\Formatting\Html\HtmlSanitizer::class);
// $Description = $htmlSanitizer->filter($this->data('Category.Description', $this->description()));
echo wrapIf($Description, 'div', ['class' => 'P PageDescription']);

$this->fireEvent('AfterPageTitle');


$subtreeView = $this->fetchViewLocation('subtree', 'categories', 'vanilla', false);
if ($subtreeView) {
    // This use of subtree is deprecated.
    include $subtreeView;
} elseif (isset($this->CategoryModel) && $this->CategoryModel instanceof CategoryModel) {
    $childCategories = $this->data('CategoryTree', []);
    $this->CategoryModel->joinRecent($childCategories);
    if ($childCategories) {
        include($this->fetchViewLocation('helper_functions', 'categories', 'vanilla'));
        if (c('Vanilla.Categories.Layout') === 'table') {
            writeCategoryTable($childCategories);
        } else {
            writeCategoryList($childCategories);
        }
    }
}

$this->fireEvent('AfterCategorySubtree');

$PagerOptions = ['Wrapper' => '<span class="PagerNub">&#160;</span><div %1$s>%2$s</div>', 'RecordCount' => $this->data('CountDiscussions'), 'CurrentRecords' => $this->data('Discussions')->numRows()];
if ($this->data('_PagerUrl'))
    $PagerOptions['Url'] = $this->data('_PagerUrl');

echo '<div class="PageControls Top">';
PagerModule::write($PagerOptions);
// Avoid displaying in a category's list of discussions.
if ($this->data('EnableFollowingFilter')) {
    // comment out to avoid display filter dropdown in discussions page
    echo discussionFilters();
}
$this->fireEvent('PageControls');
echo '</div>';

$ulClass = "DataList Discussions";

// if ($this->data('Home')) {
//     $ulClass = $ulClass . " Home";
// }

$this->addModule('DiscussionFilterModule', 'Content');

if ($this->DiscussionData->numRows() > 0 || (isset($this->AnnounceData) && is_object($this->AnnounceData) && $this->AnnounceData->numRows() > 0)) {
    ?>
    <h2 class="sr-only"><?php echo t('Discussion List'); ?></h2>
    <ul class="<?php echo $ulClass ?>">
        <?php include($this->fetchViewLocation('ordered_discussions', 'Discussions', 'Vanilla')); ?>
    </ul>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
    <?php

} else {
    ?>
    <?php $this->fireEvent('AfterDiscussionsList'); ?>
<?php
}
