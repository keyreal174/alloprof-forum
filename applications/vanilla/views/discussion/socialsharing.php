<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$Discussion = $this->data('Discussion');
$DesktopTitle = $this->data('DesktopTitle');
$MobileTitle = $this->data('MobileTitle');
$SubTitle = $this->data('SubTitle');
$CopySubTitle = $this->data('CopySubTitle');
?>
    <div>
    <img src='<?= url("/themes/alloprof/design/images/sharingAvatar.svg") ?>'' alt='image' class='FlagAvatar' />
    </div>
    <h2 class="d-desktop"><?php echo t($DesktopTitle); ?></h2>
    <h2 class="d-mobile"><?php echo t($MobileTitle); ?></h2>
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
        <span>Facebook</span>
    </a>';
echo '<a target="_blank" class="twitter-share-button" href="https://twitter.com/intent/tweet?url='.$Discussion["CanonicalUrl"].'&via=alloprof" data-size="large">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkTwitter.svg").'" />
        <span>Twitter</span>
    </a>';
echo '<a href="whatsapp://send?text='.urlencode($Discussion["CanonicalUrl"]).'" data-action="share/whatsapp/share" target="_blank" class="whatsapp-share-button">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkWhatsApp.svg").'" />
        <span>Whatsapp</span>
    </a>';
echo '<a href="javascript:void();" class="copy-button" id="clickCopy" disId="'.$Discussion["DiscussionID"].'" value="'.$Discussion["CanonicalUrl"].'">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkCopy.svg").'" />
        <span>'.t("Copy link").'</span>
    </a>';
echo '</div>';
echo $this->Form->close();
?>
