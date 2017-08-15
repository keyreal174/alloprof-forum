<?php
/**
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package vanilla-smarty
 * @since 2.0
 */

/**
 *
 *
 * @param array $params
 * @param object $smarty
 * @return string
 */
function smarty_function_drafts_link($params, &$smarty) {
    $wrap = val('wrap', $params, 'li');
    return Gdn_Theme::link('drafts',
        val('text', $params, t('My Drafts')),
        val('format', $params, wrap('<a href="%url" class="%class">%text</a>', $wrap)));
}
