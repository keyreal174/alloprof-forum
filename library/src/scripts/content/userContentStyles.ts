/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { singleBorder } from "@library/styles/styleHelpers";
import { ColorsUtils } from "@library/styles/ColorsUtils";
import { styleUnit } from "@library/styles/styleUnit";
import { Mixins } from "@library/styles/Mixins";
import { Variables } from "@library/styles/Variables";
import { shadowHelper, shadowOrBorderBasedOnLightness } from "@library/styles/shadowHelpers";
import { CSSObject } from "@emotion/css";
import { TLength } from "@library/styles/styleShim";
import { styleFactory, variableFactory } from "@library/styles/styleUtils";
import { useThemeCache } from "@library/styles/themeCache";
import { em, important, percent, px, border } from "csx";
import { lineHeightAdjustment } from "@library/styles/textUtils";
import { FontSizeProperty } from "csstype";
import { blockQuoteVariables } from "@rich-editor/quill/components/blockQuoteStyles";
import { cssOut } from "@dashboard/compatibilityStyles/cssOut";
import { media } from "@library/styles/styleShim";
import { IThemeVariables } from "@library/theming/themeReducer";

export enum TableStyle {
    HORIZONTAL_BORDER = "horizontalBorder",
    HORIZONTAL_BORDER_STRIPED = "horizontalBorderStriped",
    VERTICAL_BORDER = "verticalBorder",
    VERTICAL_BORDER_STRIPED = "verticalBorderStriped",
}

/**
 * @varGroup userContent
 * @commonTitle User Content
 */
