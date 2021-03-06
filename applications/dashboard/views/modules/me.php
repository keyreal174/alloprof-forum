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

$UserMetaData = Gdn::userModel()->getMeta(Gdn::session()->UserID, 'Plugin.%', 'Plugin.');
$Language = Gdn::config('Garden.Locale');

$session_invalid = $Session->isValid()?'':'data-session="invalid"';

if (preg_match('/zonedentraide/i', $_SERVER['REQUEST_URI'])) {
    $newURI = str_replace('zonedentraide', 'helpzone', $_SERVER['REQUEST_URI']);
    echo '<div slot="lang_switcher" class="language-btn link link--xsm" '.$session_invalid.' data-url="'. $newURI .'" id="en_GB"><span class="link__text locale-switcher__text">English</span></div>';
} else {
    $newURI = str_replace('helpzone', 'zonedentraide', $_SERVER['REQUEST_URI']);
    echo '<div slot="lang_switcher" class="language-btn link link--xsm" '.$session_invalid.' data-url="'. $newURI .'" id="fr_CA"><span class="link__text locale-switcher__text">Français</span></div>';
}

echo '<div slot="main_menu_right_before" class="Header-right">';
echo '<div class="MeBox-header">';
if ($Session->isValid()):
    echo '<div class="MeBox'.$CssClass.'">';
    if (!$useNewFlyouts) {
        echo userPhoto($User);
    }

    $currentLocale = 'fr';

    if (preg_match('/zonedentraide/i', $_SERVER['REQUEST_URI'])) {
        $currentLocale = 'fr';
        $newURI = str_replace('zonedentraide', 'helpzone', $_SERVER['REQUEST_URI']);
        echo '<div slot="main_menu_right_after" class="language-btn d-mobile" data-url="'. $newURI .'" id="en_GB">en</div>';
    } else {
        $currentLocale = 'en';
        $newURI = str_replace('helpzone', 'zonedentraide', $_SERVER['REQUEST_URI']);
        echo '<div slot="main_menu_right_after" class="language-btn d-mobile" data-url="'. $newURI .'" id="fr_CA">fr</div>';
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

    echo anchor('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M19.9161 14.9179L19.9161 14.9179L19.92 14.9227L21.2281 16.51C21.8749 17.333 21.2922 18.5 20.3059 18.5H2.69413C1.70638 18.5 1.12341 17.3296 1.77473 16.5064L1.77743 16.5029L3.01156 14.9147C4.05484 13.6148 4.61805 12.0281 4.61805 10.361V7.40559C4.61805 4.08081 6.92567 1.32724 9.99536 0.657052C11.0045 0.436726 12.0219 0.458866 12.9297 0.657053C15.9994 1.32722 18.307 4.0808 18.307 7.40559V10.361C18.307 12.0295 18.8712 13.6175 19.9161 14.9179Z" stroke="black"/>
    <path d="M15.2588 20.8681C14.9771 21.6419 14.4635 22.31 13.788 22.781C13.1125 23.2521 12.3081 23.5031 11.4846 23.5C10.6611 23.4968 9.8587 23.2396 9.18686 22.7633C8.51502 22.2871 8.00654 21.6152 7.73083 20.8392" stroke="black" stroke-linecap="round"/>
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
    $dropdown = new DropdownModule('', '', 'account-options', 'authorized');
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

    // echo '<div class="menu-mobile-header">';
    // echo '<svg width="115" height="25" viewBox="0 0 147 32" fill="none" xmlns="http://www.w3.org/2000/svg">
    //         <path d="M58.4417 28.5418C52.6308 28.5418 47.9027 23.7727 47.9027 17.9115C47.9027 12.0503 52.6308 7.28125 58.4417 7.28125C64.2526 7.28125 68.9808 12.0503 68.9808 17.9115C68.9808 23.7727 64.2526 28.5418 58.4417 28.5418ZM58.4417 10.8125C54.5798 10.8125 51.4398 13.9798 51.4398 17.8751C51.4398 21.7704 54.5798 24.9377 58.4417 24.9377C62.3036 24.9377 65.4437 21.7704 65.4437 17.8751C65.4437 13.9798 62.3036 10.8125 58.4417 10.8125Z" fill="#1A1919"/>
    //         <path d="M123.517 28.5418C117.706 28.5418 112.978 23.7727 112.978 17.9115C112.978 12.0503 117.706 7.28125 123.517 7.28125C129.328 7.28125 134.056 12.0503 134.056 17.9115C134.056 23.7727 129.328 28.5418 123.517 28.5418ZM123.517 10.8125C119.655 10.8125 116.515 13.9798 116.515 17.8751C116.515 21.7704 119.655 24.9377 123.517 24.9377C127.379 24.9377 130.519 21.7704 130.519 17.8751C130.519 13.9798 127.379 10.8125 123.517 10.8125Z" fill="#1A1919"/>
    //         <path d="M31.228 0.328125H27.4383V28.5784H31.228V0.328125Z" fill="#1A1919"/>
    //         <path d="M42.0197 0.328125H38.23V28.5784H42.0197V0.328125Z" fill="#1A1919"/>
    //         <path d="M92.0079 10.3758C90.0228 8.3007 87.352 7.13574 84.5007 7.13574C82.6239 7.13574 80.8192 7.64541 79.2312 8.59194C79.1229 8.66475 79.0146 8.70116 78.9063 8.77397C76.0189 10.667 74.2865 13.9071 74.2865 17.4748V19.2586V27.5954V30.7626V31.964L77.8957 29.8525L81.6854 27.6318C82.7321 27.923 83.8149 28.0686 84.9338 28.0322C90.2755 27.8138 94.6427 23.4452 94.8953 18.0572C95.0036 15.1812 93.993 12.4509 92.0079 10.3758ZM84.7894 24.5009C83.4179 24.5373 82.1547 24.2097 81.0719 23.5544L77.8596 25.4111V19.2586V18.9674V17.584C77.8596 15.3269 78.9785 13.179 80.8553 11.9412C80.9275 11.9048 80.9997 11.8684 81.0719 11.832C82.0825 11.2131 83.2374 10.8855 84.5007 10.8855C88.3265 10.8855 91.4304 14.1255 91.25 18.0208C91.1056 21.5157 88.2543 24.3917 84.7894 24.5009Z" fill="#1A1919"/>
    //         <path d="M0.0078753 18.0937C0.260523 23.4816 4.62773 27.8502 9.96943 28.0686C11.0883 28.105 12.1711 27.9594 13.2178 27.6682L17.0075 29.8889L20.6167 32.0004V30.799V27.6318V19.2586V17.4748C20.6167 13.9071 18.8843 10.667 15.9969 8.77397C15.8886 8.70116 15.7803 8.62835 15.6721 8.59194C14.084 7.64541 12.2433 7.13574 10.4025 7.13574C7.55123 7.13574 4.88038 8.3007 2.89528 10.3758C0.91019 12.4509 -0.100402 15.1812 0.0078753 18.0937ZM3.61713 18.0208C3.43667 14.1255 6.54064 10.8855 10.3665 10.8855C11.6297 10.8855 12.7847 11.2495 13.7952 11.832C13.8674 11.8684 13.9396 11.9048 14.0118 11.9412C15.8886 13.179 17.0075 15.3269 17.0075 17.584V18.9674V19.2586V25.4111L13.7952 23.5544C12.7125 24.2097 11.4492 24.5373 10.0777 24.5009C6.61282 24.3917 3.76151 21.5157 3.61713 18.0208Z" fill="#1A1919"/>
    //         <path d="M146.363 0C141.924 0 139.47 2.40273 139.47 6.80774V28.5779H143.259V12.2685H146.363V8.44596H143.259V6.80774C143.259 5.75199 143.44 5.02389 143.801 4.58703C144.234 4.07736 145.1 3.82253 146.327 3.82253H146.472V0H146.363Z" fill="#1A1919"/>
    //         <path d="M107.889 6.77148C101.717 6.77148 100.778 11.759 100.778 14.7078V28.5781H104.568V14.7442C104.568 12.0502 105.109 10.6304 107.889 10.6304H108.033V6.77148H107.889Z" fill="#1A1919"/>
    //     </svg>';
    // echo '<a href="#" class="Close">×</a>';
    // echo '</div>';
    $dropdown->setTrigger('', 'anchor', 'MeButton FlyoutButton MeButton-user', $triggerIcon, '/', ['title' => $triggerTitle, 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    $editModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-editprofile'];
    $preferencesModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-preferences'];

    // $dropdown->addLinkIf(hasEditProfile(Gdn::session()->UserID), $triggerIcon.t('My Profile'), '/profile/edit', 'profile.edit', '', [], $editModifiers);
    $dropdown->addLinkIf(!hasEditProfile(Gdn::session()->UserID), t('Preferences'), '/profile/preferences', 'profile.preferences', '', [], $preferencesModifiers);

    $site_url = 'https://www.alloprof.qc.ca/'.$currentLocale.'/';

    $dropdown->addLink(t('My space'), $site_url.($currentLocale == 'fr'?'espace':'space'), '', '');
    $dropdown->addLink(t('My profile'), $site_url.($currentLocale == 'fr'?'profil':'profile'), '', '');
    $dropdown->addLink(t('My settings'), $site_url.($currentLocale == 'fr'?'parametres':'settings'), '', '');

    $dropdown->addDivider();

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

    $dropdown->addLink('<span class="link__text">'.t('Log out').'</span>', signOutUrl(), 'entry.signout', 'link link--sm link--active text-align-center', $signoutModifiers);

    $this->EventArguments['Dropdown'] = &$dropdown;
    $this->fireEvent('FlyoutMenu');
    echo $dropdown;

    // $LinkToDropdown = new DropdownModule('', '', 'additional-links', 'additional-links__popup withHeader');
    // $LinkToDropdownTitle = t('additional-links');
    // $LinkToDropdownIcon = "<img class='ProfilePhoto BergerIcon' src='".url('/themes/alloprof/design/images/icons/Burger.svg')."'/>";
    // $LinkToDropdown->setTrigger('', 'anchor', 'LinksButton FlyoutButton', $LinkToDropdownIcon, '/', ['title' => $LinkToDropdownTitle, 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    // $LinkToDropdown->addLink(t('Alloprof Home'), preg_match('/^en/', $Language) ? "https://www.alloprof.qc.ca/en/students" : "https://www.alloprof.qc.ca/");
    // $LinkToDropdown->addLink(t('Alloprof 100% solutions'), preg_match('/^en/', $Language) ? "https://www.alloprof.qc.ca/en/solutions" : "https://www.alloprof.qc.ca/fr/solutions");
    // echo $LinkToDropdown;

    echo '<span class="ToggleFlyout d-desktop">';
    echo '<a class="MeButton FlyoutButton toggle-search"><svg class="search-open" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="10.5852" cy="10.5852" r="9.0852" stroke="black"/>
        <path d="M17 17L23 23" stroke="black" stroke-linecap="round"/>
        </svg>
        <svg class="search-close" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 2.00046L2.00018 22.0002" stroke="black" stroke-linecap="round"/>
        <path d="M22 21.9996L2 1.99997" stroke="black" stroke-linecap="round"/>
        </svg></a>';
    echo '</span>';

    if ($useNewFlyouts) {
        echo "<button class='MeBox-mobileClose'>×</button>";
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
else:
    echo '<div class="MeBox MeBox-SignIn'.$CssClass.'">';
    // if (preg_match('/zonedentraide/i', $_SERVER['REQUEST_URI'])) {
    //     $newURI = str_replace('zonedentraide', 'helpzone', $_SERVER['REQUEST_URI']);
    //     echo '<div slot="main_menu_right_after" class="language-btn" data-session="invalid" data-url="'. $newURI .'" id="en_GB">en</div>';
    // } else {
    //     $newURI = str_replace('helpzone', 'zonedentraide', $_SERVER['REQUEST_URI']);
    //     echo '<div slot="main_menu_right_after" class="language-btn" data-session="invalid" data-url="'. $newURI .'" id="fr_CA">fr</div>';
    // }

    echo '<div class="SignInLinks">';

    $dropdown = new DropdownModule('', '', 'account-options', 'unauthorized');
    $dropdown->setData('DashboardCount', $DashboardCount);

    $dropdown->setTrigger('', 'anchor', 'MeButton FlyoutButton MeButton-user unauthorized', '<svg style="width: 24px;" viewBox="0 0 25 25" class="header__avatar ng-tns-c83-1 ng-star-inserted"><g transform="translate(18.000000, 18.000000)" class="ng-tns-c83-1"><path d="M2-3c2.8,0,5,2.2,5,5S4.8,7,2,7h-15c-2.8,0-5-2.2-5-5s2.2-5,5-5H2z M-5.5-17c3,0,5.5,2.5,5.5,5.5
    S-2.5-6-5.5-6S-11-8.5-11-11.5S-8.5-17-5.5-17z" class="login__state ng-tns-c83-1" style="fill: transparent"></path><path d="M2-3c2.8,0,5,2.2,5,5S4.8,7,2,7h-15c-2.8,0-5-2.2-5-5s2.2-5,5-5H2z M2-2h-15c-2.2,0-4,1.8-4,4
    s1.8,4,4,4H2c2.2,0,4-1.8,4-4S4.2-2,2-2z M-5.5-18c3.6,0,6.5,2.9,6.5,6.5S-1.9-5-5.5-5S-12-7.9-12-11.5S-9.1-18-5.5-18z M-5.5-17
    c-3,0-5.5,2.5-5.5,5.5S-8.5-6-5.5-6S0-8.5,0-11.5S-2.5-17-5.5-17z" class="login__stroke ng-tns-c83-1"></path></g></svg>', '', ['title' => '', 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    $editModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-editprofile'];
    $preferencesModifiers['listItemCssClasses'] = ['EditProfileWrap', 'link-preferences'];

    $dropdown->addLink(t('Sign In'), false, '', 'SignInStudentPopupAgent btn btn--sm btn--login text-align-center', ['rel' => 'nofollow'], $editModifiers);
    $dropdown->addLink(t('Sign In'), '/entry/signinstudent?Target='.$this->_Sender->SelfUrl, 'studentsignin', 'SignInStudentPopup HiddenImportant', ['rel' => 'nofollow'], $editModifiers);
    $dropdown->addLink('<span class="link__text">'.t('Create an account').'</span>', registerUrl($this->_Sender->SelfUrl), 'register', 'registerPopup link link--sm link--active text-align-center', [], $editModifiers);
    // $dropdown->addLink(t('Teacher'), signInUrl($this->_Sender->SelfUrl), 'teachersignin', 'SignInPopup', ['rel' => 'nofollow'], $preferencesModifiers);

    $this->EventArguments['Dropdown'] = &$dropdown;
    $this->fireEvent('FlyoutMenu');
    echo $dropdown;
    echo '</div>';

    // $LinkToDropdown = new DropdownModule('', '', 'additional-links', 'additional-links__popup withHeader');
    // $LinkToDropdownTitle = t('additional-links');
    // $LinkToDropdownIcon = "<img class='ProfilePhoto BergerIcon' src='".url('/themes/alloprof/design/images/icons/Burger.svg')."'/>";
    // $LinkToDropdown->setTrigger('', 'anchor', 'LinksButton FlyoutButton', $LinkToDropdownIcon, '/', ['title' => $LinkToDropdownTitle, 'tabindex' => '0', "role" => "button", "aria-haspopup" => "true"]);
    // $LinkToDropdown->addLink(t('Alloprof Home'), preg_match('/^en/', $Language) ? "https://www.alloprof.qc.ca/en/students" : "https://www.alloprof.qc.ca/");
    // $LinkToDropdown->addLink(t('Alloprof 100% solutions'), preg_match('/^en/', $Language) ? "https://www.alloprof.qc.ca/en/solutions" : "https://www.alloprof.qc.ca/fr/solutions");
    // echo $LinkToDropdown;

    echo '<span class="ToggleFlyout d-desktop">';
    echo '<a class="MeButton FlyoutButton toggle-search"><svg class="search-open" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="10.5852" cy="10.5852" r="9.0852" stroke="black"/>
        <path d="M17 17L23 23" stroke="black" stroke-linecap="round"/>
        </svg>
        <svg class="search-close" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 2.00046L2.00018 22.0002" stroke="black" stroke-linecap="round"/>
        <path d="M22 21.9996L2 1.99997" stroke="black" stroke-linecap="round"/>
        </svg></a>';
    echo '</span>';

    echo ' <div class="SignInIcons">';
    $this->fireEvent('SignInIcons');
    echo '</div>';
    echo '</div>';
endif;
echo '</div>';
echo '</div>';