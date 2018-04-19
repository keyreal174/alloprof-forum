/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import Quill, { Blot } from "quill/core";
import Delta from "quill-delta";
import Parchment from "parchment";
import KeyboardModule from "quill/modules/keyboard";
import Module from "quill/core/module";
import { RangeStatic } from "quill/core";
import { delegateEvent } from "@core/dom";
import FocusableEmbedBlot from "./Blots/Abstract/FocusableEmbedBlot";
import { normalizeBlotIntoBlock, insertNewLineAtEndOfScroll, insertNewLineAtStartOfScroll } from "./utility";

/**
 * A module for managing focus of Embeds. For this to work for a new Embed,
 * ensure that you're embed extends FocusEmbedBlot
 *
 * @see {FocusableEmbedBlot}
 */
export default class EmbedFocusModule extends Module {
    /**
     * Some keyboard bindings need to run before quill's own event listeners. Events here are added to Quill's
     * KeyboardModule and run before its built in listeners.
     */
    public earlyKeyBoardBindings = {};

    private keyboard: KeyboardModule;

    /** The previous selection */
    private lastSelection: RangeStatic = {
        index: 0,
        length: 0,
    };

    /**
     * Whether or not an early keyboard binding has prevented default.
     * Quill doesn't stop event propagation or give the event in the handler.
     */
    private hasHandledDelete = false;

    constructor(quill: Quill, options = {}) {
        super(quill, options);

        // This needs to be exported in set in the quill options, before the keyboard module is instantiated, so that it's handler runs before quill's delete handler. Otherwise we can't prevent default.
        this.earlyKeyBoardBindings["Backspace over Blot"] = {
            key: KeyboardModule.keys.BACKSPACE,
            handler: this.earlyQuillDeleteListener,
        };

        // Add event listeners.
        quill.on("selection-change", (range, oldRange, source) => {
            if (range && range.index && source !== Quill.sources.SILENT) {
                this.lastSelection = range;
            }
        });

        delegateEvent(
            "click",
            ".js-richText .embed",
            (event, clickedElement) => {
                const embed = Parchment.find(clickedElement);
                if (embed instanceof FocusableEmbedBlot) {
                    this.focusEmbedBlot(embed);
                    event.preventDefault();
                }
            },
            this.quill.container,
        );

        this.quill.container.addEventListener("keydown", this.keyDownListener);
    }

    /**
     * This needs to be exported in set in the quill options, before the keyboard module is instantiated.
     *
     * @if
     * - Backspace is pressed
     * - The current Blot is an empty Blot.
     * - The previous blot is an embed blot.
     *
     * @then
     * - Delete the current empty line.
     * - Set focus on the previous embed Blot.
     * - Prevent handleDeleteOnEmbed on from running.
     */
    private earlyQuillDeleteListener = () => {
        const [currentBlot] = this.quill.getLine(this.quill.getSelection().index);
        const previousBlot = currentBlot.prev;
        const isPreviousBlotEmbed = previousBlot instanceof FocusableEmbedBlot;
        const isCurrentBlotEmpty = currentBlot.domNode.textContent === "";

        if (isPreviousBlotEmbed && isCurrentBlotEmpty) {
            this.hasHandledDelete = true;

            // This is kind of ugly but I can't think of a better way to handle it. There is no way to know in the context of `handleDeleteOnEmbed` otherwise if quill has prevented default (it doesn't stop propagation).
            setTimeout(() => {
                this.hasHandledDelete = false;
            }, 0);
            (currentBlot as Blot).remove();
            this.quill.update(Quill.sources.USER);
            this.focusEmbedBlot(previousBlot);
            return false;
        }

        return true;
    };

    /**
     * Keydown listener on the current quill instance.
     */
    private keyDownListener = (event: KeyboardEvent) => {
        if (!this.quill.container.contains(document.activeElement)) {
            return;
        }

        const exclusiveHandlers = [
            this.handleArrowKeyFromEmbed,
            this.handleArrowKeyAwayFromQuill,
            this.handleDeleteOnEmbed,
            this.handleEnterOnEmbed,
        ];

        // Run all of the event listeners until one returns false;
        return exclusiveHandlers.reduce((shouldContinue, currentHandler) => {
            return shouldContinue ? currentHandler(event) : false;
        }, true);
    };

