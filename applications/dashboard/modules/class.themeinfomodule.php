<?php

class ThemeInfoModule extends Gdn_Module {

    const THEME_PLACEHOLDER_IMAGE_URL = 'applications/dashboard/design/images/theme-placeholder.svg';

    private $iconUrl;
    private $themeName;
    private $description;
    private $version;
    private $author;
    private $authorEmail;
    private $authorUrl;
    private $newVersion;
    private $hasOptions;
    private $themeUrl;
    private $requirements;
    private $isCurrent = false;
    private $infoTag = 'span';
    private $hasUpgrade;


    public function __construct($themeName) {
        $themeInfo = Gdn::themeManager()->getThemeInfo($themeName);
        if ($themeInfo) {
            $this->iconUrl = val('IconUrl', $themeInfo, val('ScreenshotUrl', $themeInfo, asset(self::THEME_PLACEHOLDER_IMAGE_URL)));
            $this->themeName = val('Name', $themeInfo, val('Index', $themeInfo, $themeName));
            $this->description = val('Description', $themeInfo, '');
            $this->version = val('Version', $themeInfo, '');
            $this->author = val('AuthorName', $themeInfo, '');
            $this->authorEmail = val('AuthorEmail', $themeInfo, '');
            $this->authorUrl = val('AuthorUrl', $themeInfo, '');
            $this->newVersion = val('NewVersion', $themeInfo, '');
            $this->hasOptions = !empty(val('Options', $themeInfo, []));
            $this->themeUrl = val('ThemeUrl', $themeInfo, '');
            $this->hasUpgrade = $this->newVersion != '' && version_compare($this->newVersion, $this->version, '>');
            $this->requirements = val('RequiredApplications', $themeInfo, []);
            $this->getInfo();
        } else {
            throwException(sprintf(t('%s not found.'), $themeName));
        }
    }

    /**
     * @return bool|mixed
     */
    public function getIconUrl() {
        return $this->iconUrl;
    }

    /**
     * @param bool|mixed $iconUrl
     */
    public function setIconUrl($iconUrl) {
        $this->iconUrl = $iconUrl;
    }

    /**
     * @return bool|mixed
     */
    public function getThemeName() {
        return $this->themeName;
    }

    /**
     * @param bool|mixed $themeName
     */
    public function setThemeName($themeName) {
        $this->themeName = $themeName;
    }

    /**
     * @return bool|mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param bool|mixed $themeName
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return bool|mixed
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param bool|mixed $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * @return bool|mixed
     */
    public function getAuthor() {
        return $this->author;
    }

    /**
     * @param bool|mixed $author
     */
    public function setAuthor($author) {
        $this->author = $author;
    }

    /**
     * @return bool|mixed
     */
    public function getAuthorEmail() {
        return $this->authorEmail;
    }

    /**
     * @param bool|mixed $authorEmail
     */
    public function setAuthorEmail($authorEmail) {
        $this->authorEmail = $authorEmail;
    }

    /**
     * @return bool|mixed
     */
    public function getAuthorUrl() {
        return $this->authorUrl;
    }

    /**
     * @param bool|mixed $authorUrl
     */
    public function setAuthorUrl($authorUrl) {
        $this->authorUrl = $authorUrl;
    }

    /**
     * @return bool|mixed
     */
    public function getNewVersion() {
        return $this->newVersion;
    }

    /**
     * @param bool|mixed $newVersion
     */
    public function setNewVersion($newVersion) {
        $this->newVersion = $newVersion;
    }

    /**
     * @return boolean
     */
    public function hasOptions() {
        return $this->hasOptions;
    }

    /**
     * @param boolean $hasOptions
     */
    public function setHasOptions($hasOptions) {
        $this->hasOptions = $hasOptions;
    }

    /**
     * @return boolean
     */
    public function hasUpgrade() {
        return $this->hasUpgrade;
    }

    /**
     * @param boolean $hasUpgrade
     */
    public function setHasUpgrade($hasUpgrade) {
        $this->hasUpgrade = $hasUpgrade;
    }

    /**
     * @return bool|mixed
     */
    public function getThemeUrl() {
        return $this->themeUrl;
    }

    /**
     * @param bool|mixed $themeUrl
     */
    public function setThemeUrl($themeUrl) {
        $this->themeUrl = $themeUrl;
    }

    /**
     * @return bool|mixed
     */
    public function getRequirements() {
        return $this->requirements;
    }

    /**
     * @param bool|mixed $requirements
     */
    public function setRequirements($requirements) {
        $this->requirements = $requirements;
    }

    /**
     * @return boolean
     */
    public function isCurrent() {
        return $this->isCurrent;
    }

    /**
     * @param boolean $isCurrent
     */
    public function setIsCurrent($isCurrent) {
        $this->isCurrent = $isCurrent;
    }

    public function getInfo() {
        if ($this->author != '') {
            $info[] = '<'.$this->infoTag.' class="media-meta author">'.sprintf('Created by %s', $this->authorUrl != '' ? anchor($this->author, $this->authorUrl) : $this->author).'</'.$this->infoTag.'>';
        }
        if ($this->version != '') {
            $info[] = '<'.$this->infoTag.' class="media-meta  version">'.sprintf(t('Version %s'), $this->version).'</'.$this->infoTag.'>';
        }
        $required = [];
        $requiredString = '';
        if (!empty($this->requiredApplications)) {
            foreach ($this->requiredApplications as $requiredApplication => $versionInfo) {
                $required[] = printf(t('%1$s Version %2$s'), $requiredApplication, $versionInfo);
            }
        }
        if (!empty($required)) {
            $requiredString .= '<'.$this->infoTag.' class="media-meta requirements">'.t('Requires: ').implode(', ', $required).'</'.$this->infoTag.'>';
        }

        if ($requiredString) {
            $info[] = $requiredString;
        }
        return $info;
    }

    public function getEventString() {
        $this->fireAs('SettingsController');
        $this->fireEvent('AfterCurrentTheme');
    }
}
