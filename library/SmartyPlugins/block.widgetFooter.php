<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package vanilla-smarty
 * @since 2.0
 */

/**
 *
 *
 * @param array $params The parameters passed into the function.
 * @param string $content
 * @param object $smarty The smarty object rendering the template.
 * @param bool $repeat
 * @return string The url.
 */
function smarty_block_widgetFooter($params, $content, &$smarty, &$repeat) {
    if (!$repeat){
        $class = '_widget-footer '.trim(val('class', $params, ''));
        return <<<EOT
        <div class="$class">
            $content
        </div>
EOT;
    }
}

