<?php

class SubMenuModule extends Gdn_Module {
    public $additionalClass;

    public function __construct($sender = '', $applicationFolder = false) {
        parent::__construct($sender, 'Vanilla');
    }

    public function toString() {
        $session = Gdn::session();

        if (!$session->isValid()) {
            return parent::toString();
        }

        return parent::toString();
    }
}
