/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { useThemeCache, variableFactory, styleFactory } from "@library/styles/styleUtils";
import { globalVariables } from "@library/styles/globalStyleVars";
import { px, important } from "csx";
import { colorOut, placeholderStyles, borders, IBorderStyles } from "@library/styles/styleHelpers";
import { cssRule } from "typestyle";

export const inputVariables = useThemeCache(() => {
    const gVars = globalVariables();
    const makeThemeVars = variableFactory("input");

    const colors = makeThemeVars("colors", {
        placeholder: gVars.mixBgAndFg(0.5),
        fg: gVars.mixBgAndFg(0.8),
        bg: gVars.mainColors.bg,
    });

    const border: IBorderStyles = makeThemeVars("borders", {
        ...gVars.border,
        width: px(1),
    });

    return { colors, border };
});

export const inputClasses = useThemeCache(() => {
    const vars = inputVariables();
    const style = styleFactory("input");

    const textStyles = {
        backgroundColor: colorOut(vars.colors.bg),
        color: colorOut(vars.colors.fg),
        ...borders(vars.border),
        $nest: placeholderStyles({
            color: colorOut(vars.colors.placeholder),
        }),
    };

    // Use as assignable unique style.
    const text = style("text", textStyles);

    // Use as a global selector. This should be refactored in the future.
    const applyInputCSSRules = () => cssRule(".inputText", textStyles);

    return { text, applyInputCSSRules };
});
