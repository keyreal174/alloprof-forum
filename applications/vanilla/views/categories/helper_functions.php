<?php
if (!defined('APPLICATION')) exit();

use Vanilla\Forum\Modules\FoundationCategoriesShim;
use Vanilla\Utility\HtmlUtils;

if (!function_exists('CategoryHeading')):

    /**
     * Write the category heading in a category table.
     * Good for plugins that want to override whats displayed in the heading to the category name.
     *
     * @return string
     * @since 2.1
     */
    function categoryHeading() {
        return t('Categories');
    }

endif;

if (!function_exists('CategoryPhoto')):

    /**
     *
     * @since 2.1
     */
    function categoryPhoto($row) {
        $photoUrl = val('PhotoUrl', $row);

        if ($photoUrl) {
            $result = anchor(
                '<img src="'.$photoUrl.'" class="CategoryPhoto" alt="'.htmlspecialchars(val('Name', $row, '')).'" />',
                categoryUrl($row, '', '//'),
                'Item-Icon PhotoWrap PhotoWrap-Category');
        } else {
            $result = anchor(
                '<span class="sr-only">'.t('Expand for more options.').'</span>',
                categoryUrl($row, '', '//'),
                'Item-Icon PhotoWrap PhotoWrap-Category Hidden NoPhoto');
        }

        return $result;
    }

endif;

if (!function_exists('CategoryString')):

    function categoryString($rows) {
        $result = '';
        foreach ($rows as $row) {
            if ($result)
                $result .= '<span class="Comma">, </span>';
            $result .= anchor(htmlspecialchars($row['Name']), $row['Url']);
        }
        return $result;
    }
endif;

if (!function_exists('getOptions')):
    /**
     * Render options that the user has for this category. Returns an empty string if the session isn't valid.
     *
     * @param $category The category to render the options for.
     * @return DropdownModule|string A dropdown with the category options or an empty string if the session is not valid.
     * @throws Exception
     */
    function getOptions($category) {
        if (!Gdn::session()->isValid()) {
            return '';
        }
        $sender = Gdn::controller();
        $categoryID = val('CategoryID', $category);

        $dropdown = new DropdownModule('dropdown', '', 'OptionsMenu');
        $tk = urlencode(Gdn::session()->transientKey());
        $followed = val('Followed', $category);

        $dropdown->addLink(t('Mark Read'), "/category/markread?categoryid={$categoryID}&tkey={$tk}", 'mark-read');

        if (c('Vanilla.EnableCategoryFollowing') && val('DisplayAs', $category) == 'Discussions') {
            $dropdown->addLink(
                t($followed ? 'Unfollow' : 'Follow'),
                "/category/followed?tkey={$tk}&categoryid={$categoryID}&value=" . ($followed ? 0 : 1),
                'hide'
            );
        }

        // Allow plugins to add options
        $sender->EventArguments['CategoryOptionsDropdown'] = &$dropdown;
        $sender->EventArguments['Category'] = &$category;
        $sender->fireEvent('CategoryOptionsDropdown');

        return $dropdown;
    }
endif;

if (!function_exists('MostRecentString')):
    function mostRecentString($row, $options = []) {
        $options = (array)$options + [
            'showUser' => true,
            'showDate' => true,
        ];

        if (!$row['LastTitle']) {
            return '';
        }

        $r = '';

        $r .= '<span class="MostRecent">';
        $r .= '<span class="MLabel">'.t('Most recent:').'</span> ';
        $r .= anchor(
            sliceString(Gdn_Format::text($row['LastTitle']), 150),
            $row['LastUrl'],
            'LatestPostTitle');

        if ($options['showUser'] && val('LastName', $row)) {
            $r .= ' ';

            $r .= '<span class="MostRecentBy">'.t('by').' ';
            $r .= userAnchor($row, 'UserLink', 'Last');
            $r .= '</span>';
        }

        if ($options['showDate'] && val('LastDateInserted', $row)) {
            $r .= ' ';

            $r .= '<span class="MostRecentOn"><span class="CommentDate">';
            $r .= Gdn_Format::date($row['LastDateInserted'], 'html');
            $r .= '</span></span>';
        }

        $r .= '</span>';

        return $r;
    }
endif;

