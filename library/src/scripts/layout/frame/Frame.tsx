/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { frameClasses } from "@library/layout/frame/frameStyles";
import classNames from "classnames";

interface IProps {
    className?: string;
    children: React.ReactNode;
}

/**
 * Generic "frame" component. A frame is our generic term for flyouts or modals,
 * since they often use similar components.
 */
export default class Frame extends React.Component<IProps> {
    public render() {
        const classes = frameClasses();
        return (
            <section className={classNames("frame", this.props.className, classes.root)}>{this.props.children}</section>
        );
    }
}
