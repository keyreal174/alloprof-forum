<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace VanillaTests\APIv2;

use PHPUnit\Framework\TestCase;
use Vanilla\Utility\ArrayUtils;

/**
 * Allows an `AbstractResourceTest` to test API sorting.
 */
trait TestSortingTrait {
    /**
     * @var array The fields that are allowed for sorting.
     */
    protected $sortFields = [];

    /**
     * Test the sorting of a single field.
     *
     * @param string $field
     * @dataProvider provideSortFields
     */
    public function testIndexSort(string $field): void {
        $rows = $this->generateIndexRows();

        $fields = [$field, '-'.$field];

        foreach ($fields as $field) {
            /* @var AbstractResourceTest $this */
            $actual = $this->api()->get($this->indexUrl(), ['sort' => $field, 'pinOrder' => 'mixed'])->getBody();
            $expected = $actual;
            usort($expected, ArrayUtils::sortCallback($field));

            $actual = array_values(array_unique(array_column($actual, trim($field, '-'))));
            $expected = array_values(array_unique(array_column($expected, trim($field, '-'))));

            $this->assertSame($expected, $actual, "Sort test failed for $field");
        }
    }

    /**
     * Get the sort fields as a data provider.
     *
     * @return string[]
     */
    public function provideSortFields(): array {
        $r = [];
        foreach ($this->sortFields as $field) {
            $r[$field] = [$field];
        }
        return $r;
    }
}
