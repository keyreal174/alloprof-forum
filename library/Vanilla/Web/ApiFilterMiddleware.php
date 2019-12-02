<?php
/**
 * @author Dani M <danim@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Web;

use Garden\Web\Data;
use Garden\Web\Exception\ServerException;
use Garden\Web\RequestInterface;

/**
 * Class ApiFilterMiddleWare A middleware to filter api v2 responses.
 *
 * @package Vanilla\Web
 */
class ApiFilterMiddleware {

    /**
     * @var array The blacklisted fields.
     */
    private $blacklist = ['password', 'email', 'insertipaddress', 'updateipaddress'];

    /**
     * Validate an api v2 response.
     *
     * @param RequestInterface $request
     * @param callable $next
     * @return Data
     */
    public function __invoke(RequestInterface $request, callable $next) {
        /** @var Data $response */
        $response = $next($request);
        $data = $response->getData();
        $apiAllow = $response->getMeta('api-allow');
        if (!is_array($apiAllow)) {
            $apiAllow = [];
        }

        // Make sure filtering is done for apiv2.
        if (is_array($data)) {
            // Check for blacklisted fields.
            array_walk_recursive($data, function (&$value, $key) use ($apiAllow) {
                $isBlacklisted = in_array(strtolower($key), $this->blacklist);
                $isAllowedField = in_array(strtolower($key), $apiAllow);
                if ($isBlacklisted && !$isAllowedField) {
                    throw new ServerException('Validation failed for field'.' '.$key);
                }
            });
        }
        return $response;
    }

    /**
     * Modify the blacklist.
     *
     * @param array $fields The fields to add to the blacklist.
     */
    protected function addBlacklistFields(array $fields) {
        $this->blacklist = array_merge($this->blacklist, $fields);
    }
}
