<?php if (!defined('APPLICATION')) exit();

require_once Gdn::controller()->fetchViewLocation('helper_functions', 'discussions', 'Vanilla');

$Controller = Gdn::controller();
$Session = Gdn::session();
$User = val('User', Gdn::controller());
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
?>

<div class="BoxFilter BoxProfileInfo">
    <?php
    // Get sorted filter links
    if (Gdn::session()->UserID == $User->UserID) {
        if(userRoleCheck() != Gdn::config('Vanilla.ExtraRoles.Teacher')) {
            echo "<div class='BoxProfileInfo_detailbox'>";
            foreach ($SortOrder as $TabCode) {
                $CssClass = $TabCode == $Controller->CurrentTab ? 'Active ' : '';
                // array_key_exists: Just in case a method was removed but is still present in sortorder
                if (array_key_exists($TabCode, $Controller->ProfileTabs)) {
                    $TabInfo = val($TabCode, $Controller->ProfileTabs, []);
                    $CssClass .= val('CssClass', $TabInfo, '');
                    echo '<div class="BoxProfileInfo_detailbox__item"><img src="/themes/alloprof/design/images/icons/'.$TabCode.'.svg"/>'.anchor(val('TabHtml', $TabInfo, $TabCode), val('TabUrl', $TabInfo))."</div>";
                }
            }
            echo "</div>";
        }
    } else {
        echo "<div class='BoxProfileInfo_detailbox'>";
        $photoUrl = val('PhotoUrl', $Category);

        if($photoUrl)
            $photo = '<span class="category-icon"><img src="'.$photoUrl.'" class="CategoryPhoto" /></span>';
        else $photo = '<span class="category-icon"></span>';

        if ($Category["DisplayAs"] === 'Heading') {
            echo $CountText.' '.htmlspecialchars($Category["Name"]);
        } else {
            echo anchor($photo.''.$CountText.' '.htmlspecialchars($Category["Name"]), categoryUrl($Category), 'ItemLink');
        }
        echo "</div>";
    }

        ?>
    <?php if (Gdn::session()->UserID == $User->UserID) { ?>
    <div class="BoxProfileInfo_viewprofile">
        <a href="/profile" class="BoxProfileInfo_viewprofile__btn"><img src="/themes/alloprof/design/images/icons/emoticon.svg"/><?php echo t('View Profile') ?></a>
    </div>
    <?php } ?>
</div>
<?php } ?>

