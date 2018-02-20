import Embed from "quill/blots/embed";
import { setData, getData} from "@core/dom-utility";
import { parseEmoji, isEmojiSupported } from "@core/emoji-utility";

export default class EmojiBlot extends Embed {
    static create(data) {
        const node = super.create();
        if (isEmojiSupported()) {
            node.innerHTML = data.emojiChar;
            node.classList.add("nativeEmoji");
        } else {
            node.innerHTML = parseEmoji(data.emojiChar);
        }
        setData(node, "data", data);
        return node;
    }

    static value(node) {
        return getData(node, "data");
    }
}

EmojiBlot.className = 'safeEmoji';
EmojiBlot.blotName = 'emoji';
EmojiBlot.tagName = 'span';
