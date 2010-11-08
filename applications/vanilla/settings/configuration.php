<?php if (!defined('APPLICATION')) exit();
/**
 * Vanilla default configuration options.
 *
 * DO NOT EDIT THIS FILE!
 * All of the settings defined here can be overridden in the /conf/config.php file.
 * Called by VanillaHooks::Setup() to add config options upon enabling app.
 *
 * @package Vanilla
 */

$Configuration['Vanilla']['Installed'] = '0';
$Configuration['Vanilla']['Comment']['MaxLength']              = '8000';

// Spam settings explained:
// Users cannot post more than $SpamCount comments within $SpamTime seconds or
// their account will be locked from posting for $SpamLock seconds.
$Configuration['Vanilla']['Comment']['SpamCount']              = '5';
$Configuration['Vanilla']['Comment']['SpamTime']               = '60';
$Configuration['Vanilla']['Comment']['SpamLock']               = '120';
$Configuration['Vanilla']['Discussion']['SpamCount']           = '3';
$Configuration['Vanilla']['Discussion']['SpamTime']            = '60';
$Configuration['Vanilla']['Discussion']['SpamLock']            = '120';

$Configuration['Vanilla']['Comments']['PerPage']               = '50';
$Configuration['Vanilla']['Discussions']['PerCategory']        = '5';
$Configuration['Vanilla']['Discussions']['PerPage']            = '30';
$Configuration['Vanilla']['Discussions']['Home']               = 'discussions';
$Configuration['Vanilla']['Categories']['Use']                 = TRUE;

// Should users be automatically pushed to the last comment they read in a discussion?
$Configuration['Vanilla']['Comments']['AutoOffset']            = TRUE;
