<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Web;
use Garden\SafeCurl\SafeCurl;
/*
 * SafeCurl wrapper.
 */
Class CurlWrapper {
    /**
     * Executes a safecurl request.
     *
     * @param $url
     * @param resource $ch The curl handle to execute.
     * @param bool $followLocation
     * @return string
     */
    Static function curlExec($url, $ch, $followLocation = false) {
        $safeCurl = new SafeCurl($ch);
        $safeCurl->setFollowLocation($followLocation);
        return $safeCurl->execute($url);
    }
}
