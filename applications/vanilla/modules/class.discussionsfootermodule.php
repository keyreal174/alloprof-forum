<?php defined('APPLICATION') or die;

class DiscussionsFooterModule extends Gdn_Module {
  public function __construct($Empty = False, $Text1 = "", $Text2 = "") {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
    $this->Empty = $Empty;
    $this->Text1 = $Text1;
    $this->Text2 = $Text2;
  }
  public function assetTarget() {
    return 'Content';
  }
}