<?php
if (!defined('APPLICATION')) exit();

use Vanilla\Cache\StaticCache;
use Vanilla\Utility\HtmlUtils;


if (!function_exists('AdminCheck')) {
    /**
     *
     *
     * @param null $discussion
     * @param bool|FALSE $wrap
     * @return string
     */
    function adminCheck($discussion = null, $wrap = FALSE) {
        $useAdminChecks = StaticCache::getOrHydrate("useAdminCheck", function () {
            return c('Vanilla.AdminCheckboxes.Use') && Gdn::session()->checkPermission('Garden.Moderation.Manage');;
        });
        if (!$useAdminChecks) {
            return '';
        }

        static $canEdits = [], $checked = NULL;
        $result = '';

        if ($discussion) {
            if (!isset($canEdits[$discussion->CategoryID])) {
                $canEdits[$discussion->CategoryID] = val('PermsDiscussionsEdit', CategoryModel::categories($discussion->CategoryID));
            }

            if ($canEdits[$discussion->CategoryID]) {
                // Grab the list of currently checked discussions.
                if ($checked === null) {
                    $checked = (array)Gdn::session()->getAttribute('CheckedDiscussions', []);

                    if (!is_array($checked)) {
                        $checked = [];
                    }
                }

                if (in_array($discussion->DiscussionID, $checked))
                    $itemSelected = ' checked="checked"';
                else
                    $itemSelected = '';

                $result = '<span class="AdminCheck"><input type="checkbox" name="DiscussionID[]" aria-label="' . t('Select Discussion') . '" value="' . $discussion->DiscussionID . '" $itemSelected /></span>';
            }
        } else {
            $result = '<span class="AdminCheck"><input type="checkbox" aria-label="' . t('Select Discussion') . '" name="Toggle" /></span>';
        }

        if ($wrap) {
            $result = $wrap[0].$result.$wrap[1];
        }

        return $result;
    }
}

if (!function_exists('BookmarkButton')) {
    /**
     *
     *
     * @param $discussion
     * @return string
     */
    function bookmarkButton($discussion) {
        if (!Gdn::session()->isValid()) {
            return '';
        }

        $popupClass = "";
        $popupLink = "/discussion/confirmFollow/".$discussion->DiscussionID;

        $hasFollowedTeacher = false;
        if (userRoleCheck() == 'Teacher' && $discussion->Bookmarked != '1') {
            $data = Gdn::sql()
                    ->select('*')
                    ->from('UserDiscussion')
                    ->where('DiscussionID', $discussion->DiscussionID)
                    ->where('Bookmarked', 1)
                    ->get();
            foreach ($data as $row) {
                $followedUserID = val('UserID', $row);
                if ($followedUserID != Gdn::session()->UserID) {
                    if (userRoleCheck($followedUserID) == 'Teacher') {
                        $hasFollowedTeacher = true;
                        break;
                    }
                }
            }

            if ($hasFollowedTeacher) {
                $popupClass = " OptionsLink Popup";
            }
        }

        // Bookmark link
        $isBookmarked = $discussion->Bookmarked == '1';

        // Bookmark link
        $title = t($isBookmarked ? 'UnFollow' : 'Follow');

        $accessibleLabel= HtmlUtils::accessibleLabel('%s for discussion: "%s"', [t($isBookmarked? 'UnFollow' : 'Follow'), is_array($discussion) ? $discussion["Name"] : $discussion->Name]);

        $icon_following = <<<EOT
        <svg width="21" height="24" viewBox="0 0 21 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.5 3.75C0.5 2.09315 1.84315 0.75 3.5 0.75H17.8077C19.4645 0.75 20.8077 2.09315 20.8077 3.75V22.4894C20.8077 23.3576 19.7774 23.8134 19.135 23.2294L10.9902 15.825C10.7995 15.6516 10.5082 15.6516 10.3175 15.825L2.17267 23.2294C1.5303 23.8134 0.5 23.3576 0.5 22.4894V3.75Z" fill="black"/>
        </svg>
        EOT;

        $icon_follow = <<<EOT
        <svg width="21" height="24" viewBox="0 0 19 21" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1.75049 3.5C1.75049 2.5335 2.53399 1.75 3.50049 1.75H15.2697C16.2362 1.75 17.0197 2.5335 17.0197 3.5V18.6743L10.5623 12.8039C9.89479 12.1971 8.87541 12.1971 8.20792 12.8039L1.75049 18.6743V3.5Z" stroke="black" stroke-width="2.5"/>
        </svg>
        EOT;

        $icon = $isBookmarked ? $icon_following : $icon_follow;

        if ($hasFollowedTeacher) {
            return anchor(
                $icon,
                $popupLink,
                'Hijack followButton Option-Icon Popup'.($isBookmarked ? ' TextColor isFollowing' : ''),
                ['title' => $title, 'id' => 'followButton'.$discussion->DiscussionID, 'aria-pressed' => $isBookmarked ? 'true' : 'false', 'role' => 'button', 'aria-label' => $accessibleLabel]
            );
        }

        return anchor(
            $icon,
            '/discussion/bookmark/'.$discussion->DiscussionID.'/'.Gdn::session()->transientKey(),
            'Hijack followButton Option-Icon'.($isBookmarked ? ' TextColor isFollowing' : ''),
            ['title' => $title, 'id' => 'followButton'.$discussion->DiscussionID, 'aria-pressed' => $isBookmarked ? 'true' : 'false', 'role' => 'button', 'aria-label' => $accessibleLabel]
        );
    }
}

