<?php
/**
 * @author Alexandre (DaazKu) Chouinard <alexandre.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace VanillaTests\APIv2;


use Vanilla\Authenticator\Authenticator;
use Vanilla\Models\AuthenticatorModel;

class AuthenticatorsTest extends AbstractAPIv2Test {

    /** @var Authenticator[] */
    private static $authenticators;

    /** @var string */
    private $baseUrl;

    /**
     * AuthenticatorsTest constructor.
     *
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '') {
        $this->baseUrl = '/authenticators';
    }

    /**
     * @inheritdoc
     */
    public static function setupBeforeClass() {
        parent::setupBeforeClass();

        /** @var AuthenticatorModel $authenticatorModel */
        $authenticatorModel = self::container()->get(AuthenticatorModel::class);
        self::$authenticators = $authenticatorModel->getAuthenticators();
    }

    /**
     * @inheritdoc
     */
    public function setUp() {
        parent::setUp();

        if (!self::$authenticators) {
            $this->markTestSkipped('No Authenticator found.');
        }
    }

    /**
     * @param array $record
     */
    public function assertIsAuthenticator(array $record) {
        $this->assertInternalType('array', $record);

        $this->assertArrayHasKey('authenticatorID', $record);
        $this->assertArrayHasKey('type', $record);
        $this->assertArrayHasKey('resourceUrl', $record);
        $this->assertArrayHasKey('ui', $record);
        $this->assertInternalType('array', $record['ui']);
        $this->assertArrayHasKey('isActive', $record);
        $this->assertInternalType('bool', $record['isActive']);
    }

    /**
     * Test GET /authenticators/:type/:id
     */
    public function testGetAuthenticators() {
        $type = self::$authenticators[0]::getType();
        $id = self::$authenticators[0]->getID();

        $response = $this->api()->get($this->baseUrl.'/'.$type.'/'.$id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsAuthenticator($body);
    }

    /**
     * Test GET /authenticators/ucfirst(:type)/ucfirst(:id)
     */
    public function testGetAuthenticatorsUCFirst() {
        $type = ucfirst(self::$authenticators[0]::getType());
        $id = ucfirst(self::$authenticators[0]->getID());

        $response = $this->api()->get($this->baseUrl.'/'.$type.'/'.$id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsAuthenticator($body);
    }

    /**
     * Test GET /authenticators/strtolower(:type)/strtolower(:id)
     *
     * This should be what is returned by the api in the URL fields.
     */
    public function testGetAuthenticatorsLowerCase() {
        $type = strtolower(self::$authenticators[0]::getType());
        $id = strtolower(self::$authenticators[0]->getID());

        $response = $this->api()->get($this->baseUrl.'/'.$type.'/'.$id);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsAuthenticator($body);
    }

    /**
     * Test GET /authenticators
     */
    public function testListAuthenticators() {
        $response = $this->api()->get($this->baseUrl);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();

        $this->assertInternalType('array', $body);
        $this->assertCount(count(self::$authenticators), $body);

        foreach ($body as $record) {
            $this->assertIsAuthenticator($record);
        }
    }

    /**
     * Test PATCH /authenticators/:id
     */
    public function testPatchAuthenticator() {
        $id = self::$authenticators[0]->getID();

        $response = $this->api()->patch($this->baseUrl.'/'.$id, [
            'isActive' => !self::$authenticators[0]->isActive(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $body = $response->getBody();
        $this->assertIsAuthenticator($body);
    }
}
