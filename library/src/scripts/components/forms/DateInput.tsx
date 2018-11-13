/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import DayPickerInput from "react-day-picker/DayPickerInput";
import { formatDate, parseDate } from "react-day-picker/moment";
import { guessOperatingSystem, OS } from "@library/utility";
import classNames from "classnames";
import { t } from "@library/application";
import Button, { ButtonBaseClass } from "@library/components/forms/Button";
import { leftChevron, rightChevron } from "@library/components/icons";
import { NullComponent } from "@library/components/forms/select/overwrites";

interface IProps {
    value: string; // ISO formatted date
    onChange: (value: string) => void;
    className?: string;
    alignment: "left" | "right";
    disabledDays?: any; // See http://react-day-picker.js.org/examples/disabled
}

interface IState {
    hasBadValue: boolean;
    wasBlurred: boolean;
}

/**
 * Implements the DateRange component
 */
export default class DateInput extends React.PureComponent<IProps, IState> {
    public static defaultProps: Partial<IProps> = {
        alignment: "left",
    };

    public state: IState = {
        hasBadValue: false,
        wasBlurred: false,
    };

    public render() {
        // Attempt to use a native input on operating systems that have nice, accessible built in date pickers.
        // EG. mobile
        const os = guessOperatingSystem();
        const useNativeInput = os === OS.ANDROID || os === OS.IOS;

        return useNativeInput ? this.renderNativeInput() : this.renderReactInput();
    }

    /**
     * Render a react day picker component.
     */
    private renderReactInput() {
        const value = this.props.value ? new Date(this.props.value) : undefined;
        return (
            <DayPickerInput
                format="YYYY-MM-DD"
                placeholder={t(`yyyy-mm-dd`)}
                formatDate={formatDate}
                parseDate={parseDate}
                value={value}
                overlayComponent={this.CustomOverlay}
                onDayChange={this.handleDateChange}
                onChange={this.handleTextChange}
                dayPickerProps={{
                    captionElement: NullComponent,
                    navbarElement: this.CustomNavBar,
                    disabledDays: this.props.disabledDays,
                    showOutsideDays: true,
                }}
                inputProps={{
                    className: classNames("inputText", this.props.className, {
                        isInvalid: this.state.hasBadValue && this.state.wasBlurred,
                    }),
                    "aria-label": t("Date Input ") + "(yyyy-mm-dd)",
                    onBlur: this.handleBlur,
                    onFocus: this.handleFocus,
                }}
            />
        );
    }

    /**
     * Render a native date picker component. These can be much nicer on mobile devices.
     */
    private renderNativeInput() {
        // The native date input MUST have it's value in short ISO format, even it doesn't display that way.
        const value = this.props.value ? this.props.value.substr(0, 10) : "";
        return (
            <input
                className="inputText"
                type="date"
                placeholder={t(`yyyy-mm-dd`)}
                onChange={this.handleNativeInputChange}
                value={value}
            />
        );
    }

    /**
     * Handle a new date.
     */
    private handleDateChange = (date?: Date | null) => {
        if (date) {
            this.setState({ hasBadValue: false });
            this.props.onChange(date.toISOString());
        } else {
            // invalid date
            this.setState({ hasBadValue: true });
            this.props.onChange("");
        }
    };

    /**
     * Track blurred state.
     */
    private handleBlur = (event: React.FocusEvent) => {
        this.setState({ wasBlurred: true });
    };

    /**
     * Track blurred state.
     */
    private handleFocus = (event: React.FocusEvent) => {
        this.setState({ wasBlurred: false });
    };

    /**
     * Handle text changes in the main input.
     */
    private handleTextChange = (event: React.ChangeEvent<any>) => {
        const date = new Date(event.target.value);
        this.handleDateChange(date);
    };

    /**
     * Handle changes in the native input.
     */
    private handleNativeInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        this.handleDateChange(event.target.valueAsDate);
    };

    /**
     * Override for the date pickers dropdown.
     */
    private CustomOverlay = ({ classNames: c, selectedDay, children, ...props }) => {
        const contentsClasses = classNames("dropDown-contents", "isOwnWidth", {
            isRightAligned: this.props.alignment === "right",
        });
        return (
            <div className="dropDown" {...props}>
                <div className={contentsClasses}>{children}</div>
            </div>
        );
    };

    /**
     * Override date pickers navigation component to use our icons.
     */
    private CustomNavBar = ({ month, onPreviousClick, onNextClick, className }) => {
        // The example override shows these methods being rebound in this way.
        // If you attempt to pass these callbacks directly to the overriden component,
        // They crash it when clicked.
        const prev = () => onPreviousClick();
        const next = () => onNextClick();
        const title = (month as Date).toLocaleDateString(undefined, { year: "numeric", month: "long" });

        return (
            <div className="datePicker-header">
                <h3 className="datePicker-title">{title}</h3>
                <span className={classNames("datePicker-navigation", className)}>
                    <Button baseClass={ButtonBaseClass.ICON} onClick={prev}>
                        {leftChevron()}
                    </Button>
                    <Button baseClass={ButtonBaseClass.ICON} onClick={next}>
                        {rightChevron()}
                    </Button>
                </span>
            </div>
        );
    };
}
