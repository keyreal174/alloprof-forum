/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import {
    modifyColorBasedOnLightness,
    colorOut,
    IBackground,
    modifyColorSaturationBasedOnLightness,
} from "@library/styles/styleHelpers";
import { useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { color, ColorHelper, percent, viewHeight } from "csx";

export const globalVariables = useThemeCache(() => {
    let colorPrimary = color("#0291db");
    const makeThemeVars = variableFactory("global");

    const utility = {
        "percentage.third": percent(100 / 3),
        "percentage.nineSixteenths": percent((9 / 16) * 100),
        "svg.encoding": "data:image/svg+xml,",
    };

    const elementaryColors = {
        black: color("#000"),
        grey: color("#555a62"),
        white: color("#fff"),
        transparent: `transparent`,
    };

    const initialMainColors = makeThemeVars("mainColors", {
        fg: color("#555a62"),
        bg: color("#fff"),
        primary: colorPrimary,
        secondary: colorPrimary,
    });

    colorPrimary = initialMainColors.primary;

    const generatedMainColors = makeThemeVars("mainColors", {
        secondary: colorPrimary.lightness() >= 0.5 ? colorPrimary.darken(0.05) : colorPrimary.lighten(0.05),
    });

    const mainColors = {
        ...initialMainColors,
        ...generatedMainColors,
    };

    const mixBgAndFg = (weight: number) => {
        return mainColors.fg.mix(mainColors.bg, weight) as ColorHelper;
    };

    const mixPrimaryAndFg = (weight: number) => {
        return mainColors.primary.mix(mainColors.fg, weight) as ColorHelper;
    };

    const mixPrimaryAndBg = (weight: number) => {
        return mainColors.primary.mix(mainColors.bg, weight) as ColorHelper;
    };

    const errorFg = color("#555A62");
    const warning = color("#ffce00");
    const deleted = color("#D0021B");
    const feedbackColors = makeThemeVars("feedbackColors", {
        warning,
        error: {
            fg: errorFg,
            bg: color("#FFF3D4"),
        },
        confirm: color("#60bd68"),
        unresolved: warning.mix(mainColors.fg, 10),
        deleted,
    });

    const links = makeThemeVars("links", {
        colors: {
            default: mainColors.primary,
            hover: mainColors.secondary,
            focus: mainColors.secondary,
            accessibleFocus: mainColors.secondary,
            active: mainColors.secondary,
        },
    });

    interface IBody {
        backgroundImage: IBackground;
    }

    const body: IBody = makeThemeVars("body", {
        backgroundImage: {
            color: mainColors.bg,
        },
    });

    const border = makeThemeVars("border", {
        color: mixBgAndFg(0.24),
        width: 1,
        style: "solid",
        radius: 6,
    });

    const gutterSize = 24;
    const gutter = makeThemeVars("gutter", {
        size: gutterSize,
        half: gutterSize / 2,
        quarter: gutterSize / 4,
    });

    const lineHeights = makeThemeVars("lineHeight", {
        base: 1.5,
        condensed: 1.25,
        code: 1.45,
        excerpt: 1.45,
        meta: 1.5,
    });

    const panelWidth = 216;
    const panel = makeThemeVars("panelWidth", {
        width: panelWidth,
        paddedWidth: panelWidth + gutter.size,
    });

    const middleColumnWidth = 672;
    const middleColumn = makeThemeVars("middleColumn", {
        width: middleColumnWidth,
        paddedWidth: middleColumnWidth + gutter.size,
    });

    const content = makeThemeVars("content", {
        width:
            panel.paddedWidth * 2 +
            middleColumn.paddedWidth +
            gutter.size * 3 /* *3 from margin between columns and half margin on .container*/,
    });

    const fonts = makeThemeVars("fonts", {
        size: {
            large: 16,
            medium: 14,
            small: 12,
            title: 32,
            smallTitle: 22,
            subTitle: 18,
        },

        mobile: {
            size: {
                title: 26,
            },
        },
        weights: {
            normal: 400,
            semiBold: 600,
            bold: 700,
        },
    });

    const icon = makeThemeVars("icon", {
        sizes: {
            large: 32,
            default: 24,
            small: 16,
        },
        color: mixBgAndFg(0.18),
    });

    const spacer = makeThemeVars("spacer", {
        size: fonts.size.medium * lineHeights.base,
    });

    const animation = makeThemeVars("animation", {
        defaultTiming: ".15s",
        defaultEasing: "ease-out",
    });

    const embed = makeThemeVars("embed", {
        error: {
            bg: feedbackColors.error,
        },
        focus: {
            color: mainColors.primary,
        },
        text: {
            padding: fonts.size.medium,
        },
        sizing: {
            smallPadding: 4,
            width: 640,
        },
        select: {
            borderWidth: 2,
        },
        overlay: {
            hover: {
                color: mainColors.bg.fade(0.5),
            },
        },
    });

    const meta = makeThemeVars("meta", {
        text: {
            fontSize: fonts.size.small,
            color: mixBgAndFg(0.85),
            margin: 4,
        },
        spacing: {
            verticalMargin: 12,
            default: gutter.quarter,
        },
        lineHeights: {
            default: lineHeights.base,
        },
        colors: {
            fg: mixBgAndFg(0.85),
            deleted: feedbackColors.deleted,
        },
    });

    const states = makeThemeVars("states", {
        icon: {
            opacity: 0.6,
        },
        text: {
            opacity: 0.75,
        },
        hover: {
            color: mixPrimaryAndBg(0.08),
            opacity: 1,
        },
        selected: {
            color: mixPrimaryAndBg(0.5),
            opacity: 1,
        },
        active: {
            color: mixPrimaryAndBg(0.2),
            opacity: 1,
        },
        focus: {
            color: mixPrimaryAndBg(0.15),
            opacity: 1,
        },
    });

    const overlayBg = modifyColorBasedOnLightness(mainColors.fg, mainColors.fg, 0.5, true);
    const overlay = makeThemeVars("overlay", {
        dropShadow: `2px -2px 5px ${colorOut(overlayBg.fade(0.3))}`,
        bg: overlayBg,
        border: {
            color: mixBgAndFg(0.1),
            radius: border.radius,
        },
        fullPageHeadingSpacer: 32,
        spacer: 32,
    });

    const userContent = makeThemeVars("userContent", {
        font: {
            sizes: {
                default: fonts.size.medium,
                h1: "2em",
                h2: "1.5em",
                h3: "1.25em",
                h4: "1em",
                h5: ".875em",
                h6: ".85em",
            },
        },
        list: {
            margin: "2em",
            listDecoration: {
                minWidth: "2em",
            },
        },
    });

    const buttonIconSize = 36;
    const buttonIcon = makeThemeVars("buttonIcon", {
        size: buttonIconSize,
        offset: (buttonIconSize - icon.sizes.default) / 2,
    });

    const separator = makeThemeVars("separator", {
        color: border.color,
        size: 1,
    });

    return {
        utility,
        elementaryColors,
        mainColors,
        feedbackColors,
        body,
        border,
        meta,
        gutter,
        panel,
        content,
        fonts,
        spacer,
        lineHeights,
        icon,
        buttonIcon,
        animation,
        links,
        embed,
        states,
        overlay,
        userContent,
        mixBgAndFg,
        mixPrimaryAndFg,
        mixPrimaryAndBg,
        separator,
    };
});

export enum IIconSizes {
    SMALL = "small",
    DEFAULT = "default",
    LARGE = "large",
}
