<?php defined('APPLICATION') or die;

class SetLocaleModule extends Gdn_Module {
    /** @var string HTML links to activate locales. */
    public $Links = '';

    public function assetTarget() {
        return 'Panel';
    }

    /**
     * Build footer link to change locale.
     */
    public function buildLocaleLink($name, $urlCode) {
        $url = '/profile/setlocale/'.$urlCode;

        return wrap(anchor($name, $url, 'js-hijack'), 'option', ['class' => 'LocaleOption '.$name.'Locale', 'data-url' => $url, 'value' => $urlCode]);
    }

    /**
     * Return HTML links of all active locales.
     *
     * @return string HTML.
     */
    public function buildLocales() {
        $locales = MultilingualPlugin::enabledLocales();

        $links = '';
        foreach ($locales as $code => $name) {
            $links .= $this->buildLocaleLink($name, $code);
        }

        return $links;
    }

    /**
     * Confirm selected locale is valid and available.
     *
     * @param string $locale Locale code.
     * @return string Returns the canonical version of the locale on success or an empty string otherwise.
     */
    protected function validateLocale($locale) {
        $canonicalLocale = Gdn_Locale::canonicalize($locale);
        $locales = static::enabledLocales();

        $result = isset($locales[$canonicalLocale]) ? $canonicalLocale : '';
        return $result;
    }

    /**
     * Get user preference or queried locale.
     */
    public function getAlternateLocale() {
        $locale = false;

        // User preference
        if (Gdn::session()->isValid()) {
            $locale = Gdn::userMetaModel()->getUserMeta(Gdn::session()->UserID, 'Plugin.Multilingual.Locale', false);
            $locale = $locale["Plugin.Multilingual.Locale"];
        }
        // Query string
        // if (!$locale) {
        //     $locale = $this->validateLocale(Gdn::request()->get('locale'));
        //     if ($locale) {
        //         Gdn::session()->stash('Locale', $locale);
        //     }
        // }
        // Session
        if (!$locale) {
            $locale = Gdn::session()->stash('Locale', '', false);
        }

        return $locale;
    }

    /**
     * Output locale links.
     *
     * @return string|void
     */
    public function toString() {
        if (!$this->Links)
            $this->Links = $this->buildLocales();

        $currentLocale = $this->getAlternateLocale();

        $select = wrap($this->Links, 'select', ['class' => 'LocaleSelect', 'value' => $currentLocale]);

        echo wrap($select, 'form', ['id' => 'LocaleSelectForm', 'method' => 'POST']);
    }
}