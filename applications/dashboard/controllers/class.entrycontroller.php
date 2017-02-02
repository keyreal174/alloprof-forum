<?php
/**
 * Manages users manually authenticating (signing in).
 *
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Dashboard
 * @since 2.0
 */

/**
 * Handles /entry endpoint.
 */
class EntryController extends Gdn_Controller {

    /** @var array Models to include. */
    public $Uses = array('Database', 'Form', 'UserModel');

    /** @var Gdn_Form */
    public $Form;

    /** @var UserModel */
    public $UserModel;

    /** @var string Reusable username requirement error message. */
    public $UsernameError = '';

    /** @var string Place to store DeliveryType. */
    protected $_RealDeliveryType;

    /**
     * Setup error message & override MasterView for popups.
     *
     * @since 2.0.0
     * @access public
     */
    public function __construct() {
        parent::__construct();
        $this->internalMethods[] = 'target';

        // Set error message here so it can run thru t()
        $this->UsernameError = t('UsernameError', 'Username can only contain letters, numbers, underscores, and must be between 3 and 20 characters long.');

        // Allow use of a master popup template for easier theming.
        if (Gdn::request()->get('display') === 'popup') {
            $this->MasterView = 'popup';
        }
    }

    /**
     * Include JS and CSS used by all methods.
     *
     * Always called by dispatcher before controller's requested method.
     *
     * @since 2.0.0
     * @access public
     */
    public function initialize() {
        $this->Head = new HeadModule($this);
        $this->Head->addTag('meta', array('name' => 'robots', 'content' => 'noindex'));

        $this->addJsFile('jquery.js');
        $this->addJsFile('jquery.form.js');
        $this->addJsFile('jquery.popup.js');
        $this->addJsFile('jquery.gardenhandleajaxform.js');
        $this->addJsFile('global.js');

        $this->addCssFile('style.css');
        $this->addCssFile('vanillicon.css', 'static');
        parent::initialize();
        Gdn_Theme::section('Entry');
    }

    /**
     * Authenticate the user attempting to sign in.
     *
     * Events: BeforeAuth
     *
     * @since 2.0.0
     * @access public
     *
     * @param string $AuthenticationSchemeAlias Type of authentication we're attempting.
     */
    public function auth($AuthenticationSchemeAlias = 'default') {
        Gdn::session()->ensureTransientKey();

        $this->EventArguments['AuthenticationSchemeAlias'] = $AuthenticationSchemeAlias;
        $this->fireEvent('BeforeAuth');

        // Allow hijacking auth type
        $AuthenticationSchemeAlias = $this->EventArguments['AuthenticationSchemeAlias'];

        // Attempt to set Authenticator with requested method or fallback to default
        try {
            $Authenticator = Gdn::authenticator()->authenticateWith($AuthenticationSchemeAlias);
        } catch (Exception $e) {
            $Authenticator = Gdn::authenticator()->authenticateWith('default');
        }

        // Set up controller
        $this->View = 'auth/'.$Authenticator->getAuthenticationSchemeAlias();
        $this->Form->setModel($this->UserModel);
        $this->Form->addHidden('ClientHour', date('Y-m-d H:00')); // Use the server's current hour as a default.

        $Target = $this->target();

        $this->Form->addHidden('Target', $Target);

        // Import authenticator data source
        switch ($Authenticator->dataSourceType()) {
            case Gdn_Authenticator::DATA_FORM:
                $Authenticator->fetchData($this->Form);
                break;

            case Gdn_Authenticator::DATA_REQUEST:
            case Gdn_Authenticator::DATA_COOKIE:
                $Authenticator->fetchData(Gdn::request());
                break;
        }

        // By default, just render the view
        $Reaction = Gdn_Authenticator::REACT_RENDER;

        // Where are we in the process? Still need to gather (render view) or are we validating?
        $AuthenticationStep = $Authenticator->currentStep();

        switch ($AuthenticationStep) {

            // User is already logged in
            case Gdn_Authenticator::MODE_REPEAT:
                $Reaction = $Authenticator->repeatResponse();
                break;

            // Not enough information to perform authentication, render input form
            case Gdn_Authenticator::MODE_GATHER:
                $this->addJsFile('entry.js');
                $Reaction = $Authenticator->loginResponse();
                if ($this->Form->isPostBack()) {
                    $this->Form->addError('ErrorCredentials');
                    Logger::event(
                        'signin_failure',
                        Logger::WARNING,
                        '{username} failed to sign in. Some or all credentials were missing.'
                    );
                }
                break;

            // All information is present, authenticate
            case Gdn_Authenticator::MODE_VALIDATE:

                // Attempt to authenticate.
                try {
                    if (!$this->Request->isAuthenticatedPostBack() && !c('Garden.Embed.Allow')) {
                        $this->Form->addError('Please try again.');
                        $Reaction = $Authenticator->failedResponse();
                    } else {
                        $AuthenticationResponse = $Authenticator->authenticate();

                        $UserInfo = array();
                        $UserEventData = array_merge(array(
                            'UserID' => Gdn::session()->UserID,
                            'Payload' => val('HandshakeResponse', $Authenticator, false)
                        ), $UserInfo);

                        Gdn::authenticator()->trigger($AuthenticationResponse, $UserEventData);
                        switch ($AuthenticationResponse) {
                            case Gdn_Authenticator::AUTH_PERMISSION:
                                $this->Form->addError('ErrorPermission');
                                Logger::event(
                                    'signin_failure',
                                    Logger::WARNING,
                                    '{username} failed to sign in. Permission denied.'
                                );
                                $Reaction = $Authenticator->failedResponse();
                                break;

                            case Gdn_Authenticator::AUTH_DENIED:
                                $this->Form->addError('ErrorCredentials');
                                Logger::event(
                                    'signin_failure',
                                    Logger::WARNING,
                                    '{username} failed to sign in. Authentication denied.'
                                );
                                $Reaction = $Authenticator->failedResponse();
                                break;

                            case Gdn_Authenticator::AUTH_INSUFFICIENT:
                                // Unable to comply with auth request, more information is needed from user.
                                Logger::event(
                                    'signin_failure',
                                    Logger::WARNING,
                                    '{username} failed to sign in. More information needed from user.'
                                );
                                $this->Form->addError('ErrorInsufficient');
                                $Reaction = $Authenticator->failedResponse();
                                break;

                            case Gdn_Authenticator::AUTH_PARTIAL:
                                // Partial auth completed.
                                $Reaction = $Authenticator->partialResponse();
                                break;

                            case Gdn_Authenticator::AUTH_SUCCESS:
                            default:
                                // Full auth completed.
                                if ($AuthenticationResponse == Gdn_Authenticator::AUTH_SUCCESS) {
                                    $UserID = Gdn::session()->UserID;
                                } else {
                                    $UserID = $AuthenticationResponse;
                                }

                                safeHeader("X-Vanilla-Authenticated: yes");
                                safeHeader("X-Vanilla-TransientKey: ".Gdn::session()->transientKey());
                                $Reaction = $Authenticator->successResponse();
                        }
                    }
                } catch (Exception $Ex) {
                    $this->Form->addError($Ex);
                }
                break;

            case Gdn_Authenticator::MODE_NOAUTH:
                $Reaction = Gdn_Authenticator::REACT_REDIRECT;
                break;
        }

        switch ($Reaction) {

            case Gdn_Authenticator::REACT_RENDER:
                // Do nothing (render the view)
                break;

            case Gdn_Authenticator::REACT_EXIT:
                exit();
                break;

            case Gdn_Authenticator::REACT_REMOTE:
                // Let the authenticator handle generating output, using a blank slate
                $this->_DeliveryType = DELIVERY_TYPE_VIEW;

                exit;
                break;

            case Gdn_Authenticator::REACT_REDIRECT:
            default:

                if (is_string($Reaction)) {
                    $Route = $Reaction;
                } else {
                    $Route = $this->redirectTo();
                }

                if ($this->_RealDeliveryType != DELIVERY_TYPE_ALL && $this->_DeliveryType != DELIVERY_TYPE_ALL) {
                    $this->RedirectUrl = url($Route);
                } else {
                    if ($Route !== false) {
                        redirect($Route);
                    } else {
                        redirect(Gdn::router()->getDestination('DefaultController'));
                    }
                }
                break;
        }

        $this->setData('SendWhere', "/entry/auth/{$AuthenticationSchemeAlias}");
        $this->render();
    }

