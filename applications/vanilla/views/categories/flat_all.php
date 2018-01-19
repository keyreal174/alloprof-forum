<?php if (!defined('APPLICATION')) exit();

    if (!function_exists('GetOptions')) {
        include $this->fetchViewLocation('helper_functions', 'categories');
    }
?>

<h1 class="H HomepageTitle"><?php echo $this->data('Title').followButton(true); ?></h1>

<?php
    if ($description = $this->description()) {
        echo wrap($description, 'div', ['class' => 'P PageDescription']);
    }
    $this->fireEvent('AfterPageTitle');

    if (c('Vanilla.EnableCategoryFollowing')) {
        echo '<div class="PageControls Top">';
        echo categoryFilters([['url' => 'http://google.ca', 'active' => true, 'name' => 'All'], ['url' => 'http://google.ca', 'name' => 'Following']]);
        echo '</div>';
    }

    $categories = $this->data('CategoryTree');
    $this->EventArguments['NumRows'] = count($categories);
?>

<ul class="DataList CategoryList">
<?php
    foreach ($categories as $category) {
        $this->EventArguments['Category'] = &$category;
        $this->fireEvent('BeforeCategoryItem');

        writeListItem($category, 1);
    }
?>
</ul>

<div class="PageControls Bottom">
    <?php PagerModule::write(); ?>
</div>
