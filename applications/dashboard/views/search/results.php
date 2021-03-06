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
            <li class="Item Item-Search">
                <div class="Item-Body Media">
                    <?php
                    $category = CategoryModel::categories($Row->CategoryID);
                    if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                        ?>
                        <span class="Options-Icon">
                        <?php
                            echo optionsList($Row);
                        ?>
                        </span>
                        <?php
                    }
                    ?>
                    <div class="AuthWrapper">
                        <?php echo "<img src='".$Row->FirstPhoto."' alt='photo' />" ?>
                        <div class="AuthDate">
                            <span class="UserName"><?php echo $Row->FirstName ?></span>
                            <div>
                            <?php
                                if ($Row->GradeID) {
                                    echo ' <span class="MItem-Grade">'.
                                    t(getGrade($Row->GradeID)).
                                    ' • </span> ';
                                    echo ' <span class="MItem-DateInserted">'.
                                    t(timeElapsedString($Row->DateInserted)).
                                    '</span> ';
                                } else {
                                    echo ' <span class="MItem-DateInserted">'.
                                    t(timeElapsedString($Row->DateInserted)).
                                    '</span> ';
                                }
                            ?>
                            </div>
                        </div>
                        <?php
                            if ($Row->DateAccepted) {
                                echo "<div class='verfied-badge'>
                                        <img src='/themes/alloprof/design/images/icons/verifiedbadge.svg'/>
                                        <span>". t('Verified by Alloprof') ."</span>
                                    </div>";
                            }
                        ?>
                    </div>
                    <div class="Media-Body">
                        <div class="Summary">
                            <?php echo formatBody($Row); ?>
                        </div>
                        <?php
                        $Count = val('Count', $Row);

                        if (($Count) > 1) {
                            $url = $this->data('SearchUrl').'&discussionid='.urlencode($Row->DiscussionID).'#search-results';
                            echo '<div>'.anchor(plural($Count, '%s result', '%s results'), $url).'</div>';
                        }
                        ?>
                    </div>
                </div>
                <div class='SearchResultCategory'>
                    <?php
                        echo "<a class='DiscussionHeader_category' href='/categories/".$category["UrlCode"]."'>".$category["Name"]."</a>";
                    ?>
                </div>
                <?php
                    writeDiscussionFooter($Row, $this, 'search');
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
