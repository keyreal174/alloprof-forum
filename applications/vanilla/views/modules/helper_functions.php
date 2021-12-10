<?php
if (!defined('APPLICATION')) exit();

use Vanilla\Utility\HtmlUtils;

if (!function_exists('WriteModuleDiscussion')):
    function writeModuleDiscussion($discussion, $px = 'Bookmark', $showPhotos = false) {
        /** @var Vanilla\Formatting\Html\HtmlSanitizer */
        $htmlSanitizer = Gdn::getContainer()->get(Vanilla\Formatting\Html\HtmlSanitizer::class);

        ?>
<li id="<?php echo "{$px}_{$discussion->DiscussionID}"; ?>" class="<?php echo cssClass($discussion); ?>">
    <?php if ($showPhotos) :
                $firstUser = userBuilder($discussion, 'First');
                echo userPhoto($firstUser, ['LinkClass' => 'IndexPhoto']);
            endif; ?>
    <span class="Options">
        <?php
      //      echo optionsList($Discussion);
      echo bookmarkButton($discussion);
      ?>
    </span>

    <div class="Title"><?php
                echo anchor(
                    $htmlSanitizer->filter($discussion->Name), // Should already be encoded, but filter as an additional measure.
                    discussionUrl($discussion).($discussion->CountCommentWatch > 0 ? '#Item_'.$discussion->CountCommentWatch : ''),
                    'DiscussionLink'
                );
            ?></div>
    <div class="Meta DiscussionsModuleMeta">
        <?php
                $last = new stdClass();
                $last->UserID = $discussion->LastUserID;
                $last->Name = $discussion->LastName;

                echo newComments($discussion);

                $translation = pluralTranslate($discussion->CountComments, '%s comment html', '%s comments html', t('%s comment'), t('%s comments'));
                echo '<span class="MItem">'.Gdn_Format::date($discussion->FirstDate, 'html').userAnchor($last).'</span>';
                echo '<span class="MItem CountComments Hidden">'.sprintf($translation, $discussion->CountComments).'</span>';
                ?>
    </div>
</li>
<?php
    }
endif;

if (!function_exists('WritePromotedContent')):
    /**
     * Generates html output of $content array
     *
     * @param array|object $content
     * @param PromotedContentModule $sender
     */
    function writePromotedContent($content, $sender) {
        static $userPhotoFirst = NULL;
        if ($userPhotoFirst === null)
            $userPhotoFirst = c('Vanilla.Comment.UserPhotoFirst', true);

        $contentType = val('RecordType', $content);
        $contentID = val("{$contentType}ID", $content);
        $author = val('Author', $content);
        $contentURL = val('Url', $content);
        $sender->EventArguments['Content'] = &$content;
        $sender->EventArguments['ContentUrl'] = &$contentURL;
        ?>
<div id="<?php echo "Promoted_{$contentType}_{$contentID}"; ?>" class="<?php echo cssClass($content); ?>">
    <div class="AuthorWrap">
        <span class="Author">
            <?php
            if ($userPhotoFirst) {
                echo userPhoto($author);
                echo userAnchor($author, 'Username');
            } else {
                echo userAnchor($author, 'Username');
                echo userPhoto($author);
            }
            $sender->fireEvent('AuthorPhoto');
            ?>
        </span>
        <span class="AuthorInfo">
            <?php
            echo ' '.wrapIf(htmlspecialchars(val('Title', $author)), 'span', ['class' => 'MItem AuthorTitle']);
            echo ' '.wrapIf(htmlspecialchars(val('Location', $author)), 'span', ['class' => 'MItem AuthorLocation']);
            $sender->fireEvent('AuthorInfo');
            ?>
        </span>
    </div>
    <div class="Meta CommentMeta CommentInfo">
        <span class="MItem DateCreated">
            <?php echo anchor(Gdn_Format::date($content['DateInserted'], 'html'), $contentURL, 'Permalink', ['rel' => 'nofollow']); ?>
        </span>
        <?php
                // Include source if one was set
                if ($source = val('Source', $content))
                    echo wrap(sprintf(t('via %s'), t($source.' Source', $source)), 'span', ['class' => 'MItem Source']);

                $sender->fireEvent('ContentInfo');
                ?>
    </div>
    <div class="Title">
        <?php echo anchor(htmlspecialchars(sliceString($content['Name'], $sender->TitleLimit), false), $contentURL, 'DiscussionLink'); ?>
    </div>
    <div class="Body">
        <?php
                $linkContent = Gdn::formatService()->renderExcerpt($content['Body'], $content['Format']);
                $trimmedLinkContent = sliceString($linkContent, $sender->BodyLimit);

                echo anchor(htmlspecialchars($trimmedLinkContent), $contentURL, 'BodyLink');

                $sender->fireEvent('AfterPromotedBody'); // separate event to account for less space.
                ?>
    </div>
</div>
<?php
    }
