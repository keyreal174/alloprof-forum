/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { t } from "@library/application";
import CloseButton from "@library/components/CloseButton";
import { withEditor, IWithEditorProps } from "@rich-editor/components/context";
import { richEditorClasses } from "@rich-editor/styles/richEditorStyles/richEditorClasses";
import classNames from "classnames";
import { insertLinkClasses } from "@rich-editor/styles/richEditorStyles/insertLinkClasses";
import { dropDownClasses } from "@library/styles/dropDownStyles";

interface IProps extends IWithEditorProps {
    inputRef: React.RefObject<HTMLInputElement>;
    inputValue: string;
    onInputKeyDown: React.KeyboardEventHandler<any>;
    onInputChange: React.ChangeEventHandler<any>;
    onCloseClick: React.MouseEventHandler<any>;
}

export class InlineToolbarLinkInput extends React.PureComponent<IProps, {}> {
    constructor(props) {
        super(props);

        this.state = {
            linkValue: "",
            cachedRange: {
                index: 0,
                length: 0,
            },
            showFormatMenu: false,
            showLinkMenu: false,
        };
    }

    public render() {
        const classesRichEditor = richEditorClasses();
        const classesInsertLink = insertLinkClasses();
        const classesDropDown = dropDownClasses();
        return (
            <div
                className={classNames(
                    "richEditor-menu",
                    "insertLink",
                    "likeDropDownContent",
                    classesRichEditor.menu,
                    classesInsertLink.root,
                    classesDropDown.likeDropDownContent,
                )}
                role="dialog"
                aria-label={t("Insert Url")}
            >
                <input
                    value={this.props.inputValue}
                    onChange={this.props.onInputChange}
                    ref={this.props.inputRef}
                    onKeyDown={this.props.onInputKeyDown}
                    className={classNames("InputBox", "inputText", "insertLink-input", classesInsertLink.input)}
                    placeholder={t("Paste or type url")}
                />
                <CloseButton
                    className={classNames("richEditor-close", classesRichEditor.close)}
                    onClick={this.props.onCloseClick}
                    legacyMode={this.props.legacyMode}
                />
            </div>
        );
    }
}

export default withEditor<IProps>(InlineToolbarLinkInput);