if (!function_exists('CategoryLink')) :
    /**
     *
     *
     * @param $discussion
     * @param string $prefix
     * @return string
     */
    function categoryLink($discussion, $prefix = ' ') {
        $category = CategoryModel::categories(val('CategoryID', $discussion));
        if ($category) {
            $name = is_array($category) ? $category["Name"] : $category->Name;
            $accessibleLabel= HtmlUtils::accessibleLabel('Category: "%s"', [$name]);
            return wrap(
        $prefix.anchor(htmlspecialchars($name), $category['Url'], ["aria-label" => $accessibleLabel]),
                'span',
                ['class' => 'MItem Category']
            );
        }
    }

endif;

if (!function_exists('DiscussionHeading')) :
    /**
     *
     *
     * @return string
     */
    function discussionHeading() {
        return t('Discussion');
    }

endif;

if (!function_exists('WriteDiscussion')) :
    /**
     *
     *
     * @param $discussion
     * @param $sender
     * @param $session
     */
    function writeDiscussion($discussion, $sender, $session) {
        $cssClass = cssClass($discussion);
        $discussionUrl = $discussion->Url;
        $category = CategoryModel::categories($discussion->CategoryID);
        /** @var Vanilla\Formatting\DateTimeFormatter */
        $dateTimeFormatter = Gdn::getContainer()->get(\Vanilla\Formatting\DateTimeFormatter::class);


        if ($session->UserID) {
            $discussionUrl .= '#latest';
        }
        $sender->EventArguments['DiscussionUrl'] = &$discussionUrl;
        $sender->EventArguments['Discussion'] = &$discussion;
        $sender->EventArguments['CssClass'] = &$cssClass;

        $first = userBuilder($discussion, 'First');
        $last = userBuilder($discussion, 'Last');
        $sender->EventArguments['FirstUser'] = &$first;
        $sender->EventArguments['LastUser'] = &$last;

        $sender->fireEvent('BeforeDiscussionName');

        $discussionName = $discussion->Name;
        $sender->EventArguments['DiscussionName'] = &$discussionName;

        static $firstDiscussion = true;
        if (!$firstDiscussion) {
            $sender->fireEvent('BetweenDiscussion');
        } else {
            $firstDiscussion = false;
        }

        $discussion->CountPages = ceil($discussion->CountComments / $sender->CountCommentsPerPage);
        ?>
        <li id="Discussion_<?php echo $discussion->DiscussionID; ?>" class="<?php echo $cssClass; ?>">
            <?php
            if (!property_exists($sender, 'CanEditDiscussions')) {
                $sender->CanEditDiscussions = val('PermsDiscussionsEdit', CategoryModel::categories($discussion->CategoryID)) && c('Vanilla.AdminCheckboxes.Use');
            }
            $sender->fireEvent('BeforeDiscussionContent');
            ?>
      <?php
      // render legacy options
      if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
            echo '<span class="Options">';
            echo optionsList($discussion);
            echo bookmarkButton($discussion);
            echo '</span>';
        }
      ?>

            <div class="ItemContent Discussion">
                <div class="Title" role="heading" aria-level="3">
                    <?php
                    echo adminCheck($discussion, ['', ' ']).anchor($discussionName, $discussionUrl);
                    $sender->fireEvent('AfterDiscussionTitle');
                    ?>
                </div>
                <div class="Meta Meta-Discussion">
                    <?php
                    writeTags($discussion);
                    ?>
                    <span class="MItem MCount ViewCount"><?php
                        printf(pluralTranslate($discussion->CountViews,
                            '%s view html', '%s views html', t('%s view'), t('%s views')),
                            bigPlural($discussion->CountViews, '%s view'));
                        ?></span>
         <span class="MItem MCount CommentCount"><?php
             printf(pluralTranslate($discussion->CountComments,
                 '%s comment html', '%s comments html', t('%s comment'), t('%s comments')),
                 bigPlural($discussion->CountComments, '%s comment'));
             ?></span>
         <span class="MItem MCount DiscussionScore Hidden"><?php
             $score = $discussion->Score;
             if ($score == '') $score = 0;
             printf(plural($score,
                 '%s point', '%s points',
                 bigPlural($score, '%s point')));
             ?></span>
                    <?php
                    echo newComments($discussion);
                    $layout = c('Vanilla.Categories.Layout');

                    $sender->fireEvent('AfterCountMeta');

                    $discussionName = is_array($discussion) ? $discussion['Name'] : $discussion->Name;

                    if ($discussion->LastCommentID != '') {
                        echo ' <span class="MItem LastCommentBy">'.sprintf(t('Most recent by %1$s'), userAnchor($last)).'</span> ';
                        echo ' <span class="MItem LastCommentDate">'.Gdn_Format::date($discussion->LastDate, "html").'</span>';
                        $userName = $last->Name;

                        if ($layout !== "mixed") {
                            $template = t('Most recent comment on date %s, in discussion "%s", by user "%s"');
                            $accessibleVars = [$dateTimeFormatter->formatDate($discussion->LastDate, false), $discussionName, $userName];
                        } else {
                            $template = t('Category: "%s"');
                            $accessibleVars = [$discussion->Category];
                        }

                    } else {
                        echo ' <span class="MItem LastCommentBy">'.sprintf(t('Started by %1$s'), userAnchor($first)).'</span> ';
                        echo ' <span class="MItem LastCommentDate">'.Gdn_Format::date($discussion->FirstDate, "html");
                        if ($source = val('Source', $discussion)) {
                            echo ' '.sprintf(t('via %s'), t($source.' Source', $source));
                        }
                        echo '</span> ';
                        $template = t('User "%s" started discussion "%s" on date %s');
                        $userName = $first->Name;
                        $accessibleVars = [$userName, $discussionName, $dateTimeFormatter->formatDate($discussion->FirstDate, false)];
                    }

                    if ($sender->data('_ShowCategoryLink', true) && $category && c('Vanilla.Categories.Use') &&
                        CategoryModel::checkPermission($category, 'Vanilla.Discussions.View')) {
                        $accessibleAttributes = ["tabindex" => "0", "aria-label" => HtmlUtils::accessibleLabel($template, $accessibleVars)];
                        if ($layout === "mixed") { // The links to categories are duplicates and have no accessible value
                            $accessibleAttributes['tabindex'] = "-1";
                            $accessibleAttributes['aria-hidden'] = "true";
                        }
                        echo wrap(
                            anchor(htmlspecialchars($discussion->Category),
                                categoryUrl($discussion->CategoryUrlCode), $accessibleAttributes),
                            'span',
                            ['class' => 'MItem Category '.$category['CssClass']]
                        );
                    }
                    $sender->fireEvent('DiscussionMeta');
                    ?>
                </div>
            </div>
            <?php
                // render enhanced accessibility options
                if (Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                    echo '<span class="Options">';
                    echo bookmarkButton($discussion);
                    echo optionsList($discussion);
                    echo '</span>';
                }
                $sender->fireEvent('AfterDiscussionContent');
            ?>
        </li>
    <?php
    }
