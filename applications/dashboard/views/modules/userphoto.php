<?php if (!defined('APPLICATION')) exit();
require Gdn::controller()->fetchViewLocation('helper_functions', 'Discussions', 'Vanilla');
$dataDriven = \Gdn::themeFeatures()->useDataDrivenTheme();
// $User = val('User', Gdn::controller());
$Discussion = val('Discussion', Gdn::controller());
$IsProfilePage = val('IsProfilePage', Gdn::controller());
$profileUrl = Gdn::request()->url('/profile/edit');
$User = Gdn::session()->User;
if (!$User && Gdn::session()->isValid()) {
    $User = Gdn::session()->User;
}

if (!$User) {
    return;
}

$Photo = $User->Photo;
if ($Photo) {
    $Photo = (isUrl($Photo)) ? $Photo : Gdn_Upload::url(changeBasename($Photo, 'p%s'));
    $PhotoAlt = t('Avatar');
} else {
    $Photo = UserModel::getDefaultAvatarUrl($User, 'profile');
    $PhotoAlt = t('Default Avatar');
}

if ($User->Banned) {
    $BannedPhoto = c('Garden.BannedPhoto', 'https://images.v-cdn.net/banned_large.png');
    if ($BannedPhoto) {
        $Photo = Gdn_Upload::url($BannedPhoto);
    }
}

if ($IsProfilePage) {
    // $AllowEditClass = "ChangePicture Popup";
    $AllowEditClass = "";
} else {
    $AllowEditClass = "";
}

$UserMetaData = Gdn::userModel()->getMeta(Gdn::session()->UserID, 'Profile.%', 'Profile.');
$UserName = $UserMetaData["DisplayName"] ?? "";
echo "<div class='Boxuserphoto'>";
if ($Photo) : ?>
    <div class="Photo PhotoWrap PhotoWrapLarge <?php echo val('_CssClass', $User); ?>">
        <?php
            $IsDefault = str_contains($Photo, 'avatars/0.svg');
            $ClassName = $IsDefault ? 'ProfilePhotoLarge ProfilePhotoDefaultWrapper' : 'ProfilePhotoLarge';
            echo "<a ".($IsProfilePage ? '' : 'href="'.$profileUrl.'"')." class='".$ClassName."' avatar--first-letter='".$UserName[0]."'>";
            echo "<img src='".$Photo."' class='ProfilePhotoLarge' alt='".$PhotoAlt."'/>";
            echo "</a>";
        ?>
    </div>
<?php elseif ($User->UserID == Gdn::session()->UserID || Gdn::session()->checkPermission('Garden.Users.Edit')) : ?>
    <div class="Photo">
        <?php echo anchor(t('Add a Profile Picture'), '/profile/picture?userid='.$User->UserID, 'AddPicture BigButton'); ?>
    </div>
<?php
endif;
?>
<div class="userphoto-personalinfo">
    <a <?php echo $IsProfilePage || $User->UserID !== Gdn::session()->UserID ? '' : 'href="'.$profileUrl.'"' ?> class="userphoto-personalinfo__name"><?php echo $UserName; ?></a>
    <a <?php echo $IsProfilePage || $User->UserID !== Gdn::session()->UserID ? '' : 'href="'.$profileUrl.'"' ?> class="userphoto-personalinfo__secondary">
        <?php
            if(userRoleCheck() == Gdn::config('Vanilla.ExtraRoles.Teacher')) {
                echo t('Alloprof Teacher').'<svg width="15" height="15" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.64495 0.776516C9.40048 0.0744949 10.5995 0.0744944 11.3551 0.776516L12.0133 1.38817C12.4454 1.78962 13.0461 1.9771 13.6413 1.89624L14.5482 1.77303C15.589 1.63163 16.5591 2.30857 16.7407 3.30306L16.8989 4.16953C17.0028 4.73822 17.3741 5.22906 17.9051 5.49967L18.7142 5.91197C19.6428 6.3852 20.0133 7.4805 19.5516 8.3876L19.1494 9.17793C18.8854 9.69665 18.8854 10.3034 19.1494 10.8221L19.5516 11.6124C20.0133 12.5195 19.6428 13.6148 18.7142 14.088L17.9051 14.5003C17.3741 14.7709 17.0028 15.2618 16.8989 15.8305L16.7407 16.6969C16.5591 17.6914 15.589 18.3684 14.5482 18.227L13.6413 18.1038C13.0461 18.0229 12.4454 18.2104 12.0133 18.6118L11.3551 19.2235C10.5995 19.9255 9.40048 19.9255 8.64495 19.2235L7.98668 18.6118C7.55463 18.2104 6.95389 18.0229 6.35868 18.1038L5.45182 18.227C4.41097 18.3684 3.44092 17.6914 3.2593 16.6969L3.10106 15.8305C2.9972 15.2618 2.62591 14.7709 2.0949 14.5003L1.28584 14.088C0.357241 13.6148 -0.0132854 12.5195 0.44837 11.6124L0.850598 10.8221C1.11459 10.3034 1.11459 9.69665 0.850598 9.17793L0.448371 8.3876C-0.0132849 7.4805 0.357241 6.3852 1.28584 5.91197L2.0949 5.49967C2.62591 5.22906 2.9972 4.73822 3.10106 4.16953L3.2593 3.30306C3.44092 2.30857 4.41097 1.63163 5.45182 1.77303L6.35868 1.89624C6.95388 1.9771 7.55463 1.78962 7.98668 1.38817L8.64495 0.776516Z" fill="#05BF8E"/>
                <path d="M6.25 10L8.75 12.25L13.75 7.75" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg></span>';
            } else {
                $Grade = t($UserMetaData["Grade"]);
                echo $Grade;
            }
        ?>
    </a>
</div>
</div>
