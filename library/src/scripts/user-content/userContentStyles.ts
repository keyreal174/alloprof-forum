/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { useThemeCache, variableFactory, styleFactory } from "@library/styles/styleUtils";
import { NestedCSSProperties, NestedCSSSelectors, TLength } from "typestyle/lib/types";
import { margins, allLinkStates, setAllLinkColors, paddings, colorOut, borders } from "@library/styles/styleHelpers";
import { em, percent, px, important } from "csx";
import { lineHeightAdjustment } from "@library/styles/textUtils";
import { FontSizeProperty } from "csstype";
import { shadowOrBorderBasedOnLightness, shadowHelper } from "@library/styles/shadowHelpers";

const userContentVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory("userContent");
    const globalVars = globalVariables();
    const { mainColors } = globalVars;

    const fonts = makeThemeVars("fonts", {
        size: globalVars.fonts.size.large,
        headings: {
            h1: "2em",
            h2: "1.5em",
            h3: "1.25em",
            h4: "1em",
            h5: ".875em",
            h6: ".85em",
        },
    });

    const blocks = makeThemeVars("blocks", {
        margin: fonts.size,
        bg: mainColors.fg.mix(mainColors.bg, 0.05),
        fg: mainColors.bg.lightness() > 0.5 ? mainColors.fg.darken(0.2) : mainColors.fg.lighten(0.2),
    });

    const embeds = makeThemeVars("embeds", {
        bg: mainColors.bg,
        fg: mainColors.fg,
        borderRadius: px(2),
    });

    const code = makeThemeVars("code", {
        fontSize: em(0.85),
        borderRadius: 0,
        // bg target rgba(127, 127, 127, .15);
        bg: blocks.bg,
        fg: blocks.fg,
    });

    const codeInline = makeThemeVars("codeInline", {
        borderRadius: code.borderRadius,
        paddingVertical: em(0.2),
        paddingHorizontal: em(0.4),
        bg: mainColors.fg.mix(mainColors.bg, 0.08),
    });

    const codeBlock = makeThemeVars("codeBlock", {
        borderRadius: globalVars.border.radius,
        paddingVertical: fonts.size,
        paddingHorizontal: fonts.size,
        lineHeight: 1.45,
    });

    const list = makeThemeVars("list", {
        spacing: {
            top: em(0.5),
            left: em(2),
        },
        listDecoration: {
            minWidth: em(2),
        },
    });

    return { fonts, list, blocks, code, codeInline, codeBlock, embeds };
});

