<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Vanilla
 * @since 2.0.17.9
 */

/**
 * Handles the /connexionenseignant endpoint.
 */
class ConnexionenseignantController extends VanillaController {
    public function __construct() {
        parent::__construct();
    }

    public function index($error=null) {
        if (Gdn::session()->isValid()) {
            redirectTo('/');
        }

        if ($error == 'banned') {
            $this->addJsFile('teachersignin_banned.js');
        }  else {
            $this->addJsFile('teachersignin.js');
        }
        return $this->render();
    }
}
