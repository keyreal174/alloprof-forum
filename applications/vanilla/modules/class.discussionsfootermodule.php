<?php defined('APPLICATION') or die;

class DiscussionsFooterModule extends Gdn_Module {
  public function __construct($Empty = False) {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
    $this->Empty = $Empty;
  }
  public function assetTarget() {
    return 'Content';
  }
}