    /**
     * Check the default provider to see if it overrides one of the entry methods and then redirect.
     *
     * @param string $Type One of the following.
     *  - SignIn
     *  - Register
     *  - SignOut (not complete)
     * @param string $Target
     * @param string $TransientKey
     */
    protected function checkOverride($Type, $Target, $TransientKey = null) {
        if (!$this->Request->get('override', true)) {
            return;
        }

        $Provider = Gdn_AuthenticationProviderModel::getDefault();
        if (!$Provider) {
            return;
        }

        $this->EventArguments['Target'] = $Target;
        $this->EventArguments['DefaultProvider'] =& $Provider;
        $this->EventArguments['TransientKey'] = $TransientKey;
        $this->fireEvent("Override{$Type}");

        $Url = $Provider[$Type.'Url'];
        if ($Url) {
            switch ($Type) {
                case 'Register':
                case 'SignIn':
                    // When the other page comes back it needs to go through /sso to force a sso check.
                    $Target = '/sso?target='.urlencode($Target);
                    break;
                case 'SignOut':
                    $Cookie = c('Garden.Cookie.Name');
                    if (strpos($Url, '?') === false) {
                        $Url .= '?vfcookie='.urlencode($Cookie);
                    } else {
                        $Url .= '&vfcookie='.urlencode($Cookie);
                    }

                    // Check to sign out here.
                    $SignedOut = !Gdn::session()->isValid();
                    if (!$SignedOut && (Gdn::session()->validateTransientKey($TransientKey) || $this->Form->isPostBack())) {
                        Gdn::session()->end();
                        $SignedOut = true;
                    }

                    // Sign out is a bit of a tricky thing so we configure the way it works.
                    $SignoutType = c('Garden.SSO.Signout');
                    switch ($SignoutType) {
                        case 'redirect-only':
                            // Just redirect to the url.
                            break;
                        case 'post-only':
                            $this->setData('Method', 'POST');
                            break;
                        case 'post':
                            // Post to the url after signing out here.
                            if (!$SignedOut) {
                                return;
                            }
                            $this->setData('Method', 'POST');
                            break;
                        case 'none':
                            return;
                        case 'redirect':
                        default:
                            if (!$SignedOut) {
                                return;
                            }
                            break;
                    }

                    break;
                default:
                    throw new Exception("Unknown entry type $Type.");
            }

            $Url = str_ireplace('{target}', rawurlencode(url($Target, true)), $Url);

            if ($this->deliveryType() == DELIVERY_TYPE_ALL && strcasecmp($this->data('Method'), 'POST') != 0) {
                redirectUrl($Url, 302);
            } else {
                $this->setData('Url', $Url);
                $this->render('Redirect', 'Utility');
                die();
            }
        }
    }

