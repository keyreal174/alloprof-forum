<?php if (!defined('APPLICATION')) exit();

// Conversations
$Configuration['Conversations']['Conversation']['SpamCount'] = '2';
$Configuration['Conversations']['Conversation']['SpamTime'] = '30';
$Configuration['Conversations']['Conversation']['SpamLock'] = '60';
$Configuration['Conversations']['ConversationMessage']['SpamCount'] = '2';
$Configuration['Conversations']['ConversationMessage']['SpamTime'] = '30';
$Configuration['Conversations']['ConversationMessage']['SpamLock'] = '60';

// Database
$Configuration['Database']['Name'] = 'vanilla_dev';
$Configuration['Database']['Host'] = 'database';
$Configuration['Database']['User'] = 'root';
$Configuration['Database']['Password'] = '';

// EnabledApplications
$Configuration['EnabledApplications']['Conversations'] = 'conversations';
$Configuration['EnabledApplications']['Vanilla'] = 'vanilla';

// EnabledLocales
$Configuration['EnabledLocales']['vf_fr_CA'] = 'fr_CA';

// EnabledPlugins
$Configuration['EnabledPlugins']['recaptcha'] = false;
$Configuration['EnabledPlugins']['GettingStarted'] = 'GettingStarted';
$Configuration['EnabledPlugins']['stubcontent'] = true;
$Configuration['EnabledPlugins']['swagger-ui'] = true;
$Configuration['EnabledPlugins']['Quotes'] = false;
$Configuration['EnabledPlugins']['rich-editor'] = true;
$Configuration['EnabledPlugins']['Akismet'] = false;
$Configuration['EnabledPlugins']['Flagging'] = true;
$Configuration['EnabledPlugins']['StopForumSpam'] = false;
$Configuration['EnabledPlugins']['Reactions'] = true;
$Configuration['EnabledPlugins']['ProfileExtender'] = true;
$Configuration['EnabledPlugins']['VanillaStats'] = true;
$Configuration['EnabledPlugins']['vanillicon'] = true;
$Configuration['EnabledPlugins']['Multilingual'] = true;
$Configuration['EnabledPlugins']['jsconnect'] = false;
$Configuration['EnabledPlugins']['oauth2'] = false;
$Configuration['EnabledPlugins']['MathJax'] = true;
$Configuration['EnabledPlugins']['InfiniteScroll'] = true;
$Configuration['EnabledPlugins']['QnA'] = true;
$Configuration['EnabledPlugins']['googlesignin'] = true;
$Configuration['EnabledPlugins']['alloprof'] = true;

// Garden
$Configuration['Garden']['Debug'] = true;
$Configuration['Garden']['Errors']['LogFile'] = 'log/debug.log';
$Configuration['Garden']['Title'] = 'Alloprof';
$Configuration['Garden']['Cookie']['Salt'] = 'CvRpbMCk8CRQiTUd';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Registration']['ConfirmEmail'] = false;
$Configuration['Garden']['Registration']['Method'] = 'Basic'; // Options are: Basic, Captcha, Approval, Invitation
$Configuration['Garden']['Email']['SupportName'] = 'Alloprof';
$Configuration['Garden']['Email']['Format'] = 'html';
$Configuration['Garden']['Email']['UseSmtp'] = false;
$Configuration['Garden']['Email']['SmtpHost'] = 'smtp.postmarkapp.com';
$Configuration['Garden']['Email']['SmtpUser'] = '';
$Configuration['Garden']['Email']['SmtpPassword'] = '';
$Configuration['Garden']['Email']['SmtpPort'] = '587';
$Configuration['Garden']['Email']['SmtpSecurity'] = 'tls';
$Configuration['Garden']['Email']['MimeType'] = 'text/plain';
$Configuration['Garden']['Email']['SupportAddress'] = 'support@bbox.digital';
$Configuration['Garden']['SystemUserID'] = 1;
$Configuration['Garden']['UpdateToken'] = '4c0eb03d3f6d822dac9a74ab9839599747658393';
$Configuration['Garden']['InputFormatter'] = 'rich';
$Configuration['Garden']['Version'] = 'Undefined';
$Configuration['Garden']['CanProcessImages'] = true;
$Configuration['Garden']['MobileInputFormatter'] = 'rich';
$Configuration['Garden']['Installed'] = true;
$Configuration['Garden']['InstallationID'] = 'FF61-A96A2EF9-738F63CD';
$Configuration['Garden']['InstallationSecret'] = '765eddc30baa22dafe4cc461356382df40d19233';
$Configuration['Garden']['Theme'] = 'alloprof';
$Configuration['Garden']['MobileTheme'] = 'alloprof';
$Configuration['Garden']['Locale'] = 'fr_CA';
$Configuration['Garden']['EditContentTimeout'] = '-1';
$Configuration['Garden']['Format']['DisableUrlEmbeds'] = false;

// ImageUpload
$Configuration['ImageUpload']['Limits']['Width'] = '1000';
$Configuration['ImageUpload']['Limits']['Height'] = '1400';
$Configuration['ImageUpload']['Limits']['Enabled'] = false;