endif;

if (!function_exists('writeCategoryDropDown')) :
    function writeCategoryDropDown($sender, $fieldName = 'CategoryID', $options = []) {
        $sender->EventArguments['Options'] = &$options;
        $sender->fireEvent('BeforeCategoryDropDown');

        $value = arrayValueI('Value', $options); // The selected category id
        $categoryData = val('CategoryData', $options);

        // Grab the category data.
        if (!$categoryData) {
            $categoryData = CategoryModel::getByPermission(
                'Discussions.View',
                $value,
                val('Filter', $options, ['Archived' => 0]),
                val('PermFilter', $options, [])
            );
        }

        // Remove categories the user shouldn't see.
        $safeCategoryData = [];
        $discussionType = val('DiscussionType', $options);
        foreach ($categoryData as $categoryID => $category) {
            if ($value != $categoryID) {
                if ($category['CategoryID'] <= 0 || !$category['PermsDiscussionsView']) {
                    continue;
                }

                if ($category['Archived']) {
                    continue;
                }

                // Filter out categories that don't allow our discussion type, if specified
                if ($discussionType) {
                    $permissionCategory = CategoryModel::permissionCategory($category);
                    $allowedDiscussionTypes = CategoryModel::allowedDiscussionTypes($permissionCategory, $category);
                    if (!array_key_exists($discussionType, $allowedDiscussionTypes)) {
                        continue;
                    }
                }
            }

            $safeCategoryData[$categoryID] = $category;
        }
        unset($discussionType, $permissionCategory, $allowedDiscussionTypes);

        unset($options['Filter'], $options['PermFilter'], $options['Context'], $options['CategoryData']);

        // Opening select tag
        $return = '<select name='.$fieldName.' id='.$fieldName.'>';

        // Get value from attributes
        if (!is_array($value)) {
            $value = [$value];
        }

        // Prevent default $Value from matching key of zero
        $hasValue = ($value !== [false] && $value !== ['']) ? true : false;

        // Start with null option?
        $includeNull = val('IncludeNull', $options);
        if ($includeNull === true) {
            // $return .= '<option value="">'.t('Select a category...').'</option>';
        } elseif (is_array($includeNull))
            $return .= "<option value=\"{$includeNull[0]}\">{$includeNull[1]}</option>\n";
        elseif ($includeNull)
            $return .= "<option value=\"\">$includeNull</option>\n";
        elseif (!$hasValue)
            $return .= '<option value=""></option>';

        // Show root categories as headings (ie. you can't post in them)?
        $doHeadings = val('Headings', $options, c('Vanilla.Categories.DoHeadings'));

        // If making headings disabled and there was no default value for
        // selection, make sure to select the first non-disabled value, or the
        // browser will auto-select the first disabled option.
        $forceCleanSelection = ($doHeadings && !$hasValue && !$includeNull);

        // Write out the category options.
        $enableHeadings = $options['EnableHeadings'] ?? false;
        if (is_array($safeCategoryData)) {
            foreach ($safeCategoryData as $categoryID => $category) {
                $depth = val('Depth', $category, 0);
                $isHeading = ($depth == 1 && $doHeadings) || $category['DisplayAs'] !== 'Discussions' || !$category['AllowDiscussions'];
                $disabled = $isHeading && !$enableHeadings;
                $selected = in_array($categoryID, $value) && $hasValue;
                if ($forceCleanSelection && $depth > 1) {
                    $selected = true;
                    $forceCleanSelection = false;
                }

                if ($category['AllowDiscussions']) {
                    if ($permission == 'add' && !$category['PermsDiscussionsAdd']) {
                        $disabled = true;
                    }
                }

                $return .= '<option value="'.$categoryID.'" data-img_src="'.$category['Photo'].'"';
                if ($disabled) {
                    $return .= ' disabled="disabled"';
                } elseif ($selected) {
                    $return .= ' selected="selected"'; // only allow selection if NOT disabled
                }

                $name = htmlspecialchars(val('Name', $category, 'Blank Category Name'));
                if ($depth > 1) {
                    $name = str_repeat('&#160;', 4 * ($depth - 1)).$name;
                }

                $return .= '>'.$name."</option>\n";
            }
        }

        echo '<div class="Category rich-select select2 select2-category">';
        echo '<div class="category-selected-img pre-icon"><img src="'.url("/themes/alloprof/design/images/icons/subject.svg").'"/></div>';
        echo $return.'</select>';
        echo '</div>';
    }
