<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

 namespace Vanilla\Formatting;

 /**
  * Static utilities for text formatting.
  *
  * Do NOT put any stateful logic in this file.
  */
class FormatUtil {
    /**
     * Do a preg_replace, but don't affect things inside <code> tags.
     *
     * The three parameters are identical to the ones you'd pass
     * preg_replace.
     *
     * @param mixed $search The value being searched for, just like in
     *              preg_replace or preg_replace_callback.
     * @param string|callable $replace The replacement value, just like in
     *              preg_replace or preg_replace_callback.
     * @param string $subject The string being searched.
     * @param bool $isCallback If true, do preg_replace_callback. Do
     *             preg_replace otherwise.
     * @return string
     */
    public static function replaceButProtectCodeBlocks(string $search, $replace, string $subject, bool $isCallback = false): string {
        // Take the code blocks out, replace with a hash of the string, and
        // keep track of what substring got replaced with what hash.
        $codeBlockContents = [];
        $codeBlockHashes = [];
        $subject = preg_replace_callback(
            '/<code.*?>.*?<\/code>/is',
            function ($matches) use (&$codeBlockContents, &$codeBlockHashes) {
                // Surrounded by whitespace to try to prevent the characters
                // from being picked up by $Pattern.
                $replacementString = ' '.sha1($matches[0]).' ';
                $codeBlockContents[] = $matches[0];
                $codeBlockHashes[] = $replacementString;
                return $replacementString;
            },
            $subject
        );

        // Do the requested replacement.
        if ($isCallback) {
            $subject = preg_replace_callback($search, $replace, $subject);
        } else {
            $subject = preg_replace($search, $replace, $subject);
        }

        // Put back the code blocks.
        $subject = str_replace($codeBlockHashes, $codeBlockContents, $subject);

        return $subject;
    }

    /** @var array Unicode to ascii conversion table. */
    const CHAR_MAPPING = [
        '-' => ' ', '_' => ' ', '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
        '&quot;' => '', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'Ae',
        '&Auml;' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'Ae',
        '??' => 'C', '??' => 'C', '??' => 'C', '??' => 'C', '??' => 'C', '??' => 'D', '??' => 'D',
        '??' => 'D', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E',
        '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'E', '??' => 'G', '??' => 'G',
        '??' => 'G', '??' => 'G', '??' => 'H', '??' => 'H', '??' => 'I', '??' => 'I',
        '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I',
        '??' => 'I', '??' => 'IJ', '??' => 'J', '??' => 'K', '??' => 'K', '??' => 'K',
        '??' => 'K', '??' => 'K', '??' => 'K', '??' => 'N', '??' => 'N', '??' => 'N',
        '??' => 'N', '??' => 'N', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O',
        '??' => 'Oe', '&Ouml;' => 'Oe', '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'O',
        '??' => 'OE', '??' => 'R', '??' => 'R', '??' => 'R', '??' => 'S', '??' => 'S',
        '??' => 'S', '??' => 'S', '??' => 'S', '??' => 'T', '??' => 'T', '??' => 'T',
        '??' => 'T', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'Ue', '??' => 'U',
        '&Uuml;' => 'Ue', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U',
        '??' => 'W', '??' => 'Y', '??' => 'Y', '??' => 'Y', '??' => 'Z', '??' => 'Z',
        '??' => 'Z', '??' => 'T', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a',
        '??' => 'ae', '&auml;' => 'ae', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a',
        '??' => 'ae', '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c',
        '??' => 'd', '??' => 'd', '??' => 'd', '??' => 'e', '??' => 'e', '??' => 'e',
        '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e',
        '??' => 'f', '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'h',
        '??' => 'h', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i',
        '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'ij', '??' => 'j',
        '??' => 'k', '??' => 'k', '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l',
        '??' => 'l', '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n',
        '??' => 'n', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'oe',
        '&ouml;' => 'oe', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'oe',
        '??' => 'r', '??' => 'r', '??' => 'r', '??' => 's', '??' => 'u', '??' => 'u',
        '??' => 'u', '??' => 'ue', '??' => 'u', '&uuml;' => 'ue', '??' => 'u', '??' => 'u',
        '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'w', '??' => 'y', '??' => 'y',
        '??' => 'y', '??' => 'z', '??' => 'z', '??' => 'z', '??' => 't', '??' => 'ss',
        '??' => 'ss', '????' => 'iy', '??' => 'A', '??' => 'B', '??' => 'V', '??' => 'G',
        '??' => 'D', '??' => 'E', '??' => 'YO', '??' => 'ZH', '??' => 'Z', '??' => 'I',
        '??' => 'I', '??' => 'Y', '??' => 'K', '??' => 'L', '??' => 'M',
        '??' => 'N', '??' => 'O', '??' => 'P', '??' => 'R', '??' => 'S', '??' => 'T',
        '??' => 'U', '??' => 'F', '??' => 'H', '??' => 'C', '??' => 'CH', '??' => 'SH',
        '??' => 'SCH', '??' => '', '??' => 'Y', '??' => '', '??' => 'E', '??' => 'YU',
        '??' => 'YA', '??' => 'YE', '??' => 'YI', '??' => 'a', '??' => 'b', '??' => 'v',
        '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'yo', '??' => 'zh', '??' => 'z',
        '??' => 'i', '??' => 'i', '??' => 'y', '??' => 'k', '??' => 'l', '??' => 'm',
        '??' => 'n', '??' => 'o', '??' => 'p', '??' => 'r', '??' => 's', '??' => 't',
        '??' => 'u', '??' => 'f', '??' => 'h', '??' => 'c', '??' => 'ch', '??' => 'sh',
        '??' => 'sch', '??' => '', '??' => 'y', '??' => '', '??' => 'e', '??' => 'ye',
        '??' => 'yu', '??' => 'ya', '??' => 'yi'
    ];

    /**
     * Convert certain unicode characters into their ascii equivalents.
     *
     * @param string $text The text to clean.
     * @return string
     */
    public static function transliterate(string $text): string {
        $text = strtr($text, self::CHAR_MAPPING);
        $text = preg_replace('/[^A-Za-z0-9 ]/', '', urldecode($text));
        $text = preg_replace('/ +/', '-', trim($text));
        return strtolower($text);
    }
}
