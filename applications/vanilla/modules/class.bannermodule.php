<?php defined('APPLICATION') or die;

class BannerModule extends Gdn_Module {
  public function __construct($page, $breadcrumb, $title1="", $title2="", $description="", $image="", $backgroundImage="", $backgroundColor="") {
    parent::__construct();
    $this->_ApplicationFolder = 'vanilla';
    $this->page = $page;
    $this->breadcrumb = $breadcrumb;
    $this->title1 = $title1;
    $this->title2 = $title2;
    $this->description = $description;
    $this->image = $image;
    $this->BackgroundImage = $backgroundImage;
    $this->BackgroundColor = $backgroundColor;
  }

  public function assetTarget() {
    return 'Banner';
  }
}