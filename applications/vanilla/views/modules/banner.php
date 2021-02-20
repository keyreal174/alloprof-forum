<?php
    if($this->page === 'Home') {
?>
    <div class="Banner-content General">
        <div class="Container">
            <p class="Banner-content__pagename"><?php echo t($this->breadcrumb); ?></p>
            <h1>Welcome in</h1>
            <h1><strong>the Mutual Aid Zone,</strong></h1>
            <p class="Banner-content__description">Do you have a question? Here are the explanations!</p>
        </div>
    </div>
<?php }
    else {
?>
    <div class="Banner-content">
        <?php if($this->image) echo '<img class="Banner-content__background" src="'.$this->image.'"/>'; ?>
        <div class="Container">
            <p class="Banner-content__pagename"><?php echo t($this->breadcrumb); ?></p>
            <h1><strong><?php echo t($this->title1); ?></strong></h1>
        </div>
    </div>
<?php } ?>
