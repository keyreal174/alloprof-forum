<?php
use Vanilla\FeatureFlagHelper;
if (!defined('APPLICATION')) exit();
$Session = Gdn::session();
$User = $Session->User;
$CssClass = '';
$transientKey = Gdn::session()->transientKey();

if ($this->CssClass)
    $CssClass .= ' '.$this->CssClass;

$DashboardCount = 0;
$ModerationCount = 0;
$spamCount = 0;
// Spam & Moderation Queue
if ($Session->checkPermission(['Garden.Settings.Manage', 'Garden.Moderation.Manage', 'Moderation.Spam.Manage', 'Moderation.ModerationQueue.Manage'], false)) {
    $LogModel = new LogModel();
    $spamCount = $LogModel->getOperationCount('spam');
    $ModerationCount = $LogModel->getOperationCount('moderate,pending');
    $DashboardCount += $ModerationCount;
}
// Applicant Count
if ($Session->checkPermission('Garden.Users.Approve')) {
    $RoleModel = new RoleModel();
    $ApplicantCount = $RoleModel->getApplicantCount();
    $DashboardCount += $ApplicantCount;
} else {
    $ApplicantCount = null;
}

$useNewFlyouts = Gdn::themeFeatures()->useNewFlyouts();

$this->EventArguments['DashboardCount'] = &$DashboardCount;
$this->fireEvent('BeforeFlyoutMenu');

