<?php
    if($this->page === 'Categories') {
?>
    <div class="Banner-content">
        <?php if($this->image) echo '<img class="Banner-content__background" src="'.$this->image.'"/>'; ?>
        <div class="Banner-content__Container">
            <!-- <p class="Banner-content__pagename"><?php // echo t($this->breadcrumb); ?></p> -->
            <!-- <h1><strong><?php echo t($this->title2); ?></strong></h1> -->
        </div>
    </div>
<?php }
    else {
?>
    <div class="Banner-content General <?php echo $this->additionalClass; ?>">
        <div class="Banner-content__Container">
            <!-- <p class="Banner-content__pagename"><?php echo t($this->breadcrumb); ?></p> -->
            <!-- <h1><strong><?php echo t($this->title2); ?></strong></h1> -->
            <p class="Banner-content__description d-desktop">
                <?php echo t("Welcome to the Mutual Aid Zone! <br/> Do you have a question? Here are the explanations!"); ?>
            </p>
            <p class="Banner-content__description d-mobile">
                <?php echo t("Do you have a question?<br/> Here are the explanations!"); ?>
            </p>
        </div>
    </div>
<?php } ?>
