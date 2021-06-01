<?php if (!defined('APPLICATION')) exit(); ?>

<div class="Center SplashInfo">
<img src=<?php echo url("/themes/alloprof/design/images/fileNotFound.svg") ?> />
    <h1><?php echo t('Oh no! It\'s a 404 error'); ?></h1>

    <div id="Message"><?php echo t('The page you\'re looking for has disappeared!'); ?></div>

    <a href=<?php echo url("/discussions"); ?> class="btn btn-default btn-shadow"><?php echo t("Return to Home") ?></a>
</div>

<?php if (debug() && $this->data('Trace')): ?>
<h2 class="Trace-Title">Trace</h2>
<pre class="Trace"><?php echo htmlspecialchars($this->data('Trace')); ?></pre>
<?php endif; ?>
