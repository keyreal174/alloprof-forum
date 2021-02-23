<?php defined('APPLICATION') or die;

class CheckAnswerModule extends Gdn_Module {
    public function __construct() {
        parent::__construct();
        $this->_ApplicationFolder = 'vanilla';
    }
    public function assetTarget() {
        return 'Content';
    }
    public function toString() {
        return parent::toString();
    }
}