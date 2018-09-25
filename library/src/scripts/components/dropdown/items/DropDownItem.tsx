/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";

export interface IProps {
    children: React.ReactNode;
    className?: string;
}

export default class DropDownItem extends React.Component<IProps> {
    public render() {
        return <li className={classNames("dropDown-item", this.props.className)}>{this.props.children}</li>;
    }
}
