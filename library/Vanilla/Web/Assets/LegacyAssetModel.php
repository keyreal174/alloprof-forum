<?php
/**
 * Contains functions for combining Javascript and CSS files.
 *
 * Use the AssetModel_StyleCss_Handler event to include CSS files in your plugin.
 *
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @since 2.1
 */

namespace Vanilla\Web\Assets;

use Gdn_Controller;
use Vanilla\Addon;
use Gdn_Model;
use Gdn;
use Vanilla\AliasLoader;

/**
 * Manages Assets.
 */
class LegacyAssetModel extends Gdn_Model {
    /**
     * The number of seconds to wait after a deploy before switching the cache buster.
     */
    const CACHE_GRACE_PERIOD = 90;

    /** @var string Directory for webpack-built files. */
    const WEBPACK_DIST_DIRECTORY_NAME = "dist";

    /** @var string Webpack built script extension. */
    const WEBPACK_SCRIPT_EXTENSION = ".min.js";

    /** @var array List of CSS files to serve. */
    protected $_CssFiles = [];

     /** @var string */
    public $UrlPrefix = '';

    /**
     * @var \Vanilla\AddonManager
     */
    private $addonManager;

    public function __construct(\Vanilla\AddonManager $addonManager) {
        parent::__construct();
        // Set the old class name for Gdn_Pluggable.
        $this->ClassName = "AssetModel";
        $this->addonManager = $addonManager;
    }

    /**
     * Get list of CSS anchor files
     *
     * Fires an event to allow loaded applications to create their own CSS
     * aggregation domains.
     *
     * @return array
     */
    public static function getAnchors() {
        static $anchors = null;
        if (is_null($anchors)) {
            $anchors = ['style.css', 'admin.css'];
            Gdn::pluginManager()->EventArguments['CssAnchors'] = &$anchors;
            Gdn::pluginManager()->fireAs('AssetModel')->fireEvent('getAnchors');
        }
        return $anchors;
    }

    /**
     * Add to the list of CSS files to serve.
     *
     * @param $filename
     * @param bool $folder
     * @param bool $options
     */
    public function addCssFile($filename, $folder = false, $options = false) {
        if (is_string($options)) {
            $options = ['Css' => $options];
        }
        $this->_CssFiles[] = [$filename, $folder, $options];
    }

    /**
     *
     * @param $themeType
     * @param $basename
     * @param $eTag
     * @param null $notFound
     * @param string|null $currentTheme
     * @return array
     */
    public function getCssFiles($themeType, $basename, $eTag, &$notFound = null, $currentTheme = null) {
        $notFound = [];
        $basename = strtolower($basename);

        // Gather all of the css paths.
        switch ($basename) {
            case 'style':
                $this->_CssFiles = [
                    ['style.css', 'dashboard', ['Sort' => -10]],
                ];
                break;
            case 'admin':
                $this->_CssFiles = [
                    ['admin.css', 'dashboard', ['Sort' => -10]],
                ];
                break;
            default:
                $this->_CssFiles = [];
        }

        // Throw an event so that plugins can add their css too.
        $this->EventArguments['ETag'] = $eTag;
        $this->EventArguments['ThemeType'] = $themeType;
        $this->fireEvent("{$basename}Css");

        // Include theme customizations last so that they override everything else.
        switch ($basename) {
            case 'style':
                $this->addCssFile(asset('/applications/dashboard/design/style-compat.css', true), false, ['Sort' => -9.999]);
                $this->addCssFile('custom.css', false, ['Sort' => 1000]);

                if (Gdn::controller()->Theme && Gdn::controller()->ThemeOptions) {
                    $filenames = valr('Styles.Value', Gdn::controller()->ThemeOptions);
                    if (is_string($filenames) && $filenames != '%s') {
                        $this->addCssFile(changeBasename('custom.css', $filenames), false, ['Sort' => 1001]);
                    }
                }

                break;
            case 'admin':
                $this->addCssFile('customadmin.css', false, ['Sort' => 10]);
                break;
        }

        $this->fireEvent('AfterGetCssFiles');

        // Hunt the css files down.
        $paths = [];
        foreach ($this->_CssFiles as $info) {
            list($filename, $folder, $options) = $info;
            $css = $options['Css'] ?? false;

            if ($css) {
                // Add some literal Css.
                $paths[] = [false, $folder, $options];

            } else {
                list($path, $urlPath) = self::cssPath($filename, $folder, $themeType, $currentTheme);
                if ($path) {
                    $paths[] = [$path, $urlPath, $options];
                } else {
                    $notFound[] = [$filename, $folder, $options];
                }
            }
        }

        // Sort the paths.
        usort($paths, ['AssetModel', '_comparePath']);

        return $paths;
    }

