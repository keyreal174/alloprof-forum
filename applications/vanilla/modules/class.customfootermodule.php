<?php defined('APPLICATION') or die;

class CustomFooterModule extends Gdn_Module {
  public function __construct() {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
  }
  public function assetTarget() {
    return 'CustomFooter';
  }
}