import extend from "extend";
import Emitter from "quill/core/emitter";
import BaseTheme, { BaseTooltip } from "quill/themes/base";
import { Range } from "quill/core/selection";
import icons from "quill/ui/icons";


class VanillaTooltip extends BaseTooltip {
    static TEMPLATE = `
        <div class="ql-tooltip-editor">
            <input type="text"
                data-formula="e=mc^2"
                data-link="https://quilljs.com"
                data-video="Embed URL">
           '<a class="ql-close"></a>'
        </div>`;

    constructor(quill, bounds) {
        super(quill, bounds);
        this.root.classList.add("richEditor-menu");

        this.quill.on(Emitter.events.EDITOR_CHANGE, (type, range, oldRange, source) => {
            if (type !== Emitter.events.SELECTION_CHANGE) {
                return;
            }
            if (range !== null && range.length > 0 && source === Emitter.sources.USER) {
                this.show();

                // Lock our width so we will expand beyond our offsetParent boundaries
                this.root.style.left = "0px";
                this.root.style.width = "";
                this.root.style.width = this.root.offsetWidth + "px";
                const lines = this.quill.getLines(range.index, range.length);
                if (lines.length === 1) {
                    this.position(this.quill.getBounds(range));
                } else {
                    const lastLine = lines[lines.length - 1];
                    const index = this.quill.getIndex(lastLine);
                    const length = Math.min(lastLine.length() - 1, range.index + range.length - index);
                    const bounds = this.quill.getBounds(new Range(index, length));
                    this.position(bounds);
                }
            } else if (document.activeElement !== this.textbox && this.quill.hasFocus()) {
                this.hide();
            }
        });
    }

    listen() {
        super.listen();
        this.root.querySelector(".ql-close").addEventListener("click", () => {
            this.root.classList.remove("ql-editing");
        });
        this.quill.on(Emitter.events.SCROLL_OPTIMIZE, () => {

            // Let selection be restored by toolbar handlers before repositioning
            setTimeout(() => {
                if (this.root.classList.contains("ql-hidden")) {
                    return;
                }
                const range = this.quill.getSelection();
                if (range !== null) {
                    this.position(this.quill.getBounds(range));
                }
            }, 1);
        });
    }

    cancel() {
        this.show();
    }

    position(reference) {
        const shift = super.position(reference);
        const arrow = this.root.querySelector(".ql-tooltip-arrow");
        arrow.style.marginLeft = "";
        if (shift === 0) {
            return shift;
        }
        arrow.style.marginLeft = (-1*shift - arrow.offsetWidth/2) + "px";

        return arrow.style.marginLeft;
    }
}

export default class VanillaTheme extends BaseTheme {
    static TOOLBAR_CONFIG = [
        "bold", "italic", "strike", "code", "link",
    ];

    static DEFAULT = extend(true, {}, BaseTheme.DEFAULTS, {
        modules: {
            toolbar: {
                handlers: {
                    link: (value) => {
                        if (!value) {
                            this.quill.format("link", false);
                        } else {
                            this.quill.theme.tooltip.edit();
                        }
                    },
                },
            },
        },
    });

    constructor(quill, options) {
        if (options.modules.toolbar !== null && options.modules.toolbar.container === null) {
            options.modules.toolbar.container = VanillaTheme.TOOLBAR_CONFIG;
        }
        super(quill, options);
        this.quill.container.classList.add("ql-vanilla");
    }

    /**
     * Create the HTML structure for the buttons
     *
     * @param {HTMLElement[]} buttons - The button elements.
     */
    buildButtons(buttons) {
        for (const button of buttons) {
            for (const className of button.classList) {
                if (!className.startsWith("ql-")) {
                    return;
                }

                const name = className.slice("ql-".length);

                button.innerHTML = `<svg><use xlink:href="#editor-${name}"></use></svg>`;
            }
        }
    }

    extendToolbar(toolbar) {
        this.tooltip = new VanillaTooltip(this.quill, this.options.bounds);
        this.tooltip.root.appendChild(toolbar.container);
        this.buildButtons(toolbar.container.querySelectorAll("button"));

        // this.buildPickers([].slice.call(toolbar.container.querySelectorAll("select")), icons);
    }
}
