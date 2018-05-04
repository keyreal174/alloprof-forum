<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace VanillaTests\APIv2\Authenticate;

use VanillaTests\APIv2\AbstractAPIv2Test;

class PasswordAuthenticatorTest extends AbstractAPIv2Test {

    private $currentUser;

    private $baseUrl = '/authenticate';

    /**
     * @inheritdoc
     */
    public function setUp() {
        parent::setUp();

        $uniqueID = self::randomUsername('pa');
        $userData = [
            'name' => $uniqueID,
            'email' => $uniqueID.'@example.com',
            'password' => 'pwd_'.$uniqueID,
        ];

        /** @var \UsersApiController $usersAPIController */
        $usersAPIController = $this->container()->get('UsersAPIController');
        $userFragment = $usersAPIController->post($userData)->getData();
        $this->currentUser = array_merge($userFragment, $userData);

        $session = $this->container()->get(\Gdn_Session::class);
        $session->end();
    }

    /**
     * A user should be able to sign in through /authenticate/password
     */
    public function testPostPasswordShortcut() {
        $this->assertNoSession();

        $this->api()->post("{$this->baseUrl}/password", [
            'username' => $this->currentUser['email'],
            'password' => $this->currentUser['password']
        ]);

        $this->assertSessionUserID();
    }

    /**
     * /authenticate/password should work even if the PasswordAuthenticator is inactive.
     */
    public function testPostPasswordShortcutAlwaysOn() {
        $this->assertNoSession();

        /** @var \Gdn_Configuration $config */
        $config = $this->container()->get(\Gdn_Configuration::class);
        $config->set('Garden.SignIn.DisablePassword', true, true, false);
        try {
            $this->api()->post("{$this->baseUrl}/password", [
                'username' => $this->currentUser['email'],
                'password' => $this->currentUser['password']
            ]);
        } finally {
            $config->set('Garden.SignIn.DisablePassword', false, true, false);
        }

        $this->assertSessionUserID();
    }

    /**
     * A user should be able to sign in with their username and password.
     */
    public function testPostPasswordName() {
        $this->assertNoSession();

        $this->api()->post("{$this->baseUrl}", [
            'authenticate' => [
                'authenticatorType' => 'password',
                'authenticatorID' => 'password',
            ],
            'username' => $this->currentUser['name'],
            'password' => $this->currentUser['password']
        ]);

        $this->assertSessionUserID();
    }

    /**
     * A user should be able to sign in with their email and password.
     */
    public function testPostPasswordEmail() {
        $this->assertNoSession();

        $this->api()->post("{$this->baseUrl}", [
            'authenticate' => [
                'authenticatorType' => 'password',
                'authenticatorID' => 'password',
            ],
            'username' => $this->currentUser['email'],
            'password' => $this->currentUser['password']
        ]);

        $this->assertSessionUserID();
    }

    /**
     * An incorrect username should return 404.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 404
     */
    public function testPostPasswordNotFound() {
        $this->api()->post("{$this->baseUrl}", [
            'authenticate' => [
                'authenticatorType' => 'password',
                'authenticatorID' => 'password',
            ],
            'username' => $this->currentUser['email'].'!!!!',
            'password' => $this->currentUser['password']
        ]);
    }

   /**
     * /authenticate with password/password should not work if the PasswordAuthenticator is inactive.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot authenticate with an inactive authenticator.
     */
    public function testPostPasswordInactive() {
        $this->assertNoSession();

        /** @var \Gdn_Configuration $config */
        $config = $this->container()->get(\Gdn_Configuration::class);
        $config->set('Garden.SignIn.DisablePassword', true, true, false);
        try {
            $this->api()->post("{$this->baseUrl}", [
                'authenticate' => [
                    'authenticatorType' => 'password',
                    'authenticatorID' => 'password',
                ],
                'username' => $this->currentUser['email'],
                'password' => $this->currentUser['password']
            ]);
        } finally {
            $config->set('Garden.SignIn.DisablePassword', false, true, false);
        }
    }

    /**
     * An incorrect password should return 401.
     *
     * @expectedException \Exception
     * @expectedExceptionCode 401
     */
    public function testPostPasswordIncorrect() {
        $this->api()->post("{$this->baseUrl}", [
            'authenticate' => [
                'authenticatorType' => 'password',
                'authenticatorID' => 'password',
            ],
            'username' => $this->currentUser['email'],
            'password' => $this->currentUser['password'].'!!!'
        ]);
    }

    /**
     * Assert that there is not currently a user in the session.
     *
     * @throws \Garden\Container\ContainerException
     * @throws \Garden\Container\NotFoundException
     */
    public function assertNoSession() {
        /* @var \Gdn_Session $session */
        $session = $this->container()->get(\Gdn_Session::class);
        $this->assertEquals(0, $session->UserID);
    }

    /**
     * Assert that a given user has a session.
     *
     * @param int|null $expected The expected user or **null** for the current user.
     *
     * @throws \Garden\Container\ContainerException
     * @throws \Garden\Container\NotFoundException
     */
    public function assertSessionUserID(int $expected = null) {
        if ($expected === null) {
            $expected = $this->currentUser['userID'];
        }

        /* @var \Gdn_Session $session */
        $session = $this->container()->get(\Gdn_Session::class);
        $this->assertEquals($expected, $session->UserID);
    }
}