    /**
     * SSO facilitator page. Plugins use event `ConnectData` to complete SSO connections.
     *
     * Users only see this page for non-seamless connections that prompt them to finish connecting
     * by entering a username and/or password (and possibly email).
     *
     * @since 2.0.0
     * @access public
     *
     * @param string $Method Used to register multiple providers on ConnectData event.
     */
    public function connect($Method) {
        // Basic page setup.
        $this->addJsFile('entry.js');
        $this->View = 'connect';
        $this->addDefinition('Username already exists.', t('Username already exists.'));
        $this->addDefinition('Choose a name to identify yourself on the site.', t('Choose a name to identify yourself on the site.'));

        // Determine what step in the process we're at.
        $IsPostBack = $this->Form->isPostBack() && $this->Form->getFormValue('Connect', null) !== null;
        $UserSelect = $this->Form->getFormValue('UserSelect');

        /**
         * When a user is connecting through SSO she is prompted to choose a username.
         * If she chooses an existing username, she is prompted to enter the password to claim it.
         * Setting AllowConnect = false disables that workflow, forcing the user to choose a unique username.
         */
        $allowConnect = c('Garden.Registration.AllowConnect', true);
        $this->setData('AllowConnect', $allowConnect);
        $this->addDefinition('AllowConnect', $allowConnect);

        if (!$IsPostBack) {
            // Initialize data array that can be set by a plugin.
            $Data = [
                'Provider' => '',
                'ProviderName' => '',
                'UniqueID' => '',
                'FullName' => '',
                'Name' => '',
                'Email' => '',
                'Photo' => '',
                'Target' => $this->target()
            ];
            $this->Form->setData($Data);
            $this->Form->addHidden('Target', $this->Request->get('Target', '/'));
        }

        // SSO providers can check to see if they are being used and modify the data array accordingly.
        $this->EventArguments = [$Method];

        // Filter the form data for users.
        // SSO plugins must reset validated data each postback.
        $currentData = $this->Form->formValues();
        $filteredData = Gdn::userModel()->filterForm($currentData, true);
        $filteredData = array_replace($filteredData, arrayTranslate($currentData, ['TransientKey', 'hpt']));
        unset($filteredData['Roles'], $filteredData['RoleID']);
        $this->Form->formValues($filteredData);

        // Fire ConnectData event & error handling.
        try {
            // Where your SSO plugin does magic.
            $this->EventArguments['Form'] = $this->Form;
            $this->fireEvent('ConnectData');
            $this->fireEvent('AfterConnectData');
        } catch (Gdn_UserException $Ex) {
            // Your SSO magic said no.
            $this->Form->addError($Ex);
            return $this->render('ConnectError');
        } catch (Exception $Ex) {
            // Your SSO magic blew up.
            if (debug()) {
                $this->Form->addError($Ex);
            } else {
                $this->Form->addError('There was an error fetching the connection data.');
            }
            return $this->render('ConnectError');
        }

        // Allow a provider to not send an email address but require one be manually entered.
        if (!UserModel::noEmail()) {
            $emailProvided = $this->Form->getFormValue('Email');
            $emailRequested = $this->Form->getFormValue('EmailVisible');
            if (!$emailProvided || $emailRequested) {
                $this->Form->setFormValue('EmailVisible', true);
                $this->Form->addHidden('EmailVisible', true);

                if ($IsPostBack) {
                    $this->Form->setFormValue('Email', val('Email', $currentData));
                }
            }
            if ($IsPostBack && $emailRequested) {
                $this->Form->validateRule('Email', 'ValidateRequired');
                $this->Form->validateRule('Email', 'ValidateEmail');
            }
        }

        // Make sure the minimum required data has been provided by the connection.
        if (!$this->Form->getFormValue('Provider')) {
            $this->Form->addError('ValidateRequired', t('Provider'));
        }
        if (!$this->Form->getFormValue('UniqueID')) {
            $this->Form->addError('ValidateRequired', t('UniqueID'));
        }

        if (!$this->data('Verified')) {
            // Whatever event handler catches this must set the data 'Verified' = true
            // to prevent a random site from connecting without credentials.
            // This must be done EVERY postback and is VERY important.
            $this->Form->addError(t('The connection data has not been verified.'));
        }

        // If we've accrued errors, stop here and show them.
        if ($this->Form->errorCount() > 0) {
            $this->render();
            return;
        }

        // Check if we need to sync roles
        if (($this->data('Trusted') || c('Garden.SSO.SyncRoles')) && $this->Form->getFormValue('Roles', null) !== null) {
            $SaveRoles = $SaveRolesRegister = true;

            // Translate the role names to IDs.
            $Roles = $this->Form->getFormValue('Roles', null);
            $Roles = RoleModel::getByName($Roles);
            $RoleIDs = array_keys($Roles);

            // Ensure user has at least one role.
            if (empty($RoleIDs)) {
                $RoleIDs = $this->UserModel->newUserRoleIDs();
            }

            // Allow role syncing to only happen on first connect.
            if (c('Garden.SSO.SyncRolesBehavior') === 'register') {
                $SaveRoles = false;
            }

            $this->Form->setFormValue('RoleID', $RoleIDs);
        } else {
            $SaveRoles = false;
            $SaveRolesRegister = false;
        }

        $UserModel = Gdn::userModel();

        // Find an existing user associated with this provider & uniqueid.
        $Auth = $UserModel->getAuthentication($this->Form->getFormValue('UniqueID'), $this->Form->getFormValue('Provider'));
        $UserID = val('UserID', $Auth);

        // The user is already in the UserAuthentication table
        if ($UserID) {
            $this->Form->setFormValue('UserID', $UserID);

            // Update their info.
            if (c('Garden.Registration.ConnectSynchronize', true)) {
                $User = Gdn::userModel()->getID($UserID, DATASET_TYPE_ARRAY);
                $Data = $this->Form->formValues();

                // Don't overwrite the user photo if the user uploaded a new one.
                $Photo = val('Photo', $User);
                if (!val('Photo', $Data) || ($Photo && !isUrl($Photo))) {
                    unset($Data['Photo']);
                }

                // Synchronize the user's data.
                $UserModel->save($Data, ['NoConfirmEmail' => true, 'FixUnique' => true, 'SaveRoles' => $SaveRoles]);
                $this->EventArguments['UserID'] = $UserID;
                $this->fireEvent('AfterConnectSave');
            }

            // Always save the attributes because they may contain authorization information.
            if ($Attributes = $this->Form->getFormValue('Attributes')) {
                $UserModel->saveAttribute($UserID, $Attributes);
            }

            // Sign the user in.
            Gdn::session()->start($UserID, true, (bool)$this->Form->getFormValue('RememberMe', true));
            Gdn::userModel()->fireEvent('AfterSignIn');

            // Send them on their way.
            $this->_setRedirect(Gdn::request()->get('display') === 'popup');

        // If a name of email has been provided
        } elseif ($this->Form->getFormValue('Name') || $this->Form->getFormValue('Email')) {
            // Decide how to handle our first time connecting.
            $NameUnique = c('Garden.Registration.NameUnique', true);
            $EmailUnique = c('Garden.Registration.EmailUnique', true);
            $AutoConnect = c('Garden.Registration.AutoConnect');

            // Decide which name to search for.
            if ($IsPostBack && $this->Form->getFormValue('ConnectName')) {
                $searchName = $this->Form->getFormValue('ConnectName');
            } else {
                $searchName = $this->Form->getFormValue('Name');
            }

            // Find existing users that match the name or email of the connection.
            // First, discover if we have search criteria.
            $Search = false;
            $ExistingUsers = [];
            if ($searchName && $NameUnique) {
                $UserModel->SQL->orWhere('Name', $searchName);
                $Search = true;
            }
            if ($this->Form->getFormValue('Email') && ($EmailUnique || $AutoConnect)) {
                $UserModel->SQL->orWhere('Email', $this->Form->getFormValue('Email'));
                $Search = true;
            }
            if (is_numeric($UserSelect)) {
                $UserModel->SQL->orWhere('UserID', $UserSelect);
                $Search = true;
            }
            // Now do the search if we found some criteria.
            if ($Search) {
                $ExistingUsers = $UserModel->getWhere()->resultArray();
            }

            // Get the email and decide if we can safely find a match.
            $submittedEmail = $this->Form->getFormValue('Email');
            $canMatchEmail = (strlen($submittedEmail) > 0) && !UserModel::noEmail();

            // Check to automatically link the user.
            if ($AutoConnect && count($ExistingUsers) > 0) {
                if ($IsPostBack && $this->Form->getFormValue('ConnectName')) {
                    $this->Form->setFormValue('Name', $this->Form->getFormValue('ConnectName'));
                }

                if ($canMatchEmail) {
                    // Check each existing user for an exact email match.
                    foreach ($ExistingUsers as $Row) {
                        if (strcasecmp($submittedEmail, $Row['Email']) === 0) {
                            // Add the UserID to the form, then get the unified user data set from it.
                            $UserID = $Row['UserID'];
                            $this->Form->setFormValue('UserID', $UserID);
                            $Data = $this->Form->formValues();

                            // User synchronization.
                            if (c('Garden.Registration.ConnectSynchronize', true)) {
                                // Don't overwrite a photo if the user has already uploaded one.
                                $Photo = val('Photo', $Row);
                                if (!val('Photo', $Data) || ($Photo && !stringBeginsWith($Photo, 'http'))) {
                                    unset($Data['Photo']);
                                }

                                // Update the user.
                                $UserModel->save($Data, ['NoConfirmEmail' => true, 'FixUnique' => true, 'SaveRoles' => $SaveRoles]);
                                $this->EventArguments['UserID'] = $UserID;
                                $this->fireEvent('AfterConnectSave');
                            }

                            // Always save the attributes because they may contain authorization information.
                            if ($Attributes = $this->Form->getFormValue('Attributes')) {
                                $UserModel->saveAttribute($UserID, $Attributes);
                            }

                            // Save the user authentication association.
                            $UserModel->saveAuthentication([
                                'UserID' => $UserID,
                                'Provider' => $this->Form->getFormValue('Provider'),
                                'UniqueID' => $this->Form->getFormValue('UniqueID')
                            ]);

                            // Sign the user in.
                            Gdn::session()->start($UserID, true, (bool)$this->Form->getFormValue('RememberMe', true));
                            Gdn::userModel()->fireEvent('AfterSignIn');
                            $this->_setRedirect(Gdn::request()->get('display') === 'popup');
                            $this->render();
                            return;
                        }
                    }
                }
            } // Did not autoconnect!
            // Explore alternatives for a first-time connection.

            // This will be zero for a guest.
            $CurrentUserID = Gdn::session()->UserID;

            // Evaluate the existing users for matches.
            foreach ($ExistingUsers as $Index => $UserRow) {
                if ($EmailUnique && $canMatchEmail && $UserRow['Email'] == $submittedEmail) {
                    // An email match overrules any other options.
                    $EmailFound = $UserRow;
                    break;
                }

                // Detect a simple name match.
                if ($UserRow['Name'] == $this->Form->getFormValue('Name')) {
                    $NameFound = $UserRow;
                }

                // Detect if we have a match on the current user session.
                if ($CurrentUserID > 0 && $UserRow['UserID'] == $CurrentUserID) {
                    unset($ExistingUsers[$Index]);
                    $CurrentUserFound = true;
                }
            }

            // Handle special cases for what we matched on.
            if (isset($EmailFound)) {
                // The email address was found and can be the only user option.
                $ExistingUsers = [$UserRow];
                $this->setData('NoConnectName', true);
            } elseif (isset($CurrentUserFound)) {
                // If we're already logged in to Vanilla, assume that's an option we want.
                $ExistingUsers = array_merge(
                    ['UserID' => 'current', 'Name' => sprintf(t('%s (Current)'), Gdn::session()->User->Name)],
                    $ExistingUsers
                );
            }

            // Pre-populate our ConnectName field with the passed name if we couldn't match it.
            if (!isset($NameFound) && !$IsPostBack) {
                $this->Form->setFormValue('ConnectName', $this->Form->getFormValue('Name'));
            }

            // Block connecting to an existing user if it's disallowed.
            if (!$allowConnect) {
                // Make sure photo of existing user doesn't show on the form.
                $this->Form->setFormValue("Photo", null);
                // Ignore existing users found.
                $ExistingUsers = [];
            }

            // Set our final answer on matched users.
            $this->setData('ExistingUsers', $ExistingUsers);

            // Validate our email address if we have one.
            if (UserModel::noEmail()) {
                $emailValid = true;
            } else {
                $emailValid = validateRequired($this->Form->getFormValue('Email'));
            }

            // Set some nice variable names for logic clarity.
            $noMatches = (!is_array($ExistingUsers) || count($ExistingUsers) == 0);
            $didNotPickUser = (!$UserSelect || $UserSelect == 'other');
            $haveName = $this->Form->getFormValue('Name');

            // Should we create a new user?
            if ($didNotPickUser && $haveName && $emailValid && $noMatches) {
                // Create the user.
                $User = $this->Form->formValues();
                $User['Password'] = randomString(16); // Required field.
                $User['HashMethod'] = 'Random';
                $User['Source'] = $this->Form->getFormValue('Provider');
                $User['SourceID'] = $this->Form->getFormValue('UniqueID');
                $User['Attributes'] = $this->Form->getFormValue('Attributes', null);
                $User['Email'] = $this->Form->getFormValue('ConnectEmail', $this->Form->getFormValue('Email', null));
                $User['Name'] = $this->Form->getFormValue('ConnectName', $this->Form->getFormValue('Name', null));
                $UserID = $UserModel->register($User, [
                    'CheckCaptcha' => false,
                    'ValidateEmail' => false,
                    'NoConfirmEmail' => true,
                    'SaveRoles' => $SaveRolesRegister
                ]);
                $User['UserID'] = $UserID;

                $this->EventArguments['UserID'] = $UserID;
                $this->fireEvent('AfterConnectSave');

                $this->Form->setValidationResults($UserModel->validationResults());

                // Save the association to the new user.
                if ($UserID) {
                    $UserModel->saveAuthentication([
                        'UserID' => $UserID,
                        'Provider' => $this->Form->getFormValue('Provider'),
                        'UniqueID' => $this->Form->getFormValue('UniqueID')
                    ]);

                    $this->Form->setFormValue('UserID', $UserID);
                    $this->Form->setFormValue('UserSelect', false);

                    // Sign in as the new user.
                    Gdn::session()->start($UserID, true, (bool)$this->Form->getFormValue('RememberMe', true));
                    Gdn::userModel()->fireEvent('AfterSignIn');

                    // Send the welcome email.
                    if (c('Garden.Registration.SendConnectEmail', false)) {
                        try {
                            $providerName = $this->Form->getFormValue('ProviderName', $this->Form->getFormValue('Provider', 'Unknown'));
                            $UserModel->sendWelcomeEmail($UserID, '', 'Connect', ['ProviderName' => $providerName]);
                        } catch (Exception $Ex) {
                            // Do nothing if emailing doesn't work.
                        }
                    }

                    // Move along.
                    $this->_setRedirect(Gdn::request()->get('display') === 'popup');
                }
            }
        } // Finished our connection logic.

        // Save the user's choice.
        if ($IsPostBack) {
            $PasswordHash = new Gdn_PasswordHash();

            if (!$UserSelect || $UserSelect == 'other') {
                // The user entered a username. Validate it.
                $ConnectNameEntered = true;
                if ($this->Form->validateRule('ConnectName', 'ValidateRequired')) {
                    $ConnectName = $this->Form->getFormValue('ConnectName');
                    $User = false;

                    if (c('Garden.Registration.NameUnique')) {
                        // Check to see if there is already a user with the given name.
                        $User = $UserModel->getWhere(array('Name' => $ConnectName))->firstRow(DATASET_TYPE_ARRAY);
                    }

                    if (!$User) {
                        // Using a new username, so validate it.
                        $this->Form->validateRule('ConnectName', 'ValidateUsername');
                    }
                }
            } else {
                // The user selected an existing user.
                $ConnectNameEntered = false;
                if ($UserSelect == 'current') {
                    if (Gdn::session()->UserID == 0) {
                        // This should never happen, but a user could click submit on a stale form.
                        $this->Form->addError('@You were unexpectedly signed out.');
                    } else {
                        $UserSelect = Gdn::session()->UserID;
                    }
                }
                $User = $UserModel->getID($UserSelect, DATASET_TYPE_ARRAY);
            } // End user selection.

            if (isset($User) && $User) {
                // Make sure the user authenticates.
                if (!$User['UserID'] == Gdn::session()->UserID && $allowConnect) {
                    $hasPassword = $this->Form->validateRule('ConnectPassword', 'ValidateRequired', sprintf(t('ValidateRequired'), t('Password')));
                    if ($hasPassword) {
                        // Validate their password.
                        try {
                            $password = $this->Form->getFormValue('ConnectPassword');
                            $name = $this->Form->getFormValue('ConnectName');
                            if (!$PasswordHash->checkPassword($password, $User['Password'], $User['HashMethod'], $name)) {
                                if ($ConnectNameEntered) {
                                    $this->Form->addError('The username you entered has already been taken.');
                                } else {
                                    $this->Form->addError('The password you entered is incorrect.');
                                }
                            }
                        } catch (Gdn_UserException $Ex) {
                            $this->Form->addError($Ex);
                        }
                    }
                }
            } elseif ($this->Form->errorCount() == 0) {
                // The user doesn't exist so we need to add another user.
                $User = $this->Form->formValues();
                $User['Name'] = $User['ConnectName'];
                $User['Password'] = randomString(16); // Required field.
                $User['HashMethod'] = 'Random';
                $UserID = $UserModel->register($User, [
                    'CheckCaptcha' => false,
                    'NoConfirmEmail' => true,
                    'SaveRoles' => $SaveRolesRegister
                ]);
                $User['UserID'] = $UserID;

                $this->EventArguments['UserID'] = $UserID;
                $this->fireEvent('AfterConnectSave');

                $this->Form->setValidationResults($UserModel->validationResults());

                // Send the welcome email.
                if ($UserID && c('Garden.Registration.SendConnectEmail', false)) {
                    $providerName = $this->Form->getFormValue('ProviderName', $this->Form->getFormValue('Provider', 'Unknown'));
                    $UserModel->sendWelcomeEmail($UserID, '', 'Connect', ['ProviderName' => $providerName]);
                }
            }

            // Save the user authentication association.
            if ($this->Form->errorCount() == 0) {
                if (isset($User) && val('UserID', $User)) {
                    $UserModel->saveAuthentication([
                        'UserID' => $User['UserID'],
                        'Provider' => $this->Form->getFormValue('Provider'),
                        'UniqueID' => $this->Form->getFormValue('UniqueID')
                    ]);
                    $this->Form->setFormValue('UserID', $User['UserID']);
                }

                // Sign the user in.
                Gdn::session()->start($this->Form->getFormValue('UserID'), true, (bool)$this->Form->getFormValue('RememberMe', true));
                Gdn::userModel()->fireEvent('AfterSignIn');

                // Move along.
                $this->_setRedirect(Gdn::request()->get('display') === 'popup');
            }
        } // End of user choice processing.

        $this->render();
    }

