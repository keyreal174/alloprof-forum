/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { formElementsVariables } from "@library/components/forms/formElementStyles";
import { globalVariables } from "@library/styles/globalStyleVars";
import { componentThemeVariables, debugHelper, unit, userSelect } from "@library/styles/styleHelpers";
import { useThemeCache } from "@library/styles/styleUtils";
import { percent, px } from "csx";
import { style } from "typestyle";

export const tokensVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const themeVars = componentThemeVariables("tokens");

    const token = {
        fontSize: globalVars.meta.text.fontSize,
        bg: globalVars.mixBgAndFg(0.15),
        textShadow: `${globalVars.mainColors.bg} 0 0 1px`,
    };

    const clear = {
        width: 16,
        ...themeVars.subComponentStyles("clear"),
    };

    const clearIcon = {
        width: 8,
        ...themeVars.subComponentStyles("clearIcon"),
    };

    return { clearIcon, clear, token };
});

export const tokensClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = tokensVariables();
    const formElVars = formElementsVariables();
    const debug = debugHelper("tokens");

    const root = style({
        ...debug.name(),
        $nest: {
            ".tokens-clear": {
                height: unit(vars.clear.width),
                width: unit(vars.clear.width),
                padding: 0,
                borderRadius: percent(50),
                marginLeft: px(1),
                $nest: {
                    "&:hover, &:focus": {
                        backgroundColor: globalVars.mainColors.primary.toString(),
                        color: globalVars.mainColors.bg.toString(),
                    },
                },
            },
            ".tokens__value-container": {
                minHeight: unit(formElVars.sizing.height),
                paddingTop: 0,
                paddingRight: px(3),
                paddingBottom: 0,
                paddingLeft: px(3),
                $nest: {
                    "&.tokens__value-container--has-value": {
                        padding: px(3),
                    },
                },
            },
            ".tokens__multi-value": {
                fontSize: unit(vars.token.fontSize),
                fontWeight: globalVars.fonts.weights.bold,
                textShadow: vars.token.textShadow,
                paddingLeft: px(6),
                paddingRight: px(2),
                margin: px(3),
                backgroundColor: vars.token.bg.toString(),
                ...userSelect(),
            },
        },
    });

    const removeIcon = style({
        $nest: {
            "&.icon": {
                width: unit(vars.clearIcon.width),
                height: unit(vars.clearIcon.width),
            },
        },
        ...debug.name("removeIcon"),
    });

    return { root, removeIcon };
});
