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
    echo '<p class="SearchResult">', sprintf(t('%d results for "%s"'), count($this->data('SearchResults')), $this->data('SearchTerm')), '</p>';
?>
    <ol id="search-results" class="DataList DataList-Search" start="<?php echo $this->data('From'); ?>">
        <?php foreach ($this->data('SearchResults') as $Row): ?>
            <?php $dis = $this->getDiscusson($Row['DiscussionID']); ?>
            <li class="Item Item-Search">
                <div class="Item-Body Media">
                    <?php
                    if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                        ?>
                        <span class="Options-Icon">
                        <?php
                            echo optionsList($dis);
                        ?>
                        </span>
                        <?php
                    }
                    ?>
                    <div class="AuthWrapper">
                        <?php echo "<img src='".$Row['Photo']."' alt='photo' />" ?>
                        <div class="AuthDate">
                            <span class="UserName"><?php echo $Row['Name'] ?></span>
                            <div>
                            <?php
                                echo ' <span class="MItem-Grade">'.
                                t(getGrade($dis['GradeID'])).
                                ' • </span> ';
                                echo ' <span class="MItem-DateInserted">'.
                                t(timeElapsedString($Row['DateInserted'])).
                                '</span> ';
                            ?>
                            </div>
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
                <?php
                    writeDiscussionFooter($dis, $this, 'search');
                ?>
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