/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
export const userContentClasses = useThemeCache(() => {
    const style = styleFactory("userContent");
    const vars = userContentVariables();
    const globalVars = globalVariables();

    const listItem: NestedCSSProperties = {
        position: "relative",
        ...margins({
            top: vars.list.spacing.top,
            left: vars.list.spacing.left,
        }),
        $nest: {
            "&:first-child": {
                marginTop: 0,
            },
            "&:last-child": {
                marginBottom: 0,
            },
        },
    };

    const headingStyle = (tag: string, fontSize: FontSizeProperty<TLength>): NestedCSSProperties => {
        return {
            marginTop: globalVars.spacer.size,
            fontSize,
            $nest: lineHeightAdjustment(globalVars.lineHeights.condensed),
        };
    };
    const headings: NestedCSSSelectors = {
        "& h1": headingStyle("h1", vars.fonts.headings.h1),
        "& h2": headingStyle("h2", vars.fonts.headings.h2),
        "& h3": headingStyle("h3", vars.fonts.headings.h3),
        "& h4": headingStyle("h4", vars.fonts.headings.h4),
        "& h5": headingStyle("h5", vars.fonts.headings.h5),
        "& h6": headingStyle("h6", vars.fonts.headings.h6),
    };

    const lists: NestedCSSSelectors = {
        "& ol": {
            listStylePosition: "inside",
        },
        "& ol li": {
            ...listItem,
            listStyle: "decimal",
        },
        "& ul li": {
            ...listItem,
            listStyle: "initial",
        },
    };

    const paragraphSpacing: NestedCSSSelectors = {
        "& p": {
            marginTop: 0,
            marginBottom: 0,
            $nest: {
                "&:not(:first-child)": {
                    marginTop: vars.blocks.margin * 0.5,
                },
                "&:first-child": {
                    $nest: lineHeightAdjustment(globalVars.lineHeights.base),
                },
            },
        },

        "&& > *:not(:last-child)": {
            marginBottom: vars.blocks.margin,
        },

        "&& > *:first-child": {
            marginTop: 0,
        },
    };

    const linkStyle = setAllLinkColors({
        hover: {
            textDecoration: "underline",
        },
    });

    const linkStyles: NestedCSSSelectors = {
        "p a": linkStyle,
        "li a": linkStyle,
    };

    const codeStyles: NestedCSSSelectors = {
        "& .code": {
            position: "relative",
            verticalAlign: "middle",
            fontSize: vars.code.fontSize,
            fontFamily: `Menlo, Monaco, Consolas, "Courier New", monospace`,
            maxWidth: percent(100),
            overflowX: "auto",
            margin: 0,
            color: colorOut(vars.code.fg),
            backgroundColor: colorOut(vars.code.bg),
            border: "none",
        },
        "&& .codeInline": {
            whiteSpace: "normal",
            ...paddings({
                top: vars.codeInline.paddingVertical,
                bottom: vars.codeInline.paddingVertical,
                left: vars.codeInline.paddingHorizontal,
                right: vars.codeInline.paddingHorizontal,
            }),
            background: colorOut(vars.codeInline.bg),
            borderRadius: vars.codeInline.borderRadius,
            // We CAN'T use display: `inline` & position: `relative` together.
            // This causes the cursor to disappear in a contenteditable.
            // @see https://bugs.chromium.org/p/chromium/issues/detail?id=724821
            display: "inline",
            position: "static",
        },
        "&& .codeBlock": {
            display: "block",
            wordWrap: "normal",
            lineHeight: vars.codeBlock.lineHeight,
            borderRadius: vars.codeBlock.borderRadius,
            flexShrink: 0, // Needed so code blocks don't collapse in the editor.
            whiteSpace: "pre",
            ...paddings({
                top: vars.codeBlock.paddingVertical,
                bottom: vars.codeBlock.paddingVertical,
                left: vars.codeBlock.paddingHorizontal,
                right: vars.codeBlock.paddingHorizontal,
            }),
        },
    };

    // Blockquotes & spoilers
    // These are temporarily kludged here due to lack of time.
    // They should be fully converted in the future but at the moment
    // Only the bare minimum is convverted in order to make the colors work.
    const spoilersAndQuotes: NestedCSSSelectors = {
        [`& .spoiler,
          & .button-spoiler,
          & .spoiler-icon`]: {
            background: colorOut(vars.blocks.bg),
            color: colorOut(vars.blocks.fg),
        },
        "& .spoiler-icon": {
            margin: 0,
        },
        "& .embedExternal-content": {
            borderRadius: vars.embeds.borderRadius,
            $nest: {
                "&::after": {
                    borderRadius: vars.embeds.borderRadius,
                },
            },
        },
        "& .embedText-content": {
            background: colorOut(vars.embeds.bg),
            color: colorOut(vars.embeds.fg),
            overflow: "hidden",
            ...shadowOrBorderBasedOnLightness(
                globalVars.body.backgroundImage.color,
                borders({
                    color: vars.embeds.fg.fade(0.3),
                }),
                shadowHelper().embed(),
            ),
        },
        [`& .embedText-title,
          & .embedLink-source,
          & .embedLink-excerpt`]: {
            color: colorOut(vars.blocks.fg),
        },
        "& .metaStyle": {
            opacity: 0.8,
        },
        "& .embedLoader-box": {
            background: colorOut(vars.embeds.bg),
            ...borders({
                color: vars.embeds.fg.fade(0.3),
            }),
        },
    };

    const root = style({
        fontSize: vars.fonts.size,
        // These CAN'T be flexed. That breaks margin collapsing.
        display: important("block"),
        position: "relative",
        width: percent(100),
        wordBreak: "break-word",
        lineHeight: globalVars.lineHeights.base,
        $nest: {
            ...headings,
            ...lists,
            ...paragraphSpacing,
            ...linkStyles,
            ...codeStyles,
            ...spoilersAndQuotes,
        },
    });

    return { root };
});
