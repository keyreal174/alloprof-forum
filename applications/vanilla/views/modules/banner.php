<?php
    if($this->page === 'Categories') {
?>
    <div class="Banner-content">
        <?php if($this->image) echo '<img class="Banner-content__background" src="'.$this->image.'"/>'; ?>
        <div class="Container">
            <p class="Banner-content__pagename"><?php echo t($this->breadcrumb); ?></p>
            <h1><strong><?php echo t($this->title2); ?></strong></h1>
        </div>
    </div>
<?php }
    else {
?>
    <div class="Banner-content General" style="<?php
        if ($this->BackgroundImage) echo 'background-image: url('.$this->BackgroundImage.');';
        if ($this->BackgroundColor) echo 'background-color: '.$this->BackgroundColor.';';
     ?>">
        <div class="Container">
            <p class="Banner-content__pagename"><?php echo t($this->breadcrumb); ?></p>
            <h1><?php echo t($this->title1); ?></h1>
            <h1><strong><?php echo t($this->title2); ?></strong></h1>
            <p class="Banner-content__description"><?php echo t($this->description); ?></p>
        </div>
    </div>
<?php } ?>
