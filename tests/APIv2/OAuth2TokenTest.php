<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\APIv2;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use VanillaTests\SiteTestTrait;
use VanillaTests\TestOAuth\TestOAuthPlugin;

/**
 * Tests for `POST /api/v2/tokens/oauth`.
 */
final class OAuth2TokenTest extends AbstractAPIv2Test {
    protected const CLIENT_ID = 'test123';

    /**
     * The addons to run tests with.
     *
     * @return array
     */
    protected static function getAddons(): array {
        return ['vanilla', 'test-oauth'];
    }

    /**
     * Setup tests.
     */
    public function setUp() {
        parent::setUp();
        $this->configureProvider(['AssociationKey' => self::CLIENT_ID, 'AssociationSecret' => 'shh...', 'Active' => 1]);

        // Clean out the provider.
        $oauth = $this->container()->get(TestOAuthPlugin::class);
        \Closure::bind(function () {
            $this->accessToken = null;
            $this->provider = false;
        }, $oauth, TestOAuthPlugin::class)();

        // Clean up the test user.
        /* @var TestOAuthPlugin $plugin */
        $plugin = $this->container()->get(TestOAuthPlugin::class);
        $plugin->cleanUp();
    }

    /**
     * An unconfigured client should not be allowed.
     *
     * @expectedException \Garden\Web\Exception\HttpException
     * @expectedExceptionCode 500
     */
    public function testNotConfigured() {
        $this->configureProvider(['AssociationKey' => '']);

        $r = $this->postAccessToken();
    }

    /**
     * An inactive client should not be allowed.
     *
     * @expectedException \Garden\Web\Exception\HttpException
     * @expectedExceptionCode 500
     */
    public function testNotActive() {
        $this->configureProvider(['Active' => 0]);

        $r = $this->postAccessToken();
    }

    /**
     * A client ID mismatch should be an error.
     *
     * @expectedException \Garden\Web\Exception\HttpException
     * @expectedExceptionCode 422
     * @expectedExceptionMessage Invalid client ID.
     */
    public function testBadClientID() {
        $this->configureProvider(['AssociationKey' => 'different']);
        $r = $this->postAccessToken();
    }

    /**
     * A bad access token should be forbidden.
     *
     * @expectedException \Garden\Web\Exception\HttpException
     * @expectedExceptionCode 403
     */
    public function testBadToken() {
        $r = $this->postAccessToken('foo');
    }

    /**
     * A good access token should work.
     */
    public function testGoodToken() {
        $r = $this->postAccessToken();

        $this->assertNotEmpty($r['accessToken']);
    }

    /**
     * Make a request to the token endpoint with default valid values.
     *
     * @param string $oauthAccessToken
     * @return array
     */
    private function postAccessToken(string $oauthAccessToken = TestOAuthPlugin::GOOD_ACCESS_TOKEN): array {
        $r = $this->api()->post('/tokens/oauth', [
            'clientID' => self::CLIENT_ID,
            'oauthAccessToken' => $oauthAccessToken,
        ]);

        return $r->getBody();
    }

    /**
     * Configure the provider for different test scenarios.
     *
     * @param array $set The values to set.
     */
    private function configureProvider(array $set): void {
        /* @var \Gdn_AuthenticationProviderModel $model */
        $model = static::container()->get(\Gdn_AuthenticationProviderModel::class);
        $provider = \Gdn_AuthenticationProviderModel::getProviderByKey(TestOAuthPlugin::PROVIDER_KEY);

        $provider = array_replace($provider, $set);
        $r = $model->save($provider);
        if (!$r) {
            throw new AssertionFailedError("Could not save the provider!!!");
        }
    }
}
