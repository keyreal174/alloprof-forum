/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

// Quill
import Quill, { QuillOptionsStatic, Blot } from "quill/core";
import ThemeBase from "quill/core/theme";
import { closeEditorFlyouts } from "./utility";
import KeyboardBindings from "./KeyboardBindings";

// React
import React from "react";
import ReactDOM from "react-dom";
import InlineToolbar from "../Editor/InlineToolbar";
import ParagraphToolbar from "../Editor/ParagraphToolbar";
import EmojiPopover from "../Editor/EmojiPopover";
import EmbedPopover from "../Editor/EmbedPopover";
import EditorProvider from "../Editor/ContextProvider";
import MentionToolbar from "../Editor/MentionToolbar";

export default class VanillaTheme extends ThemeBase {
    private jsBodyBoxContainer: Element;

    /**
     * Constructor.
     *
     * @param quill - The quill instance the theme is applying to.
     * @param options - The current options for the instance.
     */
    constructor(quill: Quill, options: QuillOptionsStatic) {
        const themeOptions = {
            ...options,
            placeholder: "Create a new post...",
        };

        super(quill, themeOptions);
        this.quill.root.classList.add("richEditor-text");
        this.quill.root.classList.add("userContent");
        this.quill.root.addEventListener("focusin", () => closeEditorFlyouts());

        // Add keyboard bindings to options.
        this.addModule("embed/insertion");
        const embedFocus = this.addModule("embed/focus");
        const keyboardBindings = new KeyboardBindings(this.quill);
        this.options.modules.keyboard.bindings = {
            ...this.options.modules.keyboard.bindings,
            ...keyboardBindings.bindings,
            ...embedFocus.earlyKeyBoardBindings,
        };

        // Find the editor root.
        this.jsBodyBoxContainer = this.quill.container.closest(".richEditor") as Element;
        if (!this.jsBodyBoxContainer) {
            throw new Error("Could not find .richEditor to mount editor components into.");
        }
        this.mountMentionToolbar();
        this.mountEmojiMenu();
    }

    public init() {
        // Mount react components
        this.mountToolbar();
        this.mountParagraphMenu();
        this.mountEmbedPopover();
    }

    /**
     * Mount an inline toolbar (react component).
     */
    private mountToolbar() {
        const container = this.jsBodyBoxContainer.querySelector(".js-InlineEditorToolbar");
        ReactDOM.render(
            <EditorProvider quill={this.quill}>
                <InlineToolbar />
            </EditorProvider>,
            container,
        );
    }

    /**
     * Mount the paragraph formatting toolbar (react component).
     */
    private mountParagraphMenu() {
        const container = this.jsBodyBoxContainer.querySelector(".js-ParagraphEditorToolbar");
        ReactDOM.render(
            <EditorProvider quill={this.quill}>
                <ParagraphToolbar />
            </EditorProvider>,
            container,
        );
    }

    /**
     * Mount Emoji Menu (react component).
     */
    private mountEmojiMenu() {
        const container = this.jsBodyBoxContainer.querySelector(".js-emojiHandle");
        ReactDOM.render(
            <EditorProvider quill={this.quill}>
                <EmojiPopover />
            </EditorProvider>,
            container,
        );
    }

    private mountEmbedPopover() {
        const container = this.jsBodyBoxContainer.querySelector(".js-EmbedDialogue");
        ReactDOM.render(
            <EditorProvider quill={this.quill}>
                <EmbedPopover />
            </EditorProvider>,
            container,
        );
    }

    private mountMentionToolbar() {
        const container = this.jsBodyBoxContainer.querySelector(".js-MentionToolbar");
        ReactDOM.render(
            <EditorProvider quill={this.quill}>
                <MentionToolbar />
            </EditorProvider>,
            container,
        );
    }
}
