<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Contracts\Models;

/**
 * Interface representing a generic fragment provider.
 */
interface FragmentProviderInterface {
    /**
     * Get a single fragment by it's ID.
     *
     * @param int $id The ID to lookup.
     * @param bool $useUnknownFallback Whether or not to use the unknown fragment as a fallback.
     * @return array
     */
    public function getFragmentByID(int $id, bool $useUnknownFallback = false): array;

    /**
     * Populate records with fragments based on various ID fields.
     *
     * @param array $records The records to populate.
     * @param array $columnNames The column names to check for. These should end with `ID`.
     *        The resulting values will be populated on a field without the ID suffix.
     */
    public function expandFragments(array &$records, array $columnNames): void;

    /**
     * Return an array of keys that can be used to generate some record.
     *
     * @return string[]
     */
    public function getAllowedGeneratedRecordKeys(): array;

    /**
     * Get a fragment representing when a record isn't found.
     *
     * @param string $key A key representing some generated record.
     *
     * @return array
     */
    public function getGeneratedFragment(string $key): array;
}
