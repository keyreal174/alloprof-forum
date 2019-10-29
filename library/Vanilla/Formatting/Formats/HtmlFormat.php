<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Formatting\Formats;

use Garden\StaticCacheTranslationTrait;
use Vanilla\Contracts\Formatting\FormatInterface;
use Vanilla\Formatting\BaseFormat;
use Vanilla\Formatting\Exception\FormattingException;
use Vanilla\Formatting\Heading;
use Vanilla\Formatting\Html\HtmlEnhancer;
use Vanilla\Formatting\Html\HtmlPlainTextConverter;
use Vanilla\Formatting\Html\HtmlSanitizer;
use Vanilla\Formatting\Html\LegacySpoilerTrait;

/**
 * Format definition for HTML based formats.
 */
class HtmlFormat extends BaseFormat {

    use StaticCacheTranslationTrait;

    const FORMAT_KEY = "html";

    /** @var HtmlSanitizer */
    private $htmlSanitizer;

    /** @var HtmlEnhancer */
    private $htmlEnhancer;

    /** @var bool */
    private $shouldCleanupLineBreaks;

    /** @var HtmlPlainTextConverter */
    private $plainTextConverter;

    /**
     * Constructor for dependency injection.
     *
     * @param HtmlSanitizer $htmlSanitizer
     * @param HtmlEnhancer $htmlEnhancer
     * @param HtmlPlainTextConverter $plainTextConverter
     * @param bool $shouldCleanupLineBreaks
     */
    public function __construct(
        HtmlSanitizer $htmlSanitizer,
        HtmlEnhancer $htmlEnhancer,
        HtmlPlainTextConverter $plainTextConverter,
        bool $shouldCleanupLineBreaks = true
    ) {
        $this->htmlSanitizer = $htmlSanitizer;
        $this->htmlEnhancer = $htmlEnhancer;
        $this->plainTextConverter = $plainTextConverter;
        $this->shouldCleanupLineBreaks = $shouldCleanupLineBreaks;
    }

