<?php
/**
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 */

use Garden\Web\Exception\ClientException;
use Garden\Web\Exception\NotFoundException;
use Garden\Web\Exception\ServerException;
use Garden\Web\RequestInterface;
use Vanilla\Models\SSOModel;
use Vanilla\Models\SSOUserInfo;
use Vanilla\Utility\CapitalCaseScheme;

/**
 * API Controller for the `/authenticate` resource.
 */
class AuthenticateApiController extends AbstractApiController {

    const SESSION_ID_EXPIRATION = 1200; // 20 minutes

    /** @var CapitalCaseScheme */
    private $caseScheme;

    /** @var Gdn_Configuration */
    private $config;

    /** @var RequestInterface */
    private $request;

    /** @var SessionModel */
    private $sessionModel;

    /** @var SSOModel */
    private $ssoModel;

    /** @var UserModel */
    private $userModel;

    /**
     * AuthenticationController constructor.
     *
     * @param Gdn_Configuration $config
     * @param RequestInterface $request
     * @param SessionModel $sessionModel
     * @param SSOModel $ssoModel
     * @param UserModel $userModel
     */
    public function __construct(
        Gdn_Configuration $config,
        RequestInterface $request,
        SessionModel $sessionModel,
        SSOModel $ssoModel,
        UserModel $userModel
    ) {
        $this->caseScheme = new CapitalCaseScheme();
        $this->config = $config;
        $this->request = $request;
        $this->sessionModel = $sessionModel;
        $this->ssoModel = $ssoModel;
        $this->userModel = $userModel;
    }

    /**
     * Automatically makes a link in Gdn_UserAuthentication using the email address.
     *
     * @param SSOUserInfo $ssoUserInfo
     * @return array|bool User data if found or false otherwise.
     */
    private function autoConnect(SSOUserInfo $ssoUserInfo) {
        $userData = $this->userModel->getWhere(['Email' => $ssoUserInfo['email']])->firstRow(DATASET_TYPE_ARRAY);
        if ($userData !== false) {
            $this->userModel->saveAuthentication([
                'UserID' => $userData['UserID'],
                'Provider' => $ssoUserInfo['authenticatorID'],
                'UniqueID' => $ssoUserInfo['uniqueID']
            ]);
        }
        return $userData;
    }

    /**
     * Store the data and return the associated SessionID to retrieve it.
     *
     * @param array $data The data to store.
     * @return string SessionID
     */
    private function createSession($data) {
        $sessionID = betterRandomString(32, 'aA0');

        $this->sessionModel->insert([
            'SessionID' => $sessionID,
            'UserID' => $this->getSession()->UserID,
            'DateExpire' => date(MYSQL_DATE_FORMAT, time() + self::SESSION_ID_EXPIRATION),
            'Attributes' => $data,
        ]);

        return $sessionID;
    }

    /**
     * Unlink a user from the specified authenticator.
     * If no user is specified it will unlink the current user.
     *
     * @throws Exception
     *
     * @param $authenticator
     * @param string $authenticatorID
     * @param array $query The query string as an array.
     */
    public function delete($authenticator, $authenticatorID = '', array $query) {
        $in = $this->schema([
            'authenticator:s' => 'The authenticator that will be used.',
            'authenticatorID:s?' => 'Authenticator instance\'s identifier.',
            'userID:i?' => 'UserID to unlink authenticator from.',
        ])->setDescription('Authenticate a user using a specific authenticator.');
        $out = $this->schema([], 'out');

        $in->validate($query, true);

        if (isset($query['UserID'])) {
            $this->permission('Garden.Users.Edit');
            $userID = $query['UserID'];
        } else {
            $this->permission('Garden.SignIn.Allow');
            $userID = $this->getSession()->UserID;
        }

        $authenticatorInstance = $this->ssoModel->getSSOAuthenticator($authenticator, $authenticatorID);

        $data = [];
        $this->userModel->getDelete(
            'UserAuthentication',
            ['UserID' => $userID, 'ProviderKey' => $authenticatorInstance->getID()],
            $data
        );
    }

