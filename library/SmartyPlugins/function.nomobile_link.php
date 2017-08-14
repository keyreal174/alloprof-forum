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
function smarty_function_nomobile_link($params, &$smarty) {
    $path = val('path', $params, '', true);
    $text = val('text', $params, '', true);
    $wrap = val('wrap', $params, 'li');
    return Gdn_Theme::link('profile/nomobile',
        val('text', $params, t("Full Site")),
        val('format', $params, wrap('<a href="%url" class="%class">%text</a>', $wrap)),
        ['class' => 'js-hijack']
    );
}