    /**
     * After sign in, send them along.
     *
     * @since 2.0.0
     * @access protected
     *
     * @param bool $CheckPopup
     */
    protected function _setRedirect($CheckPopup = false) {
        $Url = url($this->redirectTo(), true);

        $this->RedirectUrl = $Url;
        $this->MasterView = 'popup';
        $this->View = 'redirect';

        if ($this->_RealDeliveryType != DELIVERY_TYPE_ALL && $this->deliveryType() != DELIVERY_TYPE_ALL) {
            $this->deliveryMethod(DELIVERY_METHOD_JSON);
            $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        } elseif ($CheckPopup || $this->data('CheckPopup')) {
            $this->addDefinition('CheckPopup', true);
        } else {
            safeRedirect(url($this->RedirectUrl));
        }
    }

    /**
     * Default to SignIn().
     *
     * @access public
     * @since 2.0.0
     */
    public function index() {
        $this->View = 'SignIn';
        $this->signIn();
    }

    /**
     * Auth via password.
     *
     * @access public
     * @since 2.0.0
     */
    public function password() {
        $this->auth('password');
    }

    /**
     * Auth via default method. Simpler, old version of SignIn().
     *
     * Events: SignIn
     *
     * @access public
     * @return void
     */
    public function signIn2() {
        $this->fireEvent("SignIn");
        $this->auth('default');
    }

    /**
     * Good afternoon, good evening, and goodnight.
     *
     * Events: SignOut
     *
     * @access public
     * @since 2.0.0
     *
     * @param string $TransientKey (default: "")
     */
    public function signOut($TransientKey = "", $Override = "0") {
        $this->checkOverride('SignOut', $this->target(), $TransientKey);

        if (Gdn::session()->validateTransientKey($TransientKey) || $this->Form->isPostBack()) {
            $User = Gdn::session()->User;

            $this->EventArguments['SignoutUser'] = $User;
            $this->fireEvent("BeforeSignOut");

            // Sign the user right out.
            Gdn::session()->End();
            $this->setData('SignedOut', true);

            $this->EventArguments['SignoutUser'] = $User;
            $this->fireEvent("SignOut");

            $this->_setRedirect();
        } elseif (!Gdn::session()->isValid())
            $this->_setRedirect();

        $this->setData('Override', $Override);
        $this->setData('Target', $this->target());
        $this->Leaving = false;
        $this->render();
    }

