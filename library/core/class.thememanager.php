<?php
/**
 * Theme manager.
 *
 * @author Mark O'Sullivan <markm@vanillaforums.com>
 * @author Todd Burry <todd@vanillaforums.com>
 * @author Tim Gunter <tim@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Core
 * @since 2.0
 */
use Vanilla\Addon;
use Vanilla\AddonManager;

/**
 * Manages available themes, enabling and disabling them.
 */
class Gdn_ThemeManager extends Gdn_Pluggable {

    /** @var array An array of search paths for themes and their files. */
    private $themeSearchPaths = null;

    /** @var array */
    private $alternateThemeSearchPaths = null;

    /** @var array An array of available plugins. Never access this directly, instead use $this->AvailablePlugins(); */
    private $themeCache = null;

    /** @var bool Whether to use APC for theme cache storage. */
    private $apc = false;

    /**
     * @var AddonManager
     */
    private $addonManager;

    /**
     *
     */
    public function __construct(AddonManager $addonManager = null) {
        parent::__construct();
        $this->addonManager = $addonManager;
    }

    /**
     * Sets up the theme framework
     *
     * This method indexes all available themes and extracts their information.
     * It then determines which plugins have been enabled, and includes them.
     * Finally, it parses all plugin files and extracts their events and plugged
     * methods.
     */
    public function start($Force = false) {
        // Do nothing. The plugin manager handles the theme hooks.
    }

    /**
     * Looks through the themes directory for valid themes.
     *
     * The themes are returned as an associative array of "Theme Name" => "Theme Info Array".
     *
     * @param bool $force Deprecated.
     * @return array Returns the available themes in an array.
     */
    public function availableThemes($force = false) {
        $addons = $this->addonManager->lookupAllByType(Addon::TYPE_THEME);
        $result = [];
        /* @var Addon $addon */
        foreach ($addons as $addon) {
            $result[$addon->getRawKey()] = Gdn::pluginManager()->calcOldInfoArray($addon);
        }
        return $result;
    }

    /**
     *
     *
     * @param $SearchPath
     * @param $ThemeInfo
     * @param null $PathListing
     * @return bool|string
     */
    public function indexSearchPath($SearchPath, &$ThemeInfo, $PathListing = null) {
        if (is_null($PathListing) || !is_array($PathListing)) {
            $PathListing = scandir($SearchPath, 0);
            sort($PathListing);
        }

        if ($PathListing === false) {
            return false;
        }

        foreach ($PathListing as $ThemeFolderName) {
            if (substr($ThemeFolderName, 0, 1) == '.') {
                continue;
            }

            $ThemePath = CombinePaths(array($SearchPath, $ThemeFolderName));
            $ThemeFiles = $this->findThemeFiles($ThemePath);

            if (val('about', $ThemeFiles) === false) {
                continue;
            }

            $ThemeAboutFile = val('about', $ThemeFiles);
            $SearchThemeInfo = $this->scanThemeFile($ThemeAboutFile);

            // Don't index archived themes.
//         if (val('Archived', $SearchThemeInfo, FALSE))
//            continue;

            // Add the screenshot.
            if (array_key_exists('screenshot', $ThemeFiles)) {
                $RelativeScreenshot = ltrim(str_replace(PATH_ROOT, '', val('screenshot', $ThemeFiles)), '/');
                $SearchThemeInfo['ScreenshotUrl'] = Asset($RelativeScreenshot, true);
            }

            // Add the mobile screenshot.
            if (array_key_exists('mobilescreenshot', $ThemeFiles)) {
                $RelativeScreenshot = ltrim(str_replace(PATH_ROOT, '', val('mobilescreenshot', $ThemeFiles)), '/');
                $SearchThemeInfo['MobileScreenshotUrl'] = Asset($RelativeScreenshot, true);
            }

            if (array_key_exists('hooks', $ThemeFiles)) {
                $SearchThemeInfo['HooksFile'] = val('hooks', $ThemeFiles, false);
                $SearchThemeInfo['RealHooksFile'] = realpath($SearchThemeInfo['HooksFile']);
            }

            if ($SearchThemeInfo === false) {
                continue;
            }

            $ThemeInfo[$ThemeFolderName] = $SearchThemeInfo;
        }

        return md5(serialize($PathListing));
    }