endif;

if (!function_exists('timeElapsedString')) :
    function timeElapsedString($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        // $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->
                $k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            // $string = array_slice($string, 0, 1);
            $frenchStr = "";
            if ($diff->y) {
                if ($diff->y > 1) $frenchStr = "Il y a ". $diff->y . " ans";
                else $frenchStr = "Il y a 1 an";
            } else {
                if ($diff->m) {
                    if ($diff->m > 1) $frenchStr = "Il ya ". $diff->m . " mois";
                    else $frenchStr = "Il ya 1 mois";
                } else {
                    if ($diff->d) {
                        if ($diff->d > 1) $frenchStr = "Il y a ". $diff->d . " jours";
                        else $frenchStr = "Il y a 1 jour";
                    } else {
                        if ($diff->h) {
                            if ($diff->h > 1) $frenchStr = "Il y a ". $diff->h . " heures";
                            else $frenchStr = "Il y a 1 heure";
                        } else {
                            if ($diff->i) {
                                if ($diff->i > 1) $frenchStr = "Il y a moins de ". $diff->i . " minutes";
                                else $frenchStr = "Il y a 1 minute";
                            } else {
                                $frenchStr = "Il y a moins de 1 minute";
                            }
                        }
                    }
                }
            }
            return $frenchStr;
        }

        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
