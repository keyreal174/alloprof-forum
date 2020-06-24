/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { containerClasses } from "@library/layout/components/containerStyles";
import classNames from "classnames";

export interface IContainer {
    className?: string;
    children?: React.ReactNode;
    tag?: keyof JSX.IntrinsicElements;
    fullGutter?: boolean; // Use when a component wants a full mobile/desktop gutter.
    // Useful for components that don't provide their own padding.
    narrow?: boolean;
}

/*
 * Implements "Container" component used to set max width of content of page.
 */
export function Container(props: IContainer) {
    const { tag, children, className, fullGutter = false, narrow = false } = props;

    if (children) {
        const classes = containerClasses();
        const Tag = tag || "div";
        return (
            <Tag
                className={classNames(classes.root, className, {
                    [classes.fullGutter]: fullGutter,
                    isNarrow: narrow,
                })}
            >
                {children}
            </Tag>
        );
    } else {
        return null;
    }
}

export default Container;
