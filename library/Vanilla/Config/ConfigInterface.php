<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @since 2.8
 */

namespace Vanilla\Config;

/**
 * Interface for reading configuration values.
 */
interface ConfigInterface {
    /**
     * Gets a setting from the configuration array. Returns $defaultValue if the value isn't found.
     *
     * @param string $key The name of the configuration setting to get.
     * @param mixed $defaultValue If the parameter is not found in the group, this value will be returned.
     * @return mixed The configuration value.
     */
    public function get(string $key, $defaultValue = false);
}
