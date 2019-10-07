/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import {
    absolutePosition,
    colorOut,
    defaultTransition,
    paddings,
    singleBorder,
    unit,
} from "@library/styles/styleHelpers";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { calc, linearGradient, percent, px, translateY } from "csx";
import { buttonResetMixin } from "@library/forms/buttonStyles";
import { userLabelVariables } from "@library/content/userLabelStyles";
import { formElementsVariables } from "@library/forms/formElementStyles";

export const translationGridVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory("translationGrid");
    const globalVars = globalVariables();
    const { mainColors } = globalVars;

    const paddings = makeThemeVars("paddings", {
        vertical: 9,
        horizontal: 12,
    });

    const header = makeThemeVars("header", {
        height: 52,
    });

    const cell = makeThemeVars("cell", {
        color: globalVars.mixBgAndFg(0.22),
        paddings: {
            inner: 20,
            outer: 15,
        },
    });

    return { paddings, header, cell };
});

export const translationGridClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const formElVars = formElementsVariables();
    const vars = translationGridVariables();
    const style = styleFactory("translationGrid");

    const innerPadding = vars.cell.paddings.inner;
    const oneLineHeight = Math.ceil(globalVars.lineHeights.condensed * globalVars.fonts.size.medium);

    const input = style("input", {
        $nest: {
            "&&&": {
                border: 0,
                borderRadius: 0,
                fontSize: unit(globalVars.fonts.size.medium),
                lineHeight: globalVars.lineHeights.condensed,
                ...paddings({
                    vertical: vars.cell.paddings.inner,
                    left: vars.cell.paddings.outer + vars.cell.paddings.inner,
                    right: vars.cell.paddings.inner,
                }),
                minHeight: unit(innerPadding * 2 + oneLineHeight),
                flexGrow: 1,
            },
        },
    });

    const isFirst = style("isFirst", {
        $nest: {
            [`.${input}.${input}.${input}`]: {
                paddingTop: unit(vars.cell.paddings.outer - vars.paddings.vertical),
            },
        },
    });

    const isLast = style("isLast", {
        $nest: {
            [`.${input}.${input}.${input}`]: {
                paddingBottom: unit(vars.cell.paddings.outer - vars.paddings.vertical),
            },
        },
    });

    const root = style({});

    const inScrollContainer = style("inScrollContainer", {
        ...absolutePosition.fullSizeOfParent(),
    });

    const text = style("text", {});

    const row = style("row", {
        display: "flex",
        flexWrap: "nowrap",
        alignItems: "stretch",
    });

    const leftCell = style("leftCell", {
        width: percent(50),
        display: "flex",
        alignItems: "flex-start",
        justifyContent: "flex-start",
        fontSize: unit(globalVars.fonts.size.medium),
        lineHeight: globalVars.lineHeights.condensed,
        cursor: "default",
        borderRight: singleBorder({
            color: vars.cell.color,
        }),
        borderBottom: singleBorder({
            color: vars.cell.color,
        }),
        ...paddings({
            vertical: vars.cell.paddings.inner,
            left: vars.cell.paddings.outer,
            right: vars.cell.paddings.outer + vars.cell.paddings.inner,
        }),
        $nest: {
            [`&.${isLast}`]: {
                borderBottom: 0,
            },
        },
    });

    const rightCell = style("rightCell", {
        width: percent(50),
        display: "flex",
        alignItems: "center",
        justifyContent: "flex-start",
        borderBottom: singleBorder({
            color: vars.cell.color,
        }),
        $nest: {
            [`&.${isLast}`]: {
                borderBottom: 0,
            },
        },
    });

    const header = style("header", {
        display: "flex",
        flexWrap: "nowrap",
        width: percent(100),
        backgroundColor: colorOut(globalVars.mainColors.bg),
    });

    const frame = style("frame", {
        display: "flex",
        flexDirection: "column",
        height: percent(100),
        width: percent(100),
    });

    const headerLeft = style("headerLeft", {});

    const headerRight = style("headerRight", {});

    const inputWrapper = style("inputWrapper", {
        $nest: {
            "&&&": {
                margin: 0,
            },
        },
    });

    const body = style("body", {
        flexGrow: 1,
        height: calc(`100% - ${unit(vars.header.height)}`),
        overflow: "auto",
        ...paddings(vars.paddings),
    });

    return {
        root,
        text,
        isFirst,
        isLast,
        row,
        leftCell,
        rightCell,
        header,
        headerLeft,
        frame,
        headerRight,
        input,
        inputWrapper,
        body,
        inScrollContainer,
    };
});