endif;

if (!function_exists('writeDiscussionDetail')) :
    function writeDiscussionDetail($Discussion, $sender, $session) {
        $Author = Gdn::userModel()->getID($Discussion->InsertUserID); // userBuilder($Discussion, 'Insert');
        $AuthorMetaData = Gdn::userModel()->getMeta($Author->UserID, 'Profile.%', 'Profile.');
        $cssClass = cssClass($Discussion);
        $category = CategoryModel::categories($Discussion->CategoryID);
        $discussionUrl = $Discussion->Url;

        $grade = getGrade($Discussion->GradeID);

        $dateTimeFormatter = Gdn::getContainer()->get(\Vanilla\Formatting\DateTimeFormatter::class);

        $sender->EventArguments['Discussion'] = &$DiscussionDiscussion;
        $sender->EventArguments['DiscussionUrl'] = &$discussionUrl;
        $sender->EventArguments['Author'] = &$Author;
        $sender->EventArguments['CssClass'] = &$cssClass;

        $sender->EventArguments['Object'] = &$Discussion;
        $sender->EventArguments['Type'] = 'Discussion';

        $userId = Gdn::session()->UserID;

        $sender->fireEvent('BeforeDiscussionDisplay');
        ?>
        <li id="Discussion_<?php echo $Discussion->DiscussionID; ?>" class="<?php echo $cssClass; ?>">
            <?php
                if ($Discussion->DateAccepted) {
                    echo "<div class='verified-info mobile'>
                        <img src='".url("/themes/alloprof/design/images/icons/verifiedbadge.svg")."'/><span>".t("Verified by Alloprof")."</span></div>";
                }
            ?>

            <div class="Discussion">
                <div class="Item-Header DiscussionHeader">
                    <?php
                    if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                        ?>
                        <span class="Options-Icon">
                        <?php
                            echo optionsList($Discussion);
                        ?>
                        </span>
                        <?php
                    }
                    ?>
                    <div class="AuthorWrap">
                        <?php
                            if(!$Discussion->Published) {
                                echo '<div class="not-published-badge">';
                                echo '<img src="'.url("/themes/alloprof/design/images/icons/eyebreak.svg").'"/>';
                                echo t('Awaiting publication');
                                echo '</div>';
                            }
                        ?>
                        <span class="Author">
                            <?php
                            if ($UserPhotoFirst) {
                                echo userPhoto($Author);
                                echo userAnchor($Author, 'Username');
                            } else {
                                echo userAnchor($Author, 'Username');
                                echo userPhoto($Author);
                            }
                            echo formatMeAction($Discussion);
                            ?>
                        </span>
                        <span class="AuthorInfo">
                            <?php
                                $sender->fireEvent('AuthorInfo');
                            ?>
                        </span>
                        <?php
                            if ($sender->data('_ShowCategoryLink', true) && $category && c('Vanilla.Categories.Use') &&
                                CategoryModel::checkPermission($category, 'Vanilla.Discussions.View')) {
                                $accessibleAttributes = ["tabindex" => "0", "aria-label" => HtmlUtils::accessibleLabel($template, $accessibleVars)];
                                if ($layout === "mixed") { // The links to categories are duplicates and have no accessible value
                                    $accessibleAttributes['tabindex'] = "-1";
                                    $accessibleAttributes['aria-hidden'] = "true";
                                }
                                echo wrap(
                                    anchor(htmlspecialchars($discussion->Category),
                                        categoryUrl($discussion->CategoryUrlCode), $accessibleAttributes),
                                    'span',
                                    ['class' => 'MItem Category '.$category['CssClass']]
                                );
                            }
                        ?>
                        <?php
                            if ($Discussion->DateAccepted) {
                                echo "<div class='verfied-badge'>
                                        <img src='".url("/themes/alloprof/design/images/icons/verifiedbadge.svg")."'/>
                                        <span>". t('Verified by Alloprof') ."</span>
                                    </div>";
                            }
                        ?>
                    </div>
                    <div class="Meta DiscussionMeta">
                        <span class="MItem TimeAgo">
                            <?php
                                if ($grade) {
                                    echo $grade . ' • ' . timeElapsedString($Discussion->FirstDate, false);
                                } else {
                                    echo timeElapsedString($Discussion->FirstDate, false);
                                }
                            ?>
                        </span>
                        <?php
                            $sender->fireEvent('DiscussionInfo');
                            $sender->fireEvent('AfterDiscussionMeta'); // DEPRECATED
                        ?>
                    </div>
                </div>
                <?php $sender->fireEvent('BeforeDiscussionBody'); ?>
                <div class="Item-BodyWrap">
                    <div class="Item-Body">
                        <div class="Message userContent">
                            <?php
                            echo formatBody($Discussion);
                            ?>
                        </div>
                        <?php
                        $sender->fireEvent('AfterDiscussionBody');
                        if (val('Attachments', $Discussion)) {
                            writeAttachments($Discussion->Attachments);
                        }
                        ?>
                        <?php  echo "<a class='QuestionCategory' style='background: ".$category["Color"]."' href='".url('/categories/'.$category["UrlCode"])."'>".$category["Name"]."</a>"; ?>
                    </div>
                    <?php
                        writeDiscussionFooter($Discussion, $sender);
                    ?>
                </div>
            </div>
        </li>
        <?php
    }
