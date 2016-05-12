<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<?php
$nav = [
    'Dashboard' => 'Overview, Updates',
    'Moderation' => 'Gate Keeping',
    'Settings' => 'Preferences & Addons',
    'Analytics' => 'Eye Candy For Your Boss'
];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo htmlspecialchars(Gdn::locale()->Locale); ?>">
<head>
    <?php $this->renderAsset('Head'); ?>
    <!-- Robots should not see the dashboard, but tell them not to index it just in case. -->
    <meta name="robots" content="noindex,nofollow"/>
</head>
<body id="<?php echo htmlspecialchars($BodyIdentifier); ?>" class="<?php echo htmlspecialchars($this->CssClass); ?>">
<div class="navbar ">
    <?php $title = c('Garden.Title'); ?>
    <?php if ($logo = c('Garden.Logo', false)) { ?>
    <div class="navbar-brand navbar-image logo"><?php echo img(Gdn_Upload::url($logo), array('alt' => $title));?></div>
    <?php } else { ?>
    <h1 class="navbar-brand "><?php echo anchor($title, '/'); ?></h1>
    <?php } ?>
    <div class="btn btn-navbar"><?php echo anchor(t('Visit Site'), '/'); ?></div>

    <nav class="nav nav-pills">
        <?php
        foreach ($nav as $navHeading => $navDescription) { ?>
            <div class="nav-item">
                <a class="nav-link" href="#">
                    <div class="nav-link-heading"><?php echo $navHeading; ?></div>
                    <div class="nav-link-description"><?php echo $navDescription; ?></div>
                </a>
            </div>
        <?php } ?>
    </nav>
    <?php
    if (Gdn::session()->isValid()) {
        $this->fireEvent('BeforeUserOptionsMenu');
        $photo = userPhoto(Gdn::session()->User);
        $CountNotifications = Gdn::session()->User->CountNotifications;
        if (is_numeric($CountNotifications) && $CountNotifications > 0) {
            $photo .= wrap($CountNotifications);
        }

        echo userPhoto(Gdn::session()->User, 'Profile');
        echo anchor(t('Sign Out'), SignOutUrl(), 'btn btn-navbar Leave');
    }
    ?>

</div>
<div class="container-fluid">
    <div class="row">
        <?php if($this->data('HelpModule') || true) { ?>
            <div class="col-sm-3 col-md-2 panel panel-nav panel-left">
                <?php $this->renderAsset('Panel'); ?>
            </div>
            <div class="col-sm-6 col-md-8 main">
                <?php $this->renderAsset('Content'); ?>
            </div>
            <div class="col-sm-3 col-md-2 panel panel-help panel-right">
                <?php echo $this->data('HelpModule'); ?>
            </div>
        <?php } else { ?>
            <div class="col-sm-3 col-md-2 panel panel-nav panel-left">
                <?php $this->renderAsset('Panel'); ?>
            </div>
            <div class="col-sm-9 col-md-10 main">
                <?php $this->renderAsset('Content'); ?>
            </div>
        <?php } ?>
    </div>
</div>
<div class="container footer">
    <div class="row">
    <?php
    $this->renderAsset('Foot');
    echo '<div class="Version">Version ', APPLICATION_VERSION, '</div>';
    echo wrap(anchor(img('/applications/dashboard/design/images/logo_footer.png', array('alt' => 'Vanilla Forums')), c('Garden.VanillaUrl')), 'div');
    ?>
    </div>
</div>
<?php $this->fireEvent('AfterBody'); ?>
</body>
</html>