endif;

if (!function_exists('writePromotedContentList')):
    /**
     * Generate a modern view of array $data.
     *
     * @param array $data The data used to generate the view
     */
    function writePromotedContentList($data) {
        ?>
<ul class="PromotedContentList DataList">
    <?php foreach ($data as $row) {
                writePromotedContentRow($row, 'modern');
            } ?>
</ul>
<?php
    }
endif;

if (!function_exists('writePromotedContentTable')):
    /**
     * Generate a table view of array $data.
     *
     * @param array $data The $data used to generate the view
     */
    function writePromotedContentTable($data) {
        ?>
<div class="DataTableContainer">
    <div class="DataTableWrap">
        <table class="DataTable">
            <thead>
                <tr>
                    <td class="DiscussionName" role="columnheader">
                        <div class="Wrap"><?php echo t('Subject'); ?></div>
                    </td>
                    <td class="BlockColumn BlockColumn-User LastUser" role="columnheader">
                        <div class="Wrap"><?php echo t('Author'); ?></div>
                    </td>
                </tr>
            </thead>
            <?php foreach ($data as $row) {
                        writePromotedContentRow($row, 'table');
                    } ?>
        </table>
    </div>
</div>
<?php
    }
endif;

if (!function_exists('writePromotedContentRow')):
    /**
     * Write a promoted content item in a table or modern view.
     *
     * @param array $row The row to output.
     * @param string $view The view to use.
     */
    function writePromotedContentRow($row, $view) {
        $title = htmlspecialchars(val('Name', $row));
        $url = val('Url', $row);
        $body = Gdn_Format::plainText(val('Body', $row), val('Format', $row));
        $categoryUrl = val('CategoryUrl', $row);
        $categoryName = val('CategoryName', $row);
        $date = val('DateUpdated', $row) ?: val('DateInserted', $row);
        $date = Gdn_Format::date($date, 'html');
        $type = val('RecordType', $row, 'post');
        $id = val('CommentID', $row, val('DiscussionID', $row, ''));
        $author = val('Author', $row);
        $username = val('Name', $author);
        $userUrl = val('Url', $author);
        $userPhoto = val('PhotoUrl', $author);
        $cssClass = val('CssClass', $author);

        $accessibleLabel = HtmlUtils::accessibleLabel('Category: "%s"', [$categoryName]);

        if ($view == 'table') {
            ?>
<tr id="Promoted_<?php echo $type.'_'.$id; ?>" class="Item PromotedContent-Item <?php echo $cssClass; ?>">
    <td class="Name">
        <div class="Wrap">
            <a class="Title" href="<?php echo $url; ?>">
                <?php echo $title; ?>
            </a>
            <span class="MItem Category"><?php echo t('in'); ?> <a href="<?php echo $categoryUrl; ?>"
                    aria-label="<?php echo $accessibleLabel; ?>"
                    class="MItem-CategoryName"><?php echo $categoryName; ?></a></span>

            <div class="Description"><?php echo $body; ?></div>
        </div>
    </td>
    <td class="BlockColumn BlockColumn-User User">
        <div class="Block Wrap">
            <a class="PhotoWrap PhotoWrapSmall" href="<?php echo $userUrl; ?>">
                <?php
                                $accessibleLabel = HtmlUtils::accessibleLabel('User: "%s"', [$username]);
                            ?>
                <img class="ProfilePhoto ProfilePhotoSmall" src="<?php echo $userPhoto; ?>"
                    alt="<?php echo $accessibleLabel; ?>">
            </a>
            <a class="UserLink BlockTitle" href="<?php echo $userUrl; ?>"><?php echo $username; ?></a>

            <div class="Meta">
                <a class="CommentDate MItem" href="<?php echo $url; ?>"><?php echo $date; ?></a>
            </div>
        </div>
    </td>
</tr>

<?php } else { ?>

<li id="Promoted_<?php echo $type.'_'.$id; ?>" class="Item PromotedContent-Item <?php echo $cssClass; ?>">
    <?php if (c('EnabledPlugins.IndexPhotos')) { ?>
    <a title="<?php echo $username; ?>" href="<?php echo $userUrl; ?>" class="IndexPhoto PhotoWrap">
        <img src="<?php echo $userPhoto; ?>" alt="<?php echo $username; ?>" class="ProfilePhoto ProfilePhotoMedium">
    </a>
    <?php } ?>
    <div class="ItemContent Discussion">
        <div class="Title">
            <a href="<?php echo $url; ?>">
                <?php echo $title; ?>
            </a>
        </div>
        <div class="Excerpt"><?php echo $body; ?></div>
        <div class="Meta">
            <span class="MItem DiscussionAuthor">
                <ahref="<?php echo $userUrl; ?> "><?php echo $username; ?></a></span>
                        <span class=" MItem Category"><?php echo t('in'); ?> <a href="<?php echo $categoryUrl; ?>"
                        aria-label="<?php echo $accessibleLabel; ?>"
                        class="MItem-CategoryName"><?php echo $categoryName; ?></a>
            </span>
        </div>
    </div>
</li>

<?php }
    }
