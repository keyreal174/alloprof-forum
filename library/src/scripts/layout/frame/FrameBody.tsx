/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { frameBodyClasses } from "@library/layout/frame/frameStyles";
import classNames from "classnames";

export interface IFrameBodyProps {
    className?: string;
    children: React.ReactNode;
}

/**
 * This section goes between the header/footer.
 * Note the each child will be split into a separate "FramePanel". This will allow animations/transitions in the future.
 */
export default class FrameBody extends React.PureComponent<IFrameBodyProps> {
    public render() {
        const classes = frameBodyClasses();
        return <div className={classNames("frameBody", this.props.className, classes.root)}>{this.props.children}</div>;
    }
}
