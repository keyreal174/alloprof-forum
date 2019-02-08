<?php if (!defined('APPLICATION')) exit();
// DO NOT EDIT THIS FILE. If you want to override the settings in this file then edit config.php.
// This is the global application configuration file that sets up default values for configuration settings.
$Configuration = [];

// Auto-enable some addons.
$Configuration['EnabledPlugins']['stubcontent'] = true;
$Configuration['EnabledPlugins']['swagger-ui'] = true;
$Configuration['EnabledApplications']['Dashboard'] = 'dashboard';
$Configuration['EnabledPlugins']['rich-editor'] = true;

// Database defaults.
$Configuration['Database']['Engine'] = 'MySQL';
$Configuration['Database']['Host'] = 'dbhost';
$Configuration['Database']['Name'] = 'dbname';
$Configuration['Database']['User'] = 'dbuser';
$Configuration['Database']['Password']  = '';
$Configuration['Database']['CharacterEncoding'] = 'utf8mb4';
$Configuration['Database']['DatabasePrefix'] = 'GDN_';
$Configuration['Database']['ExtendedProperties']['Collate'] = 'utf8mb4_unicode_ci';
$Configuration['Database']['ConnectionOptions'] = [
    12 => false, // PDO::ATTR_PERSISTENT
    1000 => true, // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY (missing in some PHP installations)
];

// Use a dirty cache by default. Try Vanilla with memcached!
$Configuration['Cache']['Enabled'] = true;
$Configuration['Cache']['Method'] = 'dirtycache';
$Configuration['Cache']['Filecache']['Store']  = PATH_CACHE.'/Filecache';

// Technical content stuff.
$Configuration['Garden']['ContentType'] = 'text/html';
$Configuration['Garden']['Locale'] = 'en';
$Configuration['Garden']['LocaleCodeset'] = 'UTF8';

// Site specifics.
$Configuration['Garden']['Installed'] = false; // Has Garden been installed yet? This blocks setup when true.
$Configuration['Garden']['Title'] = 'Vanilla';
$Configuration['Garden']['Domain'] = '';
$Configuration['Garden']['WebRoot'] = false; // You can set this value if you are using htaccess to direct into the application, but the correct webroot isn't being recognized.
$Configuration['Garden']['StripWebRoot'] = false;
$Configuration['Garden']['AllowSSL'] = true;
$Configuration['Garden']['PrivateCommunity'] = false;
$Configuration['Garden']['Forms']['HoneypotName'] = 'hpt';

// Developer stuff.
$Configuration['Garden']['Debug'] = false;
$Configuration['Garden']['Errors']['LogFile'] = '';
$Configuration['Garden']['FolderBlacklist'] = ['.', '..', '_svn', '.git']; // Folders we should never search for classes.

// User registration & authentication.
$Configuration['Garden']['Session']['Length'] = '15 minutes';
$Configuration['Garden']['Cookie']['Salt'] = ''; // We do this during setup, chill.
$Configuration['Garden']['Cookie']['Name'] = 'Vanilla';
$Configuration['Garden']['Cookie']['Path']  = '/';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Cookie']['HashMethod'] = 'md5'; // md5 or sha1
$Configuration['Garden']['Authenticator']['DefaultScheme'] = 'password'; // Types include 'Password', 'Handshake', 'Openid'
$Configuration['Garden']['Authenticator']['RegisterUrl'] = '/entry/register?Target=%2$s';
$Configuration['Garden']['Authenticator']['SignInUrl'] = '/entry/signin?Target=%2$s';
$Configuration['Garden']['Authenticator']['SignOutUrl'] = '/entry/signout/{Session_TransientKey}?Target=%2$s';
$Configuration['Garden']['Authenticator']['EnabledSchemes'] = ['password'];
$Configuration['Garden']['Authenticator']['SyncScreen'] = "smart";
$Configuration['Garden']['Authenticators']['password']['Name'] = "Password";
$Configuration['Garden']['UserAccount']['AllowEdit'] = true; // Allow users to edit their account information? (SSO requires accounts be edited in external system).
$Configuration['Garden']['Registration']['Method'] = 'Captcha'; // Options are: Basic, Captcha, Approval, Invitation
$Configuration['Garden']['Registration']['InviteExpiration'] = '1 week'; // When invitations expire. This will be plugged into strtotime().
$Configuration['Garden']['Registration']['InviteRoles'] = 'FALSE';
$Configuration['Garden']['Registration']['ConfirmEmail'] = false;
$Configuration['Garden']['Registration']['MinPasswordLength'] = 6;
$Configuration['Garden']['Registration']['NameUnique'] = true;
$Configuration['Garden']['TermsOfService'] = '/home/termsofservice'; // The url to the terms of service.
$Configuration['Garden']['Password']['MinLength'] = 6;
$Configuration['Garden']['Roles']['Manage'] = true; // @deprecated

