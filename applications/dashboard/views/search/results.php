<?php if (!defined('APPLICATION')) exit();
if (!function_exists('writeDiscussionFooter')) {
    include $this->fetchViewLocation('helper_functions', 'discussion', 'vanilla');
    include $this->fetchViewLocation('helper_functions', 'discussions', 'vanilla');
}
?>
<?php echo "<h1 class='sr-only'>" . t('Search') . "</h1>" ?>
<?php
if (!count($this->data('SearchResults')) && $this->data('SearchTerm'))
    echo '<p class="NoResults">', sprintf(t('No results for %s.', 'No results for <b>%s</b>.'), $this->data('SearchTerm')), '</p>';
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
