<?php if (!defined('APPLICATION')) exit(); ?>
<?php
$Title = sprintf("Share your question!");
$SubTitle = sprintf("Asking yourself the same question? Share it with your friends, they may have the explanation!");
?>
    <div>
    <img src='/themes/alloprof/design/images/sharingAvatar.svg' alt='image' class='FlagAvatar' />
    </div>
    <h2><?php echo t($Title); ?></h2>
    <h4><?php echo t($SubTitle); ?></h4>
<?php
echo $this->Form->open();
echo $this->Form->errors();
?>
<?php
echo '<div class="SocialSharingButtons">';
echo '<div class="fb-share-button" data-href="http://dev.vanilla.localhost/discussion/10/question" data-layout="button" data-size="large"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=http://dev.vanilla.localhost/discussion/10/question;src=sdkpreparse" class="fb-xfbml-parse-ignore">Share</a></div>';
echo '</div>';
echo $this->Form->close();
?>
