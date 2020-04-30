/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { t } from "@library/utility/appUtils";
import React, { Component } from "react";
import moment from "moment";
import { getJSLocaleKey } from "@vanilla/i18n";

interface IProps {
    /** The timestamp to format and display */
    timestamp: string;
    /** Pass an explicit time zone to format in. */
    timezone: string;
    /** An additional classname to apply to the root of the component */
    className?: string;
    /** Display a fixed or relative visible time. */
    mode?: "relative" | "fixed";
    extended?: boolean;
}

/**
 * Component for displaying an accessible nicely formatted time string.
 */
export default class DateTime extends Component<IProps> {
    public static defaultProps: Partial<IProps> = {
        mode: "fixed",
    };
    private interval;

    public render() {
        return (
            <time className={this.props.className} dateTime={this.props.timestamp} title={this.titleTime}>
                {this.humanTime}
            </time>
        );
    }

    public componentDidMount() {
        if (this.props.mode === "relative") {
            this.interval = setInterval(() => {
                this.forceUpdate();
            }, 30000);
        }
    }

    public componentWillUnmount() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

    /**
     * Get the title of the time tag (long extended date)
     */
    private get titleTime(): string {
        const date = new Date(this.props.timestamp);
        return date.toLocaleString(getJSLocaleKey(), {
            year: "numeric",
            month: "long",
            day: "numeric",
            weekday: "long",
            hour: "numeric",
            minute: "numeric",
            timeZone: this.props.timezone,
        });
    }

    /**
     * Get a shorter human readable time for the time tag.
     */
    private get humanTime(): string {
        const inputMoment = moment(this.props.timestamp);

        if (this.props.mode === "relative") {
            const difference = moment.duration(moment().diff(inputMoment));
            const seconds = difference.asSeconds();
            if (seconds >= 0 && seconds <= 5) {
                return t("just now");
            }

            return inputMoment.from(moment());
        } else {
            const options = this.props.extended
                ? {
                      year: "numeric",
                      month: "short",
                      day: "numeric",
                      hour: "numeric",
                      minute: "numeric",
                      timeZone: this.props.timezone,
                  }
                : {
                      year: "numeric",
                      month: "short",
                      day: "numeric",
                      timeZone: this.props.timezone,
                  };
            return inputMoment.toDate().toLocaleString(getJSLocaleKey(), options);
        }
    }
}
