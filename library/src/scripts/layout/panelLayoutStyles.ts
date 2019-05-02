/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { calc, percent, px, viewHeight } from "csx";
import { cssRule, media } from "typestyle";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { globalVariables } from "@library/styles/globalStyleVars";
import { margins, paddings, sticky, unit } from "@library/styles/styleHelpers";
import { important } from "csx/lib/strings";
import { lineHeightAdjustment } from "@library/styles/textUtils";
import { panelListClasses } from "@library/layout/panelListStyles";
import { titleBarVariables } from "@library/headers/titleBarStyles";

export const layoutVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory("globalVariables");

    // Important variables that will be used to calculate other variables
    const foundationalWidths = makeThemeVars("foundationalWidths", {
        fullGutter: 48,
        panelWidth: 216,
        middleColumnWidth: 672,
        minimalMiddleColumnWidth: 500, // Will break if middle column width is smaller than this value.
        narrowContentWidth: 900, // For home page widgets, narrower than full width
        breakPoints: {
            // Other break points are calculated
            twoColumns: 1200,
            xs: 500,
        },
    });

    const gutter = makeThemeVars("gutter", {
        full: foundationalWidths.fullGutter, // 48
        size: foundationalWidths.fullGutter / 2, // 24
        halfSize: foundationalWidths.fullGutter / 4, // 12
        quarterSize: foundationalWidths.fullGutter / 8, // 6
    });

    const panel = makeThemeVars("panel", {
        width: foundationalWidths.panelWidth,
        paddedWidth: foundationalWidths.panelWidth + gutter.full,
    });

    const middleColumn = makeThemeVars("middleColumn", {
        width: foundationalWidths.middleColumnWidth,
        paddedWidth: foundationalWidths.middleColumnWidth + gutter.full,
    });

    const globalContentWidth = middleColumn.paddedWidth + panel.paddedWidth * 2 + gutter.size;

    const contentSizes = makeThemeVars("content", {
        full: globalContentWidth,
        narrow:
            foundationalWidths.narrowContentWidth < globalContentWidth
                ? foundationalWidths.narrowContentWidth
                : globalContentWidth,
    });

    const panelLayoutBreakPoints = makeThemeVars("panelLayoutBreakPoints", {
        noBleed: globalContentWidth - 1,
        twoColumn: foundationalWidths.breakPoints.twoColumns,
        oneColumn: foundationalWidths.minimalMiddleColumnWidth - panel.paddedWidth,
        xs: foundationalWidths.breakPoints.xs,
    });

    const panelLayoutSpacing = makeThemeVars("panelLayoutSpacing", {
        margin: {
            top: 0,
            bottom: 50,
        },
        padding: {
            top: gutter.halfSize * 1.5,
        },
        largePadding: {
            top: 64,
        },
    });

    const mediaQueries = () => {
        const noBleed = styles => {
            return media({ maxWidth: px(panelLayoutBreakPoints.noBleed) }, styles);
        };

        const twoColumns = styles => {
            return media({ maxWidth: px(panelLayoutBreakPoints.twoColumn) }, styles);
        };

        const oneColumn = styles => {
            return media({ maxWidth: px(panelLayoutBreakPoints.oneColumn) }, styles);
        };

        const xs = styles => {
            return media({ maxWidth: px(panelLayoutBreakPoints.xs) }, styles);
        };

        return { noBleed, twoColumns, oneColumn, xs };
    };

    return {
        foundationalWidths,
        gutter,
        panel,
        middleColumn,
        contentSizes,
        mediaQueries,
        panelLayoutSpacing,
    };
});

