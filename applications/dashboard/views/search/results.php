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
            <li class="Item Item-Search" data-url="<?php echo $Row->Url; ?>">
                <?php
                    if ($Row->DateAccepted) {
                        echo "<div class='verified-info mobile'>
                            <img src='".url("/themes/alloprof/design/images/icons/verifiedbadge.svg")."'/><span>".t("Verified by Alloprof")."</span></div>";
                    }
                ?>
                <div class="Item-Body Media">
                    <?php
                    $category = CategoryModel::categories($Row->CategoryID);
                    if (!Gdn::themeFeatures()->get('EnhancedAccessibility')) {
                        ?>
                        <span class="Options-Icon DisableClick">
                        <?php
                            echo optionsList($Row);
                        ?>
                        </span>
                        <?php
                    }
                    ?>
                    <div class="AuthWrapper">
                        <?php
                            if(!$Row->Published) {
                                echo '<div class="not-published-badge DisableClick desktop">';
                                echo '<img src="'.url("/themes/alloprof/design/images/icons/eyebreak.svg").'"/>';
                                echo t('Awaiting publication');
                                echo '</div>';
                            }
                        ?>
                        <?php
                            $User = Gdn::userModel()->getID($Row->InsertUserID);
                            echo '<span class="DisableClick">';
                            echo userPhoto($User);
                            echo '</span>';
                        ?>
                        <div class="AuthDate DisableClick">
                            <span class="UserName"><?php echo userAnchor($User); ?></span>
                            <div>
                            <?php
                                if (getGrade($Row->GradeID)) {
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
                                echo "<div class='verified-badge'>
                                        <img src='".url('/themes/alloprof/design/images/icons/verifiedbadge.svg')."'/>
                                        <span>". t('Verified by Alloprof') ."</span>
                                    </div>";
                            }
                        ?>
                    </div>
                    <div class="Media-Body">
                        <div class="Summary">
                            <div class="MessageWrapper">
                                <?php echo formatBody($Row); ?>
                            </div>
                        </div>
                        <?php
                            $this->fireEvent('AfterDiscussionBody');
                            if (val('Attachments', $Row)) {
                                writeAttachments($Row->Attachments);
                            }
                        ?>
                        <?php
                        $Count = val('Count', $Row);

                        if (($Count) > 1) {
                            $url = $this->data('SearchUrl').'&discussionid='.urlencode($Row->DiscussionID).'#search-results';
                            echo '<div>'.anchor(plural($Count, '%s result', '%s results'), $url).'</div>';
                        }
                        ?>
                    </div>
                </div>
                <div class='SearchResultCategory DisableClick DisableClickWrapper'>
                    <?php
                        echo "<a class='DiscussionHeader_category' style='background: ".$category["Color"]."' href='/categories/".$category["UrlCode"]."'>".$category["Name"]."</a>";
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
