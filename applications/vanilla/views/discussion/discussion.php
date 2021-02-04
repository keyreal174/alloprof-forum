<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
 */

if (!defined('APPLICATION')) {
    exit();
}

use Vanilla\Utility\HtmlUtils;
if (!function_exists('writeDiscussionDetail'))
    include($this->fetchViewLocation('helper_functions', 'discussion', 'vanilla'));

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

$UserPhotoFirst = c('Vanilla.Comment.UserPhotoFirst', true);

$Discussion = $this->data('Discussion');
$Author = Gdn::userModel()->getID($Discussion->InsertUserID); // userBuilder($Discussion, 'Insert');
$category = CategoryModel::categories($Discussion->CategoryID);

// Prep event args.
$CssClass = cssClass($Discussion, false);
$this->EventArguments['Discussion'] = &$Discussion;
$this->EventArguments['Author'] = &$Author;
$this->EventArguments['CssClass'] = &$CssClass;

// DEPRECATED ARGUMENTS (as of 2.1)
$this->EventArguments['Object'] = &$Discussion;
$this->EventArguments['Type'] = 'Discussion';

// Discussion template event
$this->fireEvent('BeforeDiscussionDisplay');
?>
<div id="<?php echo 'Discussion_'.$Discussion->DiscussionID; ?>" class="<?php echo $CssClass; ?>">
    <div class="Discussion">
        <div class="Item-Header DiscussionHeader">
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
                echo wrapIf(htmlspecialchars(val('Title', $Author)), 'span', ['class' => 'MItem AuthorTitle']);
                echo wrapIf(htmlspecialchars(val('Location', $Author)), 'span', ['class' => 'MItem AuthorLocation']);
                $this->fireEvent('AuthorInfo');
                ?>
            </span>
            </div>
            <div class="Meta DiscussionMeta">
                <span class="MItem TimeAgo">
                    <?php
                        echo 'Secondaire 1 • ' . timeElapsedString($Discussion->LastDate, false);
                    ?>
                </span>
                <span class="MItem DateCreated">

                    <?php
                    // echo anchor(Gdn_Format::date($Discussion->DateInserted, 'html'), $Discussion->Url, 'Permalink', ['rel' => 'nofollow']);
                    ?>
                </span>
                <?php
                // echo dateUpdated($Discussion, ['<span class="MItem">', '</span>']);
                ?>
                <?php
                // // Include source if one was set
                // if ($Source = val('Source', $Discussion)) {
                //     echo ' '.wrap(sprintf(t('via %s'), t($Source.' Source', $Source)), 'span', ['class' => 'MItem MItem-Source']).' ';
                // }
                // // Category
                // if (c('Vanilla.Categories.Use')) {
                //     $accessibleLabel = HtmlUtils::accessibleLabel('Category: "%s"', [$this->data('Discussion.Category')]);
                //     echo ' <span class="MItem Category">';
                //     echo ' '.t('in').' ';
                //     echo anchor(htmlspecialchars($this->data('Discussion.Category')), categoryUrl($this->data('Discussion.CategoryUrlCode')), ["aria-label" => $accessibleLabel]);
                //     echo '</span> ';
                // }

                // // Include IP Address if we have permission
                // if ($Session->checkPermission('Garden.PersonalInfo.View')) {
                //     echo wrap(ipAnchor($Discussion->InsertIPAddress), 'span', ['class' => 'MItem IPAddress']);
                // }

                $this->fireEvent('DiscussionInfo');
                $this->fireEvent('AfterDiscussionMeta'); // DEPRECATED
                ?>
            </div>
        </div>
        <?php $this->fireEvent('BeforeDiscussionBody'); ?>
        <div class="Item-BodyWrap">
            <div class="Item-Body">
                <div class="Message userContent">
                    <?php
                    echo formatBody($Discussion);
                    ?>
                </div>
                <?php
                $this->fireEvent('AfterDiscussionBody');
                writeReactions($Discussion);
                if (val('Attachments', $Discussion)) {
                    writeAttachments($Discussion->Attachments);
                }
                ?>
            </div>
        </div>
    </div>
</div>