    /**
     * Signin process that multiple authentication methods.
     *
     * @access public
     * @since 2.0.0
     * @author Tim Gunter
     *
     * @param string $Method
     * @param array $Arg1
     * @return string Rendered XHTML template.
     */
    public function signIn($Method = false, $Arg1 = false) {
        if (!$this->Request->isPostBack()) {
            $this->checkOverride('SignIn', $this->target());
        }

        Gdn::session()->ensureTransientKey();

        $this->addJsFile('entry.js');
        $this->setData('Title', t('Sign In'));
        $this->Form->addHidden('Target', $this->target());
        $this->Form->addHidden('ClientHour', date('Y-m-d H:00')); // Use the server's current hour as a default.

        // Additional signin methods are set up with plugins.
        $Methods = array();

        $this->setData('Methods', $Methods);
        $this->setData('FormUrl', url('entry/signin'));

        $this->fireEvent('SignIn');

        if ($this->Form->isPostBack()) {
            $this->Form->validateRule('Email', 'ValidateRequired', sprintf(t('%s is required.'), t(UserModel::signinLabelCode())));
            $this->Form->validateRule('Password', 'ValidateRequired');

            if (!$this->Request->isAuthenticatedPostBack() && !c('Garden.Embed.Allow')) {
                $this->Form->addError('Please try again.');
            }

            // Check the user.
            if ($this->Form->errorCount() == 0) {
                $Email = $this->Form->getFormValue('Email');
                $User = Gdn::userModel()->GetByEmail($Email);
                if (!$User) {
                    $User = Gdn::userModel()->GetByUsername($Email);
                }

                if (!$User) {
                    $this->Form->addError('@'.sprintf(t('User not found.'), strtolower(t(UserModel::SigninLabelCode()))));
                    Logger::event('signin_failure', Logger::INFO, '{signin} failed to sign in. User not found.', array('signin' => $Email));
                    $this->fireEvent('BadSignIn', [
                        'Email' => $Email,
                        'Password' => $this->Form->getFormValue('Password'),
                        'Reason' => 'NotFound'
                    ]);
                } else {
                    // Check the password.
                    $PasswordHash = new Gdn_PasswordHash();
                    $Password = $this->Form->getFormValue('Password');
                    try {
                        $PasswordChecked = $PasswordHash->checkPassword($Password, val('Password', $User), val('HashMethod', $User));

                        // Rate limiting
                        Gdn::userModel()->rateLimit($User, $PasswordChecked);

                        if ($PasswordChecked) {
                            // Update weak passwords
                            $HashMethod = val('HashMethod', $User);
                            if ($PasswordHash->Weak || ($HashMethod && strcasecmp($HashMethod, 'Vanilla') != 0)) {
                                $Pw = $PasswordHash->hashPassword($Password);
                                Gdn::userModel()->setField(val('UserID', $User), array('Password' => $Pw, 'HashMethod' => 'Vanilla'));
                            }

                            Gdn::session()->start(val('UserID', $User), true, (bool)$this->Form->getFormValue('RememberMe'));
                            if (!Gdn::session()->checkPermission('Garden.SignIn.Allow')) {
                                $this->Form->addError('ErrorPermission');
                                Gdn::session()->end();
                            } else {
                                $ClientHour = $this->Form->getFormValue('ClientHour');
                                $HourOffset = Gdn::session()->User->HourOffset;
                                if (is_numeric($ClientHour) && $ClientHour >= 0 && $ClientHour < 24) {
                                    $HourOffset = $ClientHour - date('G', time());
                                }

                                if ($HourOffset != Gdn::session()->User->HourOffset) {
                                    Gdn::userModel()->setProperty(Gdn::session()->UserID, 'HourOffset', $HourOffset);
                                }

                                Gdn::userModel()->fireEvent('AfterSignIn');

                                $this->_setRedirect();
                            }
                        } else {
                            $this->Form->addError('Invalid password.');
                            Logger::event(
                                'signin_failure',
                                Logger::WARNING,
                                '{username} failed to sign in.  Invalid password.',
                                array('InsertName' => $User->Name)
                            );
                            $this->fireEvent('BadSignIn', [
                                'Email' => $Email,
                                'Password' => $Password,
                                'User' => $User,
                                'Reason' => 'Password'
                            ]);
                        }
                    } catch (Gdn_UserException $Ex) {
                        $this->Form->addError($Ex);
                    }
                }
            }

        } else {
            if ($Target = $this->Request->get('Target')) {
                $this->Form->addHidden('Target', $Target);
            }
            $this->Form->setValue('RememberMe', true);
        }

        return $this->render();
    }

    /**
     * Create secure handshake with remote authenticator.
     *
     * @access public
     * @since 2.0.?
     * @author Tim Gunter
     *
     * @param string $AuthenticationSchemeAlias (default: 'default')
     */
    public function handshake($AuthenticationSchemeAlias = 'default') {

        try {
            // Don't show anything if handshaking not turned on by an authenticator
            if (!Gdn::authenticator()->canHandshake()) {
                throw new Exception();
            }

            // Try to load the authenticator
            $Authenticator = Gdn::authenticator()->authenticateWith($AuthenticationSchemeAlias);

            // Try to grab the authenticator data
            $Payload = $Authenticator->getHandshake();
            if ($Payload === false) {
                Gdn::request()->withURI('dashboard/entry/auth/password');
                return Gdn::dispatcher()->dispatch();
            }
        } catch (Exception $e) {
            Gdn::request()->WithURI('/entry/signin');
            return Gdn::dispatcher()->dispatch();
        }

        $UserInfo = array(
            'UserKey' => $Authenticator->GetUserKeyFromHandshake($Payload),
            'ConsumerKey' => $Authenticator->GetProviderKeyFromHandshake($Payload),
            'TokenKey' => $Authenticator->GetTokenKeyFromHandshake($Payload),
            'UserName' => $Authenticator->GetUserNameFromHandshake($Payload),
            'UserEmail' => $Authenticator->GetUserEmailFromHandshake($Payload)
        );

        if (method_exists($Authenticator, 'GetRolesFromHandshake')) {
            $RemoteRoles = $Authenticator->GetRolesFromHandshake($Payload);
            if (!empty($RemoteRoles)) {
                $UserInfo['Roles'] = $RemoteRoles;
            }
        }

        // Manual user sync is disabled. No hand holding will occur for users.
        $SyncScreen = c('Garden.Authenticator.SyncScreen', 'on');
        switch ($SyncScreen) {
            case 'on':

                // Authenticator events fired inside
                $this->syncScreen($Authenticator, $UserInfo, $Payload);

                break;

            case 'off':
            case 'smart':
                $UserID = $this->UserModel->synchronize($UserInfo['UserKey'], array(
                    'Name' => $UserInfo['UserName'],
                    'Email' => $UserInfo['UserEmail'],
                    'Roles' => val('Roles', $UserInfo)
                ));

                if ($UserID > 0) {
                    // Account created successfully.

                    // Finalize the link between the forum user and the foreign userkey
                    $Authenticator->finalize($UserInfo['UserKey'], $UserID, $UserInfo['ConsumerKey'], $UserInfo['TokenKey'], $Payload);

                    $UserEventData = array_merge(array(
                        'UserID' => $UserID,
                        'Payload' => $Payload
                    ), $UserInfo);
                    Gdn::authenticator()->trigger(Gdn_Authenticator::AUTH_CREATED, $UserEventData);

                    /// ... and redirect them appropriately
                    $Route = $this->redirectTo();
                    if ($Route !== false) {
                        redirect($Route);
                    } else {
                        redirect('/');
                    }

                } else {
                    // Account not created.
                    if ($SyncScreen == 'smart') {
                        $this->informMessage(t('There is already an account in this forum using your email address. Please create a new account, or enter the credentials for the existing account.'));
                        $this->syncScreen($Authenticator, $UserInfo, $Payload);

                    } else {
                        // Set the memory cookie to allow signinloopback to shortcircuit remote query.
                        $CookiePayload = array(
                            'Sync' => 'Failed'
                        );
                        $encodedCookiePayload = dbencode($CookiePayload);
                        $Authenticator->remember($UserInfo['ConsumerKey'], $encodedCookiePayload);

                        // This resets vanilla's internal "where am I" to the homepage. Needed.
                        Gdn::request()->withRoute('DefaultController');
                        $this->SelfUrl = url(''); //Gdn::request()->Path();

                        $this->View = 'syncfailed';
                        $this->ProviderSite = $Authenticator->getProviderUrl();
                        $this->render();
                    }

                }
                break;

        }
    }

