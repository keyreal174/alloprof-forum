<?php
/**
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Debugger
 */

/**
 * Class DebuggerPlugin
 */
class DebuggerPlugin extends Gdn_Plugin {

    /**
     * Install the debugger database.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Add CSS file to all pages.
     *
     * @param $Sender
     * @param $Args
     */
    public function assetModel_styleCss_handler($Sender, $Args) {
        $Sender->addCssFile('debugger.css', 'plugins/Debugger');
    }

    /**
     * Add Debugger info to frontend.
     *
     * @param $Sender
     */
    public function base_afterBody_handler($Sender) {
        $Session = Gdn::session();
        if (!Debug() || !$Session->checkPermission('Plugins.Debugger.View') || $Sender->MasterView == 'admin') {
            return;
        }

        require $Sender->fetchViewLocation('Debug', '', 'plugins/Debugger');
    }

    /**
     * Add Debugger info to dashboard after content asset.
     *
     * @param $sender
     * @param $args
     */
    public function base_afterRenderAsset_handler($sender, $args) {
        if (val('AssetName', $args) == 'Content' && $sender->MasterView == 'admin') {
            $session = Gdn::session();
            if (!Debug() || !$session->checkPermission('Plugins.Debugger.View')) {
                return;
            }
            require $sender->fetchViewLocation('Debug', '', 'plugins/Debugger');
        }
    }

    /**
     * Register the debug database that captures the queries.
     *
     * This event happens as early as possible so that all queries can be captured.
     *
     * @param Gdn_PluginManager $sender The {@link Gdn_PluginManager} firing the event.
     */
    public function gdn_pluginManager_afterStart_handler($sender) {
        $tmp = Gdn::factoryOverwrite(true);
        Gdn::factoryInstall(Gdn::AliasDatabase, 'Gdn_DatabaseDebug', __DIR__.DS.'class.databasedebug.php', Gdn::FactorySingleton, array('Database'));
        Gdn::factoryOverwrite($tmp);
        unset($tmp);
    }

    /**
     * Build HTML.
     *
     * @param $Data
     * @param string $Indent
     * @return string
     */
    public static function formatData($Data, $Indent = '') {
        $Result = '';
        if (is_array($Data)) {
            foreach ($Data as $Key => $Value) {
                if ($Key === null)
                    $Key = 'NULL';
                $Result .= "$Indent<b>$Key</b>: ";

                if ($Value === null) {
                    $Result .= "NULL\n";
                } elseif (is_numeric($Value) || is_string($Value) || is_bool($Value) || is_null($Value)) {
                    $Result .= htmlspecialchars(var_export($Value, true))."\n";
                } else {
                    if (is_a($Value, 'Gdn_DataSet'))
                        $Result .= "DataSet";

                    $Result .=
                        "\n"
                        .self::formatData($Value, $Indent.'   ');
                }
            }
        } elseif (is_a($Data, 'Gdn_DataSet')) {
            $Data = $Data->result();
            if (count($Data) == 0)
                return $Result.'EMPTY<br />';

            $Fields = array_keys((array)reset($Data));
            $Result .= $Indent.'<b>Count</b>: '.count($Data)."\n"
                .$Indent.'<b>Fields</b>: '.htmlspecialchars(implode(", ", $Fields))."\n";
            return $Result;
        } elseif (is_a($Data, 'stdClass')) {
            $Data = (array)$Data;
            return self::formatData($Data, $Indent);
        } elseif (is_object($Data)) {
            $Result .= $Indent.get_class($Data);
        } else {
            return trim(var_export($Data, true));
        }
        return $Result;
    }

    /**
     *
     *
     * @param $Sender
     */
    public function pluginController_debugger_create($Sender) {
        $Sender->render();
    }

    /**
     *
     */
    public function setup() {
    }

    /**
     *
     */
    public function onDisable() {
        saveToConfig('Debug', FALSE, array('RemoveEmpty' => TRUE));
    }
}
