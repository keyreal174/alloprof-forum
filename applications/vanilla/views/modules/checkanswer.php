<div class="BoxCheckAnswer">
    <h2><?php echo t("Did you find the answer to your question?") ?></h2>
    <div class="BoxCheckAnswer-answers">
        <a class="FeedbackPerfect">
            <img src="<?= url('/themes/alloprof/design/images/peace.svg') ?>" width="80px" height="80px" />
            <span> <?php echo t("I sure did!") ?> </span>
        </a>
        <a href=<?php echo url("/discussion/bad"); ?> class="OptionsLink Popup FeedbackHelp">
            <img src="<?= url('/themes/alloprof/design/images/neutre.svg') ?>" width="80px" height="80px" />
            <span> <?php echo t("I still need help") ?> </span>
        </a>
    </div>
</div>