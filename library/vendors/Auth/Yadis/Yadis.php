<?php

/**
 * The core PHP Yadis implementation.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005-2008 Janrain, Inc.
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

/**
 * Need both fetcher types so we can use the right one based on the
 * presence or absence of CURL.
 */
require_once "Auth/Yadis/PlainHTTPFetcher.php";
require_once "Auth/Yadis/ParanoidHTTPFetcher.php";

/**
 * Need this for parsing HTML (looking for META tags).
 */
require_once "Auth/Yadis/ParseHTML.php";

/**
 * Need this to parse the XRDS document during Yadis discovery.
 */
require_once "Auth/Yadis/XRDS.php";

/**
 * XRDS (yadis) content type
 */
define('Auth_Yadis_CONTENT_TYPE', 'application/xrds+xml');

/**
 * Yadis header
 */
define('Auth_Yadis_HEADER_NAME', 'X-XRDS-Location');

/**
 * Contains the result of performing Yadis discovery on a URI.
 *
 * @package OpenID
 */
class Auth_Yadis_DiscoveryResult {

    // The URI that was passed to the fetcher
    var $request_uri = null;

    // The result of following redirects from the request_uri
    var $normalized_uri = null;

    // The URI from which the response text was returned (set to
    // None if there was no XRDS document found)
    var $xrds_uri = null;

    var $xrds = null;

    // The content-type returned with the response_text
    var $content_type = null;

    // The document returned from the xrds_uri
    var $response_text = null;

    // Did the discovery fail miserably?
    var $failed = false;

    function __construct($request_uri)
    {
        // Initialize the state of the object
        // sets all attributes to None except the request_uri
        $this->request_uri = $request_uri;
    }

    function fail()
    {
        $this->failed = true;
    }

    function isFailure()
    {
        return $this->failed;
    }

    /**
     * Returns the list of service objects as described by the XRDS
     * document, if this yadis object represents a successful Yadis
     * discovery.
     *
     * @return array $services An array of {@link Auth_Yadis_Service}
     * objects
     */
    function services()
    {
        if ($this->xrds) {
            return $this->xrds->services();
        }

        return null;
    }

    function usedYadisLocation()
    {
        // Was the Yadis protocol's indirection used?
        return $this->normalized_uri != $this->xrds_uri;
    }

    function isXRDS()
    {
        // Is the response text supposed to be an XRDS document?
        return ($this->usedYadisLocation() ||
                $this->content_type == Auth_Yadis_CONTENT_TYPE);
    }
}

/**
 *
 * Perform the Yadis protocol on the input URL and return an iterable
 * of resulting endpoint objects.
 *
 * input_url: The URL on which to perform the Yadis protocol
 *
 * @return: The normalized identity URL and an iterable of endpoint
 * objects generated by the filter function.
 *
 * xrds_parse_func: a callback which will take (uri, xrds_text) and
 * return an array of service endpoint objects or null.  Usually
 * array('Auth_OpenID_ServiceEndpoint', 'fromXRDS').
 *
 * discover_func: if not null, a callback which should take (uri) and
 * return an Auth_Yadis_Yadis object or null.
 */
function Auth_Yadis_getServiceEndpoints($input_url, $xrds_parse_func,
                                        $discover_func=null, $fetcher=null)
{
    if ($discover_func === null) {
        $discover_function = array('Auth_Yadis_Yadis', 'discover');
    }

    $yadis_result = call_user_func_array($discover_func,
                                         array($input_url, $fetcher));

    if ($yadis_result === null) {
        return array($input_url, array());
    }

    $endpoints = call_user_func_array($xrds_parse_func,
                      array($yadis_result->normalized_uri,
                            $yadis_result->response_text));

    if ($endpoints === null) {
        $endpoints = array();
    }

    return array($yadis_result->normalized_uri, $endpoints);
}

/**
 * This is the core of the PHP Yadis library.  This is the only class
 * a user needs to use to perform Yadis discovery.  This class
 * performs the discovery AND stores the result of the discovery.
 *
 * First, require this library into your program source:
 *
 * <pre>  require_once "Auth/Yadis/Yadis.php";</pre>
 *
 * To perform Yadis discovery, first call the "discover" method
 * statically with a URI parameter:
 *
 * <pre>  $http_response = array();
 *  $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
 *  $yadis_object = Auth_Yadis_Yadis::discover($uri,
 *                                    $http_response, $fetcher);</pre>
 *
 * If the discovery succeeds, $yadis_object will be an instance of
 * {@link Auth_Yadis_Yadis}.  If not, it will be null.  The XRDS
 * document found during discovery should have service descriptions,
 * which can be accessed by calling
 *
 * <pre>  $service_list = $yadis_object->services();</pre>
 *
 * which returns an array of objects which describe each service.
 * These objects are instances of Auth_Yadis_Service.  Each object
 * describes exactly one whole Service element, complete with all of
 * its Types and URIs (no expansion is performed).  The common use
 * case for using the service objects returned by services() is to
 * write one or more filter functions and pass those to services():
 *
 * <pre>  $service_list = $yadis_object->services(
 *                               array("filterByURI",
 *                                     "filterByExtension"));</pre>
 *
 * The filter functions (whose names appear in the array passed to
 * services()) take the following form:
 *
 * <pre>  function myFilter(&$service) {
 *       // Query $service object here.  Return true if the service
 *       // matches your query; false if not.
 *  }</pre>
 *
 * This is an example of a filter which uses a regular expression to
 * match the content of URI tags (note that the Auth_Yadis_Service
 * class provides a getURIs() method which you should use instead of
 * this contrived example):
 *
 * <pre>
 *  function URIMatcher(&$service) {
 *      foreach ($service->getElements('xrd:URI') as $uri) {
 *          if (preg_match("/some_pattern/",
 *                         $service->parser->content($uri))) {
 *              return true;
 *          }
 *      }
 *      return false;
 *  }</pre>
 *
 * The filter functions you pass will be called for each service
 * object to determine which ones match the criteria your filters
 * specify.  The default behavior is that if a given service object
 * matches ANY of the filters specified in the services() call, it
 * will be returned.  You can specify that a given service object will
 * be returned ONLY if it matches ALL specified filters by changing
 * the match mode of services():
 *
 * <pre>  $yadis_object->services(array("filter1", "filter2"),
 *                          SERVICES_YADIS_MATCH_ALL);</pre>
 *
 * See {@link SERVICES_YADIS_MATCH_ALL} and {@link
 * SERVICES_YADIS_MATCH_ANY}.
 *
 * Services described in an XRDS should have a library which you'll
 * probably be using.  Those libraries are responsible for defining
 * filters that can be used with the "services()" call.  If you need
 * to write your own filter, see the documentation for {@link
 * Auth_Yadis_Service}.
 *
 * @package OpenID
 */
