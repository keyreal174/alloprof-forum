/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import WrapperBlot from "./WrapperBlot";
import ClassFormatBlot  from "./ClassFormatBlot";
import { wrappedBlot } from "../quill-utilities";

class BlockQuoteLineBlot extends ClassFormatBlot {
    static blotName = "blockquote-line";
    static className = "blockquote-line";
    static tagName = 'p';
    static parentName = "blockquote-content";
}

export default wrappedBlot(BlockQuoteLineBlot);

class ContentBlot extends WrapperBlot {
    static className = 'blockquote-content';
    static blotName = 'blockquote-content';
    static parentName = 'blockquote';
}

export const BlockQuoteContentBlot = wrappedBlot(ContentBlot);

export class BlockQuoteWrapperBlot extends WrapperBlot {
    static className = 'blockquote';
    static blotName = 'blockquote';
}