endif;

if (!function_exists('WriteDiscussionSorter')) :
    /**
     *
     *
     * @param null $selected
     * @param null $options
     */
    function writeDiscussionSorter($selected = null, $options = null) {
        deprecated('writeDiscussionSorter', 'DiscussionSortFilterModule', 'March 2016');

        if ($selected === null) {
            $selected = Gdn::session()->getPreference('Discussions.SortField', 'DateLastComment');
        }
        $selected = stringBeginsWith($selected, 'd.', TRUE, true);

        $options = [
            'DateLastComment' => t('Sort by Last Comment', 'by Last Comment'),
            'DateInserted' => t('Sort by Start Date', 'by Start Date')
        ];

        ?>
        <span class="ToggleFlyout SelectFlyout">
        <?php
        if (isset($options[$selected])) {
            $text = $options[$selected];
        } else {
            $text = reset($options);
        }
        echo wrap($text.' '.sprite('', 'DropHandle'), 'span', ['class' => 'Selected']);
        ?>
            <div class="Flyout MenuItems">
                <ul>
                    <?php
                    foreach ($options as $sortField => $sortText) {
                        echo wrap(anchor($sortText, '#', ['class' => 'SortDiscussions', 'data-field' => $sortField]), 'li');
                    }
                    ?>
                </ul>
            </div>
         </span>
        <?php
    }
endif;

if (!function_exists('WriteMiniPager')) :
    /**
     *
     *
     * @param $discussion
     */
    function writeMiniPager($discussion) {
        if (!property_exists($discussion, 'CountPages')) {
            return;
        }

        if ($discussion->CountPages > 1) {
            echo '<span class="MiniPager">';
            if ($discussion->CountPages < 5) {
                for ($i = 0; $i < $discussion->CountPages; $i++) {
                    writePageLink($discussion, $i + 1);
                }
            } else {
                writePageLink($discussion, 1);
                writePageLink($discussion, 2);
                echo '<span class="Elipsis">...</span>';
                writePageLink($discussion, $discussion->CountPages - 1);
                writePageLink($discussion, $discussion->CountPages);
                // echo anchor('Go To Page', '#', 'GoToPageLink');
            }
            echo '</span>';
        }
    }
