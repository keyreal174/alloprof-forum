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
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0)">
                <path d="M1.25 3.625C1.25 1.96815 2.59315 0.625 4.25 0.625H15.1731C16.8299 0.625 18.1731 1.96815 18.1731 3.625V18.3644C18.1731 19.2326 17.1428 19.6884 16.5004 19.1044L10.0479 13.2385C9.85716 13.0651 9.56591 13.0651 9.3752 13.2385L2.92268 19.1044C2.2803 19.6884 1.25 19.2326 1.25 18.3644V3.625Z" fill="black"/>
            </g>
            <defs>
                <clipPath id="clip0">
                    <rect width="20" height="20" fill="white"/>
                </clipPath>
            </defs>
        </svg>
        EOT;

        $icon_follow = <<<EOT
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0)">
                <path d="M4.25 1.625H15.1731C16.2776 1.625 17.1731 2.52043 17.1731 3.625V18.3644L10.7205 12.4985C10.1484 11.9784 9.27466 11.9784 8.70253 12.4985L2.25 18.3644V3.625C2.25 2.52043 3.14543 1.625 4.25 1.625Z" stroke="black" stroke-width="2"/>
            </g>
            <defs>
                <clipPath id="clip0">
                    <rect width="20" height="20" fill="white"/>
                </clipPath>
            </defs>
        </svg>
        EOT;

        $icon = $isBookmarked ? $icon_following : $icon_follow;

        if ($hasFollowedTeacher) {
            return anchor(
                $icon,
                $popupLink,
                'Hijack followButton Option-Icon SocialPopup'.($isBookmarked ? ' TextColor isFollowing' : ''),
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
    function writeCategoryDropDown($sender, $fieldName = 'CategoryID', $options = [], $isMobile=false, $form=false) {
        $sender->EventArguments['Options'] = &$options;
        $sender->fireEvent('BeforeCategoryDropDown');

        $value = arrayValueI('Value', $options); // The selected category id
        if ($value === false && $form) {
            $value = $form->getFormValue($fieldName, false);
        }
        if (!is_array($value)) {
            $value = [$value];
        }
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
        $language = Gdn::config('Garden.Locale') == 'fr_CA' ? 'fr' : 'en';
        $language = $sender->Language ? $sender->Language : $language;
        foreach ($categoryData as $categoryID => $category) {
            if ($value != $categoryID) {
                if ($category['CategoryID'] <= 0 || !$category['PermsDiscussionsView'] || $category['Language'] != $language) {
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

        if($isMobile) {
            // Prevent default $Value from matching key of zero
            $hasValue = ($value !== [false] && $value !== ['']) ? true : false;
            echo '<div class="mobile-categories">';
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

                    $name = htmlspecialchars(val('Name', $category, 'Blank Category Name'));
                    $bgColor = val('Color', $category) ?? '#F2F2F2';

                    if ($depth > 1) {
                        $name = str_repeat('&#160;', 4 * ($depth - 1)).$name;
                    }

                    echo '<div class="category-item '.($selected?'selected':'').'" id="'.$categoryID.'" style="background: '.$bgColor.'">';
                    // echo '<div class="icon">'.($category['Photo']?'<img src="'.$category['Photo'].'"/>':'').'</div>';
                    echo $name;
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            // Opening select tag
            $return = '<select name='.$fieldName.' id='.$fieldName.'>';

            // Get value from attributes
            if (!is_array($value)) {
                $value = [$value];
            }

            // Prevent default $Value from matching key of zero
            $hasValue = ($value !== [false] && $value !== ['']) ? true : false;

            // Start with null option?
            $includeNull = val('IncludeNull', $options) || $sender->LanguageChanged;
            if ($includeNull === true) {
                $return .= '<option value=""></option>';
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

        if (Gdn::config('Garden.Locale') == 'fr_CA') {
            // $string = array_slice($string, 0, 1);
            $frenchStr = "";
            if ($diff->y) {
                $frenchStr = $diff->y . "a";
            } else {
                if ($diff->m) {
                    $frenchStr = $diff->m . "m";
                } else {
                    if ($diff->d) {
                        $frenchStr = $diff->d . "j";
                    } else {
                        if ($diff->h) {
                            $frenchStr = $diff->h . "h";
                        } else {
                            if ($diff->i) {
                                $frenchStr = $diff->i . "min";
                            }
                        }
                    }
                }
            }
            return $frenchStr;
        } else {
            $enStr = "";
            if ($diff->y) {
                $enStr = $diff->y . "yr.";
            } else {
                if ($diff->m) {
                    $enStr = $diff->m . "mo.";
                } else {
                    if ($diff->d) {
                        $enStr = $diff->d . "d";
                    } else {
                        if ($diff->h) {
                            $enStr = $diff->h . "h";
                        } else {
                            if ($diff->i) {
                                $enStr = $diff->i . "min";
                            }
                        }
                    }
                }
            }
            return $enStr;
        }

        // return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
endif;


if(!function_exists('writeCategories')) :
    function writeCategories() {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel
            ->setJoinUserCategory(true)
            ->getChildTree(null, ['collapseCategories' => true]);
        $categories = CategoryModel::flattenTree($categories);

        $categories = array_filter($categories, function ($category) {
            return val('PermsDiscussionsView', $category) && val('Following', $category);
        });

        $userCategories = $categoryModel->getFollowed(Gdn::session()->UserID);

        function isFollowingCategory($followingCategories, $category) {
            foreach ($followingCategories as $element) {
                if ($element["CategoryID"] == $category["CategoryID"]) {
                    return true;
                }
            }
            return false;
        }

        foreach ($categories as $key => $value) {
            # code...
            if (isFollowingCategory($userCategories, $value)) {
                $categories[$key]["isFollowing"] = 1;
            } else {
                $categories[$key]["isFollowing"] = 0;
            }
        }

        function cmp($a, $b) {
            if ($a["isFollowing"] > $b["isFollowing"]) {
                return -1;
            } else if ($a["isFollowing"] < $b["isFollowing"]) {
                return 1;
            } else return 0;
        }

        usort($categories, 'cmp');

        $newCategorySet = array($categories);

        $data = new Gdn_DataSet($newCategorySet, DATASET_TYPE_ARRAY);
        $data->datasetType(DATASET_TYPE_OBJECT);

        return $data;
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
        $isPro = userRoleCheck($Author->UserID) == Gdn::config('Vanilla.ExtraRoles.Pro');

        $sender->fireEvent('BeforeDiscussionDisplay');
        ?>
<li id="Discussion_<?php echo $Discussion->DiscussionID; ?>" class="<?php echo $cssClass; ?>"
    data-url="<?php echo $Discussion->Url; ?>">
    <?php
                if ($Discussion->DateAccepted) {
                    echo "<div class='verified-info mobile'>
                        <img src='".url("/themes/alloprof/design/images/icons/verifiedbadge.svg")."'/><span>".t("Verified by Alloprof")."</span></div>";
                }
            ?>
    <div class="Discussion">
        <?php
                    if(!$Discussion->Published) {
                        echo '<div class="not-published-badge mobile">';
                        echo '<img src="'.url("/themes/alloprof/design/images/icons/eyebreak.svg").'"/>';
                        echo t('Awaiting publication');
                        echo '</div>';
                    }
                ?>
        <div class="Item-Header DiscussionHeader">
            <?php
                    if (!Gdn::themeFeatures()->get('EnhancedAccessibility') && Gdn::session()->isValid()) {
                        ?>
            <span class="Options-Icon DisableClick">
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
                                echo '<div class="not-published-badge DisableClick desktop">';
                                echo '<img src="'.url("/themes/alloprof/design/images/icons/eyebreak.svg").'"/>';
                                echo t('Awaiting publication');
                                echo '</div>';
                            }
                        ?>
                <span class="Author DisableClick">
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
                                echo "<div class='verified-badge DisableClick'>
                                        <img src='".url("/themes/alloprof/design/images/icons/verifiedbadge.svg")."'/>
                                        <span>". t('Verified by Alloprof') ."</span>
                                    </div>";
                            }
                        ?>
            </div>
            <div class="Meta DiscussionMeta">
                <span class="MItem TimeAgo DisableClick">
                    <?php
                                if ($isPro) {
                                    echo '<span class="ItemGrade">'.t('Help Zone Pro'). ' ??? </span>' . timeElapsedString($Discussion->FirstDate, false);
                                } else if ($grade) {
                                    echo '<span class="ItemGrade">'.$grade . ' ??? </span>' . timeElapsedString($Discussion->FirstDate, false);
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
                    <div class="MessageWrapper">
                        <?php
                                echo formatBody($Discussion);
                                ?>
                    </div>
                </div>
                <?php
                        $sender->fireEvent('AfterDiscussionBody');
                        if (val('Attachments', $Discussion)) {
                            writeAttachments($Discussion->Attachments);
                        }
                        ?>
                <?php  echo "<div class='DisableClick DisableClickWrapper'><a class='QuestionCategory' style='background: ".$category["Color"]."' href='".url('/categories/'.$category["UrlCode"])."'>".$category["Name"]."</a></div>"; ?>
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
            <input type="checkbox" aria-label="<?php echo t('Select Discussion') ?>" name="Toggle" />
        </span></span>
    <?php } ?>
    <ul>
        <?php $sender->fireEvent('BeforeDiscussionTabs'); ?>
        <li<?php echo strtolower($sender->ControllerName) == 'discussionscontroller' && strtolower($sender->RequestMethod) == 'index' ? ' class="Active"' : ''; ?>>
            <?php echo anchor(t('All Discussions'), 'discussions', 'TabLink'); ?></li>
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
            <li<?php echo $sender->RequestMethod == 'bookmarked' ? ' class="Active"' : ''; ?>>
                <?php echo anchor($bookmarked, '/discussions/bookmarked', 'MyBookmarks TabLink'); ?></li>
                <?php
                    $sender->fireEvent('AfterBookmarksTab');
                }
                if (($countDiscussions > 0 || $sender->RequestMethod == 'mine') && c('Vanilla.Discussions.ShowMineTab', true)) {
                    ?>
                <li<?php echo $sender->RequestMethod == 'mine' ? ' class="Active"' : ''; ?>>
                    <?php echo anchor($myDiscussions, '/discussions/mine', 'MyDiscussions TabLink'); ?></li>
                    <?php
                }
                if ($countDrafts > 0 || $sender->ControllerName == 'draftscontroller') {
                    ?>
                    <li<?php echo $sender->ControllerName == 'draftscontroller' ? ' class="Active"' : ''; ?>>
                        <?php echo anchor($myDrafts, '/drafts', 'MyDrafts TabLink'); ?></li>
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
            
            if(in_array('Administrator', $Roles))
                $UserRole = 'Administrator';

            else if(in_array('Principal Moderator', $Roles))
                $UserRole = 'Moderator';

            else if(in_array(Gdn::config('Vanilla.ExtraRoles.Teacher'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Teacher') ?? 'Teacher';

            else if(in_array(Gdn::config('Vanilla.ExtraRoles.Pro'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Pro') ?? 'Pro';

            else $UserRole = RoleModel::TYPE_MEMBER ?? 'student';

            return $UserRole;
        } else return null;
    }
endif;

if (!function_exists('userExtraInfo')) :
    /**
     * User Extra info
     */
    function userExtraInfo($UserID = NULL) {
        $role = userRoleCheck($UserID ?? Gdn::session()->UserID);
        $UserMetaData = Gdn::userModel()->getMeta($UserID ?? Gdn::session()->UserID, 'Profile.%', 'Profile.');
        $text = $UserMetaData["Grade"] ?? "";
        $badge = '';

        if($role == Gdn::config('Vanilla.ExtraRoles.Teacher')) {
            $badge = '<svg width="15" height="15" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.64495 0.776516C9.40048 0.0744949 10.5995 0.0744944 11.3551 0.776516L12.0133 1.38817C12.4454 1.78962 13.0461 1.9771 13.6413 1.89624L14.5482 1.77303C15.589 1.63163 16.5591 2.30857 16.7407 3.30306L16.8989 4.16953C17.0028 4.73822 17.3741 5.22906 17.9051 5.49967L18.7142 5.91197C19.6428 6.3852 20.0133 7.4805 19.5516 8.3876L19.1494 9.17793C18.8854 9.69665 18.8854 10.3034 19.1494 10.8221L19.5516 11.6124C20.0133 12.5195 19.6428 13.6148 18.7142 14.088L17.9051 14.5003C17.3741 14.7709 17.0028 15.2618 16.8989 15.8305L16.7407 16.6969C16.5591 17.6914 15.589 18.3684 14.5482 18.227L13.6413 18.1038C13.0461 18.0229 12.4454 18.2104 12.0133 18.6118L11.3551 19.2235C10.5995 19.9255 9.40048 19.9255 8.64495 19.2235L7.98668 18.6118C7.55463 18.2104 6.95389 18.0229 6.35868 18.1038L5.45182 18.227C4.41097 18.3684 3.44092 17.6914 3.2593 16.6969L3.10106 15.8305C2.9972 15.2618 2.62591 14.7709 2.0949 14.5003L1.28584 14.088C0.357241 13.6148 -0.0132854 12.5195 0.44837 11.6124L0.850598 10.8221C1.11459 10.3034 1.11459 9.69665 0.850598 9.17793L0.448371 8.3876C-0.0132849 7.4805 0.357241 6.3852 1.28584 5.91197L2.0949 5.49967C2.62591 5.22906 2.9972 4.73822 3.10106 4.16953L3.2593 3.30306C3.44092 2.30857 4.41097 1.63163 5.45182 1.77303L6.35868 1.89624C6.95388 1.9771 7.55463 1.78962 7.98668 1.38817L8.64495 0.776516Z" fill="#05BF8E"/>
            <path d="M6.25 10L8.75 12.25L13.75 7.75" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg></span>';
            $text = t('Alloprof Teacher');
        } else if ($role == Gdn::config('Vanilla.ExtraRoles.Pro')) {
            $badge = '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 8.5a8 8 0 1 1 16 0 8 8 0 1 1-16 0z" fill="#295ABA"/>
                <path d="M8 9.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M6 8.5v4l2-1 2 1v-4" stroke="#fff"/>
            </svg>';
            $text = t('Help Zone Pro');
        }

        return ['badge' => $badge, 'grade' => $text];
    }
endif;