    /**
     *
     *
     * @param null $SearchPaths
     */
    public function clearThemeCache($SearchPaths = null) {
        if (!is_null($SearchPaths)) {
            if (!is_array($SearchPaths)) {
                $SearchPaths = array($SearchPaths);
            }
        } else {
            $SearchPaths = $this->searchPaths();
        }

        foreach ($SearchPaths as $SearchPath => $SearchPathName) {
            $SearchPathCacheKey = "Garden.Themes.PathCache.{$SearchPath}";
            if ($this->apc) {
                apc_delete($SearchPathCacheKey);
            } else {
                Gdn::cache()->remove($SearchPathCacheKey, array(Gdn_Cache::FEATURE_NOPREFIX => true));
            }
        }
    }

    /**
     * Get the current search paths
     *
     * By default, get all the paths as built by the constructor. Includes the two (or one) default plugin paths
     * of PATH_PLUGINS and PATH_LOCAL_PLUGINS, as well as any extra paths defined in the config variable.
     *
     * @param boolean $OnlyCustom whether or not to exclude the two default paths and return only config paths
     * @return array Search paths
     */
    public function searchPaths($OnlyCustom = false) {
        if (is_null($this->themeSearchPaths) || is_null($this->alternateThemeSearchPaths)) {
            $this->themeSearchPaths = array();
            $this->alternateThemeSearchPaths = array();

            // Add default search path(s) to list
            $this->themeSearchPaths[rtrim(PATH_THEMES, '/')] = 'core';

            // Check for, and load, alternate search paths from config
            $RawAlternatePaths = c('Garden.PluginManager.Search', null);
            if (!is_null($RawAlternatePaths)) {
                /*
                            // Handle serialized and unserialized alternate path arrays
                            $AlternatePaths = unserialize($RawAlternatePaths);
                            if ($AlternatePaths === FALSE && is_array($RawAlternatePaths))
                */
                $AlternatePaths = $RawAlternatePaths;

                if (!is_array($AlternatePaths)) {
                    $AlternatePaths = array($AlternatePaths => 'alternate');
                }

                foreach ($AlternatePaths as $AltPath => $AltName) {
                    $this->alternateThemeSearchPaths[rtrim($AltPath, '/')] = $AltName;
                    if (is_dir($AltPath)) {
                        $this->themeSearchPaths[rtrim($AltPath, '/')] = $AltName;
                    }
                }
            }
        }

        if (!$OnlyCustom) {
            return $this->themeSearchPaths;
        }

        return $this->alternateThemeSearchPaths;
    }

    /**
     *
     *
     * @param $ThemePath
     * @return array|bool
     */
    public function findThemeFiles($ThemePath) {
        if (!is_dir($ThemePath)) {
            return false;
        }

        $ThemeFiles = scandir($ThemePath);
        $TestPatterns = array(
            'about\.php' => 'about',
            '.*\.theme\.php' => 'about',
            'class\..*themehooks\.php' => 'hooks',
            'screenshot\.(gif|jpg|jpeg|png)' => 'screenshot',
            'mobile\.(gif|jpg|jpeg|png)' => 'mobilescreenshot'
        );

        $MatchedThemeFiles = array();
        foreach ($ThemeFiles as $ThemeFile) {
            foreach ($TestPatterns as $TestPattern => $FileType) {
                if (preg_match('!'.$TestPattern.'!', $ThemeFile)) {
                    $MatchedThemeFiles[$FileType] = combinePaths(array($ThemePath, $ThemeFile));
                }
            }
        }

        return array_key_exists('about', $MatchedThemeFiles) ? $MatchedThemeFiles : false;
    }

