<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Formatting\Formats;

use Garden\Schema\ValidationException;
use Garden\StaticCacheTranslationTrait;
use Vanilla\Formatting\Attachment;
use Vanilla\Formatting\BaseFormat;
use Vanilla\Formatting\Embeds\FileEmbed;
use Vanilla\Formatting\Exception\FormattingException;
use Vanilla\Formatting\Heading;
use Vanilla\Formatting\Quill\Blots\Embeds\ExternalBlot;
use Vanilla\Formatting\Quill\Blots\Lines\HeadingTerminatorBlot;
use Vanilla\Web\TwigRenderTrait;
use Vanilla\Formatting\Quill;

/**
 * Format service for the rich editor format. Rendered and parsed using Quill.
 */
class RichFormat extends BaseFormat {

    use TwigRenderTrait;
    use StaticCacheTranslationTrait;

    const FORMAT_KEY = "Rich";

    /** @var string */
    const RENDER_ERROR_MESSAGE = 'There was an error rendering this rich post.';

    /** @var Quill\Parser */
    private $parser;

    /** @var Quill\Renderer */
    private $renderer;

    /** @var Quill\Filterer */
    private $filterer;

    /**
     * Constructor for DI.
     *
     * @param Quill\Parser $parser
     * @param Quill\Renderer $renderer
     * @param Quill\Filterer $filterer
     */
    public function __construct(Quill\Parser $parser, Quill\Renderer $renderer, Quill\Filterer $filterer) {
        $this->parser = $parser;
        $this->renderer = $renderer;
        $this->filterer = $filterer;
    }


    /**
     * @inheritdoc
     */
    public function renderHTML(string $content): string {
        try {
            $operations = Quill\Parser::jsonToOperations($content);
        } catch (\Exception $e) {
            // Catching all possible exceptions so a single bad post doesn't take down page.
            return $this->renderErrorMessage();
        }

        $blotGroups = $this->parser->parse($operations);
        return $this->renderer->render($blotGroups);
    }

    /**
     * @inheritdoc
     */
    public function renderPlainText(string $content): string {
        $text = '';
        try {
            $operations = Quill\Parser::jsonToOperations($content);
        } catch (FormattingException $e) {
            return self::t(self::RENDER_ERROR_MESSAGE);
        }

        $blotGroups = $this->parser->parse($operations);

        /** @var Quill\BlotGroup $blotGroup */
        foreach ($blotGroups as $blotGroup) {
            $text .= $blotGroup->getUnsafeText();
        }
        return $text;
    }

    /**
     * @inheritdoc
     */
    public function renderQuote(string $content): string {
        try {
            $operations = Quill\Parser::jsonToOperations($content);
        } catch (FormattingException $e) {
            return $this->renderErrorMessage();
        }

        $blotGroups = $this->parser->parse($operations, Quill\Parser::PARSE_MODE_QUOTE);
        $rendered = $this->renderer->render($blotGroups);

        // Trim out breaks and empty paragraphs.
        $result = str_replace("<p><br></p>", "", $rendered);
        $result = str_replace("<p></p>", "", $result);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function filter(string $content): string {
        return $this->filterer->filter($content);
    }

    /**
     * @inheritdoc
     */
    public function parseAttachments(string $content): array {
        $attachments = [];

        try {
            $operations = Quill\Parser::jsonToOperations($content);
        } catch (FormattingException $e) {
            return [];
        }

        $parser = (new Quill\Parser())
            ->addBlot(ExternalBlot::class);
        $blotGroups = $parser->parse($operations);

        /** @var Quill\BlotGroup $blotGroup */
        foreach ($blotGroups as $blotGroup) {
            $blot = $blotGroup->getBlotForSurroundingTags();
            if ($blot instanceof ExternalBlot &&
                ($blot->getEmbedData()['type'] ?? null) === FileEmbed::EMBED_TYPE
            ) {
                try {
                    $embedData = $blot->getEmbedData()['attributes'] ?? [];
                    $attachment = Attachment::fromArray($embedData);
                    $attachments[] = $attachment;
                } catch (ValidationException $e) {
                    continue;
                }
            }
        }
        return $attachments;
    }

    /**
     * @inheritdoc
     */
    public function parseMentions(string $content): array {
        try {
            $operations = Quill\Parser::jsonToOperations($content);
        } catch (FormattingException $e) {
            return [];
        }
        return $this->parser->parseMentionUsernames($operations);
    }

    /**
     * @inheritdoc
     */
    public function parseHeadings(string $content): array {
        $outline = [];

        try {
            $operations = Quill\Parser::jsonToOperations($content);
        } catch (FormattingException $e) {
            return [];
        }

        $parser = (new Quill\Parser())
            ->addBlot(HeadingTerminatorBlot::class);
        $blotGroups = $parser->parse($operations);

        /** @var Quill\BlotGroup $blotGroup */
        foreach ($blotGroups as $blotGroup) {
            $blot = $blotGroup->getBlotForSurroundingTags();
            if ($blot instanceof HeadingTerminatorBlot && $blot->getReference()) {
                $outline[] = new Heading(
                    $blotGroup->getUnsafeText(),
                    $blot->getHeadingLevel(),
                    $blot->getReference()
                );
            }
        }
        return $outline;
    }

    /**
     * Render an error message indicating something went wrong.
     *
     * @return string
     */
    private function renderErrorMessage(): string {
        $data = [
            'title' => self::t(self::RENDER_ERROR_MESSAGE),
            'errorUrl' => 'https://docs.vanillaforums.com/help/addons/rich-editor/#why-is-my-published-post-replaced-with-there-was-an-error-rendering-this-rich-post',
        ];

        return $this->renderTwig('resources/userContentError', $data);
    }
}
