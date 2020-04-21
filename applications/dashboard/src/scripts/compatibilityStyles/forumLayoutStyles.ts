/**
 * Compatibility styles, using the color variables.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import { globalVariables } from "@library/styles/globalStyleVars";
import { cssOut } from "@dashboard/compatibilityStyles/index";
import { containerMainStyles } from "@library/layout/components/containerStyles";
import { NestedCSSProperties } from "typestyle/lib/types";
import { useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { calc, percent, px } from "csx";
import { paddings, unit } from "@library/styles/styleHelpers";
import { media } from "typestyle";

export const forumLayoutVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const makeThemeVars = variableFactory("forumLayout");

    // Important variables that will be used to calculate other variables
    const foundationalWidths = makeThemeVars("foundationalWidths", {
        fullGutter: globalVars.constants.fullGutter,
        panelWidth: 220, // main calculated based on panel width
        breakPoints: {
            // Other break points are calculated
            oneColumn: 1200,
            tablet: 991,
            mobile: 768,
            xs: 576,
        },
    });

    const mediaQueries = () => {
        const noBleed = (styles: NestedCSSProperties, useMinWidth: boolean = true) => {
            return media(
                {
                    maxWidth: px(panel.paddedWidth),
                    minWidth: useMinWidth ? px(foundationalWidths.breakPoints.oneColumn + 1) : undefined,
                },
                styles,
            );
        };

        const noBleedDown = (styles: NestedCSSProperties) => {
            return noBleed(styles, false);
        };

        const oneColumn = (styles: NestedCSSProperties, useMinWidth: boolean = true) => {
            return media(
                {
                    maxWidth: px(foundationalWidths.breakPoints.oneColumn),
                    minWidth: useMinWidth ? px(foundationalWidths.breakPoints.tablet + 1) : undefined,
                },
                styles,
            );
        };

        const oneColumnDown = (styles: NestedCSSProperties) => {
            return oneColumn(styles, false);
        };

        const tablet = (styles: NestedCSSProperties, useMinWidth: boolean = true) => {
            return media(
                {
                    maxWidth: px(foundationalWidths.breakPoints.tablet),
                    minWidth: useMinWidth ? px(foundationalWidths.breakPoints.mobile + 1) : undefined,
                },
                styles,
            );
        };

        const tabletDown = (styles: NestedCSSProperties) => {
            return tablet(styles, false);
        };

        const mobile = (styles: NestedCSSProperties, useMinWidth: boolean = true) => {
            return media(
                {
                    maxWidth: px(foundationalWidths.breakPoints.mobile),
                    minWidth: useMinWidth ? px(foundationalWidths.breakPoints.xs + 1) : undefined,
                },
                styles,
            );
        };

        const mobileDown = (styles: NestedCSSProperties) => {
            return mobile(styles, false);
        };

        const xs = (styles: NestedCSSProperties) => {
            return media(
                {
                    maxWidth: px(foundationalWidths.breakPoints.xs),
                },
                styles,
            );
        };

        return {
            noBleed,
            noBleedDown,
            oneColumn,
            oneColumnDown,
            tablet,
            tabletDown,
            mobile,
            mobileDown,
            xs,
        };
    };

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

    const main = makeThemeVars("main", {
        width: calc(`100% - ${unit(panel.paddedWidth)}`),
    });

    const cell = makeThemeVars("cell", {
        paddings: {
            horizontal: 8,
            vertical: 12,
        },
    });

    return {
        gutter,
        panel,
        main,
        cell,
        mediaQueries,
    };
});

export const forumLayoutCSS = () => {
    const globalVars = globalVariables();
    const vars = forumLayoutVariables();

    const mainColors = globalVars.mainColors;
    const mediaQueries = vars.mediaQueries();

    cssOut(
        `.Container, body.Section-Event.NoPanel .Frame-content > .Container`,
        mediaQueries.tablet({
            ...paddings({
                horizontal: globalVars.gutter.half,
            }),
        }),
    );

    cssOut(`body.Section-Event.NoPanel .Frame-content > .Container`, containerMainStyles() as NestedCSSProperties);

    cssOut(
        `.Frame-row`,
        {
            display: "flex",
            flexWrap: "nowrap",
            ...paddings({
                horizontal: globalVars.gutter.half,
            }),
            $nest: {
                "& > *": {
                    ...paddings({
                        horizontal: globalVars.gutter.half,
                    }),
                },
            },
        },
        mediaQueries.oneColumnDown({
            flexWrap: "wrap",
        }),
        mediaQueries.tablet({
            ...paddings({
                horizontal: 0,
            }),
        }),
    );

    cssOut(
        `.Panel`,
        {
            width: unit(vars.panel.paddedWidth),
            ...paddings({
                all: globalVars.gutter.half,
            }),
            $nest: {
                "& > *": {
                    ...paddings({
                        horizontal: globalVars.gutter.half,
                    }),
                },
            },
        },
        mediaQueries.oneColumnDown({
            width: percent(100),
            $nest: {
                "& > *": {
                    ...paddings({
                        horizontal: 0,
                    }),
                },
            },
        }),
    );

    cssOut(
        `.Content.MainContent`,
        {
            width: unit(vars.main.width),
            ...paddings({
                all: globalVars.gutter.half,
            }),
        },
        mediaQueries.oneColumnDown({
            width: percent(100),
        }),
    );

    cssOut(`.Container`, containerMainStyles() as NestedCSSProperties);

    cssOut(`.Frame-row`, {
        display: "flex",
        flexWrap: "nowrap",
        ...paddings({
            horizontal: globalVars.gutter.half,
        }),
        $nest: {
            "& > *": {
                ...paddings({
                    horizontal: globalVars.gutter.half,
                }),
            },
        },
    });

    cssOut(`.Panel`, {
        width: unit(vars.panel.paddedWidth),
        ...paddings({
            all: globalVars.gutter.half,
        }),
        $nest: {
            "& > *": {
                ...paddings({
                    horizontal: globalVars.gutter.half,
                }),
            },
        },
    });

    cssOut(`.Content.MainContent`, {
        width: unit(vars.main.width),
        ...paddings({
            all: globalVars.gutter.half,
        }),
    });
};
