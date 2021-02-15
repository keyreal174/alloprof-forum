<?php if (!defined('APPLICATION')) exit(); ?>
<?php echo "<h1 class='sr-only'>" . t('Search') . "</h1>" ?>
<?php if (!count($this->data('SearchResults')) && $this->data('SearchTerm'))
    echo '<p class="NoResults">', sprintf(t('No results for %s.', 'No results for <b>%s</b>.'), $this->data('SearchTerm')), '</p>';
?>
    <ol id="search-results" class="DataList DataList-Search" start="<?php echo $this->data('From'); ?>">
        <?php foreach ($this->data('SearchResults') as $Row): ?>
            <li class="Item Item-Search">
                <div class="Item-Body Media">
                    <div class="AuthWrapper">
                        <?php echo "<img src='".$Row['Photo']."' alt='photo' />" ?>
                        <div class="AuthDate">
                            <span><?php echo $Row['Name'] ?></span>
                            <?php echo ' <span class="MItem-DateInserted">'.
                                Gdn_Format::date($Row['DateInserted'], 'html').
                                '</span> '; ?>
                        </div>
                    </div>
                    <div class="Media-Body">
                        <div class="Summary">
                            <?php echo $Row['Summary']; ?>
                        </div>
                        <?php
                        $Count = val('Count', $Row);

                        if (($Count) > 1) {
                            $url = $this->data('SearchUrl').'&discussionid='.urlencode($Row['DiscussionID']).'#search-results';
                            echo '<div>'.anchor(plural($Count, '%s result', '%s results'), $url).'</div>';
                        }
                        ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>

<?php
echo '<div class="PageControls Bottom">';

$RecordCount = $this->data('RecordCount');
if ($RecordCount) {
    echo '<span class="Gloss">'.plural($RecordCount, '%s result', '%s results').'</span>';
}

PagerModule::write(['Wrapper' => '<div %1$s>%2$s</div>']);

echo '</div>';