    /**
     * Handle delete and backspace presses while an Embed is focussed.
     *
     * @if
     * - Backspace or Delete is pressed
     * - An Embed blot is focused
     *
     * @then
     * - Delete the embed blot.
     */
    private handleDeleteOnEmbed = (event: KeyboardEvent) => {
        if (!this.isKeyCodeDelete(event.keyCode)) {
            return true;
        }

        if (this.hasHandledDelete) {
            return true;
        }

        const blotForActiveElement = this.getEmbedBlotForFocusedElement();
        const focusItemIsEmbedBlot = blotForActiveElement instanceof FocusableEmbedBlot;
        if (blotForActiveElement && focusItemIsEmbedBlot) {
            event.preventDefault();
            const offset = blotForActiveElement.offset();
            blotForActiveElement.remove();
            this.quill.update(Quill.sources.USER);

            const [potentialNewEmbedToFocus] = this.quill.scroll.descendant(FocusableEmbedBlot as any, offset);
            if (potentialNewEmbedToFocus) {
                this.focusEmbedBlot(potentialNewEmbedToFocus as any);
            } else {
                this.quill.setSelection(offset, 0, Quill.sources.USER);
            }

            return false;
        }

        return true;
    };

    /**
     * Handle enter presses while an embed is selected
     *
     * @if
     * - Enter is pressed
     * - An Embed blot is focused.
     *
     * @then
     * - Insert a newline after the embed.
     */
    private handleEnterOnEmbed = (event: KeyboardEvent) => {
        if (event.keyCode !== KeyboardModule.keys.ENTER) {
            return true;
        }

        // Only works for items scoped within itself.
        const blotForActiveElement = this.getEmbedBlotForFocusedElement();
        const focusItemIsEmbedBlot = blotForActiveElement instanceof FocusableEmbedBlot;
        if (blotForActiveElement && focusItemIsEmbedBlot) {
            event.preventDefault();
            const newBlot = Parchment.create("block", "");
            newBlot.insertInto(this.quill.scroll, blotForActiveElement.next);
            this.quill.update(Quill.sources.USER);
            this.quill.setSelection(blotForActiveElement.offset() + 1, 0, Quill.sources.USER);
            return false;
        }

        return true;
    };

    /**
     * Handle arrow keys if the quill document has focus.
     *
     * @if
     * - An arrow key is pressed
     * - The quill document has focus
     * - The next or previous item in the document is an Embed
     *
     * @then
     * - Set focus on that embed.
     */
    private handleArrowKeyAwayFromQuill = (event: KeyboardEvent) => {
        if (!this.isKeyCodeArrowKey(event.keyCode) || event.shiftKey) {
            return true;
        }

        if (document.activeElement === this.quill.root) {
            const [currentBlot] = this.quill.getLine(this.quill.getSelection().index);
            const blotToMoveTo = this.findBlotToMoveTo(currentBlot, event.keyCode);

            if (blotToMoveTo instanceof FocusableEmbedBlot) {
                this.focusEmbedBlot(blotToMoveTo);
                event.preventDefault();
                return false;
            }
        }

        return true;
    };

