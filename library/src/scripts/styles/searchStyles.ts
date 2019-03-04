/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { debugHelper, componentThemeVariables } from "@library/styles/styleHelpers";
import { style } from "typestyle";
import { IButtonType } from "@library/styles/buttonStyles";
import memoize from "lodash/memoize";

export const searchVariables = memoize(() => {
    const globalVars = globalVariables();
    const elementaryColor = globalVars.elementaryColors;
    const themeVars = componentThemeVariables("search");

    const input = {
        border: {
            color: elementaryColor.white,
        },
        bg: "transparent",
        hover: {
            bg: elementaryColor.black.fade(0.1),
        },
        ...themeVars.subComponentStyles("input"),
    };

    const placeholder = {
        color: globalVars.mainColors.fg,
        ...themeVars.subComponentStyles("placeholder"),
    };

    return { input, placeholder };
});

export const searchClasses = memoize(() => {
    const vars = searchVariables();
    const debug = debugHelper("search");

    const root = style({
        ...debug.name(),
        $nest: {
            ".inputText": {
                borderColor: vars.input.border.color.toString(),
            },
            ".searchBar-valueContainer": {
                ...debug.name("valueContainer"),
                cursor: "text",
            },
            ".searchBar__control": {
                ...debug.name("control"),
                cursor: "text",
            },
        },
    });

    return { root };
});
