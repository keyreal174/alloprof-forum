/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { paragraphMenuCheckRadioClasses } from "@rich-editor/menuBar/paragraph/paragraphMenuBarStyles";
import { t } from "@library/utility/appUtils";
import ParagraphMenuBarRadioGroup, {
    IMenuBarRadioButton,
} from "@rich-editor/menuBar/paragraph/items/ParagraphMenuBarRadioGroup";
import ParagraphMenuSeparator from "@rich-editor/menuBar/paragraph/items/ParagraphMenuSeparator";
import classNames from "classnames";
import { indent, outdent } from "@library/icons/editorIcons";
import { richEditorClasses } from "@rich-editor/editor/richEditorClasses";

interface IProps {
    closeMenu: () => void;
    closeMenuAndSetCursor: () => void;
    items: IMenuBarRadioButton[];
    setRovingIndex: () => void;
    indent: () => void;
    outdent: () => void;
    disabled?: boolean;
}

/**
 * Implemented tab content for menu list
 */
export default class ParagraphMenuListsTabContent extends React.Component<IProps> {
    public render() {
        const classes = richEditorClasses(false);
        const checkRadioClasses = paragraphMenuCheckRadioClasses();
        const handleClick = (data: IMenuBarRadioButton, index: number) => {
            this.props.items[index].formatFunction();
            this.props.setRovingIndex();
            this.props.closeMenuAndSetCursor();
        };
        return (
            <>
                <ParagraphMenuBarRadioGroup
                    className={classes.paragraphMenuPanel}
                    label={t("List Types")}
                    items={this.props.items}
                    handleClick={handleClick}
                    disabled={!!this.props.disabled}
                />
                <ParagraphMenuSeparator />
                <div className={checkRadioClasses.group}>
                    <button
                        className={classNames(checkRadioClasses.checkRadio)}
                        type="button"
                        onClick={this.props.indent}
                        disabled={!!this.props.disabled}
                        tabIndex={!!this.props.disabled ? -1 : 0}
                    >
                        <span className={checkRadioClasses.icon}>{indent()}</span>
                        <span className={checkRadioClasses.checkRadioLabel}>{t("Indent")}</span>
                    </button>
                    <button
                        className={classNames(checkRadioClasses.checkRadio)}
                        type="button"
                        onClick={this.props.outdent}
                        disabled={!!this.props.disabled}
                        tabIndex={!!this.props.disabled ? -1 : 0}
                    >
                        <span className={checkRadioClasses.icon}>{outdent()}</span>
                        <span className={checkRadioClasses.checkRadioLabel}>{t("Outdent")}</span>
                    </button>
                </div>
            </>
        );
    }
}