if (!function_exists('writeListItem')):
    /**
     * Renders a list item in a category list (modern view).
     *
     * @param $category
     * @param $depth
     * @throws Exception
     */
    function writeListItem($category, $depth) {
        $children = $category['Children'];
        $categoryID = val('CategoryID', $category);
        $cssClass = cssClass($category, true);
        $writeChildren = getWriteChildrenMethod($category, $depth);
        $rssIcon = '';
        $headingLevel = $depth + 2;
        /** @var Vanilla\Formatting\Html\HtmlSanitizer */
        $htmlSanitizer = Gdn::getContainer()->get(Vanilla\Formatting\Html\HtmlSanitizer::class);

        if (val('DisplayAs', $category) === 'Discussions') {
            $rssImage = img('applications/dashboard/design/images/rss.gif', ['alt' => t('RSS Feed')]);
            $rssIcon = anchor($rssImage, '/categories/'.val('UrlCode', $category).'/feed.rss', '', ['title' => t('RSS Feed')]);
        }

        if (val('DisplayAs', $category) === 'Heading') : ?>
            <li id="Category_<?php echo $categoryID; ?>" class="CategoryHeading <?php echo $cssClass; ?>">
                <div role="heading" aria-level="<?php echo $headingLevel; ?>" class="ItemContent Category">
                    <div class="Options"><?php echo getOptions($category); ?></div>
                    <?php echo Gdn_Format::text(val('Name', $category));
                    Gdn::controller()->EventArguments['ChildCategories'] = &$children;
                    Gdn::controller()->EventArguments['Category'] = &$category;
                    Gdn::controller()->fireEvent('AfterCategoryHeadingTitle');
                    ?>
                </div>
            </li>
        <?php else: ?>
            <li id="Category_<?php echo $categoryID; ?>" class="<?php echo $cssClass; ?>">
                <?php
                Gdn::controller()->EventArguments['ChildCategories'] = &$children;
                Gdn::controller()->EventArguments['Category'] = &$category;
                Gdn::controller()->fireEvent('BeforeCategoryItem');
                $headingClass = "CategoryNameHeading";
                if (empty($category['Description'])) {
                    $headingClass .= " isEmptyDescription";
                }
                ?>
                <div class="ItemContent Category">
                    <div class="Options">
                        <?php echo getOptions($category) ?>
                    </div>
                    <?php echo categoryPhoto($category); ?>
                    <div role="heading" aria-level="<?php echo $headingLevel; ?>" class="TitleWrap <?php echo $headingClass?>">
                        <?php echo anchor(Gdn_Format::text(val('Name', $category)), categoryUrl($category), 'Title');
                        Gdn::controller()->fireEvent('AfterCategoryTitle');
                        ?>
                    </div>
                    <div class="CategoryDescription">
                        <?php echo $htmlSanitizer->filter((string)val('Description', $category, '')); ?>
                    </div>
                    <div class="Meta">
                        <span class="MItem RSS"><?php echo $rssIcon ?></span>
                        <span class="MItem DiscussionCount">
                            <?php echo sprintf(
                                pluralTranslate(
                                    val('CountAllDiscussions', $category),
                                    '%s discussion html',
                                    '%s discussions html',
                                    t('%s discussion'),
                                    t('%s discussions')
                                ), bigPlural(val('CountAllDiscussions', $category), '%s discussion')) ?>
                        </span>
                        <span class="MItem CommentCount">
                            <?php echo sprintf(
                                pluralTranslate(
                                    val('CountAllComments', $category), '%s comment html',
                                    '%s comments html',
                                    t('%s comment'),
                                    t('%s comments')
                                ), bigPlural(val('CountAllComments', $category), '%s comment')); ?>
                        </span>

                        <?php if (val('LastTitle', $category) != '') : ?>
                            <span class="MItem LastDiscussionTitle">
                                <?php echo mostRecentString($category, ['showDate' => false]); ?>
                            </span>
                            <span class="MItem LastCommentDate">
                                <?php echo Gdn_Format::date(val('LastDateInserted', $category)); ?>
                            </span>
                        <?php endif;
                        if ($writeChildren === 'list'): ?>
                            <div class="ChildCategories">
                                <?php echo wrap(t('Child Categories').': ', 'b'); ?>
                                <?php echo categoryString($children, $depth + 1); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
        <?php endif;
        if ($writeChildren === 'items') {
            foreach ($children as $child) {
                writeListItem($child, $depth + 1);
            }
        }
    }
endif;