    /**
     * Get files built from webpack using the in-repo build process.
     *
     * These follow a pretty strict pattern of:
     *
     * - webpack runtime
     * - vendor chunk
     * - library chunk
     * - addon chunks
     * - bootstrap
     *
     * @param string $sectionName - The section of the site to lookup.
     * @return string[] Javascript file paths.
     */
    public function getWebpackJsFiles(string $sectionName) {
        if (Gdn::config("HotReload.Enabled", false)) {
            $ip = Gdn::config("HotReload.IP", "127.0.0.1");
            return [
                "http://$ip:3030/$sectionName-hot-bundle.js"
            ];
        }

        $enabledAddonKeys = [];
        $enabledAddons = $this->addonManager->getEnabled();
        /** @var Addon $addon */
        foreach ($enabledAddons as $addon) {
            $enabledAddonKeys[] = $addon->getKey();
        }

        // Make sure that we actually have some entry-points that were built for this section.
        $sectionDir = PATH_ROOT . DS . self::WEBPACK_DIST_DIRECTORY_NAME . DS . $sectionName;
        if (!file_exists($sectionDir)) {
            trace("That requested webpack asset section $sectionName does not exist\"");
            return [];
        }

        // We always have a runtime and vendor section first.
        $sectionRoot = '/' . self::WEBPACK_DIST_DIRECTORY_NAME . '/' . $sectionName;
        $scripts = [
            $sectionRoot . '/runtime' . self::WEBPACK_SCRIPT_EXTENSION,
            $sectionRoot . '/vendors' . self::WEBPACK_SCRIPT_EXTENSION,
        ];

        // The library chunk is not always created if there is nothing shared between entry-points.
        $sharedFilePath = $sectionDir . DS . 'shared' . self::WEBPACK_SCRIPT_EXTENSION;
        if (file_exists($sharedFilePath)) {
            $scripts[] = $sectionRoot . '/shared' . self::WEBPACK_SCRIPT_EXTENSION;
        }

        // Load addon bundles next.
        foreach ($enabledAddonKeys as $addonKey) {
            $filePath = $sectionDir . DS . 'addons' . DS . $addonKey . self::WEBPACK_SCRIPT_EXTENSION;
            if (file_exists($filePath)) {
                $resourcePath = $sectionRoot . '/addons/' . $addonKey . self::WEBPACK_SCRIPT_EXTENSION;
                $scripts[] = $resourcePath;
            }
        }

        // The bootstrap file goes last.
        $scripts[] = $sectionRoot . '/bootstrap' . self::WEBPACK_SCRIPT_EXTENSION;
        return $scripts;
    }

    /**
     * Get the content for an inline polyfill script.
     *
     * @return string
     */
    public function getInlinePolyfillJSContent(): string {
        $polyfillFileUrl = asset("/dist/polyfills.min.js?h=".$this->cacheBuster());

        $debug = c("Debug", false);
        $logAdding = $debug ? "console.log('Older browser detected. Initiating polyfills.');" : "";
        $logNotAdding = $debug ? "console.log('Modern browser detected. No polyfills necessary');" : "";

        // Add the polyfill loader.
        $scriptContent =
            "var supportsAllFeatures = window.Promise && window.fetch && window.Symbol"
            ."&& window.CustomEvent && Element.prototype.remove && Element.prototype.closest"
            ."&& window.NodeList && NodeList.prototype.forEach;"
            ."if (!supportsAllFeatures) {"
            .$logAdding
            ."var head = document.getElementsByTagName('head')[0];"
            ."var script = document.createElement('script');"
            ."script.src = '$polyfillFileUrl';"
            ."head.appendChild(script);"
            ."} else { $logNotAdding }";

        return $scriptContent;
    }

    /**
     * Get the resource path for a javascript bundle of a particular locale bundle.
     *
     * @param string $localeKey The key of the locale to lookup.
     *
     * @return string The path the locale javascript file.
     */
    public function getJSLocalePath(string $localeKey): string {
        // We need a web-root url, not an asset URL because this is an API endpoint resource that is dynamically generated.
        // It cannot have the assetPath joined onto the beginning.
        return Gdn::request()->url("/api/v2/locales/$localeKey/translations.js", true);
    }