    /**
     * Handle arrow keys while an embed is Focused.
     *
     * @if
     * - And Embed is Focused
     * - An arrow key is pressed
     *
     * @andif
     * - The Embed is at the beginnning or the end of the document.
     * @then
     * - Insert a new line and move the selection before or after the Embed.
     *
     * @andif
     * - The next or previous Blot is an Embed Blot
     * @then
     * - Set focus on that Blot
     *
     * @else
     * - Move the focus to the next or previous text content.
     */
    private handleArrowKeyFromEmbed = (event: KeyboardEvent) => {
        if (!this.isKeyCodeArrowKey(event.keyCode)) {
            return true;
        }

        const blotForActiveElement = this.getEmbedBlotForFocusedElement();
        if (!(blotForActiveElement instanceof FocusableEmbedBlot)) {
            return true;
        }

        // Check if we are at the beginning or end of the scroll.
        const position = blotForActiveElement.offset();
        const isEndOfScroll = position + blotForActiveElement.length() === this.quill.scroll.length();
        const isStartOfScroll = position === 0;
        const isUpOrLeft = [KeyboardModule.keys.LEFT, KeyboardModule.keys.UP].includes(event.keyCode as any);
        const isDownOrRight = [KeyboardModule.keys.RIGHT, KeyboardModule.keys.DOWN].includes(event.keyCode as any);
        if (isStartOfScroll && isUpOrLeft) {
            event.preventDefault();
            insertNewLineAtStartOfScroll(this.quill);
            return false;
        } else if (isEndOfScroll && isDownOrRight) {
            event.preventDefault();
            insertNewLineAtEndOfScroll(this.quill);
            return false;
        }

        // Check if we have a blot to move to.
        const blotToMoveTo = this.findBlotToMoveTo(blotForActiveElement, event.keyCode);
        if (!blotToMoveTo) {
            return true;
        }

        event.preventDefault();

        if (blotToMoveTo instanceof FocusableEmbedBlot) {
            this.focusEmbedBlot(blotToMoveTo);
        } else {
            // We want to mimic normal movement behaviour as if our Blot was text, so
            // We check if we need to put the cursor in the middle of the next or previous line.
            const newElementStart = blotToMoveTo.offset();
            const newElementEnd = newElementStart + blotToMoveTo.length();
            const previousIndex = this.lastSelection.index;
            const shouldUsePreviousIndex = previousIndex >= newElementStart && previousIndex < newElementEnd;
            const newIndex = shouldUsePreviousIndex ? previousIndex : newElementStart;
            this.quill.setSelection(newIndex, 0);
        }

        return false;
    };

    /**
     * Detect if an keyCode is of an arrow key.
     */
    private isKeyCodeArrowKey(keyCode: number) {
        const { UP, DOWN, LEFT, RIGHT } = KeyboardModule.keys;
        return [UP, DOWN, LEFT, RIGHT].includes(keyCode as any);
    }

    /**
     * Detect if an keyCode is of Delete or Backspace
     */
    private isKeyCodeDelete(keyCode: number) {
        const { BACKSPACE, DELETE } = KeyboardModule.keys;
        return [BACKSPACE, DELETE].includes(keyCode as any);
    }

    /**
     * Focus an Embed blot.
     */
    private focusEmbedBlot(blot: FocusableEmbedBlot) {
        const blotPosition = blot.offset();
        this.quill.setSelection(blotPosition, 0, Quill.sources.SILENT);
        blot.focus();
    }

    /**
     * Check if the focused element in the document is of an Embed blot and return it.
     */
    private getEmbedBlotForFocusedElement() {
        if (!(document.activeElement instanceof Element)) {
            return;
        }

        let activeElement = document.activeElement;
        if (!activeElement.classList.contains("embed")) {
            const closestEmbed = activeElement.closest(".embed");
            if (!closestEmbed) {
                return;
            }

            activeElement = closestEmbed;
        }

        return Parchment.find(activeElement);
    }

    /**
     * Determine which Blot we want to move to based on the arrow key pressed.
     *
     * @param currentBlot The blot to check.
     * @param keyCode The keycode that was pressed.
     */
    private findBlotToMoveTo(currentBlot: Blot, keyCode: number) {
        switch (keyCode) {
            case KeyboardModule.keys.DOWN:
                return currentBlot.next as Blot;
            case KeyboardModule.keys.UP:
                return currentBlot.prev as Blot;
            case KeyboardModule.keys.RIGHT:
                // -1 needed for because end of blot is non-inclusive.
                const endOfBlot = currentBlot.offset() + currentBlot.length() - 1;
                const currentBlotOffset = currentBlot.offset();
                const currentBlotLength = currentBlot.length();
                const currentSelection = this.quill.getSelection();
                if (this.quill.getSelection().index === endOfBlot) {
                    // If we're at the end of the line.
                    return currentBlot.next;
                }
                break;
            case KeyboardModule.keys.LEFT:
                if (this.quill.getSelection().index === currentBlot.offset()) {
                    // If we're at the start of the line.
                    return currentBlot.prev;
                }
                break;
        }
    }
}