    /**
     * Attempt to syncronize user data from remote system into Dashboard.
     *
     * @access public
     * @since 2.0.?
     * @author Tim Gunter
     *
     * @param object $Authenticator
     * @param array $UserInfo
     * @param array $Payload
     */
    public function syncScreen($Authenticator, $UserInfo, $Payload) {
        $this->addJsFile('entry.js');
        $this->View = 'handshake';
        $this->HandshakeScheme = $Authenticator->getAuthenticationSchemeAlias();
        $this->Form->setModel($this->UserModel);
        $this->Form->addHidden('ClientHour', date('Y-m-d H:00')); // Use the server's current hour as a default
        $this->Form->addHidden('Target', $this->target());

        $PreservedKeys = array(
            'UserKey', 'Token', 'Consumer', 'Email', 'Name', 'Gender', 'HourOffset'
        );
        $UserID = 0;
        $Target = $this->target();

        if ($this->Form->isPostBack() === true) {
            $FormValues = $this->Form->formValues();
            if (val('StopLinking', $FormValues)) {
                $AuthResponse = Gdn_Authenticator::AUTH_ABORTED;

                $UserEventData = array_merge(array(
                    'UserID' => $UserID,
                    'Payload' => $Payload
                ), $UserInfo);
                Gdn::authenticator()->trigger($AuthResponse, $UserEventData);

                $Authenticator->deleteCookie();
                Gdn::request()->withRoute('DefaultController');
                return Gdn::dispatcher()->dispatch();

            } elseif (val('NewAccount', $FormValues)) {
                $AuthResponse = Gdn_Authenticator::AUTH_CREATED;

                // Try and synchronize the user with the new username/email.
                $FormValues['Name'] = $FormValues['NewName'];
                $FormValues['Email'] = $FormValues['NewEmail'];
                $UserID = $this->UserModel->synchronize($UserInfo['UserKey'], $FormValues);
                $this->Form->setValidationResults($this->UserModel->validationResults());

            } else {
                $AuthResponse = Gdn_Authenticator::AUTH_SUCCESS;

                // Try and sign the user in.
                $PasswordAuthenticator = Gdn::authenticator()->authenticateWith('password');
                $PasswordAuthenticator->hookDataField('Email', 'SignInEmail');
                $PasswordAuthenticator->hookDataField('Password', 'SignInPassword');
                $PasswordAuthenticator->fetchData($this->Form);

                $UserID = $PasswordAuthenticator->authenticate();

                if ($UserID < 0) {
                    $this->Form->addError('ErrorPermission');
                } elseif ($UserID == 0) {
                    $this->Form->addError('ErrorCredentials');
                    Logger::event(
                        'signin_failure',
                        Logger::WARNING,
                        '{username} failed to sign in. Invalid credentials.'
                    );
                }

                if ($UserID > 0) {
                    $Data = $FormValues;
                    $Data['UserID'] = $UserID;
                    $Data['Email'] = val('SignInEmail', $FormValues, '');
                    $UserID = $this->UserModel->synchronize($UserInfo['UserKey'], $Data);
                }
            }

            if ($UserID > 0) {
                // The user has been created successfully, so sign in now

                // Finalize the link between the forum user and the foreign userkey
                $Authenticator->finalize($UserInfo['UserKey'], $UserID, $UserInfo['ConsumerKey'], $UserInfo['TokenKey'], $Payload);

                $UserEventData = array_merge(array(
                    'UserID' => $UserID,
                    'Payload' => $Payload
                ), $UserInfo);
                Gdn::authenticator()->trigger($AuthResponse, $UserEventData);

                /// ... and redirect them appropriately
                $Route = $this->redirectTo();
                if ($Route !== false) {
                    redirect($Route);
                }
            } else {
                // Add the hidden inputs back into the form.
                foreach ($FormValues as $Key => $Value) {
                    if (in_array($Key, $PreservedKeys)) {
                        $this->Form->addHidden($Key, $Value);
                    }
                }
            }
        } else {
            $Id = Gdn::authenticator()->getIdentity(true);
            if ($Id > 0) {
                // The user is signed in so we can just go back to the homepage.
                redirect($Target);
            }

            $Name = $UserInfo['UserName'];
            $Email = $UserInfo['UserEmail'];

            // Set the defaults for a new user.
            $this->Form->setFormValue('NewName', $Name);
            $this->Form->setFormValue('NewEmail', $Email);

            // Set the default for the login.
            $this->Form->setFormValue('SignInEmail', $Email);
            $this->Form->setFormValue('Handshake', 'NEW');

            // Add the handshake data as hidden fields.
            $this->Form->addHidden('Name', $Name);
            $this->Form->addHidden('Email', $Email);
            $this->Form->addHidden('UserKey', $UserInfo['UserKey']);
            $this->Form->addHidden('Token', $UserInfo['TokenKey']);
            $this->Form->addHidden('Consumer', $UserInfo['ConsumerKey']);
        }

        $this->setData('Name', val('Name', $this->Form->HiddenInputs));
        $this->setData('Email', val('Email', $this->Form->HiddenInputs));

        $this->render();
    }

    /**
     * Calls the appropriate registration method based on the configuration setting.
     *
     * Events: Register
     *
     * @access public
     * @since 2.0.0
     *
     * @param string $InvitationCode Unique code given to invited user.
     */
    public function register($InvitationCode = '') {
        if (!$this->Request->isPostBack()) {
            $this->checkOverride('Register', $this->target());
        }

        $this->fireEvent("Register");

        $this->Form->setModel($this->UserModel);

        // Define gender dropdown options
        $this->GenderOptions = array(
            'u' => t('Unspecified'),
            'm' => t('Male'),
            'f' => t('Female')
        );

        // Make sure that the hour offset for new users gets defined when their account is created
        $this->addJsFile('entry.js');

        $this->Form->addHidden('ClientHour', date('Y-m-d H:00')); // Use the server's current hour as a default
        $this->Form->addHidden('Target', $this->target());

        $this->setData('NoEmail', UserModel::noEmail());

        // Sub-dispatch to a specific handler for each registration method
        $registrationHandler = $this->getRegistrationhandler();
        $this->setData('Method', stringBeginsWith($registrationHandler, 'register', true, true));
        $this->$registrationHandler($InvitationCode);
    }

    /**
     * Select view/method to be used for registration (from config).
     *
     * @access protected
     * @since 2.3
     * @return string Method name to invoke for registration
     */
    protected function getRegistrationhandler() {
        $registrationMethod = Gdn::config('Garden.Registration.Method');
        if (!in_array($registrationMethod, array('Closed', 'Basic', 'Captcha', 'Approval', 'Invitation', 'Connect'))) {
            $registrationMethod = 'Basic';
        }

        // We no longer support captcha-less registration, both Basic and Captcha require a captcha
        if ($registrationMethod == 'Captcha') {
            $registrationMethod = 'Basic';
        }

        return "register{$registrationMethod}";
    }

    /**
     * Alias of EntryController::getRegistrationHandler
     *
     * @deprecated since 2.3
     * @return string
     */
    protected function _registrationView() {
        return $this->getRegistrationHandler();
    }

    /**
     * Registration that requires approval.
     *
     * Events: RegistrationPending
     *
     * @access private
     * @since 2.0.0
     */
    private function registerApproval() {
        $this->View = 'registerapproval';
        Gdn::userModel()->addPasswordStrength($this);

        // If the form has been posted back...
        if ($this->Form->isPostBack()) {
            // Add validation rules that are not enforced by the model
            $this->UserModel->defineSchema();
            $this->UserModel->Validation->applyRule('Name', 'Username', $this->UsernameError);
            $this->UserModel->Validation->applyRule('TermsOfService', 'Required', t('You must agree to the terms of service.'));
            $this->UserModel->Validation->applyRule('Password', 'Required');
            $this->UserModel->Validation->applyRule('Password', 'Strength');
            $this->UserModel->Validation->applyRule('Password', 'Match');
            $this->UserModel->Validation->applyRule('DiscoveryText', 'Required', 'Tell us why you want to join!');
            // $this->UserModel->Validation->applyRule('DateOfBirth', 'MinimumAge');

            $this->fireEvent('RegisterValidation');

            try {
                $Values = $this->Form->formValues();
                $Values = $this->UserModel->filterForm($Values, true);
                unset($Values['Roles']);
                $AuthUserID = $this->UserModel->register($Values);
                $this->setData('UserID', $AuthUserID);
                if (!$AuthUserID) {
                    $this->Form->setValidationResults($this->UserModel->validationResults());
                } else {
                    // The user has been created successfully, so sign in now.
                    Gdn::session()->start($AuthUserID);

                    if ($this->Form->getFormValue('RememberMe')) {
                        Gdn::authenticator()->SetIdentity($AuthUserID, true);
                    }

                    // Notification text
                    $Label = t('NewApplicantEmail', 'New applicant:');
                    $Story = anchor(Gdn_Format::text($Label.' '.$Values['Name']), ExternalUrl('dashboard/user/applicants'));

                    $this->EventArguments['AuthUserID'] = $AuthUserID;
                    $this->EventArguments['Story'] = &$Story;
                    $this->fireEvent('RegistrationPending');
                    $this->View = "RegisterThanks"; // Tell the user their application will be reviewed by an administrator.

                    // Grab all of the users that need to be notified.
                    $Data = Gdn::database()->sql()->getWhere('UserMeta', array('Name' => 'Preferences.Email.Applicant'))->resultArray();
                    $ActivityModel = new ActivityModel();
                    foreach ($Data as $Row) {
                        $ActivityModel->add($AuthUserID, 'Applicant', $Story, $Row['UserID'], '', '/dashboard/user/applicants', 'Only');
                    }

                    if ($this->deliveryType() !== DELIVERY_TYPE_ALL) {
                        $this->RedirectUrl = url('/entry/registerthanks');
                    }
                }
            } catch (Exception $Ex) {
                $this->Form->addError($Ex);
            }
            $this->render();
        } else {
            $this->render();
        }
    }

