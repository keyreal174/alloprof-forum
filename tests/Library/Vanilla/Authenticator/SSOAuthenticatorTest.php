<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace VanillaTests\Library\Vanilla;

use PHPUnit\Framework\TestCase;
use Vanilla\Models\AuthenticatorModel;
use Vanilla\Models\SSOData;
use VanillaTests\Fixtures\Authenticator\MockSSOAuthenticator;
use VanillaTests\SiteTestTrait;

class SSOAuthenticatorTest extends TestCase {
    use SiteTestTrait;

    /** @var AuthenticatorModel */
    private $authenticatorModel;

    /**
     * @inheritdoc
     */
    public function setUp() {
        parent::setUp();

        /** @var AuthenticatorModel $authenticatorModel */
        $this->authenticatorModel = self::container()->get(AuthenticatorModel::class);
    }

    /**
     * @inheritdoc
     */
    public function tearDown() {
        /** @var \Gdn_SQLDriver $driver */
        $driver = self::container()->get('SqlDriver');
        $driver->truncate('UserAuthenticationProvider');

        parent::tearDown();
    }

    /**
     * Test that an authenticator with minimal/properly implemented methods will instantiate.
     */
    public function testInstantiateAuthenticator() {
        $authType = MockSSOAuthenticator::getType();
        $this->authenticatorModel->createSSOAuthenticatorInstance([
            'authenticatorID' => $authType,
            'type' => $authType,
            'SSOData' => json_decode(json_encode(new SSOData($authType, $authType, uniqid())), true),
        ]);
        $this->assertTrue(true);
    }
}
