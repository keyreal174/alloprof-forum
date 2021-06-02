<?php defined('APPLICATION') or die;

class BannerModule extends Gdn_Module {
  public function __construct($page, $title, $additionalClass="", $image="", $bgColor=null, $description="") {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
    $this->page = $page;
    $this->title = $title;
    $this->description = $description;
    $this->image = $image;
    $this->additionalClass = $additionalClass;
    $this->bgColor = $bgColor;
  }

  public function assetTarget() {
    return 'Banner';
  }
}