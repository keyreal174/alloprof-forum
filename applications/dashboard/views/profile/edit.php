<?php if (!defined('APPLICATION')) exit();

use Vanilla\Utility\HtmlUtils;



require_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussions', 'Vanilla');

$Controller = Gdn::controller();
$Session = Gdn::session();
$Discussion = val('Discussion', Gdn::controller());
$Category = CategoryModel::categories($Discussion->CategoryID);
// Get the tab sort order from the user-prefs.
//$SortOrder = FALSE;
//$SortOrder = val('ProfileTabOrder', $Controller->User->Preferences, false);
// If not in the user prefs, get the sort order from the application prefs.
//if ($SortOrder === FALSE)
$SortOrder = c('Garden.ProfileTabOrder');

if (!is_array($SortOrder))
    $SortOrder = [];

if($Session->isValid()) {
// Make sure that all tabs are present in $SortOrder
foreach ($Controller->ProfileTabs as $TabCode => $TabInfo) {
    if (!in_array($TabCode, $SortOrder))
        $SortOrder[] = $TabCode;
}

echo "<div class='profile-content'>";

echo Gdn_Theme::module('UserPhotoModule');

?>

<div class="BoxFilter BoxProfileInfo">
    <?php
        if(userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
            echo "<div class='BoxProfileInfo_detailbox'>";
            foreach ($SortOrder as $TabCode) {
                $CssClass = $TabCode == $Controller->CurrentTab ? 'Active ' : '';
                // array_key_exists: Just in case a method was removed but is still present in sortorder
                if (array_key_exists($TabCode, $Controller->ProfileTabs)) {
                    $TabInfo = val($TabCode, $Controller->ProfileTabs, []);
                    $CssClass .= val('CssClass', $TabInfo, '');
                    echo '<div class="BoxProfileInfo_detailbox__item"><div class="count">'.val('TabHtml', $TabInfo, $TabCode).'</div>'.$TabCode."</div>";
                }
            }
            echo '<div class="BoxProfileInfo_detailbox__item"><div class="count">'.($Controller->data('Counts')["Like"]["Total"] ?? 0).'</div><svg width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 13.9091C1 12.8045 1.89543 11.9091 3 11.9091H5.14115C5.95896 11.9091 6.69438 11.4112 6.9981 10.6519L10.303 2.38953C10.5203 1.84633 11.1555 1.60485 11.6787 1.86649L11.8871 1.97067C12.9034 2.47885 13.5455 3.51764 13.5455 4.65395V7.72727C13.5455 8.83184 14.4409 9.72727 15.5455 9.72727H19.8137C21.0308 9.72727 21.9657 10.8052 21.7936 12.0101L20.8369 18.7071C20.485 21.1704 18.3754 23 15.8871 23H3C1.89543 23 1 22.1046 1 21V13.9091Z" stroke="black" stroke-width="2"/>
            </svg></div>';
            echo "</div>";
        }
    ?>
</div>

<a class="btn-default btn-shadow modify-btn" href="https://www.alloprof.qc.ca/fr/profil"><?php echo t('Modify my profile on Alloprof') ?></a>
</div>
<?php } ?>
