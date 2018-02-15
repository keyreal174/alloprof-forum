import { BlockEmbed } from "quill/blots/block";
import { setData } from "@core/dom-utility";
import { getData } from "@core/dom-utility";

export default class CodeBlockBlot extends BlockEmbed {
    static create(data) {
        const node = super.create(data);
        node.classList.add("embed");
        node.classList.add("codeBlock");

        const code = document.createElement('code');

        code.innerHTML = data.content;

        node.appendChild(code);

        setData(node, "data", data);

        return node;
    }

    static value(node) {
        return getData(node, "data");
    }
}

CodeBlockBlot.blotName = 'code-block';
CodeBlockBlot.tagName = 'pre';
