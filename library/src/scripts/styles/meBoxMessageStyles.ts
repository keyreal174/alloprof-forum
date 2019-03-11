/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import {
    objectFitWithFallback,
    colorOut,
    unit,
    userSelect,
    paddings,
    allLinkStates,
    absolutePosition,
} from "@library/styles/styleHelpers";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { calc, percent, px, quote } from "csx";

export const meBoxMessageVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory("meBoxMessage");

    const spacing = makeThemeVars("spacing", {
        padding: {
            top: 8,
            right: 12,
            bottom: 8,
            left: 12,
        },
    });

    const imageContainer = makeThemeVars("imageContainer", {
        width: 40,
    });

    const unreadDot = makeThemeVars("unreadDot", {
        width: 12,
    });

    return { spacing, imageContainer, unreadDot };
});

export const meBoxMessageClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = meBoxMessageVariables();
    const style = styleFactory("meBoxMessage");

    const root = style({
        display: "block",
        $nest: {
            "& + &": {
                borderTop: `solid 1px ${colorOut(globalVars.border.color)}`,
            },
        },
    });

    const link = style("link", {
        ...userSelect(),
        display: "flex",
        flexWrap: "nowrap",
        color: "inherit",
        ...paddings(vars.spacing.padding),
        ...allLinkStates({
            allStates: {
                textShadow: "none",
            },
            noState: {
                color: colorOut(globalVars.links.colors.default),
            },
            hover: {
                color: colorOut(globalVars.links.colors.hover),
            },
            focus: {
                color: colorOut(globalVars.links.colors.focus),
            },
            active: {
                color: colorOut(globalVars.links.colors.active),
            },
        }),
    });

    const imageContainer = style("imageContainer", {
        position: "relative",
        width: unit(vars.imageContainer.width),
        height: unit(vars.imageContainer.width),
        flexBasis: unit(vars.imageContainer.width),
        borderRadius: percent(50),
        overflow: "hidden",
        border: `solid 1px ${globalVars.border.color.toString()}`,
    });

    const image = style("image", {
        width: unit(vars.imageContainer.width),
        height: unit(vars.imageContainer.width),
        ...objectFitWithFallback(),
    });

    const status = style("status", {
        position: "relative",
        width: unit(vars.unreadDot.width),
        flexBasis: unit(vars.unreadDot.width),
        $nest: {
            "&.isUnread": {
                $nest: {
                    "&:after": {
                        ...absolutePosition.middleRightOfParent(),
                        content: quote(""),
                        height: unit(vars.unreadDot.width),
                        width: unit(vars.unreadDot.width),
                        backgroundColor: globalVars.mainColors.primary.toString(),
                        borderRadius: percent(50),
                    },
                },
            },
        },
    });

    const contents = style("contents", {
        flexGrow: 1,
        ...paddings({
            left: vars.spacing.padding.left,
            right: vars.spacing.padding.right,
        }),
        maxWidth: calc(`100% - ${unit(vars.unreadDot.width + vars.imageContainer.width)}`),
    });

    const message = style("message", {
        lineHeight: globalVars.lineHeights.excerpt,
        color: colorOut(globalVars.mainColors.fg),
    });

    return { root, link, imageContainer, image, status, contents, message };
});
