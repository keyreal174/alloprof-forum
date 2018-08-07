<?php
/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace Vanilla\Formatting\Quill\Blots\Embeds;

use Gdn;
use Vanilla\Formatting\Embeds\EmbedManager;
use Vanilla\Formatting\Quill\Blots\AbstractBlot;
use Vanilla\Formatting\Quill\Parser;

/**
 * Blot for rendering embeds with the embed manager.
 */
class ExternalBlot extends AbstractBlot {

    /** @var EmbedManager */
    private $embedManager;

    /**
     * @inheritDoc
     */
    public static function matches(array $operations): bool {
        return (boolean) valr("insert.embed-external", $operations[0]);
    }

    /**
     * @inheritdoc
     * @throws \Garden\Container\ContainerException
     * @throws \Garden\Container\NotFoundException
     */
    public function __construct(
        array $currentOperation,
        array $previousOperation,
        array $nextOperation,
        string $parseMode = Parser::PARSE_MODE_NORMAL
    ) {
        parent::__construct($currentOperation, $previousOperation, $nextOperation, $parseMode);

        /** @var EmbedManager embedManager */
        $this->embedManager = Gdn::getContainer()->get(EmbedManager::class);
    }

    /**
     * Render out the content of the blot using the EmbedManager.
     * @see EmbedManager
     * @inheritDoc
     */
    public function render(): string {
        if ($this->parseMode === Parser::PARSE_MODE_QUOTE) {
            return $this->renderQuote();
        }

        $value = $this->currentOperation["insert"]["embed-external"] ?? [];
        $data = $value['data'] ?? $value;
        try {
            return "<div class='js-embed embedResponsive'>".$this->embedManager->renderData($data)."</div>";
        } catch (\Exception $e) {
            // TODO: Add better error handling here.
            return '';
        }
    }

    public function renderQuote(): string {
        $value = $this->currentOperation["insert"]["embed-external"] ?? [];
        $data = $value['data'] ?? $value;

        $url = $data['url'] ?? "";
        if ($url) {
            $sanitizedUrl = \Gdn_Format::sanitizeUrl($url);
            return "<div class=\"userContent\"><p><a href=\"$sanitizedUrl\">$url</a></p></div>";
        }
        return "";
    }

    /**
     * Block embeds are always their own group.
     * @inheritDoc
     */
    public function isOwnGroup(): bool {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getGroupOpeningTag(): string {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function getGroupClosingTag(): string {
        return "";
    }
}
