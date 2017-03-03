<?php
/**
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license GPLv2
 */

namespace VanillaTests\Library\Core;

/**
 * Test the jsonFilter function.
 */
class JsonFilterTest extends \PHPUnit_Framework_TestCase {

    public function testJsonFilterDateTime() {
        $date = new \DateTime();
        $data = ['Date' => $date];
        jsonFilter($data);

        $this->assertSame($date->format('r'), $data['Date']);
    }

    public function testJsonFilterDateTimeRecursive() {
        $date = new \DateTime();
        $data = [
            'Dates' => ['FirstDate' => $date]
        ];
        jsonFilter($data);

        $this->assertSame($date->format('r'), $data['Dates']['FirstDate']);
    }

    public function testJsonFilterEncodedIP() {
        $ip = '127.0.0.1';
        $data = ['InsertIPAddress' => ipEncode($ip)];
        jsonFilter($data);

        $this->assertSame($ip, $data['InsertIPAddress']);
    }

    public function testJsonFilterEncodedIPList() {
        $ip = ['127.0.0.1', '192.168.0.1', '10.0.0.1'];
        $encoded = ipEncodeRecursive(['AllIPAddresses' => $ip]);

        jsonFilter($encoded);

        $this->assertSame($ip, $encoded['AllIPAddresses']);
    }

    public function testJsonFilterEncodedIPRecursive() {
        $ip = '127.0.0.1';
        $data = [
            'Discussion' => ['UpdateIPAddress' => ipEncode($ip)]
        ];
        jsonFilter($data);

        $this->assertSame($ip, $data['Discussion']['UpdateIPAddress']);
    }

    public function testJsonFilterPassThrough() {
        $data = [
            'Array' => ['Key' => 'Value'],
            'Boolean' => true,
            'Float' => 1.234,
            'Integer' => 10,
            'Null' => null,
            'String' => 'The quick brown fox jumps over the lazy dog.'
        ];

        $filteredData = $data;
        jsonFilter($filteredData);

        $this->assertSame($data, $filteredData);
    }
}
