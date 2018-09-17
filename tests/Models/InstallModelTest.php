<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\Model;

use PHPUnit\Framework\TestCase;
use VanillaTests\BootstrapTrait;
use VanillaTests\TestInstallModel;

/**
 * Test basic Vanilla installation.
 */
class InstallTest extends TestCase {
    use BootstrapTrait;

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass() {
        /* @var TestInstallModel $installer */
        $installer = self::container()->get(TestInstallModel::class);
        $installer->uninstall();

        BootstrapTrait::tearDownAfterClass();
    }

    /**
     * Test installing Vanilla with the {@link \Vanilla\Models\InstallModel}.
     */
    public function testInstall() {
        /* @var TestInstallModel $installer */
        $installer = self::container()->get(TestInstallModel::class);

        $result = $installer->install([
            'site' => ['title' => __METHOD__]
        ]);

        $this->assertArrayHasKey('version', $result);
        $this->assertGreaterThan(0, $result['adminUserID']);
    }
}
