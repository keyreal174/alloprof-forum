<?php if (!defined('APPLICATION')) exit();
if (!function_exists('writeDiscussionFooter')) {
    include $this->fetchViewLocation('helper_functions', 'discussion', 'vanilla');
    include $this->fetchViewLocation('helper_functions', 'discussions', 'vanilla');
}
?>
<?php echo "<h1 class='sr-only'>" . t('Search') . "</h1>" ?>
<?php
if (!count($this->data('SearchResults')) && $this->data('SearchTerm')){
        echo '<p class="NoResults d-desktop">',
            '<a class="back-btn" href="/discussions"><svg width="24" height="15" viewBox="0 0 24 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.5 7.36426H1.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7.88642 13.7282L1.52246 7.36426L7.88642 1.0003" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg></a>',
            sprintf(t('No results for %s.', 'No results for <b>%s</b>.'), $this->data('SearchTerm')), '</p>';
    ?>
        <div class="no-result__content">
            <img src="<?php echo url('/themes/alloprof/design/images/Horreur.svg'); ?>"/>
            <p class="d-desktop desc">
                <?php echo sprintf(t('There are no results for "%s".', 'There are no results for "%s".'), $this->data('SearchTerm')); ?> <br/>
                <?php echo t("Can't find an explanation? ask your question!"); ?>
            </p>
            <h2 class="d-mobile"><?php echo t('No results'); ?></h2>
            <p class="d-mobile">
                <?php echo sprintf(t('There are no results for :<br/> "%s"', 'There are no results for : <br/> "%s"'), $this->data('SearchTerm')); ?> <br/>
            </p>
            <span class="d-mobile">
                <a href="<?php echo url('/post/newQuestionPopup');?>" class="btn-default btn-shadow ask-btn QuestionPopup"><?php echo t('Ask a question'); ?></a>
            </span>
        </div>
    <?php
}
else
    echo '<p class="SearchResult">', sprintf(t('%d results for "%s"'), $this->data('CountDiscussions'), $this->data('SearchTerm')), '</p>';
?>
    <ul id="search-results" class="DataList Discussions" start="<?php echo $this->data('From'); ?>">

        <?php
            foreach ($this->data('SearchResults') as $Row)
                writeDiscussionDetail($Row, $this, Gdn::session());
        ?>
    </ul>

<?php
echo '<div class="PageControls Bottom">';

$RecordCount = $this->data('RecordCount');
if ($RecordCount) {
    echo '<span class="Gloss">'.plural($RecordCount, '%s result', '%s results').'</span>';
}

PagerModule::write(['Wrapper' => '<div %1$s>%2$s</div>']);

echo '</div>';