    /**
     * Try to find a user matching the provided SSOUserInfo.
     * Email has priority over Name if both are allowed.
     *
     * @param SSOUserInfo $ssoUserInfo SSO provided user's information.
     * @param string $findByEmail Try to find the user by Email.
     * @param string $findByName Try to find the user by Name.
     * @return array UserID that matches the SSOUserInfo.
     */
    private function findMatchingUsers(SSOUserInfo $ssoUserInfo, $findByEmail, $findByName) {
        if (!$findByEmail && !$findByName) {
            return [];
        }

        $this->userModel->SQL->select(['UserID','Name','Email','Photo']);

        if ($findByEmail) {
            $this->userModel->SQL->orWhere(['Email' => $ssoUserInfo['email']]);
        }
        if ($findByName) {
            $this->userModel->SQL->orWhere(['Name' => $ssoUserInfo['name']]);
        }

        $users = $this->userModel->getWhere()->resultArray();
        return Gdn_DataSet::index($users, 'UserID');
    }

    /**
     * Authenticate a user using the specified authenticator.
     * We allow get requests because some authenticators need that.
     *
     * @throws Exception If the authentication process fails
     * @throws NotFoundException If the $authenticatorType is not found.
     * @param string $authenticator
     * @param string $authenticatorID
     * @return array
     */
    public function get($authenticator, $authenticatorID = '') {
        return $this->post($authenticator, $authenticatorID);
    }

    /**
     * Authenticate a user using the specified authenticator.
     *
     * @throws Exception If the authentication process fails
     * @throws NotFoundException If the $authenticatorType is not found.
     *
     * @param string $authenticator
     * @param string $authenticatorID
     * @param array $query The query string as an array.
     * @return array
     */
    public function post($authenticator, $authenticatorID = '', array $query) {
        $in = $this->schema([
            'authenticator:s' => 'The authenticator that will be used.',
            'authenticatorID:s?' => 'Authenticator instance\'s identifier.',
        ])->setDescription('Authenticate a user using a specific authenticator.');
        $out = $this->schema(Schema::parse([
            'userID:i?' => 'Identifier of the authenticated user.',
            'authenticationStep:s?' => 'Tells whether the user is now authenticated or if additional step(s) are required.',
            'sessionID:s?' => 'Identifier used to do subsequent call to the api for the current authentication process.'
                .' Returned if the authentication was not a success.',
        ]), 'out');

        $in->validate($query, true);

        $authenticatorInstance = $this->ssoModel->getSSOAuthenticator($authenticator, $authenticatorID);

        // The authenticator should throw an appropriate error message on error.
        $ssoUserInfo = $authenticatorInstance->authenticate($this->request);

        if (!$ssoUserInfo) {
            throw new Exception("Unknown error while authenticating with $authenticatorType.");
        }

        // Allows registration without an email address.
        $noEmail = $this->config->get('Garden.Registration.NoEmail', false);

        // Specifies whether Emails are unique or not.
        $emailUnique = !$noEmail && $this->config->get('Garden.Registration.EmailUnique', true);

        // Specifies whether Names are unique or not.
        $nameUnique = $this->config->get('Garden.Registration.NameUnique', true);

        // Allows SSO connections to link a VanillaUser to a ForeignUser.
        $allowConnect = $this->config->get('Garden.Registration.AllowConnect', true);

        // Will automatically try to link users using the provided Email address if the Provider is "Trusted".
        $autoConnect = $allowConnect && $emailUnique && $this->config->get('Garden.Registration.AutoConnect', false);

        // Synchronize user's data.
        $syncUser = $this->config->get('Garden.Registration.ConnectSynchronize', true);

        // Synchronize user's roles only on registration.
        $syncRolesOnlyRegistration = $this->config->get('Garden.SSO.SyncRolesOnRegistrationOnly', false);

        // Synchronize user's roles.
        $syncRoles = !$syncRolesOnlyRegistration && $this->config->get('Garden.SSO.SyncRoles', false);

        $user = $this->ssoModel->sso($ssoUserInfo);

        // Let's try to find a matching user.
        if (!$user && $autoConnect) {
            $user = $this->autoConnect($ssoUserInfo);
        }

        $sessionData = [
            'ssoUserInfo' => $ssoUserInfo,
        ];

        if ($user) {
            if ($authenticatorInstance->isTrusted()) {
                if (!$this->syncUser($ssoUserInfo, $user, $syncUser, $syncRoles)) {
                    throw new ServerException(
                        "User synchronization failed",
                        500,
                        [
                            'validationResults' => $this->userModel->validationResults()
                        ]
                    );
                }
            }
            $response = array_merge(['authenticationStep' => 'authenticated'], $user);
        } else {
            // We could not authenticate or autoconnect but it may be possible to do a manual connect.
            // If that is the case we should state so in the response.
            if ($allowConnect && ($emailUnique || $nameUnique)) {
                $existingUsers = $this->findMatchingUsers($ssoUserInfo, $emailUnique, $nameUnique);
                if (!empty($existingUsers)) {
                    $sessionData['existingUsers'] = $existingUsers;
                    $response = [
                        'authenticationStep' => 'connectuser',
                    ];
                }
            }
        }

        if (!isset($response)) {
            throw new ClientException('Authentication failed.');
        }

        if ($response['authenticationStep'] === 'authenticated') {
            $this->getSession()->start($response['userID']);
        } else {
            // Store all the information needed for the next authentication step.
            $response['sessionID'] = $this->createSession($sessionData);
        }

        return $out->validate($response);
    }

