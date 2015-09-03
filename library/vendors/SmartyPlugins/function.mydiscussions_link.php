<?php
/**
 * @copyright 2009-2015 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package vanilla-smarty
 * @since 2.0
 */

/**
 *
 *
 * @param array $Params
 * @param object $Smarty
 * @return string
 */
function smarty_function_mydiscussions_link($Params, &$Smarty) {
    $Wrap = val('wrap', $Params, 'li');
    return Gdn_Theme::link('discussions/mine',
        val('text', $Params, t('My Discussions')),
        val('format', $Params, wrap('<a href="%url" class="%class">%text</a>', $Wrap)));
}