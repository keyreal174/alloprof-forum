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
     * Output locale links.
     *
     * @return string|void
     */
    public function toString() {
        if (!$this->Links)
            $this->Links = $this->buildLocales();

        $select = wrap($this->Links, 'select', ['class' => 'LocaleSelect', 'value' => $currentLocale]);

        echo wrap($select, 'form', ['id' => 'LocaleSelectForm', 'method' => 'POST']);
    }
}