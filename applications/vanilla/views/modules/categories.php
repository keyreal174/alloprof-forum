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
        <?php echo panelHeading(t('Subjects')); ?>
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

                $photoUrl = val('PhotoUrl', $Category);

                if ($Category->isFollowing) {
                    echo '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.47217 11.8889C4.47217 7.67005 7.89222 4.25 12.1111 4.25C14.137 4.25 16.08 5.05481 17.5126 6.48738C18.9451 7.91995 19.7499 9.86293 19.7499 11.8889C19.7499 16.1077 16.3299 19.5278 12.1111 19.5278C7.89222 19.5278 4.47217 16.1077 4.47217 11.8889Z" fill="#05BF8E" stroke="#05BF8E" stroke-width="2.5"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.18838 11.2994C8.51381 10.974 9.04145 10.974 9.36689 11.2994L11.1347 13.0672L15.2594 8.94239C15.5849 8.61696 16.1125 8.61696 16.438 8.94239C16.7634 9.26783 16.7634 9.79547 16.438 10.1209L11.7239 14.835C11.3985 15.1604 10.8708 15.1604 10.5454 14.835L8.18838 12.4779C7.86294 12.1525 7.86294 11.6249 8.18838 11.2994Z" fill="white"/>
                    <path d="M12.1111 18.2778C8.58257 18.2778 5.72217 15.4174 5.72217 11.8889H0.722168C0.722168 18.1788 5.82115 23.2778 12.1111 23.2778V18.2778ZM18.4999 11.8889C18.4999 15.4174 15.6395 18.2778 12.1111 18.2778V23.2778C18.401 23.2778 23.4999 18.1788 23.4999 11.8889H18.4999ZM12.1111 5.5C15.6395 5.5 18.4999 8.3604 18.4999 11.8889H23.4999C23.4999 5.59898 18.401 0.5 12.1111 0.5V5.5ZM12.1111 0.5C5.82115 0.5 0.722168 5.59898 0.722168 11.8889H5.72217C5.72217 8.3604 8.58257 5.5 12.1111 5.5V0.5Z" fill="white"/>
                    </svg>';
                }

                if($photoUrl)
                    $photo = '<span class="category-icon"><img src="'.$photoUrl.'" class="CategoryPhoto" /></span>';
                else $photo = '<span class="category-icon"></span>';

                if ($Category->DisplayAs === 'Heading') {
                    echo htmlspecialchars($Category->Name);
                } else {
                    echo anchor($photo.htmlspecialchars($Category->Name), categoryUrl($Category), 'ItemLink');
                }
                echo "</li>\n";
            }
            ?>
        </ul>
    </div>
<?php
}