    /**
     * Sorting callback for a CSS tuple.
     *
     * @param array $a A file tuple.
     * @param array $b A file tuple.
     * @return int
     */
    protected function _comparePath(array $a, array $b) {
        $sortA = $a[2]['Sort'] ?? 0;
        $sortB = $b[2]['Sort'] ?? 0;

        if ($sortA == $sortB) {
            return 0;
        }
        if ($sortA > $sortB) {
            return 1;
        }
        return -1;
    }

    /**
     * Lookup the path to a CSS file and return its info array
     *
     * @param string $filename name/relative path to css file
     * @param string $folder optional. app or plugin folder to search
     * @param string $themeType mobile or desktop
     * @param string|null $currentTheme The key of the current theme.
     * @return array|bool
     */
    public static function cssPath($filename, $folder = '', $themeType = '', $currentTheme = null) {
        if (!$themeType) {
            $themeType = isMobile() ? 'mobile' : 'desktop';
        }

        // 1. Check for a url.
        if (isUrl($filename)) {
            return [$filename, $filename];
        }

        $paths = [];

        // 2. Check for a full path.
        if (strpos($filename, '/') === 0) {
            $filename = ltrim($filename, '/');

            // Direct path was given
            $filename = "/{$filename}";
            $path = PATH_ROOT.$filename;
            if (file_exists($path)) {
                deprecated(htmlspecialchars($path).": LegacyAssetModel::CssPath() with direct paths");
                return [$path, $filename];
            }
            return false;
        }

        // 3. Check the theme.
        $theme = Gdn::themeManager()->themeFromType($themeType);

        // Let override theme dynamically
        if (isset($currentTheme) && $currentTheme != $theme) {
            $theme = $currentTheme;
        }

        if ($theme) {
            $path = "/$theme/design/$filename";
            $paths[] = [PATH_THEMES.$path, "/themes{$path}"];
        }

        // 4. Static, Plugin, or App relative file
        if ($folder) {
            if (in_array($folder, ['resources', 'static'])) {
                $path = "/resources/design/{$filename}";
                $paths[] = [PATH_ROOT.$path, $path];

            // A plugin-relative path was given
            } elseif (stringBeginsWith($folder, 'plugins/')) {
                $folder = substr($folder, strlen('plugins/'));
                $path = "/{$folder}/design/{$filename}";
                $paths[] = [PATH_PLUGINS.$path, "/plugins$path"];

                // Allow direct-to-file links for plugins
                $paths[] = [PATH_PLUGINS."/$folder/$filename", "/plugins/{$folder}/{$filename}", true]; // deprecated

            // An app-relative path was given
            } else {
                $path = "/{$folder}/design/{$filename}";
                $paths[] = [PATH_APPLICATIONS.$path, "/applications{$path}"];
            }
        }

        // 5. Check the default application.
        if ($folder != 'dashboard') {
            $paths[] = [PATH_APPLICATIONS."/dashboard/design/$filename", "/applications/dashboard/design/$filename", true]; // deprecated
        }

        foreach ($paths as $info) {
            if (file_exists($info[0])) {
                if (!empty($info[2])) {
                    // This path is deprecated.
                    unset($info[2]);
                    deprecated("The css file '$filename' in folder '$folder'");
                }

                return $info;
            }
        }
        if (!(stringEndsWith($filename, 'custom.css') || stringEndsWith($filename, 'customadmin.css'))) {
            trace("Could not find file '$filename' in folder '$folder'.");
        }

        return false;
    }

