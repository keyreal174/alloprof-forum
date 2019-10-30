<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Formatting\Formats;

use Garden\StaticCacheTranslationTrait;
use Twig\Node\DoNode;
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



    function getAttrData(string $attr, \DOMElement $domNode) {
        // Empty array to hold all classes to return
        //Loop through each tag in the dom and add it's attribute data to the array
        $attrData = [];
        if(empty($domNode->getAttribute($attr)) === false) {
            $attrData = explode(" ", $domNode->getAttribute($attr));
        } else {
            array_push($attrData, "");
        }
        //Return the array of attribute data
        return array_unique($attrData);
    }

    public function getClasses($domElmement) {
        return self::getAttrData('class', $domElmement);
    }

    public function hasClass($classes, $target) {
        foreach ($classes as $c) {
            if ($c === $target) {
                return true;
            }
        }
        return false;
    }

    public function setAttribute($domNode, $key, $value) {
        $domNode->setAttribute($key, $value);
    }

    public function appendClass(&$domNode, $class) {
        if(empty($domNode->getAttribute("class"))) {
            $domNode->setAttribute("class", $class);
        } else {
            $domNode->setAttribute("class", $domNode->getAttribute("class") . " " . $class);
        }
    }

    public function cleanupCodeBlocks(&$blockCodeBlocks) {
        foreach ($blockCodeBlocks as $c) {
            $child = $c->firstChild;

            if (property_exists( $child, "tagName") && $child->tagName === "code"){
                $children = $child->childNodes;
                $c->removeChild($child);
                foreach ($children as $child) {
                    $c->appendChild($child);
                }
            }

            $classes = self::getClasses($c);
            if (!self::hasClass($classes, "code")){
                self::appendClass($c, "code");
            }

            if (!self::hasClass($classes, "codeBlock")) {
                self::appendClass($c, "codeBlock");
            }

            self::setAttribute($c, "spellcheck", "false");
        }
    }

    public function cleanupInlineCodeBlocks(&$inlineCodeBlocks) {
        foreach ($inlineCodeBlocks as $c) {
            self::setAttribute($c, "class", "code");
            self::appendClass($c, "codeInline");
            self::setAttribute($c, "spellcheck", "false");
        }
    }

    public function cleanupImages(&$images) {
        foreach ($images as $i) {
            $classes = self::getClasses($i);
            if (!self::hasClass($classes, "emoji")) {
                self::appendClass($i, "embedImage-img");
                self::appendClass($i, "importedEmbed-img");
            }
        }
    }


    public function cleanupBlockquotes(&$blockquotes, $dom) {
        foreach ($blockquotes as $b) {
            self::setAttribute($b, "class", "blockquote");

            $children = $b->childNodes;
            foreach ($children as $child) {
                if (property_exists( $child, "tagName") && $child->tagName === "div") {
                    self::setAttribute($child, "class", "blockquote-content");
                    $grandChildren = $child->childNodes;
                    foreach ($grandChildren as $grandChild) {
                        if (property_exists( $grandChild, "tagName") && $grandChild->tagName === "p") {
                            self::appendClass($grandChild, "blockquote-line");
                        }
                    }
                }
            }
        }
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

        $contentPrefix = <<<HTML
<html><head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"></head>
<body>
HTML;
        $contentSuffix = "</body></html>";
        $dom = new \DOMDocument();
        @$dom->loadHTML($contentPrefix . $html . $contentSuffix);
        $xpath = new \DOMXPath($dom);

        $blockCodeBlocks = $xpath->query('.//*[self::pre]');
        self::cleanupCodeBlocks($blockCodeBlocks);
        $images = $xpath->query('.//*[self::img]');
        self::cleanupImages($images);
        $blockQuotes = $xpath->query('.//*[self::blockquote]');
        self::cleanupBlockquotes($blockQuotes, $dom);
        $inlineCodeBlocks = $xpath->query('.//*[self::code]');
        self::cleanupInlineCodeBlocks($inlineCodeBlocks);

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
