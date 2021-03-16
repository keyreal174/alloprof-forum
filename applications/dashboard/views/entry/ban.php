
<?php if (!defined('APPLICATION')) exit(); ?>
<?php
    $socialLink = anchor(
        '',
        'entry/banmodal',
        'BanPopup Hidden',
        ['rel' => 'nofollow']
    );

    echo wrap($socialLink, 'span', ['class' => 'MItem BanLink']);
?>
