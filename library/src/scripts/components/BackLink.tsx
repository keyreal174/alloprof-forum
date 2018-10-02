/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";
import { t } from "../application";
import { leftChevron } from "Icons";
import { Link } from "react-router-dom";

interface IBackLink {
    url?: string;
    title?: string;
    className?: string;
}

export default class BackLink extends React.Component<IBackLink> {
    public static defaultProps = {
        title: t("Back"),
    };
    public render() {
        if (this.props.url) {
            return (
                <div className={classNames("backLink", this.props.className)}>
                    <Link
                        to={this.props.url}
                        aria-label={this.props.title}
                        title={this.props.title}
                        className="backLink-link"
                    >
                        {leftChevron("backLink-icon")}
                    </Link>
                </div>
            );
        } else {
            return null;
        }
    }
}
