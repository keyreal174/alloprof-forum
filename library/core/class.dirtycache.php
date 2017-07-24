<?php
/**
 * Gdn_Dirtycache
 *
 * @author Tim Gunter <tim@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @package Core
 * @since 2.0
 */

/**
 * Cache Layer: Dirty.
 *
 * This is a cache implementation that caches nothing and always reports cache misses.
 */
class Gdn_Dirtycache extends Gdn_Cache {

    /** @var array  */
    protected $cache = [];

    /**
     *
     */
    public function __construct() {
        parent::__construct();
        $this->cacheType = Gdn_Cache::CACHE_TYPE_NULL;
    }

    public function addContainer($Options) {
        return Gdn_Cache::CACHEOP_SUCCESS;
    }

    public function add($Key, $Value, $Options = []) {
        return $this->store($Key, $Value, $Options);
    }

    public function store($Key, $Value, $Options = []) {
        $this->cache[$Key] = $Value;
        return Gdn_Cache::CACHEOP_SUCCESS;
    }

    public function exists($Key) {
        return Gdn_Cache::CACHEOP_FAILURE;
    }

    public function get($Key, $Options = []) {
        if (is_array($Key)) {
            $Result = [];
            foreach ($Key as $k) {
                if (isset($this->cache[$k])) {
                    $Result[$k] = $this->cache[$k];
                }
            }
            return $Result;
        } else {
            if (array_key_exists($Key, $this->cache)) {
                return $this->cache[$Key];
            } else {
                return Gdn_Cache::CACHEOP_FAILURE;
            }
        }
    }

    public function remove($Key, $Options = []) {
        unset($this->cache[$Key]);

        return Gdn_Cache::CACHEOP_SUCCESS;
    }

    public function replace($Key, $Value, $Options = []) {
        $this->cache[$Key] = $Value;
        return Gdn_Cache::CACHEOP_SUCCESS;
    }

    public function increment($Key, $Amount = 1, $Options = []) {
        $value = array_key_exists($Key, $this->cache) ? intval($this->cache[$Key]) : 0;
        $value += $Amount;
        $this->cache[$Key] = $value;
        return Gdn_Cache::CACHEOP_SUCCESS;
    }

    public function decrement($Key, $Amount = 1, $Options = []) {
        $value = array_key_exists($Key, $this->cache) ? intval($this->cache[$Key]) : 0;
        $value -= $Amount;
        $this->cache[$Key] = $value;
        return Gdn_Cache::CACHEOP_SUCCESS;
    }

    public function flush() {
        return true;
    }
}