    /**
     * Synchronize a user using the provided data.
     *
     * @param SSOUserInfo $ssoUserInfo SSO provided user data.
     * @param array $user Current user's data.
     * @param bool $syncUser Synchronize the user's data.
     * @param bool $syncRoles Synchronize the user's roles.
     * @return bool If the synchronisation was a success ot not.
     */
    private function syncUser(SSOUserInfo $ssoUserInfo, $user, $syncUser, $syncRoles) {
        if (!$syncUser && !$syncRoles) {
            return true;
        }

        $userInfo = [
            'UserID' => $user['UserID']
        ];

        if ($syncUser) {
            $userInfo = array_merge($this->caseScheme->convertArrayKeys($ssoUserInfo), $userInfo);

            // Don't overwrite the user photo if the user uploaded a new one.
            $photo = val('Photo', $user);
            if (!val('Photo', $userInfo) || ($photo && !isUrl($photo))) {
                unset($userInfo['Photo']);
            }
        }

        $saveRoles = $syncRoles && array_key_exists('roles', $ssoUserInfo);
        if ($saveRoles) {
            if (!empty($ssoUserInfo['roles'])) {
                $roles = \RoleModel::getByName($ssoUserInfo['roles']);
                $roleIDs = array_keys($roles);
            }

            // Ensure user has at least one role.
            if (empty($roleIDs)) {
                $roleIDs = $this->userModel->newUserRoleIDs();
            }

            $userInfo['RoleID'] = $roleIDs;
        }

        $userID = $this->userModel->save($userInfo, [
            'NoConfirmEmail' => true,
            'FixUnique' => true,
            'SaveRoles' => $saveRoles,
        ]);

        /*
         * TODO: Add a replacement event for AfterConnectSave.
         * It was added 8 months ago so it is safe to assume that the only usage of it is the CategoryRoles plugin.
         * https://github.com/vanilla/vanilla/commit/1d9ae17652213d888bbd07cac0f682959ca326b9
         */

        return $userID !== false;
    }
}
