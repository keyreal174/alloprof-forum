<ul class="DataList SearchResults">
<?php
if (is_array($this->SearchResults) && count($this->SearchResults) > 0) {
	foreach ($this->SearchResults as $Key => $Row) {
		$Row = (object)$Row;
		$this->EventArguments['Row'] = $Row;
?>
	<li class="Item">
		<?php $this->FireEvent('BeforeItemContent'); ?>
		<div class="ItemContent">
			<?php echo Anchor(Gdn_Format::Text($Row->Title), $Row->Url, 'Title'); ?>
			<div class="Excerpt"><?php
				echo Anchor(nl2br(SliceString(Gdn_Format::Text($Row->Summary, FALSE), 250)), $Row->Url);
			?></div>
			<div class="Meta">
				<span><?php printf(T('by %s'), UserAnchor($Row)); ?></span>
				<span><?php echo Gdn_Format::Date($Row->DateInserted); ?></span>
            <?php
            if (isset($Row->CategoryID)) {
               $Category = CategoryModel::Categories($Row->CategoryID);
               if ($Category !== NULL) {
                  $Url = Url('categories/'.$Category['UrlCode']);
                  echo "<span><a class='Category' href='{$Url}'>{$Category['Name']}</a></span>";
               }
            }
            ?>
				<span><?php echo Anchor(T('permalink'), $Row->Url); ?></span>
			</div>
		</div>
	</li>
<?php
	}
}
?>
</ul>