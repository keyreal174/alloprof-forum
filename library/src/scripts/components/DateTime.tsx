/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";

interface IProps {
    timestamp: string;
    className?: string;
}

export default class DateTime extends React.Component<IProps> {
    public render() {
        return (
            <time className={this.props.className} dateTime={this.props.timestamp} title={this.titleTime}>
                {this.humanTime}
            </time>
        );
    }

    /**
     * Get the title of the time tag (long extended date)
     */
    private get titleTime(): string {
        const date = new Date(this.props.timestamp);
        return date.toLocaleString(undefined, {
            year: "numeric",
            month: "long",
            day: "numeric",
            weekday: "long",
            hour: "numeric",
            minute: "numeric",
        });
    }

    /**
     * Get a shorter human readable time for the time tag.
     */
    private get humanTime(): string {
        const date = new Date(this.props.timestamp);
        return date.toLocaleString(undefined, { year: "numeric", month: "short", day: "numeric" });
    }
}