if (!function_exists('WriteTableHead')):

    function writeTableHead() {
        ?>
        <tr>
            <td class="CategoryName" role="columnheader">
                <div class="Wrap"><?php echo categoryHeading(); ?></div>
            </td>
            <td class="BigCount CountDiscussions" role="columnheader">
                <div class="Wrap"><?php echo t('Discussions'); ?></div>
            </td>
            <td class="BigCount CountComments" role="columnheader">
                <div class="Wrap"><?php echo t('Comments'); ?></div>
            </td>
            <td class="BlockColumn LatestPost">
                <div class="Wrap"><?php echo t('Latest Post'); ?></div>
            </td>
        </tr>
    <?php
    }
endif;

if (!function_exists('WriteTableRow')):

    function writeTableRow($row, $depth = 1) {
        $children = $row['Children'];
        $writeChildren = getWriteChildrenMethod($row, $depth);
        $h = 'h'.($depth + 1);
        $level = 3;
        /** @var Vanilla\Formatting\Html\HtmlSanitizer */
        $htmlSanitizer = Gdn::getContainer()->get(Vanilla\Formatting\Html\HtmlSanitizer::class);
        /** @var Vanilla\Formatting\DateTimeFormatter */
        $dateTimeFormatter = Gdn::getContainer()->get(\Vanilla\Formatting\DateTimeFormatter::class);

        ?>
        <tr class="<?php echo cssClass($row, true); ?>">
            <td class="CategoryName">
                <div class="Wrap">
                    <?php
                    echo '<div class="Options">'.getOptions($row).'</div>';

                    echo categoryPhoto($row);

                    $headingClass = "CategoryNameHeading";
                    if (empty($row['Description'])) {
                        $headingClass .= " isEmptyDescription";
                    }

                    echo "<{$h} aria-level='".$level."' class='".$headingClass."'>";
                    $safeName = htmlspecialchars($row['Name'] ?? '');
                    echo $row['DisplayAs'] === 'Heading' ? $safeName : anchor($safeName, $row['Url']);
                    Gdn::controller()->EventArguments['Category'] = $row;
                    Gdn::controller()->fireEvent('AfterCategoryTitle');
                    echo "</{$h}>";
                    ?>
                    <div class="CategoryDescription">
                        <?php echo $htmlSanitizer->filter($row['Description'] ?? ''); ?>
                    </div>
                    <?php if ($writeChildren === 'list'): ?>
                        <div class="ChildCategories">
                            <?php
                            echo wrap(t('Child Categories').': ', 'b');
                            echo categoryString($children, $depth + 1);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </td>
            <td class="BigCount CountDiscussions">
                <div class="Wrap">
                    <?php
                    //            echo "({$Row['CountDiscussions']})";
                    echo bigPlural($row['CountAllDiscussions'], '%s discussion');
                    ?>
                </div>
            </td>
            <td class="BigCount CountComments">
                <div class="Wrap">
                    <?php
                    //            echo "({$Row['CountComments']})";
                    echo bigPlural($row['CountAllComments'], '%s comment');
                    ?>
                </div>
            </td>
            <td class="BlockColumn LatestPost">
                <div class="Block Wrap">
                    <?php if ($row['LastTitle']): ?>
                        <?php
                        echo userPhoto($row, ['Size' => 'Small', 'Px' => 'Last']);
                        echo anchor(
                            sliceString(Gdn_Format::text($row['LastTitle']), 100),
                            $row['LastUrl'],
                            'BlockTitle LatestPostTitle',
                            ['title' => html_entity_decode($row['LastTitle'])]);
                        ?>
                        <div class="Meta">
                            <?php
                            echo userAnchor($row, 'UserLink MItem', 'Last');
                            ?>
                            <span class="Bullet">•</span>
                            <?php
                            echo anchor(
                                Gdn_Format::date($row['LastDateInserted'], 'html'),
                                $row['LastUrl'],
                                'CommentDate MItem', [
                                    "aria-label" => HtmlUtils::accessibleLabel('Most recent comment on date %s, in discussion "%s", by user "%s"', [$dateTimeFormatter->formatDate($row['LastDateInserted'] , false), $row['Name'], $row['LastName']]),
                                ]);

                            if (!empty($row['LastCategoryID'])) {
                                $lastCategory = CategoryModel::categories($row['LastCategoryID']);

                                if (is_array($lastCategory)) {
                                    echo ' <span>',
                                    sprintf('in %s', anchor(htmlspecialchars($lastCategory['Name'] ?? ''), categoryUrl($lastCategory, '', '//'))),
                                    '</span>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php
        if ($writeChildren === 'items') {
            foreach ($children as $childRow) {
                writeTableRow($childRow, $depth + 1);
            }
        }
    }
endif;

if (!function_exists('writeCategoryList')):
    /**
     * Renders a category list (modern view).
     *
     * @param $categories
     * @param int $depth
     */
    function writeCategoryList($categories, $depth = 1) {
        if (empty($categories)) {
            echo '<div class="Empty">'.t('No categories were found.').'</div>';
            return;
        }

        ?>
        <div class="DataListWrap">
            <h2 class="sr-only"><?php echo t('Category List'); ?></h2>

            <?php
                if (FoundationCategoriesShim::isEnabled()) {
                    FoundationCategoriesShim::printLegacyShim($categories);
                } else {
                    echo '<ul class="DataList CategoryList">';
                    foreach ($categories as $category) {
                        writeListItem($category, $depth);
                    }
                    echo '</ul>';
                }
            ?>
        </div>
        <?php
    }
endif;

if (!function_exists('writeCategoryTable')):
    function writeCategoryTable($categories, $depth = 1, $inTable = false) {
        if (empty($categories)) {
            echo '<div class="Empty">'.t('No categories were found.').'</div>';
            return;
        }

        foreach ($categories as $category) {
            $displayAs = val('DisplayAs', $category);
            $urlCode = $category['UrlCode'];
            $class = val('CssClass', $category);
            $name = htmlspecialchars($category['Name'] ?? '');

            if ($displayAs === 'Heading') :
                if ($inTable) {
                    echo '</tbody></table></div>';
                    $inTable = false;
                }
                ?>
                <div id="CategoryGroup-<?php echo $urlCode; ?>" class="CategoryGroup <?php echo $class; ?>">
                    <h2 class="H categoryList-heading"><?php echo $name; ?></h2>
                    <?php writeCategoryTable($category['Children'], $depth + 1, $inTable); ?>
                </div>
                <?php
            else :
                if (!$inTable) { ?>
                    <div class="DataTableWrap">
                        <h2 class="sr-only categoryList-genericHeading"><?php echo t('Category List') ?></h2>
                        <table class="DataTable CategoryTable">
                            <thead>
                            <?php writeTableHead(); ?>
                            </thead>
                            <tbody>
                    <?php $inTable = true;
                }
                writeTableRow($category, $depth);
            endif;
        }
        if ($inTable) {
            echo '</tbody></table></div>';
        }
    }
endif;

if (!function_exists('getWriteChildrenMethod')):
    /**
     * Calculates how to display category children. Either 'list' for a comma-separated list (usually appears in meta) or
     * 'items' to nest children below the parent or false if there are no children.
     *
     * @param $category
     * @param $depth
     * @return bool|string
     */
    function getWriteChildrenMethod($category, $depth) {
        $children = val('Children', $category);
        $writeChildren = false;
        $maxDisplayDepth = c('Vanilla.Categories.MaxDisplayDepth');
        $isHeading = val('DisplayAs', $category) === 'Heading';

        if (!empty($children)) {
            if (!$isHeading && $maxDisplayDepth > 0 && ($depth + 1) >= $maxDisplayDepth) {
                $writeChildren = 'list';
            } else {
                $writeChildren = 'items';
            }
        }

        return $writeChildren;
    }
endif;

if (!function_exists('followButton')) :
    /**
     *
     * Writes the Follow/following button
     *
     * @param int $categoryID
     * @return string
     */
    function followButton($categoryID) {
        $output = ' ';
        if (!is_numeric($categoryID)) {
            return $output;
        }

        $userID = Gdn::session()->UserID;
        $category = CategoryModel::categories($categoryID);

        if (c('Vanilla.EnableCategoryFollowing') && $userID && $category && $category['DisplayAs'] == 'Discussions') {
            $categoryModel = new CategoryModel();
            $following = $categoryModel->isFollowed($userID, $categoryID);

            $iconTitle = t('Follow');

            $icon_following = <<<EOT
            <svg width="19" height="24" viewBox="0 0 19 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.47376 0.75C10.1278 0.75 10.658 1.28019 10.658 1.93421V2.72368C10.658 3.37771 10.1278 3.9079 9.47376 3.9079C8.81974 3.9079 8.28955 3.37771 8.28955 2.72368V1.93421C8.28955 1.28019 8.81974 0.75 9.47376 0.75Z" fill="#2F80ED"/>
                <path d="M17.1467 18.8096C18.4114 18.8096 19.1103 17.3289 18.3115 16.3193L17.1467 14.9059C16.2814 13.829 15.8155 12.5166 15.8155 11.1368V8.51191C15.8155 5.34857 13.6189 2.72367 10.6902 2.08428C9.82488 1.89537 8.85659 1.87425 7.89459 2.08428C4.96587 2.72369 2.76933 5.34857 2.76933 8.51191V11.1368C2.76933 12.5166 2.3034 13.829 1.43809 14.9059L0.339822 16.3193C-0.45892 17.3289 0.239979 18.8096 1.50465 18.8096H17.1467Z" fill="#2F80ED"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.84412 19.6937C6.31516 19.4195 6.91927 19.5791 7.19342 20.0502C7.40906 20.4207 7.71752 20.7286 8.0884 20.9436C8.45928 21.1586 8.87978 21.2732 9.30846 21.2762C9.73714 21.2792 10.1592 21.1705 10.533 20.9607C10.9069 20.7509 11.2196 20.4473 11.4404 20.0798C11.7211 19.6127 12.3274 19.4615 12.7946 19.7422C13.2617 20.0229 13.4129 20.6292 13.1322 21.0963C12.7348 21.7578 12.1719 22.3042 11.4989 22.6818C10.826 23.0595 10.0663 23.2552 9.29468 23.2499C8.52306 23.2445 7.76616 23.0381 7.09858 22.6511C6.43099 22.2641 5.87577 21.7099 5.48762 21.043C5.21346 20.5719 5.37307 19.9678 5.84412 19.6937Z" fill="#2F80ED"/>
            </svg>
            EOT;

            $icon_follow = <<<EOT
            <svg width="19" height="24" viewBox="0 0 19 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.47376 0.75C10.1278 0.75 10.658 1.28019 10.658 1.93421V2.72368C10.658 3.37771 10.1278 3.9079 9.47376 3.9079C8.81974 3.9079 8.28955 3.37771 8.28955 2.72368V1.93421C8.28955 1.28019 8.81974 0.75 9.47376 0.75Z" fill="#1A1919"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M15.3096 16.4009L15.3004 16.3894C14.1003 14.8958 13.447 13.0611 13.447 11.1368V8.51191C13.447 6.47072 12.0346 4.80199 10.185 4.3982C9.63462 4.27804 9.01141 4.26466 8.39978 4.39819C6.5502 4.802 5.13775 6.47073 5.13775 8.51191V11.1368C5.13775 13.0541 4.48919 14.8825 3.29736 16.3732L3.24451 16.4412H15.3429L15.3096 16.4009ZM18.3115 16.3193C19.1103 17.3289 18.4114 18.8096 17.1467 18.8096H1.50465C0.239979 18.8096 -0.45892 17.3289 0.339822 16.3193L1.43809 14.9059C2.3034 13.829 2.76933 12.5166 2.76933 11.1368V8.51191C2.76933 5.34857 4.96587 2.72369 7.89459 2.08428C8.85659 1.87425 9.82488 1.89537 10.6902 2.08428C13.6189 2.72367 15.8155 5.34857 15.8155 8.51191V11.1368C15.8155 12.5166 16.2814 13.829 17.1467 14.9059L18.3115 16.3193Z" fill="#1A1919"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.84412 19.6937C6.31516 19.4195 6.91927 19.5791 7.19342 20.0502C7.40906 20.4207 7.71752 20.7286 8.0884 20.9436C8.45928 21.1586 8.87978 21.2732 9.30846 21.2762C9.73714 21.2792 10.1592 21.1705 10.533 20.9607C10.9069 20.7509 11.2196 20.4473 11.4404 20.0798C11.7211 19.6127 12.3274 19.4615 12.7946 19.7422C13.2617 20.0229 13.4129 20.6292 13.1322 21.0963C12.7348 21.7578 12.1719 22.3042 11.4989 22.6818C10.826 23.0595 10.0663 23.2552 9.29468 23.2499C8.52306 23.2445 7.76616 23.0381 7.09858 22.6511C6.43099 22.2641 5.87577 21.7099 5.48762 21.043C5.21346 20.5719 5.37307 19.9678 5.84412 19.6937Z" fill="#1A1919"/>
            </svg>
            EOT;

            $text = $following ? t('Unfollow Subject') : t('Follow Subject');
            $icon = $following ? $icon_following: $icon_follow;
            $output .= anchor(
                $icon.$text,
                "/category/followed/{$categoryID}/".Gdn::session()->transientKey(),
                'Hijack followButton'.($following ? ' TextColor isFollowing' : ''),
                ['title' => $text, 'aria-pressed' => $following ? 'true' : 'false', 'role' => 'button', 'tabindex' => '0']
            );
        }
        return $output;
    }
endif;
