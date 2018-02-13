<?php
/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace Vanilla\Quill\Blots;

use Vanilla\Quill\Block;

abstract class AbstractInlineEmbedBlot extends AbstractBlot {

    const ZERO_WIDTH_WHITESPACE = "&#65279;";

    /**
     * Get the wrapping HTML tag name for this blot.
     *
     * @return string
     */
    abstract protected static function getHTMLTag(): string;

    /**
     * Get the class for the wrapping HTML tag. This will generally not be a
     *
     * @return string
     */
    abstract protected static function getHTMLTagClass(): string;

    /**
     * Get the key to pull the main content out of the currentBlot.
     *
     * @return string
     */
    abstract protected static function getInsertKey(): string;

    /**
     * @inheritDoc
     */
    public function __construct(array $currentOperation, array $previousOperation, array $nextOperation) {
        parent::__construct($currentOperation, $previousOperation, $nextOperation);
        $this->content = valr(static::getInsertKey(), $this->currentOperation);
    }

    /**
     * @inheritDoc
     */
    public function render(): string {
        $result = "<" . static::getHTMLTag();

        $class = static::getHTMLTagClass();
        if ($class) {
            $result .= " class=\"$class\"";
        }
        $result .= ">";
        $result .= static::ZERO_WIDTH_WHITESPACE;
        $result .= "<span contenteditable='false'>" . $this->content . "</span>";
        $result .= static::ZERO_WIDTH_WHITESPACE;
        $result .= "</" . static::getHTMLTag() . ">";

        return $result;
    }

    /**
     * Render the content area of the blot. Try overriding this before overriding render().
     *
     * @see AbstractInlineEmbedBlot::render()
     *
     * @return string
     */
    protected function renderContent(): string {
        $result = "<span contenteditable=\"false\">";
        $result .= $this->content;
        $result .= "</span>";
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function shouldClearCurrentBlock(Block $block): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function hasConsumedNextOp(): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function matches(array $operations): bool {
        $found = false;

        foreach($operations as $op) {
            if(valr(static::getInsertKey(), $op)) {
                $found = true;
                break;
            }
        }

        return $found;
    }
}
