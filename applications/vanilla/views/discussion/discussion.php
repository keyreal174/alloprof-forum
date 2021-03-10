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

if (!function_exists('timeElapsedString'))
    include($this->fetchViewLocation('helper_functions', 'discussions', 'vanilla'));

$UserPhotoFirst = c('Vanilla.Comment.UserPhotoFirst', true);

$Discussion = $this->data('Discussion');
$Author = Gdn::userModel()->getID($Discussion->InsertUserID); // userBuilder($Discussion, 'Insert');
$AuthorMetaData = Gdn::userModel()->getMeta($Author->UserID, 'Profile.%', 'Profile.');
$category = CategoryModel::categories($Discussion->CategoryID);

$Grade = getGrade($Discussion->GradeID);

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
                        if ($Grade) {
                            echo $Grade . ' • ' . timeElapsedString($Discussion->LastDate, false);
                        } else {
                            echo timeElapsedString($Discussion->LastDate, false);
                        }
                    ?>
                </span>
                <?php
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
                // writeReactions($Discussion);
                if (val('Attachments', $Discussion)) {
                    writeAttachments($Discussion->Attachments);
                }
                ?>
            </div>
            <?php
                writeDiscussionFooter($Discussion, $this);
            ?>
        </div>
    </div>
</div>