// Plugins
$Configuration['Plugins']['GettingStarted']['Dashboard'] = '1';
$Configuration['Plugins']['GettingStarted']['Discussion'] = '1';
$Configuration['Plugins']['GettingStarted']['Profile'] = '1';
$Configuration['Plugins']['GettingStarted']['Plugins'] = '1';
$Configuration['Plugins']['Akismet']['UserID'] = '12';
$Configuration['Plugins']['Flagging']['UseDiscussions'] = false;
$Configuration['Plugins']['Flagging']['CategoryID'] = '1';
$Configuration['Plugins']['StopForumSpam']['UserID'] = '13';
$Configuration['Plugins']['Vanillicon']['Type'] = 'v2';
$Configuration['Plugins']['Reactions']['TrackPointsSeparately'] = '';
$Configuration['Plugins']['Reactions']['ShowUserReactions'] = 'popup';
$Configuration['Plugins']['Reactions']['BestOfStyle'] = 'Tiles';
$Configuration['Plugins']['Reactions']['DefaultOrderBy'] = 'DateInserted';
$Configuration['Plugins']['Reactions']['DefaultEmbedOrderBy'] = 'Score';

// Preferences
$Configuration['Preferences']['Email']['Delete'] = 2;
$Configuration['Preferences']['Email']['AnswerAccepted'] = 1;
$Configuration['Preferences']['Email']['QuestionAnswered'] = 1;
$Configuration['Preferences']['Popup']['Delete'] = 2;
$Configuration['Preferences']['Popup']['AnswerAccepted'] = 1;
$Configuration['Preferences']['Popup']['QuestionAnswered'] = 1;

// ProfileExtender
$Configuration['ProfileExtender']['Fields'][0]['FormType'] = 'Dropdown';
$Configuration['ProfileExtender']['Fields'][0]['Label'] = 'Grade';
$Configuration['ProfileExtender']['Fields'][0]['Options'] = array (
  0 => 'Primaire 1',
  1 => 'Primaire 2',
  2 => 'Primaire 3',
  3 => 'Primaire 4',
  4 => 'Primaire 5',
  5 => 'Primaire 6',
  6 => 'Secondaire 1',
  7 => 'Secondaire 2',
  8 => 'Secondaire 3',
  9 => 'Secondaire 4',
  10 => 'Secondaire 5',
  11 => 'Enseignant',
  12 => 'Parent',
);
$Configuration['ProfileExtender']['Fields'][0]['OnRegister'] = '1';
$Configuration['ProfileExtender']['Fields'][0]['OnProfile'] = '1';
$Configuration['ProfileExtender']['Fields'][0]['Required'] = '1';
$Configuration['ProfileExtender']['Fields'][0]['Name'] = 'Grade';
$Configuration['ProfileExtender']['Fields'][1]['FormType'] = 'TextBox';
$Configuration['ProfileExtender']['Fields'][1]['Label'] = 'DisplayName';
$Configuration['ProfileExtender']['Fields'][1]['Options'] = '';
$Configuration['ProfileExtender']['Fields'][1]['Required'] = '1';
$Configuration['ProfileExtender']['Fields'][1]['OnRegister'] = '1';
$Configuration['ProfileExtender']['Fields'][1]['OnProfile'] = '1';
$Configuration['ProfileExtender']['Fields'][1]['Name'] = 'DisplayName';

// QnA
$Configuration['QnA']['Points']['Enabled'] = false;
$Configuration['QnA']['Points']['Answer'] = 1;
$Configuration['QnA']['Points']['AcceptedAnswer'] = 1;

// Reactions
$Configuration['Reactions']['PromoteValue'] = '5';
$Configuration['Reactions']['BuryValue'] = '-5';

// RichEditor
$Configuration['RichEditor']['Quote']['Enable'] = true;

// Routes
$Configuration['Routes']['YXBwbGUtdG91Y2gtaWNvbi5wbmc='] = array (
  0 => 'utility/showtouchicon',
  1 => 'Internal',
);
$Configuration['Routes']['cm9ib3RzLnR4dA=='] = array (
  0 => '/robots',
  1 => 'Internal',
);
$Configuration['Routes']['dXRpbGl0eS9yb2JvdHM='] = array (
  0 => '/robots',
  1 => 'Internal',
);
$Configuration['Routes']['Y29udGFpbmVyLmh0bWw='] = array (
  0 => 'staticcontent/container',
  1 => 'Internal',
);
$Configuration['Routes']['DefaultController'] = 'discussions';

// Vanilla
$Configuration['Vanilla']['Password']['SpamCount'] = 2;
$Configuration['Vanilla']['Password']['SpamTime'] = 1;
$Configuration['Vanilla']['Password']['SpamLock'] = 120;
$Configuration['Vanilla']['Categories']['MaxDisplayDepth'] = '1';
$Configuration['Vanilla']['Discussions']['PerPage'] = '30';
$Configuration['Vanilla']['Comments']['PerPage'] = '30';
$Configuration['Vanilla']['Comment']['MaxLength'] = '8000';
$Configuration['Vanilla']['Comment']['MinLength'] = '';
$Configuration['Vanilla']['Comment']['SpamCount'] = '3';
$Configuration['Vanilla']['Comment']['SpamTime'] = '30';
$Configuration['Vanilla']['Comment']['SpamLock'] = '60';
$Configuration['Vanilla']['AdminCheckboxes']['Use'] = false;
$Configuration['Vanilla']['Email']['FullPost'] = false;
$Configuration['Vanilla']['Discussion']['SpamCount'] = '2';
$Configuration['Vanilla']['Discussion']['SpamTime'] = '30';
$Configuration['Vanilla']['Discussion']['SpamLock'] = '60';
$Configuration['Vanilla']['Activity']['SpamCount'] = '2';
$Configuration['Vanilla']['Activity']['SpamTime'] = '30';
$Configuration['Vanilla']['Activity']['SpamLock'] = '60';
$Configuration['Vanilla']['ActivityComment']['SpamCount'] = '3';
$Configuration['Vanilla']['ActivityComment']['SpamTime'] = '30';
$Configuration['Vanilla']['ActivityComment']['SpamLock'] = '60';
