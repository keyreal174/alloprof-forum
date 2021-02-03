<?php if (!defined('APPLICATION')) exit();

$Controller = Gdn::controller();
$Session = Gdn::session();

// Get the tab sort order from the user-prefs.
//$SortOrder = FALSE;
//$SortOrder = val('ProfileTabOrder', $Controller->User->Preferences, false);
// If not in the user prefs, get the sort order from the application prefs.
//if ($SortOrder === FALSE)
$SortOrder = c('Garden.ProfileTabOrder');

if (!is_array($SortOrder))
    $SortOrder = [];

// Make sure that all tabs are present in $SortOrder
foreach ($Controller->ProfileTabs as $TabCode => $TabInfo) {
    if (!in_array($TabCode, $SortOrder))
        $SortOrder[] = $TabCode;
}
?>
<div class="BoxFilter BoxProfileInfo">
    <div class="BoxProfileInfo_detailbox">
        <?php
        // Get sorted filter links
        foreach ($SortOrder as $TabCode) {
            $CssClass = $TabCode == $Controller->CurrentTab ? 'Active ' : '';
            // array_key_exists: Just in case a method was removed but is still present in sortorder
            if (array_key_exists($TabCode, $Controller->ProfileTabs)) {
                $TabInfo = val($TabCode, $Controller->ProfileTabs, []);
                $CssClass .= val('CssClass', $TabInfo, '');
                echo '<div class="BoxProfileInfo_detailbox__item"><img src="/themes/alloprof/design/images/icons/'.$TabCode.'.svg"/>'.anchor(val('TabHtml', $TabInfo, $TabCode), val('TabUrl', $TabInfo))."</div>";
            }
        }

        ?>
    </div>
    <div class="BoxProfileInfo_viewprofile">
        <a href="/profile" class="BoxProfileInfo_viewprofile__btn"><img src="/themes/alloprof/design/images/icons/emoticon.svg"/>View Profile</a>
    </div>
</div>
