<?php defined('APPLICATION') or die;

class BannerModule extends Gdn_Module {
  public function __construct($page, $breadcrumb, $title1="Home", $image="") {
    parent::__construct();
    $this->page = $page;
    $this->breadcrumb = $breadcrumb;
    $this->title1 = $title1;
    $this->image = $image;
  }

  public function assetTarget() {
    return 'Banner';
  }
}