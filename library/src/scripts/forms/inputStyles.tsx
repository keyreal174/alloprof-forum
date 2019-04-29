/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import {
    borders,
    colorOut,
    IBordersSameAllSidesStyles,
    placeholderStyles,
    textInputSizingFromFixedHeight,
} from "@library/styles/styleHelpers";
import { px } from "csx";
import { cssRule } from "typestyle";
import { formElementsVariables } from "@library/forms/formElementStyles";

export const inputVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const formElementVars = formElementsVariables();
    const makeThemeVars = variableFactory("input");

    const colors = makeThemeVars("colors", {
        placeholder: globalVars.mixBgAndFg(0.5),
        fg: globalVars.mixBgAndFg(0.8),
        bg: globalVars.mainColors.bg,
        state: {
            fg: globalVars.mainColors.primary,
        },
    });

    const sizing = makeThemeVars("sizing", {
        height: formElementVars.sizing.height,
    });

    const font = makeThemeVars("font", {
        size: globalVars.fonts.size.large,
    });

    const border: IBordersSameAllSidesStyles = makeThemeVars("borders", globalVars.border);

    return {
        colors,
        border,
        sizing,
        font,
    };
});

export const inputClasses = useThemeCache(() => {
    const vars = inputVariables();
    const style = styleFactory("input");
    const formElementVars = formElementsVariables();

    const textStyles = {
        ...textInputSizingFromFixedHeight(vars.sizing.height, vars.font.size, formElementVars.border.fullWidth),
        backgroundColor: colorOut(vars.colors.bg),
        color: colorOut(vars.colors.fg),
        ...borders(vars.border),
        outline: 0,
        $nest: {
            ...placeholderStyles({
                color: colorOut(vars.colors.placeholder),
            }),
            "&:focus, &.focus-visible": {
                borderColor: colorOut(vars.colors.state.fg),
            },
        },
    };

    // Use as assignable unique style.
    const text = style("text", textStyles);

    // Use as a global selector. This should be refactored in the future.
    const applyInputCSSRules = () => cssRule(".inputText", textStyles);

    return { text, applyInputCSSRules };
});
