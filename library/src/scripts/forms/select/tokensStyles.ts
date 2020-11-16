/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { borders, colorOut, margins, paddings, unit, userSelect } from "@library/styles/styleHelpers";
import { componentThemeVariables, styleFactory, useThemeCache } from "@library/styles/styleUtils";
import { formElementsVariables } from "@library/forms/formElementStyles";
import { important, percent, px } from "csx";

export const tokensVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const themeVars = componentThemeVariables("tokens");

    const token = {
        fontSize: globalVars.meta.text.size,
        bg: globalVars.mixBgAndFg(0.1),
        textShadow: `${globalVars.mainColors.bg} 0 0 1px`,
        minHeight: 26,
    };

    const clearIcon = {
        width: 8,
        ...themeVars.subComponentStyles("clearIcon"),
    };

    return {
        clearIcon,
        token,
    };
});

export const tokensClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = tokensVariables();
    const formElVars = formElementsVariables();
    const style = styleFactory("tokens");

    const root = style({
        $nest: {
            "& .tokens__value-container": {
                display: "flex",
                flexWrap: "wrap",
                alignItems: "center",
                justifyContent: "flexStart",
                minHeight: unit(formElVars.sizing.height),
                paddingTop: 0,
                paddingRight: px(12),
                paddingBottom: 0,
                paddingLeft: px(12),
                ...borders(globalVars.borderType.formElements.default),
                $nest: {
                    "&.tokens__value-container--has-value": {
                        ...paddings({
                            horizontal: 4,
                            vertical: 0,
                        }),
                    },
                    "& .tokens__multi-value + div:not(.tokens__multi-value)": {
                        display: "flex",
                        flexWrap: "wrap",
                        alignItems: "center",
                        justifyContent: "flexStart",
                        flexGrow: 1,
                    },
                    ".tokens__input": {
                        flexGrow: 1,
                        display: important("inline-flex"),
                        alignItems: "center",
                        justifyContent: "stretch",
                        ...margins({
                            vertical: 0,
                        }),
                        minHeight: unit(vars.token.minHeight),
                    },
                    input: {
                        width: percent(100),
                        minWidth: unit(45),
                        minHeight: 0,
                    },
                },
            },
            "& .tokens__multi-value": {
                display: "flex",
                alignItems: "center",
                flexWrap: "nowrap",
                fontSize: unit(vars.token.fontSize),
                fontWeight: globalVars.fonts.weights.bold,
                textShadow: vars.token.textShadow,
                margin: px((formElVars.sizing.height - vars.token.minHeight) / 2 - formElVars.border.width),
                backgroundColor: colorOut(vars.token.bg),
                minHeight: unit(vars.token.minHeight),
                borderRadius: px(2),
                ...userSelect(),
            },
            "& .tokens__multi-value__label": {
                paddingLeft: px(6),
                fontWeight: globalVars.fonts.weights.normal,
                fontSize: globalVars.fonts.size.small,
            },
            "& .tokens--is-disabled": {
                opacity: formElVars.disabled.opacity,
            },
            "& .tokens-clear": {
                background: 0,
                border: 0,
                height: unit(globalVars.icon.sizes.default),
                width: unit(globalVars.icon.sizes.default),
                padding: 0,
                $nest: {
                    "&:hover, &:focus": {
                        color: globalVars.mainColors.primary.toString(),
                    },
                },
            },
        },
    });

    const inputWrap = style("inputWrarp", {
        $nest: {
            "&.hasFocus .inputBlock-inputText": {
                ...borders({
                    ...globalVars.borderType.formElements.default,
                    color: globalVars.mainColors.primary,
                }),
            },
        },
    });

    const removeIcon = style("removeIcon", {
        $nest: {
            "&.icon": {
                width: unit(vars.clearIcon.width),
                height: unit(vars.clearIcon.width),
            },
        },
    });

    const withIndicator = style("withIndicator", {
        $nest: {
            "& .inputText.inputText": {
                fontSize: "inherit",
            },
        },
    });

    return { root, removeIcon, inputWrap, withIndicator };
});
