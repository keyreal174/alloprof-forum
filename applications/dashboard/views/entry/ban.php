
<?php
    // $socialLink = anchor(
    //     '',
    //     'entry/banmodal',
    //     'BanPopup Hidden',
    //     ['rel' => 'nofollow']
    // );

    // echo wrap($socialLink, 'span', ['class' => 'MItem BanLink']);
?>


<?php if (!defined('APPLICATION')) exit(); ?>
<div class="PageNotFound">
    <div class="Content">
    <div class="Center SplashInfo">
        <img src=<?php echo url("/themes/alloprof/design/images/ban.svg") ?> />
        <h1><?php echo t('Account suspended'); ?></h1>

        <div id="Message"><?php echo t('Your account has been suspended because it violated the site rules. <br/> You can get help or additional information by contacting support.'); ?></div>

        <a href="https://alloprof-ti.atlassian.net/servicedesk/customer/portal/6" class="btn btn-default btn-shadow"><?php echo t("Contact support") ?></a>
    </div>
    </div>
</div>
