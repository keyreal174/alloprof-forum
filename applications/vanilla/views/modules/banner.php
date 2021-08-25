<?php
    if($this->page === 'Categories') {
?>
    <div class="Banner-content Category" style="background-color: <?php echo $this->bgColor; ?>">
        <div class="Category-banner__container">
            <div class="Category-banner__text">
                <h1><strong><?php echo t($this->title); ?></strong></h1>
            </div>
            <div class="Category-banner__img">
                <?php if($this->image) echo '<img src="'.$this->image.'"/>'; ?>
            </div>
        </div>
    </div>
<?php }
    else {
?>
    <div class="Banner-content General <?php echo $this->additionalClass; ?>">
        <div class="Banner-content__Container">
            <p class="Banner-content__description d-desktop">
                <?php echo t("Welcome to the Help Zone! <br/> Do you have a question? Here are the explanations!"); ?>
            </p>
            <p class="Banner-content__description d-mobile">
                <?php echo t("Have questions?<br/> Find the answers here!"); ?>
            </p>
        </div>
    </div>
<?php } ?>
