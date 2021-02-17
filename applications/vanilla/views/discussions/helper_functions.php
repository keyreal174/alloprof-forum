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

        // Bookmark link
        $isBookmarked = $discussion->Bookmarked == '1';

        // Bookmark link
        $title = t($isBookmarked ? 'UnFollow' : 'Follow');

        $accessibleLabel= HtmlUtils::accessibleLabel('%s for discussion: "%s"', [t($isBookmarked? 'UnFollow' : 'Follow'), is_array($discussion) ? $discussion["Name"] : $discussion->Name]);

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

        $icon = $isBookmarked ? $icon_following : $icon_follow;

        return anchor(
            $icon,
            '/discussion/bookmark/'.$discussion->DiscussionID.'/'.Gdn::session()->transientKey(),
            'Hijack followButton Option-Icon'.($isBookmarked ? ' TextColor isFollowing' : ''),
            ['title' => $title, 'aria-pressed' => $isBookmarked ? 'true' : 'false', 'role' => 'button', 'aria-label' => $accessibleLabel]
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

if (!function_exists('timeElapsedString')) :
    function timeElapsedString($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

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

        if (!$full) $string = array_slice($string, 0, 1);
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

        $sender->fireEvent('BeforeDiscussionDisplay');
        ?>
        <li id="Discussion_<?php echo $Discussion->DiscussionID; ?>" class="<?php echo $cssClass; ?>">
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
                            echo "<a class='DiscussionHeader_category' href='/categories/".$category["UrlCode"]."'>".$category["Name"]."</a>";
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
                    </div>
                    <div class="Meta DiscussionMeta">
                        <span class="MItem TimeAgo">
                            <?php
                                if ($grade) {
                                    echo $grade . ' • ' . timeElapsedString($Discussion->LastDate, false);
                                } else {
                                    echo timeElapsedString($Discussion->LastDate, false);
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


if (!function_exists('writeCommentForm')) :
    /**
     * Output comment form.
     *
     * @since 2.1
     */
    function writeCommentForm() {
        $session = Gdn::session();
        $controller = Gdn::controller();

        // $discussion = $controller->data('Discussion');
        // $categoryID = val('CategoryID', $discussion);
        // $userCanClose = CategoryModel::checkPermission($categoryID, 'Vanilla.Discussions.Close');
        // $userCanComment = CategoryModel::checkPermission($categoryID, 'Vanilla.Comments.Add');

        // // Closed notification
        // if ($discussion->Closed == '1') {
        //     ?>
        //     <div class="Foot Closed">
        //         <div class="Note Closed"><?php echo t('This discussion has been closed.'); ?></div>
        //     </div>
        // <?php
        // } elseif (!$userCanComment) {
        //     if (!Gdn::session()->isValid()) {
        //         ?>
        //         <div class="Foot Closed">
        //             <div class="Note Closed SignInOrRegister"><?php
        //                 $popup = (c('Garden.SignIn.Popup')) ? ' class="Popup"' : '';
        //                 $returnUrl = Gdn::request()->pathAndQuery();
        //                 echo formatString(
        //                     t('Sign In or Register to Comment.', '<a href="{SignInUrl,html}"{Popup}>Sign In</a> or <a href="{RegisterUrl,html}">Register</a> to comment.'),
        //                     [
        //                         'SignInUrl' => url(signInUrl($returnUrl)),
        //                         'RegisterUrl' => url(registerUrl($returnUrl)),
        //                         'Popup' => $popup
        //                     ]
        //                 ); ?>
        //             </div>
        //             <?php //echo anchor(t('All Discussions'), 'discussions', 'TabLink'); ?>
        //         </div>
        //     <?php
        //     }
        // }

        // if (($discussion->Closed == '1' && $userCanClose) || ($discussion->Closed == '0' && $userCanComment)) {
            echo $controller->fetchView('comment', 'post', 'vanilla');
        // }
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
            return getDiscussionOptionsDropdown($discussion);
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