export const userContentVariables = useThemeCache((forcedVars?: IThemeVariables) => {
    const makeThemeVars = variableFactory("userContent", forcedVars);
    const globalVars = globalVariables(forcedVars);
    const { mainColors } = globalVars;

    /**
     * @varGroup userContent.fonts
     */
    const fonts = makeThemeVars("fonts", {
        /**
         * @var userContent.fonts.size
         * @description Default font size for user content.
         */
        size: globalVars.fonts.size.large,

        /**
         * @varGroup userContent.fonts.headings
         * @commonDescription These are best specified as a relative units. (Eg. "1.5em", "2em").
         */
        headings: {
            /**
             * @var userContent.fonts.headings.h1
             */
            h1: "2em",
            /**
             * @var userContent.fonts.headings.h2
             */
            h2: "1.5em",
            /**
             * @var userContent.fonts.headings.h3
             */
            h3: "1.25em",
            /**
             * @var userContent.fonts.headings.h4
             */
            h4: "1em",
            /**
             * @var userContent.fonts.headings.h5
             */
            h5: ".875em",
            /**
             * @var userContent.fonts.headings.h6
             */
            h6: ".85em",
        },
    });

    /**
     * @varGroup userContent.tables
     * @title User Content - Tables
     */
    const tableInit = makeThemeVars("tables", {
        /**
         * @var userContent.tables.style
         * @description Choose a preset for the table styles.
         * @type string
         * @enum horizontalBorder|horizontalBorderStriped|verticalBorder|verticalBorderStriped
         */
        style: TableStyle.HORIZONTAL_BORDER_STRIPED,
        /**
         * @varGroup userContent.tables.borders
         * @title User Content - Tables - Borders
         * @expand border
         */
        borders: globalVars.border,

        cell: {
            /**
             * @var userContent.tables.cell.alignment
             * @title Cell Alignment
             * @description Choose the alignment of table cells.
             * @type string
             * @enum "center" | "left" | "right",
             */
            alignment: "left" as "center" | "left" | "right",
        },

        /**
         * @var userContent.tables.mobileBreakpoint
         * @title Mobile Breakpoint
         * @description The device width (pixels) where the table switches to a mobile layout.
         */
        mobileBreakpoint: 600,
    });

    const tables = makeThemeVars("tables", {
        ...tableInit,
        striped: [TableStyle.HORIZONTAL_BORDER_STRIPED, TableStyle.VERTICAL_BORDER_STRIPED].includes(tableInit.style),
        stripeColor: globalVars.mixBgAndFg(0.05),
        outerBorderRadius: [TableStyle.VERTICAL_BORDER_STRIPED, TableStyle.VERTICAL_BORDER].includes(tableInit.style)
            ? 4
            : 0,
        horizontalBorders: {
            enabled: true, // All current variants have horizontal borders.
            borders: tableInit.borders,
        },
        verticalBorders: {
            enabled: [TableStyle.VERTICAL_BORDER_STRIPED, TableStyle.VERTICAL_BORDER].includes(tableInit.style),
            borders: tableInit.borders,
        },
    });

    const blocks = makeThemeVars("blocks", {
        margin: fonts.size,
        fg: mainColors.fg,
        bg: globalVars.mixBgAndFg(0.035),
    });

    /**
     * @varGroup userContent.embeds
     * @title User Content - Embeds
     */
    const embeds = makeThemeVars("embeds", {
        /**
         * @var userContent.embeds.bg
         * @title Background
         * @type string
         * @format hex-color
         */
        bg: mainColors.bg,
        /**
         * @var userContent.embeds.fg
         * @title Text Color
         * @type string
         * @format hex-color
         */
        fg: mainColors.fg,

        /**
         * @var userContent.embeds.borderRadius
         * @title Border Radius
         * @description Border radius of an embed in pixels.
         * @type string|number
         */
        borderRadius: px(2),
    });

    /**
     * @varGroup userContent.code
     * @title User Content - Code
     * @commonDescription Applies to inline and block style code items.
     */
    const code = makeThemeVars("code", {
        /**
         * @var userContent.code.fontSize
         * @type string|number
         */
        fontSize: em(0.85),
        /**
         * @var userContent.code.borderRadius
         * @type string|number
         */
        borderRadius: 2,
    });

    /**
     * @varGroup userContent.codeInline
     * @title User Content - Code (Inline)
     * @commonDescription Applies only to inline code elements. Not Blocks.
     */
    const codeInline = makeThemeVars("codeInline", {
        /**
         * @var userContent.codeInline.borderRadius
         * @type string|number
         */
        borderRadius: code.borderRadius,

        // TODO: replace with Variables.spacing()
        paddingVertical: em(0.2),
        // TODO: replace with Variables.spacing()
        paddingHorizontal: em(0.4),

        /**
         * @var userContent.codeInline.fg
         * @title Text Color
         * @type string
         * @format hex-color
         */
        fg: blocks.fg,
        /**
         * @var userContent.codeInline.bg
         * @title Background
         * @type string
         * @format hex-color
         */
        bg: blocks.bg,
    });

    /**
     * @varGroup userContent.codeBlock
     * @title User Content - Code (Block)
     * @commonDescription Applies only to code block elements. Not inline code.
     */
    const codeBlock = makeThemeVars("codeBlock", {
        /**
         * @var userContent.codeBlock.borderRadius
         * @type string|number
         */
        borderRadius: globalVars.border.radius,

        // TODO: replace with Variables.spacing()
        paddingVertical: fonts.size,
        // TODO: replace with Variables.spacing()
        paddingHorizontal: fonts.size,

        /**
         * @var userContent.codeBlock.lineHeight
         * @type number
         */
        lineHeight: 1.45,
        /**
         * @var userContent.codeBlock.fg
         * @title Text Color
         * @type string
         * @format hex-color
         */
        fg: blocks.fg,
        /**
         * @var userContent.codeBlock.bg
         * @title Background
         * @type string
         * @format hex-color
         */
        bg: blocks.bg,
    });

    /**
     * @varGroup userContent.list
     * @title User Content - Lists
     */
    const list = makeThemeVars("list", {
        /**
         * @varGroup userContent.list.spacing
         * @title User Content - List - Spacing
         * @expand spacing
         */
        spacing: Variables.spacing({
            top: em(0.5),
            left: em(2),
        }),
        listDecoration: {
            minWidth: em(2),
        },
        nestedList: {
            margin: "0 0 0 1em",
        },
    });

    const spacing = makeThemeVars("spacing", {
        base: 2 * Math.ceil((globalVars.spacer.size * 5) / 8),
    });

    return {
        fonts,
        list,
        blocks,
        code,
        codeInline,
        codeBlock,
        embeds,
        spacing,
        tables,
    };
});