if ($Session->isValid()):
    echo '<div class="MeBox'.$CssClass.'">';
    if (!$useNewFlyouts) {
        echo userPhoto($User);
    }
    echo '<div class="WhoIs">';
    if (!$useNewFlyouts) {
        echo userAnchor($User, 'Username');
    }
    echo '<div class="MeMenu">';

    echo '<div class="TalkTeacher">';
    echo '<a class="TalkTeacher-link_mobile" href="https://alloprof.qc.ca/fr/solutions"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M24.9691 14.0923V20.2534C24.9691 24.7387 21.3259 28.3778 16.8354 28.3778H15.3103C10.8198 28.3778 7.17651 24.7387 7.17651 20.2534V18.9839C7.19346 15.8865 9.70137 13.3645 12.8193 13.3645" fill="#FF55C3"/>
    <path d="M16.3778 10.7411L12.9718 12.9246C12.9549 12.9415 12.9379 12.9246 12.9379 12.9076V4.4616C12.9379 3.51375 12.311 2.59975 11.379 2.41356C10.6673 2.26123 10.0064 2.49819 9.54886 2.95519C9.19301 3.31064 8.97272 3.80149 8.97272 4.36004V18.0192C8.97272 18.0362 8.98966 18.0531 9.00661 18.0362L16.3778 10.7411Z" fill="#FF55C3"/>
    <path d="M16.937 11.3165L9.56579 18.5947L8.68463 18.7639L8.17627 18.0192V4.34305C8.17627 3.61523 8.48129 2.88742 8.98965 2.37964C9.65052 1.71953 10.5995 1.43179 11.5314 1.6349C12.8024 1.90572 13.7174 3.09053 13.7174 4.46153V11.5027L15.9542 10.0809L16.937 11.3165ZM9.75219 16.1743L12.4804 13.483L12.1415 12.9076V4.46153C12.1415 3.83527 11.7348 3.29364 11.2095 3.17516C10.8028 3.09053 10.3961 3.20901 10.108 3.49675C9.88775 3.71679 9.75219 4.02146 9.75219 4.34305V16.1743Z" fill="black"/>
    <path d="M16.9371 9.43772V16.8851C16.9371 17.1898 16.6999 17.4268 16.3949 17.4268H13.4633C13.1583 17.4268 12.9211 17.1898 12.9211 16.8851V9.43772C12.9211 9.13306 13.1583 8.8961 13.4633 8.8961H16.4118C16.6999 8.8961 16.9371 9.13306 16.9371 9.43772Z" fill="#FF55C3"/>
    <path d="M17.7335 10.8934V15.4126C17.7335 16.9528 16.4796 18.2054 14.9375 18.2054C13.3955 18.2054 12.1415 16.9528 12.1415 15.4126V10.8934C12.1415 9.35311 13.3955 8.10059 14.9375 8.10059C16.4796 8.10059 17.7335 9.37004 17.7335 10.8934ZM13.7175 15.4295C13.7175 16.1065 14.2597 16.6482 14.9375 16.6482C15.6153 16.6482 16.1576 16.1065 16.1576 15.4295V10.8934C16.1576 10.2163 15.6153 9.6747 14.9375 9.6747C14.2597 9.6747 13.7175 10.2163 13.7175 10.8934V15.4295Z" fill="black"/>
    <path d="M20.9532 9.99635V17.2237C20.9532 17.5284 20.7159 17.7653 20.4109 17.7653H17.4794C17.1744 17.7653 16.9371 17.5284 16.9371 17.2237V9.99635C16.9371 9.69168 17.1744 9.45472 17.4794 9.45472H20.4109C20.7159 9.47165 20.9532 9.70861 20.9532 9.99635Z" fill="#FF55C3"/>
    <path d="M21.7496 11.4689V15.768C21.7496 17.3083 20.4956 18.5608 18.9536 18.5608C17.4115 18.5608 16.1576 17.3083 16.1576 15.768V11.4689C16.1576 9.9286 17.4115 8.67609 18.9536 8.67609C20.4956 8.67609 21.7496 9.9286 21.7496 11.4689ZM17.7335 15.768C17.7335 16.4451 18.2758 16.9867 18.9536 16.9867C19.6314 16.9867 20.1736 16.4451 20.1736 15.768V11.4689C20.1736 10.7918 19.6314 10.2502 18.9536 10.2502C18.2758 10.2502 17.7335 10.7918 17.7335 11.4689V15.768Z" fill="black"/>
    <path d="M24.9692 15.3957V21.5398C24.9692 26.0083 21.3429 29.6304 16.8693 29.6304H15.3612C10.8876 29.6304 7.26129 26.0083 7.26129 21.5398V20.2873V18.984C7.26129 16.6144 9.19306 14.6848 11.5654 14.6848H13.6666H14.5986C15.7001 14.6848 16.5982 15.5819 16.5982 16.6821C16.5982 17.7823 15.7001 18.6793 14.5986 18.6793H12.0399V20.5073C14.1411 20.812 15.734 22.6062 15.734 24.7896" fill="#FF55C3"/>
    <path d="M25.7486 15.3957V21.5398C25.7486 26.4314 21.7665 30.4089 16.8693 30.4089H15.3611C10.4639 30.4089 6.48175 26.4314 6.48175 21.5398V18.984C6.48175 16.1743 8.76937 13.8893 11.5823 13.8893H14.6155C16.1575 13.8893 17.3946 15.1418 17.3946 16.682C17.3946 18.2223 16.1237 19.4579 14.5986 19.4579H12.8363V19.8641C14.9883 20.4904 16.5473 22.5045 16.5473 24.7895H14.9714C14.9714 23.0462 13.6666 21.5398 11.9382 21.2859L11.2603 21.1843V17.8838H14.5986C15.2594 17.8838 15.8017 17.3421 15.8017 16.682C15.8017 16.0219 15.2594 15.4803 14.5986 15.4803H11.5654C9.61664 15.4803 8.04072 17.0544 8.04072 19.0009V21.5567C8.04072 25.5851 11.3112 28.8518 15.3442 28.8518H16.8523C20.8853 28.8518 24.1558 25.5851 24.1558 21.5567V15.3957H25.7486Z" fill="black"/>
    <path d="M24.969 11.2319V17.2236C24.969 17.5283 24.7318 17.7653 24.4268 17.7653H21.4953C21.1902 17.7653 20.953 17.5283 20.953 17.2236V11.2319C20.953 10.9272 21.1902 10.6903 21.4953 10.6903H24.4268C24.7318 10.6903 24.969 10.9441 24.969 11.2319Z" fill="#FF55C3"/>
    <path d="M25.7486 12.7044V15.768C25.7486 17.3083 24.4946 18.5608 22.9526 18.5608C21.4106 18.5608 20.1566 17.3083 20.1566 15.768V12.7044C20.1566 11.1642 21.4106 9.91167 22.9526 9.91167C24.4946 9.91167 25.7486 11.1642 25.7486 12.7044ZM21.7495 15.768C21.7495 16.4451 22.2917 16.9867 22.9695 16.9867C23.6474 16.9867 24.1896 16.4451 24.1896 15.768V12.7044C24.1896 12.0274 23.6474 11.4858 22.9695 11.4858C22.2917 11.4858 21.7495 12.0274 21.7495 12.7044V15.768Z" fill="black"/>
    <path d="M6.46483 18.984C6.46483 17.4776 7.1257 16.1235 8.17631 15.1926V4.34307C8.17631 3.61526 8.48133 2.88744 8.98969 2.37967C9.65056 1.71956 10.5995 1.43182 11.5315 1.63493C12.8024 1.90574 13.7174 3.09055 13.7174 4.46155V8.40529C14.0902 8.2191 14.4969 8.11755 14.9375 8.11755C15.8864 8.11755 16.7168 8.59147 17.2251 9.30236C17.6996 8.92999 18.2927 8.69303 18.9536 8.69303C20.0889 8.69303 21.0548 9.37006 21.4954 10.3518C21.919 10.081 22.4274 9.92862 22.9527 9.92862C24.4947 9.92862 25.7486 11.1811 25.7486 12.7214V15.3957V15.7681V21.5398C25.7486 26.4314 21.7665 30.409 16.8693 30.409H15.3611C10.4639 30.409 6.48178 26.4314 6.48178 21.5398V18.984H6.46483ZM4.88892 18.984V21.5398C4.88892 27.3115 9.58278 32 15.3611 32H16.8693C22.6476 32 27.3415 27.3115 27.3415 21.5398V15.7681V15.3957V12.7045C27.3415 10.301 25.3758 8.33758 22.9696 8.33758C22.6646 8.33758 22.3765 8.37144 22.0884 8.42221C21.2751 7.59284 20.1397 7.10199 18.9536 7.10199C18.4452 7.10199 17.9538 7.18662 17.4963 7.35588C16.8523 6.89888 16.1067 6.61114 15.3103 6.54344V4.46155C15.3103 2.34581 13.87 0.517817 11.8704 0.0946695C10.3962 -0.209997 8.90496 0.230077 7.8713 1.27948C7.07487 2.075 6.6004 3.19211 6.6004 4.34307V14.5494C5.49895 15.7681 4.88892 17.3422 4.88892 18.984Z" fill="white"/>
    </svg>
    </a>';
    echo '<a class="btn btn-default TalkTeacher-link" href="https://alloprof.qc.ca/fr/solutions">'.t('Talk to a teacher').'</a>';
    echo '</div>';

    // Notifications
    $CountNotifications = $User->CountNotifications;
    $CNotifications = is_numeric($CountNotifications) && $CountNotifications > 0 ? '<span class="Alert NotificationsAlert"></span>' : '';

    echo '<span class="ToggleFlyout ToggleFlyout-notification" rel="/profile/notificationspopin?TransientKey='.htmlspecialchars(urlencode($transientKey)).'">';

    echo anchor('<svg width="21" height="24" viewBox="0 0 21 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18.5268 15.2311L18.5268 15.2312L18.5347 15.2407L19.8387 16.823C20.2338 17.332 19.8556 18 19.3064 18H1.69462C1.14305 18 0.764022 17.3264 1.16734 16.8166L1.16737 16.8166L1.17274 16.8097L2.40446 15.2245C3.51673 13.8373 4.11854 12.1416 4.11854 10.361V7.40559C4.11854 4.31771 6.26062 1.766 9.1025 1.14555C10.0377 0.941367 10.9822 0.961874 11.8235 1.14555C14.6654 1.76598 16.8075 4.3177 16.8075 7.40559V10.361C16.8075 12.1444 17.4112 13.8427 18.5268 15.2311Z" stroke="black" stroke-width="2"/>
            <path d="M13.7894 20.6971C13.543 21.3742 13.0935 21.9587 12.5025 22.3709C11.9115 22.783 11.2076 23.0027 10.487 23C9.76649 22.9972 9.06435 22.7721 8.47649 22.3554C7.88863 21.9387 7.44371 21.3508 7.20247 20.6718" stroke="black" stroke-width="2" stroke-linecap="round"/>
        </svg>'.sprite('SpNotifications', 'Sprite Sprite16', t('Notifications')).$CNotifications, userUrl($User), 'MeButton FlyoutButton js-clear-notifications', ['title' => t('Notifications'), 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    echo sprite('SpFlyoutHandle', 'Arrow');
    echo '<div class="Flyout FlyoutMenu Flyout-withFrame Flayout-notification"></div></span>';

    // // Inbox
    // if (Gdn::addonManager()->isEnabled('conversations', \Vanilla\Addon::TYPE_ADDON)) {
    //     $CountInbox = val('CountUnreadConversations', Gdn::session()->User);
    //     $CInbox = is_numeric($CountInbox) && $CountInbox > 0 ? ' <span class="Alert">'.$CountInbox.'</span>' : '';
    //     echo '<span class="ToggleFlyout" rel="/messages/popin">';
    //     echo anchor(sprite('SpInbox', 'Sprite Sprite16', t('Inbox')).$CInbox, '/messages/all', 'MeButton FlyoutButton', ['title' => t('Inbox'), 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    //     echo sprite('SpFlyoutHandle', 'Arrow');
    //     echo '<div class="Flyout FlyoutMenu Flyout-withFrame"></div></span>';
    // }

    // // Bookmarks
    // if (Gdn::addonManager()->lookupAddon('Vanilla')) {
    //     echo '<span class="ToggleFlyout" rel="/discussions/bookmarkedpopin">';
    //     echo anchor(sprite('SpBookmarks', 'Sprite Sprite16', t('Bookmarks')), '/discussions/bookmarked', 'MeButton FlyoutButton', ['title' => t('Bookmarks'), 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    //     echo sprite('SpFlyoutHandle', 'Arrow');
    //     echo '<div class="Flyout FlyoutMenu Flyout-withFrame"></div></span>';
    // }

    // Profile Settings & Logout
    $dropdown = new DropdownModule();
    $dropdown->setData('DashboardCount', $DashboardCount);
    $triggerTitle = t('Account Options');

    if ($useNewFlyouts) {
        $imgUrl = userPhotoUrl($User);
        $UserMetaData = Gdn::userModel()->getMeta(Gdn::session()->UserID, 'Profile.%', 'Profile.');
        $UserName = $UserMetaData["DisplayName"] ?? "";

        if (str_contains($imgUrl, 'avatars/0.svg')) {
            $ClassName = "ProfilePhotoDefaultWrapper";
            $triggerIcon = "<span class='".$ClassName."' avatar--first-letter='".$UserName[0]."'><img class='ProfilePhoto ProfilePhotoSmall' src='$imgUrl'/></span>";
        } else {
            $triggerIcon = "<img class='ProfilePhoto ProfilePhotoSmall' src='$imgUrl'/>";
        }
    } else {
        $triggerIcon = sprite('SpOptions', 'Sprite Sprite16', $triggerTitle);
    }

    $dropdown->setTrigger('', 'anchor', 'MeButton FlyoutButton MeButton-user', $triggerIcon, '/', ['title' => $triggerTitle, 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    $editModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-editprofile'];
    $preferencesModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-preferences'];

    $dropdown->addLinkIf(hasViewProfile(Gdn::session()->UserID), t('View Profile'), '/profile', 'profile.view', '', [], $editModifiers);
    $dropdown->addLinkIf(hasEditProfile(Gdn::session()->UserID), t('Edit Profile'), '/profile/edit', 'profile.edit', '', [], $editModifiers);
    $dropdown->addLinkIf(!hasEditProfile(Gdn::session()->UserID), t('Preferences'), '/profile/preferences', 'profile.preferences', '', [], $preferencesModifiers);

    $applicantModifiers = $ApplicantCount > 0 ? ['badge' => $ApplicantCount] : [];
    $applicantModifiers['listItemCssClasses'] = ['link-applicants'];
    $modModifiers = $ModerationCount > 0 ? ['badge' => $ModerationCount] : [];
    $spamModifiers = $spamCount > 0 ? ['badge' => $spamCount] : [];
    $modModifiers['listItemCssClasses'] = ['link-moderation'];
    $spamModifiers['listItemCssClasses'] = ['link-spam'];
    $dashboardModifiers['listItemCssClasses'] = ['link-dashboard'];
    $signoutModifiers['listItemCssClasses'] = ['link-signout', 'SignInOutWrap', 'SignOutWrap'];

    $spamPermission = $Session->checkPermission(['Garden.Settings.Manage', 'Garden.Moderation.Manage', 'Moderation.ModerationQueue.Manage'], false);
    $modPermission = $Session->checkPermission(['Garden.Settings.Manage', 'Garden.Moderation.Manage', 'Moderation.ModerationQueue.Manage'], false);
    $dashboardPermission = $Session->checkPermission(['Garden.Settings.View', 'Garden.Settings.Manage'], false);

    $dropdown->addLinkIf('Garden.Users.Approve', t('Applicants'), '/dashboard/user/applicants', 'moderation.applicants', '', [], $applicantModifiers);
    $dropdown->addLinkIf($spamPermission, t('Spam Queue'), '/dashboard/log/spam', 'moderation.spam', '', [], $spamModifiers);
    $dropdown->addLinkIf($modPermission, t('Moderation Queue'), '/dashboard/log/moderation', 'moderation.moderation', '', [], $modModifiers);
    $dropdown->addLinkIf($dashboardPermission, t('Dashboard'), '/dashboard/settings', 'dashboard.dashboard', '', [], $dashboardModifiers);

    $dropdown->addLink(t('Sign Out'), signOutUrl(), 'entry.signout', '', [], $signoutModifiers);

    $this->EventArguments['Dropdown'] = &$dropdown;
    $this->fireEvent('FlyoutMenu');
    echo $dropdown;
    if ($useNewFlyouts) {
        echo "<button class='MeBox-mobileClose'>×</button>";
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
else:
    echo '<div class="MeBox MeBox-SignIn'.$CssClass.'">';

    echo '<div class="TalkTeacher">';
    echo '<a class="TalkTeacher-link_mobile" href="https://alloprof.qc.ca/fr/solutions"><svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M24.9691 14.0923V20.2534C24.9691 24.7387 21.3259 28.3778 16.8354 28.3778H15.3103C10.8198 28.3778 7.17651 24.7387 7.17651 20.2534V18.9839C7.19346 15.8865 9.70137 13.3645 12.8193 13.3645" fill="#FF55C3"/>
    <path d="M16.3778 10.7411L12.9718 12.9246C12.9549 12.9415 12.9379 12.9246 12.9379 12.9076V4.4616C12.9379 3.51375 12.311 2.59975 11.379 2.41356C10.6673 2.26123 10.0064 2.49819 9.54886 2.95519C9.19301 3.31064 8.97272 3.80149 8.97272 4.36004V18.0192C8.97272 18.0362 8.98966 18.0531 9.00661 18.0362L16.3778 10.7411Z" fill="#FF55C3"/>
    <path d="M16.937 11.3165L9.56579 18.5947L8.68463 18.7639L8.17627 18.0192V4.34305C8.17627 3.61523 8.48129 2.88742 8.98965 2.37964C9.65052 1.71953 10.5995 1.43179 11.5314 1.6349C12.8024 1.90572 13.7174 3.09053 13.7174 4.46153V11.5027L15.9542 10.0809L16.937 11.3165ZM9.75219 16.1743L12.4804 13.483L12.1415 12.9076V4.46153C12.1415 3.83527 11.7348 3.29364 11.2095 3.17516C10.8028 3.09053 10.3961 3.20901 10.108 3.49675C9.88775 3.71679 9.75219 4.02146 9.75219 4.34305V16.1743Z" fill="black"/>
    <path d="M16.9371 9.43772V16.8851C16.9371 17.1898 16.6999 17.4268 16.3949 17.4268H13.4633C13.1583 17.4268 12.9211 17.1898 12.9211 16.8851V9.43772C12.9211 9.13306 13.1583 8.8961 13.4633 8.8961H16.4118C16.6999 8.8961 16.9371 9.13306 16.9371 9.43772Z" fill="#FF55C3"/>
    <path d="M17.7335 10.8934V15.4126C17.7335 16.9528 16.4796 18.2054 14.9375 18.2054C13.3955 18.2054 12.1415 16.9528 12.1415 15.4126V10.8934C12.1415 9.35311 13.3955 8.10059 14.9375 8.10059C16.4796 8.10059 17.7335 9.37004 17.7335 10.8934ZM13.7175 15.4295C13.7175 16.1065 14.2597 16.6482 14.9375 16.6482C15.6153 16.6482 16.1576 16.1065 16.1576 15.4295V10.8934C16.1576 10.2163 15.6153 9.6747 14.9375 9.6747C14.2597 9.6747 13.7175 10.2163 13.7175 10.8934V15.4295Z" fill="black"/>
    <path d="M20.9532 9.99635V17.2237C20.9532 17.5284 20.7159 17.7653 20.4109 17.7653H17.4794C17.1744 17.7653 16.9371 17.5284 16.9371 17.2237V9.99635C16.9371 9.69168 17.1744 9.45472 17.4794 9.45472H20.4109C20.7159 9.47165 20.9532 9.70861 20.9532 9.99635Z" fill="#FF55C3"/>
    <path d="M21.7496 11.4689V15.768C21.7496 17.3083 20.4956 18.5608 18.9536 18.5608C17.4115 18.5608 16.1576 17.3083 16.1576 15.768V11.4689C16.1576 9.9286 17.4115 8.67609 18.9536 8.67609C20.4956 8.67609 21.7496 9.9286 21.7496 11.4689ZM17.7335 15.768C17.7335 16.4451 18.2758 16.9867 18.9536 16.9867C19.6314 16.9867 20.1736 16.4451 20.1736 15.768V11.4689C20.1736 10.7918 19.6314 10.2502 18.9536 10.2502C18.2758 10.2502 17.7335 10.7918 17.7335 11.4689V15.768Z" fill="black"/>
    <path d="M24.9692 15.3957V21.5398C24.9692 26.0083 21.3429 29.6304 16.8693 29.6304H15.3612C10.8876 29.6304 7.26129 26.0083 7.26129 21.5398V20.2873V18.984C7.26129 16.6144 9.19306 14.6848 11.5654 14.6848H13.6666H14.5986C15.7001 14.6848 16.5982 15.5819 16.5982 16.6821C16.5982 17.7823 15.7001 18.6793 14.5986 18.6793H12.0399V20.5073C14.1411 20.812 15.734 22.6062 15.734 24.7896" fill="#FF55C3"/>
    <path d="M25.7486 15.3957V21.5398C25.7486 26.4314 21.7665 30.4089 16.8693 30.4089H15.3611C10.4639 30.4089 6.48175 26.4314 6.48175 21.5398V18.984C6.48175 16.1743 8.76937 13.8893 11.5823 13.8893H14.6155C16.1575 13.8893 17.3946 15.1418 17.3946 16.682C17.3946 18.2223 16.1237 19.4579 14.5986 19.4579H12.8363V19.8641C14.9883 20.4904 16.5473 22.5045 16.5473 24.7895H14.9714C14.9714 23.0462 13.6666 21.5398 11.9382 21.2859L11.2603 21.1843V17.8838H14.5986C15.2594 17.8838 15.8017 17.3421 15.8017 16.682C15.8017 16.0219 15.2594 15.4803 14.5986 15.4803H11.5654C9.61664 15.4803 8.04072 17.0544 8.04072 19.0009V21.5567C8.04072 25.5851 11.3112 28.8518 15.3442 28.8518H16.8523C20.8853 28.8518 24.1558 25.5851 24.1558 21.5567V15.3957H25.7486Z" fill="black"/>
    <path d="M24.969 11.2319V17.2236C24.969 17.5283 24.7318 17.7653 24.4268 17.7653H21.4953C21.1902 17.7653 20.953 17.5283 20.953 17.2236V11.2319C20.953 10.9272 21.1902 10.6903 21.4953 10.6903H24.4268C24.7318 10.6903 24.969 10.9441 24.969 11.2319Z" fill="#FF55C3"/>
    <path d="M25.7486 12.7044V15.768C25.7486 17.3083 24.4946 18.5608 22.9526 18.5608C21.4106 18.5608 20.1566 17.3083 20.1566 15.768V12.7044C20.1566 11.1642 21.4106 9.91167 22.9526 9.91167C24.4946 9.91167 25.7486 11.1642 25.7486 12.7044ZM21.7495 15.768C21.7495 16.4451 22.2917 16.9867 22.9695 16.9867C23.6474 16.9867 24.1896 16.4451 24.1896 15.768V12.7044C24.1896 12.0274 23.6474 11.4858 22.9695 11.4858C22.2917 11.4858 21.7495 12.0274 21.7495 12.7044V15.768Z" fill="black"/>
    <path d="M6.46483 18.984C6.46483 17.4776 7.1257 16.1235 8.17631 15.1926V4.34307C8.17631 3.61526 8.48133 2.88744 8.98969 2.37967C9.65056 1.71956 10.5995 1.43182 11.5315 1.63493C12.8024 1.90574 13.7174 3.09055 13.7174 4.46155V8.40529C14.0902 8.2191 14.4969 8.11755 14.9375 8.11755C15.8864 8.11755 16.7168 8.59147 17.2251 9.30236C17.6996 8.92999 18.2927 8.69303 18.9536 8.69303C20.0889 8.69303 21.0548 9.37006 21.4954 10.3518C21.919 10.081 22.4274 9.92862 22.9527 9.92862C24.4947 9.92862 25.7486 11.1811 25.7486 12.7214V15.3957V15.7681V21.5398C25.7486 26.4314 21.7665 30.409 16.8693 30.409H15.3611C10.4639 30.409 6.48178 26.4314 6.48178 21.5398V18.984H6.46483ZM4.88892 18.984V21.5398C4.88892 27.3115 9.58278 32 15.3611 32H16.8693C22.6476 32 27.3415 27.3115 27.3415 21.5398V15.7681V15.3957V12.7045C27.3415 10.301 25.3758 8.33758 22.9696 8.33758C22.6646 8.33758 22.3765 8.37144 22.0884 8.42221C21.2751 7.59284 20.1397 7.10199 18.9536 7.10199C18.4452 7.10199 17.9538 7.18662 17.4963 7.35588C16.8523 6.89888 16.1067 6.61114 15.3103 6.54344V4.46155C15.3103 2.34581 13.87 0.517817 11.8704 0.0946695C10.3962 -0.209997 8.90496 0.230077 7.8713 1.27948C7.07487 2.075 6.6004 3.19211 6.6004 4.34307V14.5494C5.49895 15.7681 4.88892 17.3422 4.88892 18.984Z" fill="white"/>
    </svg>
    </a>';
    echo '<a class="btn btn-default TalkTeacher-link" href="https://alloprof.qc.ca/fr/solutions">'.t('Talk to a teacher').'</a>';
    echo '</div>';

    echo '<div class="SignInLinks">';

    $dropdown = new DropdownModule('', '', '', 'unauthorized');
    $dropdown->setData('DashboardCount', $DashboardCount);

    $dropdown->setTrigger('', 'anchor', 'MeButton FlyoutButton MeButton-user unauthorized', '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="16.0005" cy="9.5" r="5.5" stroke="black" stroke-width="2"/>
    <rect x="4.00049" y="19.1667" width="24" height="8.83333" rx="4.41667" stroke="black" stroke-width="2"/>
    </svg>', '', ['title' => '', 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    $editModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-editprofile'];
    $preferencesModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-preferences'];

    // $dropdown->addLink(t('Sign In'), '/entry/jsconnect-redirect?client_id=alloprof', 'normalsignin', '', ['rel' => 'nofollow'], $editModifiers);
    // $dropdown->addLink(t('Register'), 'https://alloprof.qc.ca/jsconnect/register', 'register', '', [], $editModifiers);
    $dropdown->addLink(t('Sign In'), '/entry/signinstudent?Target='.$this->_Sender->SelfUrl, 'studentsignin', 'SignInStudentPopup', ['rel' => 'nofollow'], $editModifiers);
    $dropdown->addLink(t('Register'), registerUrl($this->_Sender->SelfUrl), 'register', 'registerPopup', [], $editModifiers);
    $dropdown->addLink(t('Teacher'), signInUrl($this->_Sender->SelfUrl), 'teachersignin', 'SignInPopup', ['rel' => 'nofollow'], $preferencesModifiers);

    $this->EventArguments['Dropdown'] = &$dropdown;
    $this->fireEvent('FlyoutMenu');
    echo $dropdown;


    // echo anchor(t('Sign In'), '/entry/jsconnect-redirect?client_id=alloprof', '', ['rel' => 'nofollow']);

    // echo '<div class="MenuDivider"></div>';
    // $Url = 'https://alloprof.qc.ca/jsconnect/register';
    // if (!empty($Url))
    //     echo bullet(' ').anchor(t('Register'), $Url, '', ['rel' => 'nofollow']).' ';

    // echo '<div class="MenuDivider"></div>';

    // if (!empty($Url))
    //     echo bullet(' ').anchor(t('Teacher'), signInUrl($this->_Sender->SelfUrl), 'SignInPopup', ['rel' => 'nofollow']).' ';

    echo '</div>';

    echo ' <div class="SignInIcons">';
    $this->fireEvent('SignInIcons');
    echo '</div>';

    echo '</div>';
endif;
