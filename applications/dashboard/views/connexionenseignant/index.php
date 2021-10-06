<?php if (!defined('APPLICATION')) exit();
$signinLink = anchor(
    '',
    'entry/signin',
    'SignInPopup TeacherSigninPopup Normal Hidden',
    ['rel' => 'nofollow']
);

$signinBannedLink = anchor(
    '',
    'entry/signin/0/0/banned',
    'SignInPopup TeacherSigninPopup Banned Hidden',
    ['rel' => 'nofollow']
);

echo wrap($signinLink, 'span', ['class' => 'MItem SignInLink']);
echo wrap($signinBannedLink, 'span', []);