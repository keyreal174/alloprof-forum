<?php /** @var MediaItemModule $this */ ?>
<div <?php echo attribute($this->getAttributes()); ?>>
    <?php if ($this->getImageHtml()) { ?>
        <div class="media-left">
            <div class="media-image-wrap">
                <?php echo $this->getImageHtml(); ?>
            </div>
        </div>
    <?php } ?>
    <div class="media-body">
        <div class="media-heading">
            <div class="media-title">
                <?php echo $this->getTitleUrl() != '' ? anchor($this->getTitle(), $this->getTitleUrl()) : $this->getTitle(); ?>
            </div>
            <?php if ($this->getMeta()) { ?>
                <div class="info">
                    <?php echo implode('<span class="spacer">•</span>', $this->getMeta()); ?>
                </div>
            <?php } ?>
        </div>
        <div class="media-description">
            <div class="description"><?php echo $this->getDescription(); ?></div>
        </div>
    </div>
    <div class="media-right media-options">
        <?php foreach($this->getButtons() as $button) { ?>
            <div class="btn-wrap">
                <a <?php echo attribute(val('attributes', $button)); ?> href="<?php echo val('url', $button); ?>">
                    <?php echo val('text', $button); ?>
                </a>
            </div>
        <?php } ?>
        <?php if ($this->getToggle()) {
            echo $this->getToggleHtml();
        } ?>
    </div>
</div>
