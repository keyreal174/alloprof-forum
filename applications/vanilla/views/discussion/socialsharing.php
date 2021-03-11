<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$Discussion = $this->data('Discussion');
$Title = sprintf("Share your question!");
$SubTitle = sprintf("Asking yourself the same question? Share it with your friends, they may have the explanation!");
?>
    <div>
    <img src='<?= url("/themes/alloprof/design/images/sharingAvatar.svg") ?>'' alt='image' class='FlagAvatar' />
    </div>
    <h2><?php echo t($Title); ?></h2>
    <p><?php echo t($SubTitle); ?></p>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
<?php
echo '<div class="SocialSharingButtons">';
echo '<div class="fb-share-button" data-href="'.$Discussion["CanonicalUrl"].'" data-layout="button" data-size="large"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='.$Discussion["CanonicalUrl"].';src=sdkpreparse" class="fb-xfbml-parse-ignore">Share</a></div>';
echo '<a class="twitter-share-button"
        href="https://twitter.com/intent/tweet?url='.$Discussion["CanonicalUrl"].'&via=alloprof"
        data-size="large">
        Tweet
    </a>';
echo '<a href=
"whatsapp://send?text=GFG Example for whatsapp sharing"
        data-action="share/whatsapp/share"
        target="_blank">
        <img src="'.url("/themes/alloprof/design/images/icons/ShareNetworkWhatsApp.svg").'" />
        Whatsapp
    </a>';
echo '</div>';
echo $this->Form->close();
?>
