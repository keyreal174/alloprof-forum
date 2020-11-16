<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license Proprietary
 */

namespace Vanilla\Web\JsInterpop;

use Gdn_Module;
use Vanilla\Web\TwigRenderTrait;
use Vanilla\Widgets\AbstractWidgetModule;

/**
 * Module using the new events UI.
 */
abstract class AbstractReactModule extends AbstractWidgetModule {
    use TwigRenderTrait;

    /**
     * Get props for react component
     *
     * @return array|null If null is returned the component will not be rendered.
     */
    abstract public function getProps(): ?array;

    /**
     * Get react component name
     * @return string
     */
    abstract public function getComponentName(): string;

    /**
     * Optional class for div
     * @return string
     */
    public function cssWrapperClass(): string {
        return "";
    }

    /**
     * Rendering function.
     *
     * @return string
     */
    public function toString(): string {
        try {
            $props = $this->getProps();
            if ($props === null) {
                return "";
            }
            return $this->renderTwigFromString(
                '<div class="{{ class }}" data-react="{{ component }}" data-props="{{ props }}"></div>',
                [
                    'props' => json_encode($this->getProps(), JSON_UNESCAPED_UNICODE),
                    'component' => $this->getComponentName(),
                    'class' => trim($this->cssWrapperClass()),
                ]
            );
        } catch (\Garden\Web\Exception\HttpException $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return "";
        }
    }
}
