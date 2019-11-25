<?php
/**
 * @author Alexander Kim <alexander.k@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Library\Vanilla;

use PHPUnit\Framework\TestCase;
use Vanilla\Web\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use VanillaTests\ExpectErrorTrait;
use VanillaTests\SiteTestTrait;
use Vanilla\Web\ContentSecurityPolicy\ContentSecurityPolicyModel;
use Vanilla\Web\ContentSecurityPolicy\DefaultContentSecurityPolicyProvider;
use Vanilla\Web\ContentSecurityPolicy\EmbedWhitelistContentSecurityPolicyProvider;
use Vanilla\Web\Asset\WebpackContentSecurityPolicyProvider;
use Vanilla\Web\Asset\WebpackAssetProvider;


/**
 * Some basic tests for the `ContentSecurityPolicyModel`.
 */
class ContentSecurityPolicyModelTest extends TestCase {
    use SiteTestTrait, ExpectErrorTrait;

    /**
     * @var ContentSecurityPolicyModel
     */
    private $cspModel;

    /**
     * Get a new model for each test.
     */
    public function setUp() {
        parent::setUp();
        $this->cspModel = $this->container()->get(ContentSecurityPolicyModel::class);
    }

    /**
     * Test CSP model with DefaultContentSecurityPolicyProvider
     */
    public function testCspModelDefaultProvider() {
        /** @var ContentSecurityPolicyProviderInterface $defaultProvider */
        $defaultProvider = $this->container()->get(DefaultContentSecurityPolicyProvider::class);
        $this->cspModel->addProvider($defaultProvider);
        $this->assertStringEndsWith('\'self\'', $this->cspModel->getHeaderString());
    }

    /**
     * Test CSP model with EmbedWhitelistContentSecurityPolicyProvider
     */
    public function testCspModeleEmbedWhiteListProvider() {
        /** @var ContentSecurityPolicyProviderInterface $embedWhiteListProvider */
        $embedWhiteListProvider = $this->container()->get(EmbedWhitelistContentSecurityPolicyProvider::class);
        $this->cspModel->addProvider($embedWhiteListProvider);
        $this->assertStringEndsWith('embed.js', $this->cspModel->getHeaderString());
    }

    /**
     * Test CSP model with WebpackAssetProvider
     */
    public function testCspModeleWebpackProvider() {
        /** @var WebpackAssetProvider $assetProvider */
        $assetProvider =  $this->container()->get(WebpackAssetProvider::class);
        $assetProvider->setHotReloadEnabled(true);
        $webpackProvider = new WebpackContentSecurityPolicyProvider($assetProvider);
        $this->cspModel->addProvider($webpackProvider);
        $this->assertContains('unsafe-eval', $this->cspModel->getHeaderString());
    }

    /**
     * Test CSP model with DefaultContentSecurityPolicyProvider, EmbedWhitelistContentSecurityPolicyProvider, WebpackAssetProvider
     *     all enabled together
     */
    public function testCspModelAllProviders() {
        /** @var ContentSecurityPolicyProviderInterface $defaultProvider */
        $defaultProvider = $this->container()->get(DefaultContentSecurityPolicyProvider::class);
        $this->cspModel->addProvider($defaultProvider);

        /** @var ContentSecurityPolicyProviderInterface $embedWhiteListProvider */
        $embedWhiteListProvider = $this->container()->get(EmbedWhitelistContentSecurityPolicyProvider::class);
        $this->cspModel->addProvider($embedWhiteListProvider);

        /** @var WebpackAssetProvider $assetProvider */
        $assetProvider =  $this->container()->get(WebpackAssetProvider::class);
        $assetProvider->setHotReloadEnabled(true);
        $webpackProvider = new WebpackContentSecurityPolicyProvider($assetProvider);
        $this->cspModel->addProvider($webpackProvider);

        $this->assertContains('\'self\'', $this->cspModel->getHeaderString());
        $this->assertContains('embed.js', $this->cspModel->getHeaderString());
        $this->assertContains('unsafe-eval', $this->cspModel->getHeaderString());
    }
}