endif;

if (!function_exists('writeCategoryDropDown')) :
    function writeCategoryDropDown($sender, $fieldName = 'CategoryID', $options = []) {
        // $sender->EventArguments['Options'] = &$options;
        // $sender->fireEvent('BeforeCategoryDropDown');

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
        $language = Gdn::config('Garden.Locale') == 'fr_CA' ? 'fr' : 'en';
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

        // Opening select tag
        $return = '<select name='.$fieldName.' id='.$fieldName.'>';

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


if (!function_exists('writeGradeFilter')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeGradeFilter($gradeID, $isMobile=false) {
        $Session = Gdn::session();
        $DefaultGrade = 0;
        if ($Session) {
            $UserID = $Session->UserID;
            $AuthorMetaData = Gdn::userModel()->getMeta($UserID, 'Profile.%', 'Profile.');
            if ($AuthorMetaData['Grade']) {
                $DefaultGrade = t($AuthorMetaData['Grade']);
            }
        }

        $fields = c('ProfileExtender.Fields', []);
        if (!is_array($fields)) {
            $fields = [];
        }
        foreach ($fields as $k => $field) {
            if ($field['Label'] == "Grade") {
                $GradeOption = array_filter($field['Options'], function($v) {
                    return preg_match('/(Primaire|Secondaire|Post-secondaire)/', $v);
                });
                $GradeOption = array_map(function($val) {
                    return t($val);
                }, $GradeOption);

                if ($DefaultGrade && $DefaultGrade !== 0) {
                    $DefaultGrade = array_search(t($DefaultGrade), $GradeOption);
                }
            }
        }

        if($isMobile) {
            echo '<div class="mobile-grade">';
            if (is_array($GradeOption)) {
                foreach ($GradeOption as $id => $text) {
                    if (is_array($text)) {
                        $attribs = $text;
                        $text = val('Text', $attribs, '');
                        unset($attribs['Text']);
                    } else {
                        $attribs = [];
                    }

                    echo '<div class="item" value="'.htmlspecialchars($id).'">'.$text.'</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="rich-select select2 select2-grade">';
            echo '<div class="pre-icon"><img src="'.url("/themes/alloprof/design/images/icons/grade.svg").'"/></div>';
            echo Gdn::controller()->Form->dropDown('GradeDropdown', $GradeOption, array('Value' => $gradeID, 'IncludeNull' => true));
            echo '</div>';
        }
    }
endif;

if (!function_exists('writeDiscussionSort')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeDiscussionSort($sort) {
        $options = [
            'desc' => t('Recent'),
            'asc' => t('Oldest')
        ];

        echo '<div class="rich-select select2 select2-grade">';
        echo '<div class="pre-icon"><img src="'.url("/themes/alloprof/design/images/icons/sort.svg").'" width="14" height="8" style="max-height: 10px"/></div>';
        echo Gdn::controller()->Form->dropDown('DiscussionSort', $options, [ 'Value' => $sort ]);
        echo '</div>';
    }
endif;

if (!function_exists('getUserRole')) :
    function getUserRole($UserID = null) {
        $userModel = Gdn::userModel();
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

            if(in_array("Administrator", $Roles))
                $UserRole = "Administrator";
            else if(in_array(Gdn::config('Vanilla.ExtraRoles.Teacher'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Teacher') ?? 'Teacher';

            else if(in_array(Gdn::config('Vanilla.ExtraRoles.Pro'), $Roles))
                $UserRole = Gdn::config('Vanilla.ExtraRoles.Pro') ?? 'Pro';

            else $UserRole = RoleModel::TYPE_MEMBER ?? 'student';

            return $UserRole;
        } else return null;
    }
endif;

if (!function_exists('writeFilterToggle')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeFilterToggle($explanation=false, $verified=false, $explanationout=false, $language=false, $IsShowLanguage=false, $isMobile=false) {
        $role = getUserRole(Gdn::session()->User->UserID);
        echo '<ul>';
        echo '<li class="form-group">';
        $text = $role === 'Teacher' ? t('Without answers only') : t('With answers only');
        $verifiedText = $role === 'Teacher' ? t('Not Verified by Alloprof only') : t('Verified by Alloprof only');
        $languageToggle = t('Show posts in all languages');

        if ($explanation == 'true') {
            echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'Explanation', $text, [ 'checked' => $explanation ]);
        } else {
            echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'Explanation', $text);
        }
        echo '</li>';
        echo '<li class="form-group">';
        if ($verified == 'true') {
            echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'VerifiedBy', $verifiedText, [ 'checked' => $verified ]);
        } else {
            echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'VerifiedBy', $verifiedText);
        }
        echo '</li>';
        if ($role != 'Teacher') {
            echo '<li class="form-group">';
            if ($explanationout == 'true') {
                echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'OutExplanation', t('Without answers only'), [ 'checked' => $explanation ]);
            } else {
                echo Gdn::controller()->Form->toggle(($isMobile?'Mobile':'').'OutExplanation', t('Without answers only'));
            }
            echo '</li>';
        }
        if ($IsShowLanguage) {
            echo '<li class="form-group">';
            if ($language == 'true') {
                echo Gdn::controller()->Form->toggle('Language', $languageToggle, [ 'checked' => true ]);
            } else {
                echo Gdn::controller()->Form->toggle('Language', $languageToggle);
            }
            echo '</li>';
        }

        echo '</ul>';
    }
endif;


// comment filter and sort
if (!function_exists('writeCommentSort')) :
    /**
     * Returns discussions grade filtering.
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeCommentSort($sort) {
        $options = [
            'desc' => t('Recent'),
            'asc' => t('Oldest')
        ];

        echo '<div class="rich-select select2 select2-grade">';
        echo '<div class="pre-icon"><img src="'.url("/themes/alloprof/design/images/icons/sort.svg").'" width="14" height="8" style="max-height: 10px"/></div>';
        echo Gdn::controller()->Form->dropDown('CommentSort', $options, [ 'Value' => $sort ]);
        echo '</div>';
    }
endif;

if (!function_exists('writeCommentVerifiedToggle')) :
    /**
     * Sort comments
     *
     * @param string $extraClasses any extra classes you add to the drop down
     * @return string
     */
    function writeCommentVerifiedToggle($verified) {
        echo '<ul>';
        echo '<li class="form-group">';
        if ($verified == 'true') {
            echo Gdn::controller()->Form->toggle('CommentVerifiedBy', t('Verified by Alloprof only'), [ 'checked' => $verified ]);
        } else {
            echo Gdn::controller()->Form->toggle('CommentVerifiedBy', t('Verified by Alloprof only'));
        }
        echo '</li>';
        echo '</ul>';
    }
endif;