class Auth_Yadis_Yadis {

    /**
     * Returns an HTTP fetcher object.  If the CURL extension is
     * present, an instance of {@link Auth_Yadis_ParanoidHTTPFetcher}
     * is returned.  If not, an instance of
     * {@link Auth_Yadis_PlainHTTPFetcher} is returned.
     *
     * If Auth_Yadis_CURL_OVERRIDE is defined, this method will always
     * return a {@link Auth_Yadis_PlainHTTPFetcher}.
     */
    function getHTTPFetcher($timeout = 20)
    {
        if (Auth_Yadis_Yadis::curlPresent() &&
            (!defined('Auth_Yadis_CURL_OVERRIDE'))) {
            $fetcher = new Auth_Yadis_ParanoidHTTPFetcher($timeout);
        } else {
            $fetcher = new Auth_Yadis_PlainHTTPFetcher($timeout);
        }
        return $fetcher;
    }

    function curlPresent()
    {
        return function_exists('curl_init');
    }

    /**
     * @access private
     */
    function _getHeader($header_list, $names)
    {
        foreach ($header_list as $name => $value) {
            foreach ($names as $n) {
                if (strtolower($name) == strtolower($n)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @access private
     */
    function _getContentType($content_type_header)
    {
        if ($content_type_header) {
            $parts = explode(";", $content_type_header);
            return strtolower($parts[0]);
        }
    }

    /**
     * This should be called statically and will build a Yadis
     * instance if the discovery process succeeds.  This implements
     * Yadis discovery as specified in the Yadis specification.
     *
     * @param string $uri The URI on which to perform Yadis discovery.
     *
     * @param array $http_response An array reference where the HTTP
     * response object will be stored (see {@link
     * Auth_Yadis_HTTPResponse}.
     *
     * @param Auth_Yadis_HTTPFetcher $fetcher An instance of a
     * Auth_Yadis_HTTPFetcher subclass.
     *
     * @param array $extra_ns_map An array which maps namespace names
     * to namespace URIs to be used when parsing the Yadis XRDS
     * document.
     *
     * @param integer $timeout An optional fetcher timeout, in seconds.
     *
     * @return mixed $obj Either null or an instance of
     * Auth_Yadis_Yadis, depending on whether the discovery
     * succeeded.
     */
    function discover($uri, &$fetcher,
                      $extra_ns_map = null, $timeout = 20)
    {
        $result = new Auth_Yadis_DiscoveryResult($uri);

        $request_uri = $uri;
        $headers = array("Accept: " . Auth_Yadis_CONTENT_TYPE .
                         ', text/html; q=0.3, application/xhtml+xml; q=0.5');

        if ($fetcher === null) {
            $fetcher = Auth_Yadis_Yadis::getHTTPFetcher($timeout);
        }

        $response = $fetcher->get($uri, $headers);

        if (!$response || ($response->status != 200 and
                           $response->status != 206)) {
            $result->fail();
            return $result;
        }

        $result->normalized_uri = $response->final_url;
        $result->content_type = Auth_Yadis_Yadis::_getHeader(
                                       $response->headers,
                                       array('content-type'));

        if ($result->content_type &&
            (Auth_Yadis_Yadis::_getContentType($result->content_type) ==
             Auth_Yadis_CONTENT_TYPE)) {
            $result->xrds_uri = $result->normalized_uri;
        } else {
            $yadis_location = Auth_Yadis_Yadis::_getHeader(
                                                 $response->headers,
                                                 array(Auth_Yadis_HEADER_NAME));

            if (!$yadis_location) {
                $parser = new Auth_Yadis_ParseHTML();
                $yadis_location = $parser->getHTTPEquiv($response->body);
            }

            if ($yadis_location) {
                $result->xrds_uri = $yadis_location;

                $response = $fetcher->get($yadis_location);

                if ((!$response) || ($response->status != 200 and
                                     $response->status != 206)) {
                    $result->fail();
                    return $result;
                }

                $result->content_type = Auth_Yadis_Yadis::_getHeader(
                                                         $response->headers,
                                                         array('content-type'));
            }
        }

        $result->response_text = $response->body;
        return $result;
    }
}

?>