    /**
     * Lookup the path to a JS file and return its info array
     *
     * @param string $filename name/relative path to js file
     * @param string $folder optional. app or plugin folder to search
     * @param string $themeType mobile or desktop
     * @return array|bool
     */
    public static function jsPath($filename, $folder = '', $themeType = '') {
        if (!$themeType) {
            $themeType = isMobile() ? 'mobile' : 'desktop';
        }

        // 1. Check for a url.
        if (isUrl($filename)) {
            return [$filename, $filename];
        }

        $paths = [];

        // 2. Check for a full path.
        if (strpos($filename, '/') === 0) {
            $filename = ltrim($filename, '/');

            // Direct path was given
            $filename = "/{$filename}";
            $path = PATH_ROOT.$filename;
            if (file_exists($path)) {
                deprecated(htmlspecialchars($path).": LegacyAssetModel::JsPath() with direct paths");
                return [$path, $filename];
            }
            return false;
        }

        // 3. Check the theme.
        $theme = Gdn::themeManager()->themeFromType($themeType);
        if ($theme) {
            $path = "/{$theme}/js/{$filename}";
            $paths[] = [PATH_THEMES.$path, "/themes{$path}"];
        }

        // 4. Static, Plugin, or App relative file
        if ($folder) {
            if (in_array($folder, ['resources', 'static'])) {
                $path = "/resources/js/{$filename}";
                $paths[] = [PATH_ROOT.$path, $path];

            // A plugin-relative path was given
            } elseif (stringBeginsWith($folder, 'plugins/')) {
                $folder = substr($folder, strlen('plugins/'));
                $path = "/{$folder}/js/{$filename}";
                $paths[] = [PATH_PLUGINS.$path, "/plugins{$path}"];

                // Allow direct-to-file links for plugins
                $paths[] = [PATH_PLUGINS."/{$folder}/{$filename}", "/plugins/{$folder}/{$filename}", true]; // deprecated

            // An app-relative path was given
            } else {

                // App-relative path under the theme
                if ($theme) {
                    $path = "/{$theme}/{$folder}/js/{$filename}";
                    $paths[] = [PATH_THEMES.$path, "/themes{$path}"];
                }

                $path = "/{$folder}/js/{$filename}";
                $paths[] = [PATH_APPLICATIONS.$path, "/applications{$path}"];
            }
        }

        // 5. Check the global js folder.
        $paths[] = [PATH_ROOT."/js/{$filename}", "/js/{$filename}"];
        $paths[] = [PATH_ROOT."/js/library/{$filename}", "/js/library/{$filename}"];

        foreach ($paths as $info) {
            if (file_exists($info[0])) {
                if (!empty($info[2])) {
                    // This path is deprecate. The script should be moved into a /js/ sub-directory
                    unset($info[2]);
                    deprecated("The file path '$folder/$filename'", "'$folder/js/$filename'");
                }

                return $info;
            }
        }
        if (!stringEndsWith($filename, 'custom.js')) {
            trace("Could not find file '$filename' in folder '$folder'.");
        }

        return false;
    }

    /**
     * Generate an e-tag for the application from the versions of all of its enabled applications/plugins.
     *
     * @return string etag
     **/
    public static function eTag() {
        $data = [];
        $data['vanilla-core-'.APPLICATION_VERSION] = true;

        // Look through the enabled addons.
        /* @var Addon $addon */
        foreach(Gdn::addonManager()->getEnabled() as $addon) {
            if ($addon->getType() == Addon::TYPE_THEME) {
                // Themes have to figured out separately.
                continue;
            }

            $key = $addon->getKey();
            $version = $addon->getVersion();
            $type = $addon->getType();
            $data[strtolower("$key-$type-$version")] = true;
        }

        // Add the desktop theme version.
        $themes = [
            '' => Gdn::addonManager()->lookupTheme(Gdn::themeManager()->desktopTheme()),
            'Mobile' => Gdn::addonManager()->lookupTheme(Gdn::themeManager()->mobileTheme()),
        ];
        foreach ($themes as $optionsPx => $theme) {
            if (!$theme instanceof Addon) {
                continue;
            }

            $data[$theme->getKey().'-theme-'.$theme->getVersion()] = true;

            // Look for theme options.
            $options = c("Garden.{$optionsPx}ThemeOptions");
            if (!empty($options)) {
                $data[valr('Styles.Value', $options)] = true;
            }
        }


        $info = Gdn::themeManager()->getThemeInfo(Gdn::themeManager()->desktopTheme());
        if (!empty($info)) {
            $version = $info['Version'] ?? 'v0';
            $data[strtolower("{$info['Index']}-theme-{$version}")] = true;

            if (Gdn::controller()->Theme && Gdn::controller()->ThemeOptions) {
                $filenames = valr('Styles.Value', Gdn::controller()->ThemeOptions);
                $data[$filenames] = true;
            }
        }

        // Add the mobile theme version.
        $info = Gdn::themeManager()->getThemeInfo(Gdn::themeManager()->mobileTheme());
        if (!empty($info)) {
            $version = $version = $info['Version'] ?? 'v0';
            $data[strtolower("{$info['Index']}-theme-{$version}")] = true;
        }

        Gdn::pluginManager()->EventArguments['ETagData'] =& $data;

        $suffix = '';
        Gdn::pluginManager()->EventArguments['Suffix'] =& $suffix;
        Gdn::pluginManager()->fireAs('AssetModel')->fireEvent('GenerateETag');
        unset(Gdn::pluginManager()->EventArguments['ETagData']);

        ksort($data);
        $result = substr(md5(implode(',', array_keys($data))), 0, 8).$suffix;
        return $result;
    }