export const panelLayoutClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = layoutVariables();
    const mediaQueries = vars.mediaQueries();
    const style = styleFactory("panelLayout");
    const classesPanelArea = panelAreaClasses();
    const classesPanelList = panelListClasses();
    const titleBarVars = titleBarVariables();

    const root = style({
        ...paddings({
            horizontal: globalVars.gutter.half,
        }),
    });

    const content = style("content", {
        display: "flex",
        flexGrow: 1,
        width: percent(100),
        justifyContent: "space-between",
    });

    const main = style("main", {
        minHeight: viewHeight(20),
        width: percent(100),
    });

    const panel = style("panel", {
        width: percent(100),
        $nest: {
            [`& > .${classesPanelArea.root}:first-child .${classesPanelList.root}`]: {
                marginTop: unit(
                    (globalVars.fonts.size.title * globalVars.lineHeights.condensed) / 2 -
                        globalVariables().fonts.size.medium / 2,
                ),
            },
        },
    });

    const panelLayout = style("panelLayout", {
        ...margins(vars.panelLayoutSpacing.margin),
        width: percent(100),
        $nest: {
            [`&.noBreadcrumbs > .${main}`]: {
                paddingTop: unit(globalVars.gutter.size),
                ...mediaQueries.oneColumn({
                    paddingTop: 0,
                }),
            },
            "&.isOneCol": {
                width: unit(vars.middleColumn.paddedWidth),
                maxWidth: percent(100),
                margin: "auto",
                ...mediaQueries.oneColumn({
                    width: percent(100),
                }),
            },
            "&.hasTopPadding": {
                ...paddings(vars.panelLayoutSpacing.padding),
            },
            "&.hasLargePadding": {
                ...paddings(vars.panelLayoutSpacing.largePadding),
            },
        },
    });

    const top = style("top", {
        width: percent(100),
        marginBottom: unit(globalVars.gutter.half),
    });

    const container = style("container", {
        display: "flex",
        flexWrap: "nowrap",
        justifyContent: "space-between",
    });

    const fullWidth = style("fullWidth", {
        position: "relative",
        padding: 0,
    });

    const leftColumn = style("leftColumn", {
        position: "relative",
        width: unit(vars.panel.width),
        flexBasis: unit(vars.panel.width),
        minWidth: unit(vars.panel.width),
    });

    const rightColumn = style("rightColumn", {
        position: "relative",
        width: unit(vars.panel.width),
        flexBasis: unit(vars.panel.width),
        minWidth: unit(vars.panel.width),
        overflow: "initial",
    });

    const middleColumn = style("middleColumn", {
        justifyContent: "space-between",
        flexGrow: 1,
        width: percent(100),
        maxWidth: percent(100),
        ...mediaQueries.oneColumn(paddings({ left: important(0), right: important(0) })),
    });

    const middleColumnMaxWidth = style("middleColumnMaxWidth", {
        $nest: {
            "&.hasAdjacentPanel": {
                flexBasis: calc(`100% - ${unit(vars.panel.paddedWidth)}`),
                maxWidth: calc(`100% - ${unit(vars.panel.paddedWidth)}`),
                ...mediaQueries.oneColumn({
                    flexBasis: percent(100),
                    maxWidth: percent(100),
                }),
            },
            "&.hasTwoAdjacentPanels": {
                flexBasis: calc(`100% - ${unit(vars.panel.paddedWidth * 2)}`),
                maxWidth: calc(`100% - ${unit(vars.panel.paddedWidth * 2)}`),
                ...mediaQueries.oneColumn({
                    flexBasis: percent(100),
                    maxWidth: percent(100),
                }),
            },
        },
    });

    const isSticky = style(
        "isSticky",
        {
            ...sticky(),
            top: titleBarVars.sizing.height * 2,
            height: percent(100),
            overflow: "auto",
        },
        mediaQueries.oneColumn({
            position: "relative",
            top: "auto",
            left: "auto",
            bottom: "auto",
        }),
    );

    // To remove when we have overlay styles converted
    cssRule(`.overlay .${panelLayout}.noBreadcrumbs .${main}`, {
        paddingTop: 0,
    });

    return {
        root,
        content,
        panelLayout,
        top,
        main,
        container,
        fullWidth,
        leftColumn,
        rightColumn,
        middleColumn,
        middleColumnMaxWidth,
        panel,
        isSticky,
    };
});

export const panelWidgetClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = layoutVariables();
    const mediaQueries = vars.mediaQueries();
    const style = styleFactory("panelWidget");

    const root = style(
        {
            display: "flex",
            flexDirection: "column",
            position: "relative",
            width: percent(100),
            ...paddings({
                all: globalVars.gutter.half,
            }),
            $nest: {
                "&.hasNoVerticalPadding": {
                    ...paddings({ vertical: 0 }),
                },
                "&.hasNoHorizontalPadding": {
                    ...paddings({ horizontal: 0 }),
                },
                "&.isSelfPadded": {
                    ...paddings({ all: 0 }),
                },
            },
        },
        mediaQueries.oneColumn({
            ...paddings({
                horizontal: 4,
            }),
        }),
    );

    return { root };
});

export const panelAreaClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = layoutVariables();
    const mediaQueries = vars.mediaQueries();
    const style = styleFactory("panelWidget");

    const root = style({
        width: percent(100),
        $nest: {
            "& .heading": {
                $nest: lineHeightAdjustment(globalVars.lineHeights.condensed),
            },
            "&.inheritHeight > .panelWidget": {
                flexGrow: 1,
            },
        },
    });

    return { root };
});