    /**
     *
     *
     * @param $ThemeFile
     * @param null $VariableName
     * @return null|void
     */
    public function scanThemeFile($ThemeFile, $VariableName = null) {
        // Find the $PluginInfo array
        if (!file_exists($ThemeFile)) {
            return;
        }
        $Lines = file($ThemeFile);

        $InfoBuffer = false;
        $ClassBuffer = false;
        $ClassName = '';
        $ThemeInfoString = '';
        if (!$VariableName) {
            $VariableName = 'ThemeInfo';
        }

        $ParseVariableName = '$'.$VariableName;
        ${$VariableName} = array();

        foreach ($Lines as $Line) {
            if ($InfoBuffer && substr(trim($Line), -2) == ');') {
                $ThemeInfoString .= $Line;
                $ClassBuffer = true;
                $InfoBuffer = false;
            }

            if (stringBeginsWith(trim($Line), $ParseVariableName)) {
                $InfoBuffer = true;
            }

            if ($InfoBuffer) {
                $ThemeInfoString .= $Line;
            }

            if ($ClassBuffer && strtolower(substr(trim($Line), 0, 6)) == 'class ') {
                $Parts = explode(' ', $Line);
                if (count($Parts) > 2) {
                    $ClassName = $Parts[1];
                }

                break;
            }

        }
        unset($Lines);
        if ($ThemeInfoString != '') {
            @eval($ThemeInfoString);
        }

        // Define the folder name and assign the class name for the newly added item.
        $var = ${$VariableName};
        if (isset($var) && is_array($var)) {
            reset($var);
            $name = key($var);
            $var = current($var);

            $var['Index'] = $name;
            $var['AboutFile'] = $ThemeFile;
            $var['RealAboutFile'] = realpath($ThemeFile);
            $var['ThemeRoot'] = dirname($ThemeFile);
            touchValue('Name', $var, $name);
            touchValue('Folder', $var, basename(dirname($ThemeFile)));

            return $var;
        } elseif ($VariableName !== null) {
            if (isset($var)) {
                return $var;
            }
        }

        return null;
    }

    /**
     *
     *
     * @param $ThemeName
     * @return mixed
     */
    public function getThemeInfo($ThemeName) {
        return val($ThemeName, $this->availableThemes(), false);
    }

    /**
     *
     *
     * @return mixed
     */
    public function currentTheme() {
        return c(!IsMobile() ? 'Garden.Theme' : 'Garden.MobileTheme', 'default');
    }

    /**
     *
     *
     * @return mixed
     */
    public function desktopTheme() {
        return c('Garden.Theme', 'default');
    }

    /**
     *
     *
     * @throws Gdn_UserException
     */
    public function disableTheme() {
        if ($this->currentTheme() == 'default') {
            throw new Gdn_UserException(T('You cannot disable the default theme.'));
        }
        $oldTheme = $this->enabledTheme();
        RemoveFromConfig('Garden.Theme');
        $newTheme = $this->enabledTheme();

        if ($oldTheme != $newTheme) {
            Logger::event(
                'theme_changed',
                'The {themeType} theme was changed from {oldTheme} to {newTheme}.',
                array(
                    'themeType' => 'desktop',
                    'oldTheme' => $oldTheme,
                    'newTheme' => $newTheme
                )
            );
        }
    }

    /**
     *
     *
     * @return Gdn_Config|mixed
     */
    public function enabledTheme() {
        $ThemeName = Gdn::config('Garden.Theme', 'default');
        return $ThemeName;
    }