/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
export const userContentClasses = useThemeCache(() => {
    const style = styleFactory("userContent");
    const vars = userContentVariables();
    const globalVars = globalVariables();

    const listItem: CSSObject = {
        position: "relative",
        ...Mixins.margin({
            top: vars.list.spacing.top,
            left: vars.list.spacing.left,
        }),
        ...{
            "&:first-child": {
                marginTop: 0,
            },
            "&:last-child": {
                marginBottom: 0,
            },
        },
    };

    const headingStyle = (tag: string, fontSize: FontSizeProperty<TLength>): CSSObject => {
        return {
            marginTop: styleUnit(vars.spacing.base),
            fontSize,
            ...lineHeightAdjustment(),
        };
    };

    const headings: CSSObject = {
        "& h1:not(.heading)": headingStyle("h1", vars.fonts.headings.h1),
        "& h2:not(.heading)": headingStyle("h2", vars.fonts.headings.h2),
        "& h3:not(.heading)": headingStyle("h3", vars.fonts.headings.h3),
        "& h4:not(.heading)": headingStyle("h4", vars.fonts.headings.h4),
        "& h5:not(.heading)": headingStyle("h5", vars.fonts.headings.h5),
        "& h6:not(.heading)": headingStyle("h6", vars.fonts.headings.h6),
    };

    const lists: CSSObject = {
        ["& ol, & ul"]: {
            listStylePosition: "inside",
            margin: `1em 0 1em 2em`,
            padding: 0,
        },
        ["& ul"]: {
            listStyle: "disc",
            ...{
                [`& > li`]: {
                    listStyle: "none",
                    position: "relative",
                },
                [`& > li::before`]: {
                    fontFamily: `'Arial', serif`,
                    content: `"???"`,
                    position: "absolute",
                    left: em(-1),
                },
                [`& ol, & ul`]: {
                    margin: vars.list.nestedList.margin,
                },
            },
        },
        ["& ol"]: {
            ...{
                [`& > li`]: {
                    listStyle: "decimal",
                },
                [`& ol > li`]: {
                    listStyle: "lower-alpha",
                },
                [`& ol ol > li`]: {
                    listStyle: "lower-roman",
                },
                [`& ol ol ol > li`]: {
                    listStyle: "decimal",
                },
                [`& ol ol ol ol > li`]: {
                    listStyle: "lower-alpha",
                },
                [`& ol ol ol ol ol > li`]: {
                    listStyle: "lower-roman",
                },
                [`& ol ol ol ol ol ol > li`]: {
                    listStyle: "decimal",
                },
                [`& ol, & ul`]: {
                    margin: vars.list.nestedList.margin,
                },
            },
        },
        [`& li`]: {
            margin: `5px 0`,
            ...{
                [`&, & *:first-child`]: {
                    marginTop: 0,
                },
                [`&, & *:last-child`]: {
                    marginBottom: 0,
                },
            },
        },
    };

    const paragraphSpacing: CSSObject = {
        "& > p": {
            marginTop: 0,
            marginBottom: 0,
            ...{
                "&:not(:first-child)": {
                    marginTop: vars.blocks.margin * 0.5,
                },
                "&:first-child": {
                    ...lineHeightAdjustment(),
                },
            },
        },

        "&& > *:not(:last-child):not(.embedResponsive)": {
            marginBottom: vars.blocks.margin,
        },

        "&& > *:first-child": {
            marginTop: 0,
            ...{
                "&::before": {
                    marginTop: 0,
                },
            },
        },
    };

    const linkColors = Mixins.clickable.itemState();
    const linkStyle = {
        "& a": {
            color: ColorsUtils.colorOut(linkColors.color as string),
        },
        "& a:hover": {
            color: ColorsUtils.colorOut(globalVars.links.colors.hover),
            textDecoration: "underline",
        },
        "& a:focus": {
            color: ColorsUtils.colorOut(globalVars.links.colors.focus),
            textDecoration: "underline",
        },
        "& a.focus-visible": {
            color: ColorsUtils.colorOut(globalVars.links.colors.keyboardFocus),
            textDecoration: "underline",
        },
        "& a:active": {
            color: ColorsUtils.colorOut(globalVars.links.colors.active),
            textDecoration: "underline",
        },
    };

    const codeStyles: CSSObject = {
        ".code": {
            position: "relative",
            fontSize: vars.code.fontSize,
            fontFamily: `Menlo, Monaco, Consolas, "Courier New", monospace`,
            maxWidth: percent(100),
            overflowX: "auto",
            margin: 0,
            color: ColorsUtils.colorOut(vars.blocks.fg),
            backgroundColor: ColorsUtils.colorOut(vars.blocks.bg),
            border: "none",
        },
        "&& .codeInline": {
            whiteSpace: "normal",
            ...Mixins.padding({
                top: vars.codeInline.paddingVertical,
                bottom: vars.codeInline.paddingVertical,
                left: vars.codeInline.paddingHorizontal,
                right: vars.codeInline.paddingHorizontal,
            }),
            color: ColorsUtils.colorOut(vars.codeInline.fg),
            backgroundColor: ColorsUtils.colorOut(vars.codeInline.bg),
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
            color: ColorsUtils.colorOut(vars.codeBlock.fg),
            backgroundColor: ColorsUtils.colorOut(vars.codeBlock.bg),
            ...Mixins.padding({
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
    const spoilersAndQuotes: CSSObject = {
        ".embedExternal-content": {
            borderRadius: vars.embeds.borderRadius,
            ...{
                "&::after": {
                    borderRadius: vars.embeds.borderRadius,
                },
            },
        },
        ".embedText-content": {
            background: ColorsUtils.colorOut(vars.embeds.bg),
            color: ColorsUtils.colorOut(vars.embeds.fg),
            overflow: "hidden",
            ...shadowOrBorderBasedOnLightness(
                globalVars.body.backgroundImage.color,
                Mixins.border({
                    color: vars.embeds.fg.fade(0.3),
                }),
                shadowHelper().embed(),
            ),
        },
        [`.embedText-title,
          .embedLink-source,
          .embedLink-excerpt`]: {
            color: ColorsUtils.colorOut(vars.blocks.fg),
        },
        ".metaStyle": {
            opacity: 0.8,
        },
        ".embedLoader-box": {
            background: ColorsUtils.colorOut(vars.embeds.bg),
            ...Mixins.border({
                color: vars.embeds.fg.fade(0.3),
            }),
        },
    };

    const embeds: CSSObject = {
        "&& .embedExternal": {
            marginBottom: vars.blocks.margin,
        },
        [`&& .float-left,
          && .float-right`]: {
            marginBottom: "0 !important",
        },
        [`&& .float-left .embedExternal-content,
          && .float-right .embedExternal-content`]: {
            marginBottom: vars.blocks.margin,
        },
    };

    const blockQuoteVars = blockQuoteVariables();

    const blockquotes: CSSObject = {
        ".blockquote": {
            color: ColorsUtils.colorOut(blockQuoteVars.colors.fg),
        },
    };

    const tables: CSSObject = {
        ".tableWrapper": {
            overflowX: "auto",
            width: percent(100),
        },
        "& > .tableWrapper > table": {
            width: percent(100),
        },
        // Rest of the table styles
        "& > .tableWrapper th": {
            whiteSpace: "nowrap",
        },
        "& > .tableWrapper td, & > .tableWrapper th": {
            overflowWrap: "break-word",
            minWidth: 80,
            ...Mixins.padding({
                vertical: 6,
                horizontal: 12,
            }),
            border: "none",
            textAlign: vars.tables.cell.alignment,
            ...(vars.tables.horizontalBorders.enabled
                ? {
                      borderTop: singleBorder(vars.tables.horizontalBorders.borders),
                      borderBottom: singleBorder(vars.tables.horizontalBorders.borders),
                  }
                : {}),
            ...(vars.tables.verticalBorders.enabled
                ? {
                      borderLeft: singleBorder(vars.tables.verticalBorders.borders),
                      borderRight: singleBorder(vars.tables.verticalBorders.borders),
                  }
                : {}),
        },
        "& > .tableWrapper tr:nth-child(even)": vars.tables.striped
            ? {
                  background: ColorsUtils.colorOut(vars.tables.stripeColor),
              }
            : {},
        "& > .tableWrapper th, & > .tableWrapper thead td": {
            fontWeight: globalVars.fonts.weights.bold,
        },

        // Mobile table styles.
        ".mobileTableHead": {
            display: "none",
        },
    };

    const outerBorderMixin = (): CSSObject => {
        return {
            borderRadius: vars.tables.outerBorderRadius,
            borderTop: vars.tables.horizontalBorders.enabled
                ? singleBorder(vars.tables.horizontalBorders.borders)
                : undefined,
            borderBottom: vars.tables.horizontalBorders.enabled
                ? singleBorder(vars.tables.horizontalBorders.borders)
                : undefined,
            borderLeft: vars.tables.verticalBorders.enabled
                ? singleBorder(vars.tables.verticalBorders.borders)
                : undefined,
            borderRight: vars.tables.verticalBorders.enabled
                ? singleBorder(vars.tables.verticalBorders.borders)
                : undefined,
        };
    };

    // Apply outer border radii.
    // border-collapse prevents our outer radius from applying.
    const tableOuterRadiusQuery = media(
        { minWidth: vars.tables.mobileBreakpoint + 1 },
        {
            ...{
                ".tableWrapper": outerBorderMixin(),
                "& > .tableWrapper thead tr:first-child > *, & > .tableWrapper tbody:first-child tr:first-child > *": {
                    // Get rid of the outer border radius.
                    borderTop: "none",
                },
                "& > .tableWrapper :not(thead) tr:last-child > *": {
                    // Get rid of the outer border radius.
                    borderBottom: "none",
                },
                "& > .tableWrapper tr > *:last-child": {
                    // Get rid of the outer border radius.
                    borderRight: "none",
                },
                "& > .tableWrapper tr > *:first-child, & > .tableWrapper tr > .mobileTableHead:first-child + *": {
                    // Get rid of the outer border radius.
                    borderLeft: "none",
                },
            },
        },
    );

    const tableMobileQuery = media(
        { maxWidth: vars.tables.mobileBreakpoint },
        {
            ...{
                ".tableWrapper .tableHead": {
                    ...Mixins.absolute.srOnly(),
                },
                ".tableWrapper tr": {
                    display: "block",
                    flexWrap: "wrap",
                    width: percent(100),
                    background: "none !important",
                    marginBottom: vars.blocks.margin,
                    ...outerBorderMixin(),
                },
                ".tableWrapper tr .mobileStripe": vars.tables.striped
                    ? {
                          borderTop: "none",
                          borderBottom: "none",
                          background: ColorsUtils.colorOut(vars.tables.stripeColor),
                      }
                    : {
                          borderTop: "none",
                          borderBottom: "none",
                      },
                // First row.
                ".tableWrapper tr > *:first-child": {
                    borderTop: "none",
                },
                // Last row.
                ".tableWrapper tr > *:last-child": {
                    borderBottom: "none",
                },
                ".tableWrapper .mobileTableHead": {
                    borderBottom: "none",
                },
                ".tableWrapper .mobileTableHead + *": {
                    marginTop: -6,
                    borderTop: "none",
                },
                ".tableWrapper tr > *": {
                    width: percent(100),
                    wordWrap: "break-word",
                    display: "block",
                    borderLeft: "none",
                    borderRight: "none",
                },
                ".tableWrapper tr > :not(.mobileTableHead)": {
                    borderRight: "none",
                },
            },
        },
    );

    const root = style(
        {
            // These CAN'T be flexed. That breaks margin collapsing.
            display: important("block"),
            position: "relative",
            width: percent(100),
            wordBreak: "break-word",
            lineHeight: globalVars.lineHeights.base,
            fontSize: vars.fonts.size,
            marginTop: lineHeightAdjustment()["::before"]?.marginTop,
            ...{
                // A placeholder might be put in a ::before element. Make sure we match the line-height adjustment.
                "& iframe": {
                    width: percent(100),
                },
                ...tables,
                ...headings,
                ...lists,
                ...paragraphSpacing,
                ...codeStyles,
                ...spoilersAndQuotes,
                ...embeds,
                ...blockquotes,
                ...linkStyle,
            },
        },
        tableOuterRadiusQuery,
        tableMobileQuery,
    );

    return { root };
});

export const userContentCSS = () => {
    const globalVars = globalVariables();
    cssOut(
        `
        .Container .userContent h1,
        .Container .userContent h2,
        .Container.userContent h3,
        .Container .userContent h4,
        .Container .userContent h5,
        .Container .userContent h6`,
        {
            color: ColorsUtils.colorOut(globalVars.mainColors.fg),
        },
    );
};