endif;

if (!function_exists('WritePageLink')):
    /**
     *
     *
     * @param $discussion
     * @param $pageNumber
     */
    function writePageLink($discussion, $pageNumber) {
        echo anchor($pageNumber, discussionUrl($discussion, $pageNumber));
    }
endif;

if (!function_exists('NewComments')) :
    /**
     *
     *
     * @param $discussion
     * @return string
     */
    function newComments($discussion) {
        if (!Gdn::session()->isValid())
            return '';

        if ($discussion->CountUnreadComments === TRUE) {
            $title = htmlspecialchars(t("You haven't read this yet."));

            return ' <strong class="HasNew JustNew NewCommentCount" title="'.$title.'">'.t('new discussion', 'new').'</strong>';
        } elseif ($discussion->CountUnreadComments > 0) {
            $title = htmlspecialchars(plural($discussion->CountUnreadComments, "%s new comment since you last read this.", "%s new comments since you last read this."));

            return ' <strong class="HasNew NewCommentCount" title="'.$title.'">'.plural($discussion->CountUnreadComments, '%s new', '%s new plural', bigPlural($discussion->CountUnreadComments, '%s new', '%s new plural')).'</strong>';
        }
        return '';
    }
endif;

if (!function_exists('tag')) :
    /**
     *
     *
     * @param $discussion
     * @param $column
     * @param $code
     * @param bool|false $cssClass
     * @return string|void
     */
    function tag($discussion, $column, $code, $cssClass = FALSE) {
        $discussion = (object)$discussion;

        if (is_numeric($discussion->$column) && !$discussion->$column)
            return '';
        if (!is_numeric($discussion->$column) && strcasecmp($discussion->$column, $code) != 0)
            return;

        if (!$cssClass)
            $cssClass = "Tag-$code";

        return ' <span class="Tag '.$cssClass.'" title="'.htmlspecialchars(t($code)).'">'.t($code).'</span> ';

    }
endif;

if (!function_exists('writeTags')) :
    /**
     *
     *
     * @param $discussion
     * @throws Exception
     */
    function writeTags($discussion) {
        Gdn::controller()->fireEvent('BeforeDiscussionMeta');

        echo tag($discussion, 'Announce', 'Announcement');
        echo tag($discussion, 'Closed', 'Closed');

        Gdn::controller()->fireEvent('AfterDiscussionLabels');
    }
endif;