// Outgoing email.
$Configuration['Garden']['Email']['UseSmtp'] = false;
$Configuration['Garden']['Email']['SmtpHost'] = '';
$Configuration['Garden']['Email']['SmtpUser'] = '';
$Configuration['Garden']['Email']['SmtpPassword'] = '';
$Configuration['Garden']['Email']['SmtpPort'] = '25';
$Configuration['Garden']['Email']['SmtpSecurity'] = ''; // ssl/tls
$Configuration['Garden']['Email']['MimeType'] = 'text/plain';
$Configuration['Garden']['Email']['SupportName'] = 'Support';
$Configuration['Garden']['Email']['SupportAddress'] = '';

// Contact with the mothership.
$Configuration['Garden']['UpdateCheckUrl'] = 'https://open.vanillaforums.com/addons/update';
$Configuration['Garden']['AddonUrl'] = 'https://open.vanillaforums.com/addons';
$Configuration['Garden']['VanillaUrl'] = 'https://open.vanillaforums.com';

// File handling.
$Configuration['Garden']['CanProcessImages'] = false;
$Configuration['Garden']['Upload']['MaxFileSize'] = '50M';
$Configuration['Garden']['Upload']['AllowedFileExtensions'] = ['txt', 'jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'ico', 'zip', 'gz', 'tar.gz', 'tgz', 'psd', 'ai', 'fla', 'pdf', 'doc', 'xls', 'ppt', 'docx', 'xlsx', 'pptx', 'log', 'rar', '7z'];
$Configuration['Garden']['Profile']['MaxHeight'] = 560;
$Configuration['Garden']['Profile']['MaxWidth'] = 560;
$Configuration['Garden']['Thumbnail']['Size'] = 200;

// Appearance.
$Configuration['Garden']['Theme'] = 'keystone';
$Configuration['Garden']['MobileTheme'] = 'mobile';
$Configuration['Garden']['Menu']['Sort'] = ['Dashboard', 'Discussions', 'Questions', 'Activity', 'Applicants', 'Conversations', 'User'];
$Configuration['Garden']['ThemeOptions']['Styles']['Key'] = 'Default';
$Configuration['Garden']['ThemeOptions']['Styles']['Value'] = '%s_default';

// Profiles.
$Configuration['Garden']['Profile']['Public']= true;
$Configuration['Garden']['Profile']['ShowAbout'] = true;
$Configuration['Garden']['Profile']['EditPhotos'] = true; // false to disable user photo editing
$Configuration['Garden']['Profile']['EditUsernames'] = false;
$Configuration['Garden']['BannedPhoto'] = 'https://images.v-cdn.net/banned_large.png';

// Embedding forum & comments.
$Configuration['Garden']['Embed']['CommentsPerPage'] = 50;
$Configuration['Garden']['Embed']['SortComments'] = 'desc';
$Configuration['Garden']['Embed']['PageToForum'] = true;
$Configuration['Garden']['SignIn']['Popup'] = true; // Should the sign-in link pop up or go to it's own page? (SSO requires going to it's own external page)

// User experience & formatting.
$Configuration['Garden']['InputFormatter'] = 'Rich'; // Html, BBCode, Markdown, Text, Rich
$Configuration['Garden']['MobileInputFormatter'] = 'Rich';
$Configuration['Garden']['Html']['AllowedElements'] = "a, abbr, acronym, address, area, audio, b, bdi, bdo, big, blockquote, br, caption, center, cite, code, col, colgroup, dd, del, details, dfn, div, dl, dt, em, figure, figcaption, font, h1, h2, h3, h4, h5, h6, hgroup, hr, i, img, ins, kbd, li, map, mark, menu, meter, ol, p, pre, q, s, samp, small, span, strike, strong, sub, sup, summary, table, tbody, td, tfoot, th, thead, time, tr, tt, u, ul, var, video, wbr";
$Configuration['Garden']['Search']['Mode'] = 'boolean'; // matchboolean, match, boolean, like
$Configuration['Garden']['EditContentTimeout'] = 3600; // -1 means no timeout. 0 means immediate timeout. > 0 is in seconds. 60 * 60 = 3600 (aka 1hr)
$Configuration['Garden']['Format']['Mentions'] = true;
$Configuration['Garden']['Format']['Hashtags'] = false;
$Configuration['Garden']['Format']['YouTube'] = true;
$Configuration['Garden']['Format']['Vimeo'] = true;
$Configuration['Garden']['Format']['EmbedSize'] = 'normal'; // tiny/small/normal/big/huge or WIDTHxHEIGHT

// Default preferences. Setting these to 'false' disables them globally.
$Configuration['Preferences']['Email']['ConversationMessage'] = '1';
$Configuration['Preferences']['Email']['BookmarkComment'] = '1';
$Configuration['Preferences']['Email']['ParticipateComment'] = '0';
$Configuration['Preferences']['Email']['WallComment'] = '0';
$Configuration['Preferences']['Email']['ActivityComment'] = '0';
$Configuration['Preferences']['Email']['DiscussionComment'] = '0';
$Configuration['Preferences']['Email']['Mention'] = '0';
$Configuration['Preferences']['Popup']['ConversationMessage'] = '1';
$Configuration['Preferences']['Popup']['BookmarkComment'] = '1';
$Configuration['Preferences']['Popup']['ParticipateComment'] = '0';
$Configuration['Preferences']['Popup']['WallComment'] = '1';
$Configuration['Preferences']['Popup']['ActivityComment'] = '1';
$Configuration['Preferences']['Popup']['DiscussionComment'] = '1';
$Configuration['Preferences']['Popup']['Mention'] = '1';

// Module visibility and sorting.
$Configuration['Garden']['Modules']['ShowGuestModule'] = true;
$Configuration['Garden']['Modules']['ShowSignedInModule'] = false;
$Configuration['Garden']['Modules']['ShowRecentUserModule'] = false;
$Configuration['Modules']['Dashboard']['Panel'] = ['MeModule', 'UserBoxModule', 'ActivityFilterModule', 'UserPhotoModule', 'ProfileFilterModule', 'SideMenuModule', 'UserInfoModule', 'GuestModule', 'Ads'];
$Configuration['Modules']['Dashboard']['Content'] = ['MessageModule', 'MeModule', 'UserBoxModule', 'ProfileOptionsModule', 'Notices', 'ActivityFilterModule', 'ProfileFilterModule', 'Content', 'Ads'];
$Configuration['Modules']['Vanilla']['Panel'] = ['MeModule', 'UserBoxModule', 'GuestModule', 'NewDiscussionModule', 'DiscussionFilterModule', 'SignedInModule', 'Ads'];
$Configuration['Modules']['Vanilla']['Content'] = ['MessageModule', 'MeModule', 'UserBoxModule', 'NewDiscussionModule', 'ProfileOptionsModule', 'Notices', 'NewConversationModule', 'NewDiscussionModule', 'DiscussionFilterModule', 'CategoryModeratorsModule', 'Content', 'Ads'];
$Configuration['Modules']['Conversations']['Panel'] = ['MeModule', 'UserBoxModule', 'NewConversationModule', 'SignedInModule', 'GuestModule', 'Ads'];
$Configuration['Modules']['Conversations']['Content'] = ['MessageModule', 'MeModule', 'UserBoxModule', 'NewConversationModule', 'Notices', 'Content', 'Ads'];

// Routes.
$Configuration['Routes']['DefaultController'] = 'discussions';
$Configuration['Routes']['DefaultForumRoot'] = 'discussions';
$Configuration['Routes']['Default404'] = ['dashboard/home/filenotfound', 'NotFound'];
$Configuration['Routes']['DefaultPermission'] = ['dashboard/home/unauthorized', 'NotAuthorized'];
$Configuration['Routes']['UpdateMode'] = 'dashboard/home/updatemode';
