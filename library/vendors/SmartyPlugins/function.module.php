<?php
/**
 * @copyright 2009-2016 Vanilla Forums Inc.
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
function smarty_function_module($Params, &$Smarty) {
    $Name = val('name', $Params);
    unset($Params['name']);
   
    $Result = Gdn_Theme::module($Name, $Params);
	return $Result;
}
