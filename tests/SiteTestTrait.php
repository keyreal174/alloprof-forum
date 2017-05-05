<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace VanillaTests;


use Garden\EventManager;
use Vanilla\AddonManager;
use Garden\Container\Container;

/**
 * Allow a class to test against
 */
trait SiteTestTrait {
    private static $container;

    protected static function createContainer() {
        $folder = strtolower(EventManager::classBasename(get_called_class()));
        $bootstrap = new Bootstrap("http://vanilla.test/$folder");

        $container = new Container();
        $bootstrap->run($container);

        return $container;
    }

    public static function setupBeforeClass() {
        $dic = self::$container = static::createContainer();


        /* @var TestInstallModel $installer */
        $installer = $dic->get(TestInstallModel::class);

        $installer->uninstall();
        $result = $installer->install([
            'site' => ['title' => EventManager::classBasename(get_called_class())]
        ]);
    }

    protected static function container() {
        return self::$container;
    }
}
