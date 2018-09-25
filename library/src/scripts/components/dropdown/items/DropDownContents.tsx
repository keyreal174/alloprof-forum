/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";

export interface IProps {
    className?: string;
    children: React.ReactNode;
    isVisible: boolean;
}

export default class DropDownContents extends React.Component<IProps> {
    public render() {
        if (this.props.isVisible) {
            return <div className={classNames("dropDown-contents", this.props.className)}>{this.props.children}</div>;
        } else {
            return null;
        }
    }
}
