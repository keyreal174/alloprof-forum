/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { style } from "typestyle";
import { px, quote, viewWidth, viewHeight, url, percent } from "csx";
import { globalVariables } from "@library/styles/globalStyleVars";
import { centeredBackgroundProps, componentThemeVariables, debugHelper } from "@library/styles/styleHelpers";
import { searchVariables } from "@library/styles/searchStyles";
import { assetUrl } from "@library/application";

export function splashVariables(theme?: object) {
    const globalVars = globalVariables(theme);
    const elementaryColor = globalVars.elementaryColors;
    const themeVars = componentThemeVariables(theme, "splash");

    const fullBackground = {
        bg: globalVars.mainColors.primary,
        image: assetUrl("/resources/design/fallbackSplashBackground.svg"),
        ...themeVars.subComponentStyles("fullBackground"),
    };

    const title = {
        fg: elementaryColor.white.toString(),
        fontSize: globalVars.fonts.size.title,
        textAlign: "center",
        fontWeight: globalVars.fonts.weights.semiBold,
        textShadow: `0 1px 25px rgba(27,31,35,0.01)`,
        marginTop: 28,
        marginBottom: 40,
        ...themeVars.subComponentStyles("title"),
    };

    const spacing = {
        top: 48,
        bottom: 48,
        ...themeVars.subComponentStyles("spacing"),
    };

    const border = {
        color: globalVars.mainColors.fg,
        ...themeVars.subComponentStyles("border"),
    };

    const search = searchVariables({
        ...themeVars.subComponentStyles("search"),
    });

    const searchContainer = {
        width: 670,
    };

    return { fullBackground, title, spacing, border, search, searchContainer };
}

export function splashStyles(theme?: object) {
    const vars = splashVariables(theme);
    const debug = debugHelper("splash");

    const bg = vars.fullBackground.image;

    const root = style({
        backgroundColor: vars.fullBackground.bg.toString(),
        position: "relative",
        ...debug.name(),
    });

    const backgroundImage = bg ? url(bg) : undefined;
    const opacity = bg ? 0.4 : undefined; // only for default bg
    const fullBackground = style({
        ...centeredBackgroundProps(),
        display: "block",
        position: "absolute",
        top: px(0),
        left: px(0),
        width: percent(100),
        height: percent(100),
        backgroundSize: "cover",
        backgroundImage,
        opacity,
        ...debug.name(),
    });

    const container = style({
        ...debug.name("container"),
    });

    const innerContainer = style({
        paddingTop: vars.spacing.top,
        paddingBottom: vars.spacing.bottom,
        ...debug.name("innerContainer"),
    });

    const title = style({
        fontSize: px(vars.title.fontSize),
        textAlign: "center",
        fontWeight: vars.title.fontWeight,
        textShadow: vars.title.textShadow,
        color: vars.title.fg,
        paddingTop: px(vars.title.marginTop),
        marginBottom: px(vars.title.marginBottom),
        ...debug.name("title"),
    });

    const search = style({
        ...debug.name("search"),
    });

    const searchContainer = style({
        ...debug.name("searchContainer"),
        maxWidth: percent(100),
        width: px(vars.searchContainer.width),
        margin: "auto",
    });

    return { root, container, innerContainer, title, search, fullBackground, searchContainer };
}
