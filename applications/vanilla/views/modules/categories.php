<?php if (!defined('APPLICATION')) exit();
$CountDiscussions = 0;
$CategoryID = isset($this->_Sender->CategoryID) ? $this->_Sender->CategoryID : '';
$OnCategories = strtolower($this->_Sender->ControllerName) == 'categoriescontroller' && !is_numeric($CategoryID);
$isHomePage = $this->_Sender->Data["isHomepage"] ?? false;
$onTopLevelCategory = $this->topLevelCategoryOnly && $OnCategories && inSection("CategoryList");
$displayModule = $isHomePage ? true : !$onTopLevelCategory;

if ($this->Data !== FALSE && $displayModule) {
    foreach ($this->Data->result() as $Category) {
        $CountDiscussions = $CountDiscussions + $Category->CountDiscussions;
    }
    ?>
    <div class="Box BoxCategories vanilla-categories">
        <?php echo panelHeading(t('Subjects followed')); ?>
        <ul class="PanelInfo PanelCategories">
            <?php
            $MaxDepth = c('Vanilla.Categories.MaxDisplayDepth');

            $dbRecords = $this->Data->result();
            foreach ($dbRecords as $Category) {
                if ($Category->CategoryID < 0 || $MaxDepth > 0 && $Category->Depth > $MaxDepth)
                    continue;

                $attributes = false;

                if ($Category->DisplayAs === 'Heading') {
                    $CssClass = 'Heading '.$Category->CssClass;
                    $attributes = ['aria-level' => $Category->Depth + 2];
                } else {
                    $CssClass = 'Depth'.$Category->Depth.($CategoryID == $Category->CategoryID ? ' Active' : '').' '.$Category->CssClass;
                }


                if (is_array($attributes)) {
                    $attributes = attribute($attributes);
                }

                echo '<li class="ClearFix '.$CssClass.'" '.$attributes.'>';

                if ($Category->CountAllDiscussions > 0) {
                    $CountText = '<span class="Aside"><span class="Count">'.bigPlural($Category->CountAllDiscussions, '%s discussion').'</span></span>';
                } else {
                    $CountText = '';
                }

                $photoUrl = val('PhotoUrl', $Category);

                if($photoUrl)
                    $photo = '<span class="category-icon"><img src="'.$photoUrl.'" class="CategoryPhoto" /></span>';
                else $photo = '<span class="category-icon"></span>';

                if ($Category->DisplayAs === 'Heading') {
                    echo $CountText.' '.htmlspecialchars($Category->Name);
                } else {
                    echo anchor($photo.''.$CountText.' '.htmlspecialchars($Category->Name), categoryUrl($Category), 'ItemLink');
                }
                echo "</li>\n";
            }
            ?>
        </ul>
        <a href="/categories" class="vanilla-categories__viewall"><?php echo t('See all subjects'); ?></a>
    </div>
<?php
}
