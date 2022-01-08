<?php
    if($this->page === 'Categories') {
?>
<div class="Frame-banner Banner" style="background-color: <?php echo $this->bgColor; ?>">
    <div class="Banner-content Category">
        <div class="Category-banner__container">
            <div class="Category-banner__text">
                <h1><strong><?php echo t($this->title); ?></strong></h1>
            </div>
            <div class="Category-banner__img">
                <?php if($this->image) echo '<img src="'.$this->image.'"/>'; ?>
            </div>
        </div>
    </div>
</div>
<?php }
    else {
        if ($this->bgColor) {
?>
<div class="Frame-banner Banner" style="background-color: <?php echo $this->bgColor; ?>">
    <?php
        } else {
?>
    <div class="Frame-banner Banner">
        <?php }  ?>
        <div class="Banner-content General <?php echo $this->additionalClass; ?>">
            <div class="Banner-content__Container">
                <p class="Banner-content__description d-desktop">
                    <?php echo t("Welcome to the Help Zone! <br/> Have questions? Find the answers here!"); ?>
                </p>
                <p class="Banner-content__description d-mobile">
                    <?php echo t("Have questions?<br/> Find the answers here!"); ?>
                </p>
            </div>
        </div>
    </div>
    <?php } ?>