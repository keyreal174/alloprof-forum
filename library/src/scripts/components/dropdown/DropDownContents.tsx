/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";
import { flyoutPosition } from "@rich-editor/components/popovers/pieces/flyoutPosition";
import { dropDownClasses } from "@library/styles/dropDownStyles";

export interface IProps {
    id: string;
    parentID: string;
    className?: string;
    children: React.ReactNode;
    isVisible?: boolean;
    renderAbove: boolean;
    renderLeft: boolean;
    onClick: (event: React.MouseEvent) => void;
    legacyMode?: boolean;
    openAsModal?: boolean;
}
/**
 * The contents of the dropdown (not the wrapper and not the button to toggle it).
 * Note that it renders an empty, hidden div when closed so that the aria-labelledby points to an element in the DOM.
 */
export default class DropDownContents extends React.Component<IProps> {
    public render() {
        const classes = dropDownClasses();
        const asDropDownClasses = !this.props.openAsModal
            ? classNames("dropDown-contents", classes.contents)
            : undefined;
        const asModalClasses = this.props.openAsModal ? classNames("dropDown-asModal", classes.asModal) : undefined;

        if (this.props.isVisible) {
            return (
                <div
                    id={this.props.id}
                    aria-labelledby={this.props.parentID}
                    className={classNames(asDropDownClasses, asModalClasses, this.props.className)}
                    style={flyoutPosition(this.props.renderAbove, this.props.renderLeft, !!this.props.legacyMode)}
                    onClick={this.props.onClick}
                >
                    {this.props.children}
                </div>
            );
        } else {
            return (
                <div id={this.props.id} aria-hidden={true} aria-labelledby={this.props.parentID} className="sr-only" />
            ); // for accessibility
        }
    }
}
