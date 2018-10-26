<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\SwaggerUI;

use AssetModel;
use Gdn_Plugin;
use SettingsController;
use Vanilla\Addon;
use Vanilla\Web\Assets\LegacyAssetModel;

/**
 * Handles the swagger UI menu options.
 */
class SwaggerUIPlugin extends Gdn_Plugin {
    /**
     * Add the APIv2 menu item.
     *
     * @param \DashboardNavModule $nav The menu to add the module to.
     */
    public function dashboardNavModule_init_handler(\DashboardNavModule $nav) {
        $nav->addLinkToSectionIf(
            \gdn::session()->checkPermission('Garden.Settings.Manage'),
            'settings',
            t('API'),
            '/settings/swagger',
            'site-settings.swagger-ui',
            'nav-swagger-ui',
            ['after' => 'security'],
            ['badge' => 'v2']
        );
    }

    /**
     * The main swagger page.
     *
     * @param SettingsController $sender The page controller.
     */
    public function settingsController_swagger_create(SettingsController $sender) {
        $sender->permission('Garden.Settings.Manage');

        $folder = 'plugins/'.$this->getAddon()->getKey();

        $relScripts = ['js/custom.js'];
        $js = [];
        foreach ($relScripts as $path) {
            $search = LegacyAssetModel::jsPath($path, $folder);
            if (!$search) {
                continue;
            }
            list($path, $url) = $search;
            $js[] = asset($url, false, true);
        }
        $sender->setData('js', $js);

        $sender->addCssFile('swagger-ui.css', $folder);

        $sender->title(t('Vanilla API v2'));
        $sender->render('swagger', 'settings', $folder);
    }
}
