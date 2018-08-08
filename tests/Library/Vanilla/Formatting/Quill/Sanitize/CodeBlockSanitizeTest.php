<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0
 */

namespace VanillaTests\Library\Vanilla\Formatting\Quill\Sanitize;

class CodeBlockSanitizeTest extends SanitizeTest {

    /**
     * @inheritdoc
     */
    protected function insertContentOperations(string $content): array {
        $operations = [
            ["insert" => $content],
            [
                "attributes" => ["codeBlock" => true],
                "insert" => "\n"
            ],
            ["insert" => $content],
            [
                "attributes" => ["codeBlock" => true],
                "insert" => "$content"
            ]
        ];
        return $operations;
    }
}
