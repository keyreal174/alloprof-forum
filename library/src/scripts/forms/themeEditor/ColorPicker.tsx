/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import Button from "@library/forms/Button";
import { ButtonTypes } from "@library/forms/buttonStyles";
import { colorPickerClasses } from "@library/forms/themeEditor/colorPickerStyles";
import { themeBuilderClasses } from "@library/forms/themeEditor/themeBuilderStyles";
import { visibility } from "@library/styles/styleHelpersVisibility";
import { isValidColor, stringIsValidColor } from "@library/styles/styleUtils";
import { uniqueIDFromPrefix } from "@library/utility/idUtils";
import { t } from "@vanilla/i18n/src";
import classNames from "classnames";
import { color, ColorHelper } from "csx";
import { useField } from "formik";
import React, { useMemo, useRef, useState } from "react";

type IErrorWithDefault = string | boolean; // Uses default message if true
type VoidFunction = () => void;
export interface IColorPicker {
    inputProps?: Omit<React.HTMLAttributes<HTMLInputElement>, "type" | "id" | "tabIndex">;
    inputID: string;
    variableID: string;
    labelID: string;
    defaultValue?: ColorHelper;
    inputClass?: string;
    errors?: IErrorWithDefault[]; // Uses default message if true;
    handleChange?: VoidFunction;
}

export const getDefaultOrCustomErrorMessage = (error: string | true, defaultMessage: string) => {
    return typeof error === "string" ? error : defaultMessage;
};

export const ensureColorHelper = (colorValue: string | ColorHelper) => {
    return typeof colorValue === "string" ? color(colorValue) : colorValue;
};

export default function ColorPicker(props: IColorPicker) {
    const classes = colorPickerClasses();
    const colorInput = useRef<HTMLInputElement>(null);
    const textInput = useRef<HTMLInputElement>(null);
    const builderClasses = themeBuilderClasses();

    const errorID = useMemo(() => {
        return uniqueIDFromPrefix("colorPickerError");
    }, []);

    // String
    const initialValidColor =
        props.defaultValue && isValidColor(props.defaultValue.toString()) ? props.defaultValue.toString() : "#000";

    const [selectedColor, selectedColorMeta, helpers] = useField(props.variableID);
    const [validColor, setValidColor] = useState(initialValidColor);

    const clickReadInput = () => {
        if (colorInput && colorInput.current) {
            colorInput.current.click();
        }
    };

    const onTextChange = e => {
        const colorString = e.target.value;
        helpers.setTouched(true);
        console.log("props.handleChange", props.handleChange);
        if (stringIsValidColor(colorString)) {
            setValidColor(colorString); // Only set valid color if passes validation
            props.handleChange?.();
        }
        helpers.setValue(colorString); // Text is unchanged
    };

    const onPickerChange = e => {
        // Will always be valid color, since it's a real picker
        const newColor: string = e.target.value;

        if (newColor) {
            helpers.setTouched(true);
            helpers.setValue(newColor);
            setValidColor(
                color(newColor)
                    .toRGB()
                    .toString(),
            );
            props.handleChange?.();
        }
    };

    const textValue =
        selectedColor.value !== undefined
            ? selectedColor.value
            : props.defaultValue && isValidColor(props.defaultValue)
            ? props.defaultValue.toHexString()
            : "";
    const hasError = !isValidColor(textValue);

    return (
        <>
            <span className={classes.root}>
                {/*"Real" color input*/}
                <input
                    {...props.inputProps}
                    ref={colorInput}
                    type="color"
                    id={props.inputID}
                    aria-describedby={props.labelID}
                    className={classNames(classes.realInput, visibility().visuallyHidden)}
                    onChange={onPickerChange}
                    onBlur={onPickerChange}
                    aria-errormessage={hasError ? errorID : undefined}
                    defaultValue={initialValidColor}
                />
                {/*Text Input*/}
                <input
                    ref={textInput}
                    type="text"
                    aria-describedby={props.labelID}
                    aria-hidden={true}
                    className={classNames(classes.textInput, {
                        [classes.invalidColor]: hasError,
                    })}
                    placeholder={"#0291DB"}
                    value={textValue}
                    onChange={onTextChange}
                    auto-correct="false"
                />
                {/*Swatch*/}
                <Button
                    onClick={clickReadInput}
                    style={{ backgroundColor: color(validColor).toString() }}
                    title={validColor}
                    aria-hidden={true}
                    className={classes.swatch}
                    tabIndex={-1}
                    baseClass={ButtonTypes.CUSTOM}
                >
                    <span className={visibility().visuallyHidden}>{color(validColor).toHexString()}</span>
                </Button>
            </span>
            {selectedColorMeta.error && (
                <ul id={errorID} className={builderClasses.errorContainer}>
                    <li className={builderClasses.error}>
                        {getDefaultOrCustomErrorMessage(builderClasses.error, t("Invalid color"))}
                    </li>
                </ul>
            )}
        </>
    );
}