    /**
     *
     *
     * @param bool $ReturnInSourceFormat
     * @return array|mixed
     */
    public function enabledThemeInfo($ReturnInSourceFormat = false) {
        $EnabledThemeName = $this->enabledTheme();
        $ThemeInfo = $this->getThemeInfo($EnabledThemeName);

        if ($ThemeInfo === false) {
            return array();
        }

        if ($ReturnInSourceFormat) {
            return $ThemeInfo;
        }

        // Update the theme info for a format consumable by views.
        if (is_array($ThemeInfo) & isset($ThemeInfo['Options'])) {
            $Options =& $ThemeInfo['Options'];
            if (isset($Options['Styles'])) {
                foreach ($Options['Styles'] as $Key => $Params) {
                    if (is_string($Params)) {
                        $Options['Styles'][$Key] = array('Basename' => $Params);
                    } elseif (is_array($Params) && isset($Params[0])) {
                        $Params['Basename'] = $Params[0];
                        unset($Params[0]);
                        $Options['Styles'][$Key] = $Params;
                    }
                }
            }
            if (isset($Options['Text'])) {
                foreach ($Options['Text'] as $Key => $Params) {
                    if (is_string($Params)) {
                        $Options['Text'][$Key] = array('Type' => $Params);
                    } elseif (is_array($Params) && isset($Params[0])) {
                        $Params['Type'] = $Params[0];
                        unset($Params[0]);
                        $Options['Text'][$Key] = $Params;
                    }
                }
            }
        }
        return $ThemeInfo;
    }

    /**
     *
     *
     * @param $ThemeName
     * @param bool $IsMobile
     * @return bool
     * @throws Exception
     */
    public function enableTheme($ThemeName, $IsMobile = false) {
        // Make sure to run the setup
        $this->testTheme($ThemeName);

        // Set the theme.
        $ThemeInfo = $this->getThemeInfo($ThemeName);
        $ThemeFolder = val('Folder', $ThemeInfo, '');

        $oldTheme = $IsMobile ? c('Garden.MobileTheme', 'mobile') : c('Garden.Theme', 'default');

        if ($ThemeFolder == '') {
            throw new Exception(t('The theme folder was not properly defined.'));
        } else {
            $Options = valr("{$ThemeName}.Options", $this->AvailableThemes());
            if ($Options) {
                if ($IsMobile) {
                    saveToConfig(array(
                        'Garden.MobileTheme' => $ThemeName,
                        'Garden.MobileThemeOptions.Name' => valr("{$ThemeName}.Name", $this->availableThemes(), $ThemeFolder)
                    ));
                } else {
                    saveToConfig(array(
                        'Garden.Theme' => $ThemeName,
                        'Garden.ThemeOptions.Name' => valr("{$ThemeName}.Name", $this->availableThemes(), $ThemeFolder)
                    ));
                }
            } else {
                if ($IsMobile) {
                    saveToConfig('Garden.MobileTheme', $ThemeName);
                    removeFromConfig('Garden.MobileThemeOptions');
                } else {
                    saveToConfig('Garden.Theme', $ThemeName);
                    removeFromConfig('Garden.ThemeOptions');
                }
            }
        }

        if ($oldTheme !== $ThemeName) {
            Logger::event(
                'theme_changed',
                Logger::NOTICE,
                'The {themeType} theme changed from {oldTheme} to {newTheme}.',
                array(
                    'themeType' => $IsMobile ? 'mobile' : 'desktop',
                    'oldTheme' => $oldTheme,
                    'newTheme' => $ThemeName
                )
            );
        }

        // Tell the locale cache to refresh itself.
        Gdn::locale()->refresh();
        return true;
    }

    /**
     * Test a theme for dependencies and parse errors.
     *
     * @param string $themeName The case-sensitive theme name.
     * @return bool Returns
     * @throws Gdn_UserException Throws an exception when there was an issue testing the theme.
     */
    public function testTheme($themeName) {
        $addon = $this->addonManager->lookupTheme($themeName);
        if (!$addon) {
            throw notFoundException('Plugin');
        }

        try {
            $this->addonManager->checkRequirements($addon, true);
            $addon->test(true);
        } catch (\Exception $ex) {
            throw new Gdn_UserException($ex->getMessage(), $ex->getCode());
        }
        return true;
    }

    /**
     *
     *
     * @return mixed
     */
    public function mobileTheme() {
        return c('Garden.MobileTheme', 'default');
    }

    /**
     *
     *
     * @param $Type
     * @return mixed
     */
    public function themeFromType($Type) {
        if ($Type === 'mobile') {
            return $this->mobileTheme();
        } else {
            return $this->desktopTheme();
        }
    }
}
