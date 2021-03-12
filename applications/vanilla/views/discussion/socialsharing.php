<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$Discussion = $this->data('Discussion');
$Title = $this->data('Title');
$SubTitle = $this->data('SubTitle');
$CopySubTitle = $this->data('CopySubTitle');
?>
    <div>
    <img src='<?= url("/themes/alloprof/design/images/sharingAvatar.svg") ?>'' alt='image' class='FlagAvatar' />
    </div>
    <h2><?php echo t($Title); ?></h2>
    <p class='SubTitle'><?php echo t($SubTitle); ?></p>
    <p class='CopySubTitle Hidden'><?php echo t($CopySubTitle); ?></p>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
<?php
echo '<p class="LinkCopiedText Hidden">'.t("Link copied!").'</p>';
echo '<div class="SocialSharingButtons">';
echo '<a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='.$Discussion["CanonicalUrl"].';src=sdkpreparse" class="fb-share-button">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkFacebook.svg").'" />
        Facebook
    </a>';
echo '<a target="_blank" class="twitter-share-button" href="https://twitter.com/intent/tweet?url='.$Discussion["CanonicalUrl"].'&via=alloprof" data-size="large">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkTwitter.svg").'" />
        Twitter
    </a>';
echo '<a href="whatsapp://send?text='.urlencode($Discussion["CanonicalUrl"]).'" data-action="share/whatsapp/share" target="_blank" class="whatsapp-share-button">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkWhatsApp.svg").'" />
        Whatsapp
    </a>';
echo '<a href="javascript:void();" class="copy-button" id="clickCopy" disId="'.$Discussion["DiscussionID"].'" value="'.$Discussion["CanonicalUrl"].'">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkCopy.svg").'" />
        '.t("Copy link").'
    </a>';
echo '</div>';
echo $this->Form->close();
?>
