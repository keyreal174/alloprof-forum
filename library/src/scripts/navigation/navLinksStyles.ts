/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import {
    colorOut,
    margins,
    paddings,
    setAllLinkColors,
    unit,
    fonts,
    extendItemContainer,
    EMPTY_FONTS,
    singleBorder,
    EMPTY_SPACING,
} from "@library/styles/styleHelpers";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { percent, px } from "csx";
import { media } from "typestyle";
import { NestedCSSProperties } from "typestyle/lib/types";
import { containerVariables } from "@library/layout/components/containerStyles";

export const navLinksVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory("navLinks");
    const globalVars = globalVariables();

    const linksWithHeadings = makeThemeVars("linksWithHeadings", {
        paddings: {
            // all: 20,
        },
        mobile: {
            paddings: {
                all: 0,
            },
        },
    });

    const item = makeThemeVars("item", {
        fontSize: globalVars.fonts.size.large,
        padding: {
            ...EMPTY_SPACING,
            vertical: 24,
            horizontal: containerVariables().spacing.paddingFull.horizontal,
        },
        paddingMobile: {
            ...EMPTY_SPACING,
            horizontal: 0,
        },
    });

    const title = makeThemeVars("title", {
        font: {
            ...EMPTY_FONTS,
            size: globalVars.fonts.size.title,
            weight: globalVars.fonts.weights.bold,
            lineHeight: globalVars.lineHeights.condensed,
        },
        maxWidth: percent(100),
        margins: {
            bottom: globalVars.gutter.size,
        },
        mobile: {
            font: {
                ...EMPTY_FONTS,
                fontSize: globalVars.fonts.size.large,
                fontWeight: globalVars.fonts.weights.bold,
            },
        },
    });

    const link = makeThemeVars("link", {
        fg: globalVars.mainColors.fg,
        fontWeight: globalVars.fonts.weights.semiBold,
        lineHeight: globalVars.lineHeights.condensed,
        width: 203,
        maxWidth: percent(100),
        fontSize: 16,
    });

    const viewAllLinkColors = setAllLinkColors();
    const viewAll = makeThemeVars("viewAll", {
        color: viewAllLinkColors.color,
        fontWeight: globalVars.fonts.weights.semiBold,
        fontSize: globalVars.fonts.size.medium,
        margins: {
            top: "auto",
        },
        paddings: {
            top: 20,
        },
        mobile: {
            paddings: {
                top: 8,
            },
        },
        $nest: viewAllLinkColors.nested,
    });

    const spacing = makeThemeVars("spacing", {
        margin: 6,
    });

    const columns = makeThemeVars("columns", {
        desktop: 2,
    });

    const separator = makeThemeVars("separator", {
        height: 1,
        bg: globalVars.mixBgAndFg(0.3),
    });

    const breakPoints = makeThemeVars("breakPoints", {
        oneColumn: 750,
    });

    const mediaQueries = () => {
        const oneColumn = styles => {
            return media({ maxWidth: px(breakPoints.oneColumn) }, styles);
        };

        return { oneColumn };
    };

    return {
        linksWithHeadings,
        item,
        title,
        columns,
        link,
        viewAll,
        spacing,
        separator,
        mediaQueries,
    };
});

export const navLinksClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = navLinksVariables();
    const style = styleFactory("navLinks");
    const mediaQueries = vars.mediaQueries();

    const root = style(
        {
            ...paddings(vars.item.padding),
            display: "flex",
            flexDirection: "column",
            maxWidth: percent(100),
            width: percent(100 / vars.columns.desktop),
        },
        mediaQueries.oneColumn({
            width: percent(100),
            ...paddings(vars.item.paddingMobile),
        }),
    );

    const items = style("items", {
        display: "flex",
        flexDirection: "column",
        flexGrow: 1,
    });

    const item = style("item", {
        display: "block",
        fontSize: unit(vars.item.fontSize),
        marginTop: unit(vars.spacing.margin),
        marginBottom: unit(vars.spacing.margin),
    });

    const title = style(
        "title",
        {
            display: "block",
            ...fonts(vars.title.font),
            maxWidth: percent(100),
            ...margins(vars.title.margins),
        },
        mediaQueries.oneColumn(fonts(vars.title.mobile.font)),
    );

    const linkColors = setAllLinkColors({
        default: globalVars.mainColors.fg,
    });

    const link = style("link", {
        display: "block",
        ...fonts({
            size: vars.link.fontSize,
            lineHeight: vars.link.lineHeight,
            // @ts-ignore
            color: linkColors.color,
        }),
        $nest: linkColors.nested as NestedCSSProperties,
    } as NestedCSSProperties);

    const viewAllItem = style(
        "viewAllItem",
        {
            display: "block",
            fontSize: unit(vars.item.fontSize),
            ...margins(vars.viewAll.margins),
            ...paddings(vars.viewAll.paddings),
        },
        mediaQueries.oneColumn({
            ...paddings(vars.viewAll.mobile.paddings),
        }),
    );

    const viewAllLinkColors = setAllLinkColors({
        default: globalVars.mainColors.primary,
    });

    const viewAll = style("viewAll", {
        display: "block",
        ...fonts({
            weight: vars.viewAll.fontWeight,
            size: vars.viewAll.fontSize,
            // @ts-ignore
            color: vars.viewAll.color,
        }),
        $nest: viewAllLinkColors.nested,
    });

    const linksWithHeadings = style(
        "linksWithHeadings",
        {
            ...paddings(vars.linksWithHeadings.paddings),
            ...extendItemContainer(vars.item.padding.horizontal),
            display: "flex",
            flexWrap: "wrap",
            alignItems: "stretch",
            justifyContent: "space-between",
        },
        mediaQueries.oneColumn({
            ...paddings(vars.linksWithHeadings.mobile.paddings),
            ...extendItemContainer(vars.item.paddingMobile.horizontal),
        }),
    );

    const separator = style(
        "separator",
        {
            display: "block",
            width: percent(100),
            height: unit(vars.separator.height),

            // Has to be a border and not a BG, because sometimes chrome rounds it's height to 0.99px and it disappears.
            borderBottom: singleBorder({ color: vars.separator.bg }),
        },
        mediaQueries.oneColumn(margins({ horizontal: vars.item.paddingMobile.horizontal })),
    );

    const separatorOdd = style(
        "separatorOdd",
        {
            $unique: true,
            display: "none",
        },
        mediaQueries.oneColumn({
            display: "block",
        }),
    );

    return {
        root,
        items,
        item,
        title,
        link,
        viewAllItem,
        viewAll,
        linksWithHeadings,
        separator,
        separatorOdd,
    };
});