if (!function_exists('writeFilterTabs')) :
    /**
     *
     *
     * @param $sender
     */
    function writeFilterTabs($sender) {
        $session = Gdn::session();
        $title = property_exists($sender, 'Category') ? val('Name', $sender->Category, '') : '';
        if ($title == '') {
            $title = t('All Discussions');
        }
        $bookmarked = t('My Bookmarks');
        $myDiscussions = t('My Discussions');
        $myDrafts = t('My Drafts');
        $countBookmarks = 0;
        $countDiscussions = 0;
        $countDrafts = 0;

        if ($session->isValid()) {
            $countBookmarks = $session->User->CountBookmarks;
            $countDiscussions = $session->User->CountDiscussions;
            $countDrafts = $session->User->CountDrafts;
        }

        if (c('Vanilla.Discussions.ShowCounts', true)) {
            $bookmarked .= countString($countBookmarks, '/discussions/UserBookmarkCount');
            $myDiscussions .= countString($countDiscussions);
            $myDrafts .= countString($countDrafts);
        }

        ?>
        <div class="Tabs DiscussionsTabs">
            <?php
            if (!property_exists($sender, 'CanEditDiscussions')) {
                $sender->CanEditDiscussions = $session->checkPermission('Vanilla.Discussions.Edit', true, 'Category', 'any') && c('Vanilla.AdminCheckboxes.Use');
            }
            if ($sender->CanEditDiscussions) {
                ?>
                <span class="Options"><span class="AdminCheck">
                    <input type="checkbox" aria-label="<?php echo t('Select Discussion') ?>" name="Toggle"/>
                </span></span>
            <?php } ?>
            <ul>
                <?php $sender->fireEvent('BeforeDiscussionTabs'); ?>
                <li<?php echo strtolower($sender->ControllerName) == 'discussionscontroller' && strtolower($sender->RequestMethod) == 'index' ? ' class="Active"' : ''; ?>><?php echo anchor(t('All Discussions'), 'discussions', 'TabLink'); ?></li>
                <?php $sender->fireEvent('AfterAllDiscussionsTab'); ?>

                <?php
                if (c('Vanilla.Categories.ShowTabs')) {
                    $cssClass = '';
                    if (strtolower($sender->ControllerName) == 'categoriescontroller' && strtolower($sender->RequestMethod) == 'all') {
                        $cssClass = 'Active';
                    }

                    echo " <li class=\"$cssClass\">".anchor(t('Categories'), '/categories/all', 'TabLink').'</li> ';
                }
                ?>
                <?php if ($countBookmarks > 0 || $sender->RequestMethod == 'bookmarked') { ?>
                    <li<?php echo $sender->RequestMethod == 'bookmarked' ? ' class="Active"' : ''; ?>><?php echo anchor($bookmarked, '/discussions/bookmarked', 'MyBookmarks TabLink'); ?></li>
                    <?php
                    $sender->fireEvent('AfterBookmarksTab');
                }
                if (($countDiscussions > 0 || $sender->RequestMethod == 'mine') && c('Vanilla.Discussions.ShowMineTab', true)) {
                    ?>
                    <li<?php echo $sender->RequestMethod == 'mine' ? ' class="Active"' : ''; ?>><?php echo anchor($myDiscussions, '/discussions/mine', 'MyDiscussions TabLink'); ?></li>
                <?php
                }
                if ($countDrafts > 0 || $sender->ControllerName == 'draftscontroller') {
                    ?>
                    <li<?php echo $sender->ControllerName == 'draftscontroller' ? ' class="Active"' : ''; ?>><?php echo anchor($myDrafts, '/drafts', 'MyDrafts TabLink'); ?></li>
                <?php
                }
                $sender->fireEvent('AfterDiscussionTabs');
                ?>
            </ul>
        </div>
    <?php
    }
endif;

if (!function_exists('optionsList')) :
    /**
     * Build HTML for discussions options menu.
     *
     * @param $discussion
     * @return DropdownModule|string
     * @throws Exception
     */
    function optionsList($discussion) {
        if (Gdn::session()->isValid() && !empty(Gdn::controller()->ShowOptions)) {
            include_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussion', 'vanilla');
            return writeDiscussionOptions($discussion);
        }
        return '';
    }
endif;

if (!function_exists('writeOptions')) :
    /**
     * Render options that the user has for this discussion.
     */
    function writeOptions($discussion) {
        if (!Gdn::session()->isValid() || !Gdn::controller()->ShowOptions)
            return;

        echo '<span class="Options">';

        // Options list.
        echo optionsList($discussion);

        // Bookmark button.
        echo bookmarkButton($discussion);

        // Admin check.
        echo adminCheck($discussion);

        echo '</span>';
    }
endif;

if (!function_exists('userRoleCheck')) :
    /**
     * User Role check
     */
    function userRoleCheck($UserID = NULL) {
        $userModel = new UserModel();
        if ($UserID) {
            $User = $userModel->getID($UserID);
        } else {
            $User = $userModel->getID(Gdn::session()->UserID);
        }

        if($User) {
            $RoleData = $userModel->getRoles($User->UserID);

            $RoleData = $userModel->getRoles($User->UserID);
            if ($RoleData !== false) {
                $Roles = array_column($RoleData->resultArray(), 'Name');
            }

            // Hide personal info roles
            if (!checkPermission('Garden.PersonalInfo.View')) {
                $Roles = array_filter($Roles, 'RoleModel::FilterPersonalInfo');
            }

            if(in_array(Gdn::config('Vanilla.ExtraRoles.Teacher'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Teacher') ?? 'Teacher';
            else $UserRole = RoleModel::TYPE_MEMBER ?? 'student';

            return $UserRole;
        } else return null;
    }
endif;
