<?php defined('APPLICATION') or die;

class MobileHeaderModule extends Gdn_Module {
  public function __construct($title='', $back=false) {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
    $this->title = $title;
    $this->back = $back;
  }
  public function assetTarget() {
    return 'MobileHeader';
  }
}