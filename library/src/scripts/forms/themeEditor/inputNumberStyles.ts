/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { calc, percent, translateX, translateY } from "csx";
import {
    absolutePosition,
    borders,
    colorOut,
    fonts,
    negativeUnit,
    textInputSizingFromFixedHeight,
    unit,
    userSelect,
} from "@library/styles/styleHelpers";
import { themeBuilderVariables } from "@library/forms/themeEditor/themeBuilderStyles";
import { IGlobalBorderStyles } from "@library/styles/globalStyleVars";

export const inputNumberVariables = useThemeCache(() => {
    // Intentionally not overwritable with theming system.
    const builderVars = themeBuilderVariables();
    return {
        label: builderVars.label,
        sizing: {
            height: builderVars.input.height,
        },
        input: {
            font: {
                size: builderVars.label.fonts.size,
            },
            width: 80,
        },
        spinner: {
            width: 18,
            fonts: {
                ...builderVars.defaultFont,
                size: 12,
            },
        },
    };
});

export const inputNumberClasses = useThemeCache(() => {
    const vars = inputNumberVariables();
    const style = styleFactory("numberInput");
    const builderVariables = themeBuilderVariables();

    const spinner = style("spinner", {
        position: "relative",
        display: "flex",
        flexDirection: "column",
        justifyContent: "stretch",
        alignItems: "stretch",
        height: unit(vars.sizing.height),
        width: unit(vars.spinner.width + builderVariables.border.width),
        flexBasis: unit(vars.spinner.width + builderVariables.border.width),
        transform: translateX(negativeUnit(builderVariables.border.width)),
    });

    const root = style({
        display: "flex",
        flexWrap: "nowrap",
        justifyContent: "stretch",
        alignItems: "stretch",
        maxWidth: unit(vars.input.width),
        width: unit(vars.input.width),
    });

    const textInput = style("textInput", {
        position: "relative",
        ...textInputSizingFromFixedHeight(
            vars.sizing.height,
            vars.label.fonts.size,
            builderVariables.border.width * 2, // 2
            vars.sizing.height,
        ),
        height: unit(vars.sizing.height),
        width: unit(vars.input.width - vars.spinner.width),
        maxWidth: unit(vars.input.width - vars.spinner.width),
        color: colorOut(builderVariables.defaultFont.color),
        flexBasis: unit(builderVariables.input.width),
        ...borders({}, builderVariables.border as IGlobalBorderStyles),
        borderRight: 0,
        borderTopLeftRadius: unit(builderVariables.wrap.borderRadius),
        borderBottomLeftRadius: unit(builderVariables.wrap.borderRadius),
        $nest: {
            "&:hover, &:focus, &:active, &.focus-visible": {
                zIndex: 1,
            },
        },
    });

    const stepUp = style("stepUp", {
        ...absolutePosition.topRight(),
        ...borders({}, builderVariables.border as IGlobalBorderStyles),
        ...userSelect(),
        borderTopRightRadius: unit(builderVariables.wrap.borderRadius),
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        padding: 0,
        height: unit(Math.ceil(vars.sizing.height / 2)),
        width: percent(100),
        ...fonts(vars.spinner.fonts),
        $nest: {
            "&:hover, &:focus, &:active, &.focus-visible": {
                zIndex: 1,
            },
        },
    });
    const stepDown = style("stepDown", {
        ...absolutePosition.bottomRight(),
        ...borders({}, builderVariables.border as IGlobalBorderStyles),
        ...userSelect(),
        borderBottomRightRadius: unit(builderVariables.wrap.borderRadius),
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        padding: 0,
        ...fonts(vars.spinner.fonts),
        height: unit(Math.ceil(vars.sizing.height / 2)),
        width: percent(100),
        $nest: {
            "&:hover": {
                zIndex: 1,
            },
            "&:focus, &:active, &.focus-accessible": {
                zIndex: 2,
            },
        },
    });

    const spinnerSpacer = style("spinnerSpacer", {
        display: "block",
        position: "relative",
        height: unit(vars.sizing.height),
        minHeight: unit(vars.sizing.height),
        width: unit(vars.spinner.width + builderVariables.border.width),
        flexBasis: unit(vars.spinner.width + builderVariables.border.width),
    });

    const inputWrap = style("inputWrap", {
        width: unit(vars.input.width),
        flexBasis: unit(vars.input.width),
    });

    return {
        root,
        textInput,
        spinner,
        stepUp,
        stepDown,
        spinnerSpacer,
        inputWrap,
    };
});
