<?php defined('APPLICATION') or die;

class AskQuestionModule extends Gdn_Module {
  public function __construct() {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
  }
  public function assetTarget() {
    return 'Extra';
  }
}