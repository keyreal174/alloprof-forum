<?php if (!defined('APPLICATION')) exit();
$signinLink = anchor(
    '',
    'entry/signin',
    'SignInPopup Hidden',
    ['rel' => 'nofollow']
);

echo wrap($signinLink, 'span', ['class' => 'MItem SignInLink']);