    /**
     * Generate a hash for a group of resources, based on keys + versions
     *
     * @param array $resources
     * @return string
     */
    public function resourceHash($resources) {
        $keys = [];

        foreach ($resources as $key => $options) {
            $version = $options['version'] ?? '';
            $keys[] = "{$key} -> {$version}";
        }

        return md5(implode("\n", $keys));
    }

    /**
     * Return a cache buster string.
     *
     * @return string Returns a string.
     */
    public function cacheBuster() {
        if ($timestamp = c('Garden.Deployed')) {
            $graced = $timestamp + static::CACHE_GRACE_PERIOD;
            if (time() >= $graced) {
                $timestamp = $graced;
            }
            $result = dechex($timestamp);
        } else {
            $result = APPLICATION_VERSION;
        }

        return $result;
    }

    /**
     * Get list of defined view handlers.
     *
     * This method no longer really works due to factory changes.
     *
     * @return array Returns an array keyed by view handler.
     * @deprecated
     */
    public static function viewHandlers() {
        deprecated('LegacyAssetModel::viewHandlers()');

        $exts = static::viewExtensions();
        $result = [];
        foreach ($exts as $ext) {
            if ($ext !== 'php') {
                $result["ViewHandler.$ext"] = [];
            }
        }

        return $result;
    }

    /**
     * Get list of allowed view extensions.
     *
     * @return array Returns an array of file extensions.
     */
    public static function viewExtensions() {
        // This is a kludge where all known extensions are included.
        $knownExts = ['tpl', 'mustache'];

        $result = ['php'];
        foreach ($knownExts as $ext) {
            $handler = "ViewHandler.$ext";
            if (Gdn::factoryExists($handler)) {
                $result[] = $ext;
            }
        }
        return $result;
    }
    /**
     * Get the path to a view.
     *
     * @param string $view the name of the view.
     * @param string $controller the name of the controller invoking the view or blank.
     * @param string $folder the application folder or plugins/<plugin> folder.
     * @param array|null $extensions optional. list of extensions to allow
     * @return string|false The path to the view or false if it wasn't found.
     */
    public static function viewLocation($view, $controller, $folder, $extensions = null) {
        $paths = [];

        // If the first character is a forward slash, this is an absolute path
        if (strpos($view, '/') === 0) {
            // This is a path to the view from the root.
            $paths[] = $view;
        } else {

            $view = strtolower($view);

            // Trim "controller" from the end of controller name, if its there
            $controller = strtolower(stringEndsWith($controller, 'Controller', true, true));
            if ($controller) {
                $controller = '/'.$controller;
            }

            // Get list of permitted view extensions
            if (is_null($extensions)) {
                $extensions = self::viewExtensions();
            }

            // 1. Gather paths from the theme, if enabled
            if (Gdn::controller() instanceof Gdn_Controller) {
                $theme = Gdn::controller()->Theme;
                if ($theme) {
                    foreach ($extensions as $ext) {
                        $paths[] = PATH_THEMES."/{$theme}/views{$controller}/$view.$ext";
                    }
                }
            }

            // 2a. Gather paths from the plugin, if the folder is a plugin folder
            if (stringBeginsWith($folder, 'plugins/')) {
                // This is a plugin view.
                foreach ($extensions as $ext) {
                    $paths[] = PATH_ROOT."/{$folder}/views{$controller}/$view.$ext";
                }

            // 2b. Gather paths from the application as a fallback
            } else {
                // This is an application view.
                $folder = strtolower($folder);
                foreach ($extensions as $ext) {
                    $paths[] = PATH_APPLICATIONS."/{$folder}/views{$controller}/$view.$ext";
                }

                if ($folder != 'dashboard' && stringEndsWith($view, '.master')) {
                    // This is a master view that can always fall back to the dashboard.
                    foreach ($extensions as $ext) {
                        $paths[] = PATH_APPLICATIONS."/dashboard/views{$controller}/$view.$ext";
                    }
                }
            }

        }

        // Now let's search the paths for the view.
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        trace([
            'view' => $view,
            'controller' => $controller,
            'folder' => $folder,
        ], 'View');
        trace($paths, __METHOD__);

        return false;
    }
}

// Create aliases for backwards compatibility.
AliasLoader::createAliases(LegacyAssetModel::class);
