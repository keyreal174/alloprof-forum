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
        $triggerIcon = "<img class='ProfilePhoto ProfilePhotoSmall' src='$imgUrl'/>";
    } else {
        $triggerIcon = sprite('SpOptions', 'Sprite Sprite16', $triggerTitle);
    }

    $dropdown->setTrigger('', 'anchor', 'MeButton FlyoutButton MeButton-user', $triggerIcon, '/profile/edit', ['title' => $triggerTitle, 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
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

    echo '<div class="SignInLinks">';

    echo anchor(t('Sign In'), signInUrl($this->_Sender->SelfUrl), (signInPopup() ? ' SignInPopup' : ''), ['rel' => 'nofollow']);

    echo '<div class="MenuDivider"></div>';
    $Url = registerUrl($this->_Sender->SelfUrl);
    if (!empty($Url))
        echo bullet(' ').anchor(t('Register'), $Url, 'SignInPopup', ['rel' => 'nofollow']).' ';
    echo '</div>';

    echo ' <div class="SignInIcons">';
    $this->fireEvent('SignInIcons');
    echo '</div>';

    echo '</div>';
endif;