    /**
     * Captcha-authenticated registration. Used by default.
     *
     * Allows immediate access upon successful registration, and optionally requires
     * email address confirmation.
     *
     * Events: RegistrationSuccessful
     *
     * @access private
     * @since 2.0.0
     */
    private function registerBasic() {
        $this->View = 'registerbasic';
        Gdn::userModel()->addPasswordStrength($this);

        if ($this->Form->isPostBack() === true) {
            // Add validation rules that are not enforced by the model
            $this->UserModel->defineSchema();
            $this->UserModel->Validation->applyRule('Name', 'Username', $this->UsernameError);
            $this->UserModel->Validation->applyRule('TermsOfService', 'Required', t('You must agree to the terms of service.'));
            $this->UserModel->Validation->applyRule('Password', 'Required');
            $this->UserModel->Validation->applyRule('Password', 'Strength');
            $this->UserModel->Validation->applyRule('Password', 'Match');
            // $this->UserModel->Validation->applyRule('DateOfBirth', 'MinimumAge');

            $this->fireEvent('RegisterValidation');

            try {
                $Values = $this->Form->formValues();
                $Values = $this->UserModel->filterForm($Values, true);
                unset($Values['Roles']);
                $AuthUserID = $this->UserModel->register($Values);
                $this->setData('UserID', $AuthUserID);
                if ($AuthUserID == UserModel::REDIRECT_APPROVE) {
                    $this->Form->setFormValue('Target', '/entry/registerthanks');
                    $this->_setRedirect();
                    return;
                } elseif (!$AuthUserID) {
                    $this->Form->setValidationResults($this->UserModel->validationResults());
                } else {
                    // The user has been created successfully, so sign in now.
                    Gdn::session()->start($AuthUserID);

                    if ($this->Form->getFormValue('RememberMe')) {
                        Gdn::authenticator()->setIdentity($AuthUserID, true);
                    }

                    try {
                        $this->UserModel->sendWelcomeEmail($AuthUserID, '', 'Register');
                    } catch (Exception $Ex) {
                    }

                    $this->fireEvent('RegistrationSuccessful');

                    // ... and redirect them appropriately
                    $Route = $this->redirectTo();
                    if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
                        $this->RedirectUrl = url($Route);
                    } else {
                        if ($Route !== false) {
                            redirect($Route);
                        }
                    }
                }
            } catch (Exception $Ex) {
                $this->Form->addError($Ex);
            }
        }
        $this->render();
    }

    /**
     * Captcha-authenticated registration.
     *
     * Deprecated in favor of 'basic' registration which now also requires
     * captcha authentication.
     *
     * Events: RegistrationSuccessful
     *
     * @deprecated since v2.2.106
     * @see registerBasic
     * @access private
     * @since 2.0.0
     */
    private function registerCaptcha() {
        $this->registerBasic();
    }

    /**
     * Connect registration
     *
     * @deprecated since 2.0.18.
     */
    private function registerConnect() {
        throw notFoundException();
    }

    /**
     * Registration not allowed.
     *
     * @access private
     * @since 2.0.0
     */
    private function registerClosed() {
        $this->View = 'registerclosed';
        $this->render();
    }

    /**
     * Invitation-only registration. Requires code.
     *
     * @param int $InvitationCode
     * @since 2.0.0
     */
    public function registerInvitation($InvitationCode = 0) {
        $this->View = 'registerinvitation';
        $this->Form->setModel($this->UserModel);

        // Define gender dropdown options
        $this->GenderOptions = array(
            'u' => t('Unspecified'),
            'm' => t('Male'),
            'f' => t('Female')
        );

        if (!$this->Form->isPostBack()) {
            $this->Form->setValue('InvitationCode', $InvitationCode);
        }

        $InvitationModel = new InvitationModel();

        // Look for the invitation.
        $Invitation = $InvitationModel
            ->getWhere(array('Code' => $this->Form->getValue('InvitationCode')))
            ->firstRow(DATASET_TYPE_ARRAY);

        if (!$Invitation) {
            $this->Form->addError('Invitation not found.', 'Code');
        } else {
            if ($Expires = val('DateExpires', $Invitation)) {
                $Expires = Gdn_Format::toTimestamp($Expires);
                if ($Expires <= time()) {
                }
            }
        }

        $this->Form->addHidden('ClientHour', date('Y-m-d H:00')); // Use the server's current hour as a default
        $this->Form->addHidden('Target', $this->target());

        Gdn::userModel()->addPasswordStrength($this);

        if ($this->Form->isPostBack() === true) {
            $this->InvitationCode = $this->Form->getValue('InvitationCode');
            // Add validation rules that are not enforced by the model
            $this->UserModel->defineSchema();
            $this->UserModel->Validation->applyRule('Name', 'Username', $this->UsernameError);
            $this->UserModel->Validation->applyRule('TermsOfService', 'Required', t('You must agree to the terms of service.'));
            $this->UserModel->Validation->applyRule('Password', 'Required');
            $this->UserModel->Validation->applyRule('Password', 'Strength');
            $this->UserModel->Validation->applyRule('Password', 'Match');
            // $this->UserModel->Validation->applyRule('DateOfBirth', 'MinimumAge');

            $this->fireEvent('RegisterValidation');

            try {
                $Values = $this->Form->formValues();
                $Values = $this->UserModel->filterForm($Values, true);
                unset($Values['Roles']);
                $AuthUserID = $this->UserModel->register($Values, array('Method' => 'Invitation'));
                $this->setData('UserID', $AuthUserID);
                if (!$AuthUserID) {
                    $this->Form->setValidationResults($this->UserModel->validationResults());
                } else {
                    // The user has been created successfully, so sign in now.
                    Gdn::session()->start($AuthUserID);
                    if ($this->Form->getFormValue('RememberMe')) {
                        Gdn::authenticator()->setIdentity($AuthUserID, true);
                    }

                    $this->fireEvent('RegistrationSuccessful');

                    // ... and redirect them appropriately
                    $Route = $this->redirectTo();
                    if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
                        $this->RedirectUrl = url($Route);
                    } else {
                        if ($Route !== false) {
                            redirect($Route);
                        }
                    }
                }
            } catch (Exception $Ex) {
                $this->Form->addError($Ex);
            }
        } else {
            // Set some form defaults.
            if ($Name = val('Name', $Invitation)) {
                $this->Form->setValue('Name', $Name);
            }

            $this->InvitationCode = $InvitationCode;
        }

        // Make sure that the hour offset for new users gets defined when their account is created
        $this->addJsFile('entry.js');
        $this->render();
    }

    /**
     * Display registration thank-you message
     *
     * @since 2.1
     */
    public function registerThanks() {
        $this->CssClass = 'SplashMessage NoPanel';
        $this->setData('_NoMessages', true);
        $this->setData('Title', t('Thank You!'));
        $this->render();
    }

    /**
     * Request password reset.
     *
     * @access public
     * @since 2.0.0
     */
    public function passwordRequest() {
        Gdn::locale()->setTranslation('Email', t(UserModel::signinLabelCode()));
        if ($this->Form->isPostBack() === true) {
            $this->Form->validateRule('Email', 'ValidateRequired');

            if ($this->Form->errorCount() == 0) {
                try {
                    $Email = $this->Form->getFormValue('Email');
                    if (!$this->UserModel->passwordRequest($Email)) {
                        $this->Form->setValidationResults($this->UserModel->validationResults());
                        Logger::event(
                            'password_reset_failure',
                            Logger::INFO,
                            'Can\'t find account associated with email/username {Input}.',
                            array('Input' => $Email)
                        );
                    }
                } catch (Exception $ex) {
                    $this->Form->addError($ex->getMessage());
                }
                if ($this->Form->errorCount() == 0) {
                    $this->Form->addError('Success!');
                    $this->View = 'passwordrequestsent';
                    Logger::event(
                        'password_reset_request',
                        Logger::INFO,
                        '{Input} has been sent a password reset email.',
                        array('Input' => $Email)
                    );
                    $this->fireEvent('PasswordRequest', [
                        'Email' => $Email
                    ]);
                }
            } else {
                if ($this->Form->errorCount() == 0) {
                    $this->Form->addError("Couldn't find an account associated with that email/username.");
                    Logger::event(
                        'password_reset_failure',
                        Logger::INFO,
                        'Can\'t find account associated with email/username {Input}.',
                        array('Input' => $this->Form->getValue('Email'))
                    );
                }
            }
        }
        $this->render();
    }

    /**
     * Do password reset.
     *
     * @access public
     * @since 2.0.0
     *
     * @param int $UserID Unique.
     * @param string $PasswordResetKey Authenticate with unique, 1-time code sent via email.
     */
    public function passwordReset($UserID = '', $PasswordResetKey = '') {
        $PasswordResetKey = trim($PasswordResetKey);

        if (!is_numeric($UserID)
            || $PasswordResetKey == ''
            || $this->UserModel->getAttribute($UserID, 'PasswordResetKey', '') != $PasswordResetKey
        ) {
            $this->Form->addError('Failed to authenticate your password reset request. Try using the reset request form again.');
            Logger::event(
                'password_reset_failure',
                Logger::NOTICE,
                '{username} failed to authenticate password reset request.'
            );
            $this->fireEvent('PasswordResetFailed', [
                'UserID' => $UserID
            ]);
        }

        $Expires = $this->UserModel->getAttribute($UserID, 'PasswordResetExpires');
        if ($this->Form->errorCount() === 0 && $Expires < time()) {
            $this->Form->addError('@'.t('Your password reset token has expired.', 'Your password reset token has expired. Try using the reset request form again.'));
            Logger::event(
                'password_reset_failure',
                Logger::NOTICE,
                '{username} has an expired reset token.'
            );
            $this->fireEvent('PasswordResetFailed', [
                'UserID' => $UserID
            ]);
        }

        if ($this->Form->errorCount() == 0) {
            $User = $this->UserModel->getID($UserID, DATASET_TYPE_ARRAY);
            if ($User) {
                $User = arrayTranslate($User, array('UserID', 'Name', 'Email'));
                $this->setData('User', $User);
            }
        } else {
            $this->setData('Fatal', true);
        }

        if ($this->Form->errorCount() == 0
            && $this->Form->isPostBack() === true
        ) {
            $Password = $this->Form->getFormValue('Password', '');
            $Confirm = $this->Form->getFormValue('Confirm', '');
            if ($Password == '') {
                $this->Form->addError('Your new password is invalid');
                Logger::event(
                    'password_reset_failure',
                    Logger::NOTICE,
                    'Failed to reset the password for {username}. Password is invalid.'
                );
            } elseif ($Password != $Confirm) {
                $this->Form->addError('Your passwords did not match.');
            }
            Logger::event(
                'password_reset_failure',
                Logger::NOTICE,
                'Failed to reset the password for {username}. Passwords did not match.'
            );

            if ($this->Form->errorCount() == 0) {
                $User = $this->UserModel->passwordReset($UserID, $Password);
                Logger::event(
                    'password_reset',
                    Logger::NOTICE,
                    '{username} has reset their password.'
                );
                Gdn::session()->start($User->UserID, true);
//            $Authenticator = Gdn::authenticator()->AuthenticateWith('password');
//            $Authenticator->FetchData($Authenticator, array('Email' => $User->Email, 'Password' => $Password, 'RememberMe' => FALSE));
//            $AuthUserID = $Authenticator->Authenticate();
                redirect('/');
            }
        }
        $this->render();
    }

    /**
     * Confirm email address is valid via sent code.
     *
     * @access public
     * @since 2.0.0
     *
     * @param int $UserID
     * @param string $EmailKey Authenticate with unique, 1-time code sent via email.
     */
    public function emailConfirm($UserID, $EmailKey = '') {
        $User = $this->UserModel->getID($UserID);

        if (!$User) {
            throw notFoundException('User');
        }

        $EmailConfirmed = $this->UserModel->confirmEmail($User, $EmailKey);
        $this->Form->setValidationResults($this->UserModel->validationResults());

        if ($EmailConfirmed) {
            $UserID = val('UserID', $User);
            Gdn::session()->start($UserID);
        }

        $this->setData('EmailConfirmed', $EmailConfirmed);
        $this->setData('Email', $User->Email);
        $this->render();
    }

    /**
     * Send email confirmation message to user.
     *
     * @access public
     * @since 2.0.?
     *
     * @param int $UserID
     */
    public function emailConfirmRequest($UserID = '') {
        if ($UserID && !Gdn::session()->checkPermission('Garden.Users.Edit')) {
            $UserID = '';
        }

        try {
            $this->UserModel->sendEmailConfirmationEmail($UserID);
        } catch (Exception $Ex) {
        }
        $this->Form->setValidationResults($this->UserModel->validationResults());

        $this->render();
    }

    /**
     * Does actual de-authentication of a user. Used by SignOut().
     *
     * @access public
     * @since 2.0.0
     *
     * @param string $AuthenticationSchemeAlias
     * @param string $TransientKey Unique value to prove intent.
     */
    public function leave($AuthenticationSchemeAlias = 'default', $TransientKey = '') {
        deprecated(__FUNCTION__);
        $this->EventArguments['AuthenticationSchemeAlias'] = $AuthenticationSchemeAlias;
        $this->fireEvent('BeforeLeave');

        // Allow hijacking deauth type
        $AuthenticationSchemeAlias = $this->EventArguments['AuthenticationSchemeAlias'];

        try {
            $Authenticator = Gdn::authenticator()->authenticateWith($AuthenticationSchemeAlias);
        } catch (Exception $e) {
            $Authenticator = Gdn::authenticator()->authenticateWith('default');
        }

        // Only sign the user out if this is an authenticated postback! Start off pessimistic
        $this->Leaving = false;
        $Result = Gdn_Authenticator::REACT_RENDER;

        // Build these before doing anything desctructive as they are supposed to have user context
        $LogoutResponse = $Authenticator->logoutResponse();
        $LoginResponse = $Authenticator->loginResponse();

        $AuthenticatedPostbackRequired = $Authenticator->requireLogoutTransientKey();
        if (!$AuthenticatedPostbackRequired || Gdn::session()->validateTransientKey($TransientKey)) {
            $Result = $Authenticator->deauthenticate();
            $this->Leaving = true;
        }

        if ($Result == Gdn_Authenticator::AUTH_SUCCESS) {
            $this->View = 'leave';
            $Reaction = $LogoutResponse;
        } else {
            $this->View = 'auth/'.$Authenticator->getAuthenticationSchemeAlias();
            $Reaction = $LoginResponse;
        }

        switch ($Reaction) {
            case Gdn_Authenticator::REACT_RENDER:

                break;

            case Gdn_Authenticator::REACT_EXIT:
                exit();
                break;

            case Gdn_Authenticator::REACT_REMOTE:
                // Render the view, but set the delivery type to VIEW
                $this->_DeliveryType = DELIVERY_TYPE_VIEW;
                break;

            case Gdn_Authenticator::REACT_REDIRECT:
            default:
                // If we're just told to redirect, but not where... try to figure out somewhere that makes sense.
                if ($Reaction == Gdn_Authenticator::REACT_REDIRECT) {
                    $Route = '/';
                    $Target = $this->target();
                    if (!is_null($Target)) {
                        $Route = $Target;
                    }
                } else {
                    $Route = $Reaction;
                }

                if ($this->_DeliveryType != DELIVERY_TYPE_ALL) {
                    $this->RedirectUrl = url($Route);
                } else {
                    if ($Route !== false) {
                        redirect($Route);
                    } else {
                        redirect(Gdn::router()->getDestination('DefaultController'));
                    }
                }
                break;
        }
        $this->render();
    }

    /**
     * Go to requested Target() or the default controller if none was set.
     *
     * @access public
     * @since 2.0.0
     *
     * @return string URL.
     */
    public function redirectTo() {
        $Target = $this->target();
        return $Target == '' ? Gdn::router()->getDestination('DefaultController') : $Target;
    }

    /**
     * Set where to go after signin.
     *
     * @access public
     * @since 2.0.0
     *
     * @param string $Target Where we're requested to go to.
     * @return string URL to actually go to (validated & safe).
     */
    public function target($Target = false) {
        if ($Target === false) {
            $Target = $this->Form->getFormValue('Target', false);
            if (!$Target) {
                $Target = $this->Request->get('Target', $this->Request->get('target', '/'));
            }
        }

        // Make sure that the target is a valid url.
        if (!preg_match('`(^https?://)`', $Target)) {
            $Target = '/'.ltrim($Target, '/');

            // Never redirect back to signin.
            if (preg_match('`^/entry/signin`i', $Target)) {
                $Target = '/';
            }
        }

        return $Target;
    }
}
