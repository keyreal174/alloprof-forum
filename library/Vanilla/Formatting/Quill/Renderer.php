<?php
/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

namespace Vanilla\Formatting\Quill;

/**
 * Class for rendering BlotGroups into HTML.
 */
class Renderer {

    /**
     * Render operations into HTML.
     *
     * @param BlotGroupCollection $blotGroups The blot groups to render.
     *
     * @return string
     */
    public function render(BlotGroupCollection $blotGroups): string {
        $result = "";
        foreach ($blotGroups as $index => $group) {
            $result .= $group->render();
        }

        return $result;
    }
}
