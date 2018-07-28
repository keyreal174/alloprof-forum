/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import React from "react";
import Quill, { RangeStatic, Sources } from "quill/core";
import Emitter from "quill/core/emitter";
import Keyboard from "quill/modules/keyboard";
import LinkBlot from "quill/formats/link";
import { t, isAllowedUrl } from "@dashboard/application";
import ToolbarContainer from "./pieces/ToolbarContainer";
import { withEditor, IWithEditorProps } from "@rich-editor/components/context";
import InlineToolbarLinkInput from "./pieces/InlineToolbarLinkInput";
import { watchFocusInDomTree } from "@dashboard/dom";
import { rangeContainsBlot, disableAllBlotsInRange } from "@rich-editor/quill/utility";
import CodeBlot from "@rich-editor/quill/blots/inline/CodeBlot";
import CodeBlockBlot from "@rich-editor/quill/blots/blocks/CodeBlockBlot";
import Formatter from "@rich-editor/quill/Formatter";
import InlineToolbarMenuItems from "@rich-editor/components/toolbars/pieces/InlineToolbarMenuItems";

interface IProps extends IWithEditorProps {}

interface IState {
    inputValue: string;
    isLinkMenuOpen: boolean;
    hasFocus: boolean;
}

export class InlineToolbar extends React.Component<IProps, IState> {
    private quill: Quill;
    private formatter: Formatter;
    private linkInput: React.RefObject<HTMLInputElement> = React.createRef();
    private selfRef: React.RefObject<HTMLDivElement> = React.createRef();

    /**
     * @inheritDoc
     */
    constructor(props) {
        super(props);

        // Quill can directly on the class as it won't ever change in a single instance.
        this.quill = props.quill;
        this.formatter = new Formatter(this.quill);

        this.state = {
            inputValue: "",
            isLinkMenuOpen: false,
            hasFocus: false,
        };
    }

    public componentDidUpdate(prevProps: IProps) {
        if (
            prevProps.instanceState.lastGoodSelection.index !== this.props.instanceState.lastGoodSelection.index ||
            prevProps.instanceState.lastGoodSelection.length !== this.props.instanceState.lastGoodSelection.length
        ) {
            this.setState({ isLinkMenuOpen: false });
        }
    }

    public render() {
        const { activeFormats, instanceState } = this.props;
        const { inputValue } = this.state;
        const alertMessage = this.isFormatMenuVisible ? (
            <span aria-live="assertive" role="alert" className="sr-only">
                {t("Inline Menu Available")}
            </span>
        ) : null;

        return (
            <div ref={this.selfRef}>
                <ToolbarContainer selection={instanceState.lastGoodSelection} isVisible={this.isFormatMenuVisible}>
                    {alertMessage}
                    <InlineToolbarMenuItems
                        formatter={this.formatter}
                        onLinkClick={this.openLinkMenu}
                        activeFormats={activeFormats}
                    />
                </ToolbarContainer>
                <ToolbarContainer selection={instanceState.lastGoodSelection} isVisible={this.isLinkMenuVisible}>
                    <InlineToolbarLinkInput
                        inputRef={this.linkInput}
                        inputValue={this.state.inputValue}
                        onInputChange={this.onInputChange}
                        onInputKeyDown={this.onInputKeyDown}
                        onCloseClick={this.onCloseClick}
                    />
                </ToolbarContainer>
            </div>
        );
    }

    private get isLinkMenuVisible(): boolean {
        return this.state.isLinkMenuOpen && this.hasSelectionOrFocus;
    }

    private get isFormatMenuVisible(): boolean {
        return !this.isLinkMenuVisible && this.hasSelectionOrFocus;
    }

    private get hasSelectionOrFocus() {
        const { currentSelection } = this.props.instanceState;
        return this.state.hasFocus || (!!currentSelection && currentSelection.length > 0);
    }

    /**
     * Mount quill listeners.
     */
    public componentDidMount() {
        document.addEventListener("keydown", this.escFunction, false);
        watchFocusInDomTree(this.selfRef.current!, this.handleFocusChange);

        // Add a key binding for the link popup.
        const keyboard: Keyboard = this.quill.getModule("keyboard");
        keyboard.addBinding(
            {
                key: "k",
                metaKey: true,
            },
            {},
            this.commandKHandler,
        );
    }

    /**
     * Be sure to remove the listeners when the component unmounts.
     */
    public componentWillUnmount() {
        document.removeEventListener("keydown", this.escFunction, false);
    }

    private handleFocusChange = hasFocus => {
        this.setState({ hasFocus });
    };

    /**
     * Handle create-link keyboard shortcut.
     */
    private commandKHandler = () => {
        const { lastGoodSelection } = this.props.instanceState;

        if (
            lastGoodSelection &&
            lastGoodSelection.length &&
            !this.isLinkMenuVisible &&
            !rangeContainsBlot(this.quill, CodeBlot) &&
            !rangeContainsBlot(this.quill, CodeBlockBlot)
        ) {
            if (rangeContainsBlot(this.quill, LinkBlot, lastGoodSelection)) {
                this.formatter.link();
                this.reset();
            } else {
                const currentText = this.quill.getText(lastGoodSelection.index, lastGoodSelection.length);
                this.setState({ isLinkMenuOpen: true });

                if (isAllowedUrl(currentText)) {
                    this.setState({
                        inputValue: currentText,
                    });
                }
            }
        }
    };

    private openLinkMenu = () => {
        if (typeof this.props.activeFormats.link === "string") {
            this.setState({ isLinkMenuOpen: false });
            this.formatter.link();
        } else {
            this.setState({ isLinkMenuOpen: true }, () => {
                this.linkInput.current!.focus();
            });
        }
    };

    /**
     * Close the menu.
     */
    private escFunction = (event: KeyboardEvent) => {
        if (event.keyCode === 27) {
            if (this.isLinkMenuVisible) {
                event.preventDefault();
                this.clearLinkInput();
            } else if (this.isFormatMenuVisible) {
                event.preventDefault();
                this.cancel();
            }
        }
    };

    private clearLinkInput() {
        this.setState({ isLinkMenuOpen: false, inputValue: "" });
    }

    /**
     * Handle clicks on the link menu's close button.
     */
    private onCloseClick = (event: React.MouseEvent<any>) => {
        event.preventDefault();
        this.clearLinkInput();
    };

    /**
     * Clear the link menu's input content and hide the link menu.
     */
    private reset = () => {
        this.props.instanceState.lastGoodSelection &&
            this.quill.setSelection(this.props.instanceState.lastGoodSelection, Emitter.sources.USER);

        this.setState({
            inputValue: "",
        });
    };

    private cancel = () => {
        const { lastGoodSelection } = this.props.instanceState;
        const newSelection = {
            index: lastGoodSelection.index + lastGoodSelection.length,
            length: 0,
        };
        this.setState({
            inputValue: "",
        });
        this.quill.setSelection(newSelection);
    };

    /**
     * Handle key-presses for the link toolbar.
     */
    private onInputKeyDown = (event: React.KeyboardEvent<any>) => {
        if (Keyboard.match(event.nativeEvent, "enter")) {
            event.preventDefault();
            this.quill.format("link", this.state.inputValue, Emitter.sources.USER);
            this.clearLinkInput();
        }
    };

    /**
     * Handle changes to the the close menu's input.
     */
    private onInputChange = (event: React.ChangeEvent<any>) => {
        this.setState({ inputValue: event.target.value });
    };
}

export default withEditor<IProps>(InlineToolbar);