    /**
     * @inheritdoc
     */
    public function renderHtml(string $content, bool $enhance = true): string {
        $result = $this->htmlSanitizer->filter($content);

        if ($this->shouldCleanupLineBreaks) {
            $result = self::cleanupLineBreaks($result);
        }

        $result = $this->legacySpoilers($result);

        if ($enhance) {
            $result = $this->htmlEnhancer->enhance($result);
        }

        $result = self::cleanupEmbeds($result);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function renderPlainText(string $content): string {
        $html = $this->renderHtml($content, false);
        return $this->plainTextConverter->convert($html);
    }

    /**
     * @inheritdoc
     */
    public function renderQuote(string $content): string {
        $result = $this->htmlSanitizer->filter($content);

        if ($this->shouldCleanupLineBreaks) {
            $result = self::cleanupLineBreaks($result);
        }

        $result = $this->legacySpoilers($result);

        // No Embeds
        $result = $this->htmlEnhancer->enhance($result, true, false);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function filter(string $content): string {
        try {
            $this->renderHtml($content);
        } catch (\Exception $e) {
            // Rethrow as a formatting exception with exception chaining.
            throw new FormattingException($e->getMessage(), 500, $e);
        }
        return $content;
    }

    /**
     * @inheritdoc
     */
    public function parseAttachments(string $content): array {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function parseHeadings(string $content): array {
        $rendered = $this->renderHtml($content);
        $dom = new \DOMDocument();
        @$dom->loadHTML($rendered);

        $xpath = new \DOMXPath($dom);
        $domHeadings = $xpath->query('.//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');

        /** @var Heading[] $headings */
        $headings = [];

        // Mapping of $key => $usageCount.
        $slugKeyCache = [];

        /** @var \DOMNode $domHeading */
        foreach ($domHeadings as $domHeading) {
            $level = (int) str_replace('h', '', $domHeading->tagName);

            $text = $domHeading->textContent;
            $slug = slugify($text);
            $count = $slugKeyCache[$slug] ?? 0;
            $slugKeyCache[$slug] = $count + 1;
            if ($count > 0) {
                $slug .= '-' . $count;
            }

            $headings[] = new Heading(
                $domHeading->textContent,
                $level,
                $slug
            );
        }

        return $headings;
    }

    /**
     * @inheritdoc
     */
    public function parseMentions(string $content): array {
        // Legacy Mention Fetcher.
        // This should get replaced in a future refactoring.
        return getMentions($content);
    }

    public function getClasses($domElmement) {
        $attributes = $domElmement["attrs"];
        $classes = explodeTrim(" ", $attributes["class"]);
        return $classes;
    }

    public function hasClass($classes, $target) {
        foreach ($classes as $c) {
            if ($c === $target) {
                return true;
            }
        }
        return false;
    }

    public function appendClass($el, $class) {
        $attributes = $el["attrs"];
        if (!array_key_exists("class", $attributes)){
            $el["attrs"]["class"] = "";
        }
        $el["attrs"]["class"] .= " " . $class;
        return $el;
    }



    /**
     * Fixes html output for embeds that were imported from another platform
     *
     * @param string $html An HTML string to process.
     *
     * @return string
     * @internal Marked public for internal backwards compatibility only.
     */
    public function cleanupEmbeds(string $html): string {
        $contentID = 'contentID';
        $contentPrefix = <<<HTML
<html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head>
<body>
HTML;
        $contentSuffix = "</body></html>";
        $dom = new \DOMDocument();
        @$dom->loadHTML($contentPrefix . $html . $contentSuffix);
        $xpath = new \DOMXPath($dom);

        $codeBlocks = $xpath->query('.//*[self::pre]');
        foreach ($codeBlocks as $codeBlock) {
            $classes = getClasses($codeBlock);
            if (!!hasClass($classes, "code") && !hasClass($classes, "codeBlock")) {
                appendClass($codeBlock, "code");
                appendClass($codeBlock, "codeBlock");
            }



//            $classes = $codeBlock["attrs"]

//            $break = "here";
//            $level = (int) str_replace('h', '', $domHeading->tagName);

//            $text = $domHeading->textContent;
//            $slug = slugify($text);
//            $count = $slugKeyCache[$slug] ?? 0;
//            $slugKeyCache[$slug] = $count + 1;
//            if ($count > 0) {
//                $slug .= '-' . $count;
//            }
        }

//        $code = $xpath->query('.//*[self::code]');


        //  = Inline =
        // code block

        // = Block =
        // Code Block
        // blockquote
        // img


//        $domHeadings = $xpath->query('.//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');
//
//        /** @var Heading[] $headings */
//        $headings = [];
//
//        // Mapping of $key => $usageCount.
//        $slugKeyCache = [];
//
//        /** @var \DOMNode $domHeading */
//        foreach ($domHeadings as $domHeading) {
//            $level = (int) str_replace('h', '', $domHeading->tagName);
//
//            $text = $domHeading->textContent;
//            $slug = slugify($text);
//            $count = $slugKeyCache[$slug] ?? 0;
//            $slugKeyCache[$slug] = $count + 1;
//            if ($count > 0) {
//                $slug .= '-' . $count;
//            }
//
//            $headings[] = new Heading(
//                $domHeading->textContent,
//                $level,
//                $slug
//            );
//        }
//
        $content = $dom->getElementsByTagName('body');
        $htmlBodyString = @$dom->saveXML($content[0], LIBXML_NOEMPTYTAG);
        return $htmlBodyString;
    }

    const BLOCK_WITH_OWN_WHITESPACE =
        "(?:table|dl|ul|ol|pre|blockquote|address|p|h[1-6]|" .
        "section|article|aside|hgroup|header|footer|nav|figure|" .
        "figcaption|details|menu|summary|li|tbody|tr|td|th|" .
        "thead|tbody|tfoot|col|colgroup|caption|dt|dd)";

    /**
     * Removes the break above and below tags that have their own natural margin.
     *
     * @param string $html An HTML string to process.
     *
     * @return string
     * @internal Marked public for internal backwards compatibility only.
     */
    public function cleanupLineBreaks(string $html): string {
        $zeroWidthWhitespaceRemoved = preg_replace(
            "/(?!<code[^>]*?>)(\015\012|\012|\015)(?![^<]*?<\/code>)/",
            "<br />",
            $html
        );
        $breakBeforeReplaced = preg_replace(
            '!(?:<br\s*/>){1,2}\s*(<' . self::BLOCK_WITH_OWN_WHITESPACE. '[^>]*>)!',
            "\n$1",
            $zeroWidthWhitespaceRemoved
        );
        $breakAfterReplaced = preg_replace(
            '!(</' . self::BLOCK_WITH_OWN_WHITESPACE . '[^>]*>)\s*(?:<br\s*/>){1,2}!',
            "$1\n",
            $breakBeforeReplaced
        );
        return $breakAfterReplaced;
    }

    /**
     * Spoilers with backwards compatibility.
     *
     * In the Spoilers plugin, we would render BBCode-style spoilers in any format post and allow a title.
     *
     * @param string $html
     * @return string
     */
    protected function legacySpoilers(string $html): string {
        if (strpos($html, '[/spoiler]') !== false) {
            $count = 0;
            do {
                $html = preg_replace(
                    '`\[spoiler(?:=(?:&quot;)?[\d\w_\',.? ]+(?:&quot;)?)?\](.*?)\[\/spoiler\]`usi',
                    '<div class="Spoiler">$1</div>',
                    $html,
                    -1,
                    $count
                );
            } while ($count > 0);
        }
        return $html;
    }
}
