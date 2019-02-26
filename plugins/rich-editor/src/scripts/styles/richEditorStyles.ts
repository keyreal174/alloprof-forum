/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { calc, important, percent, px, viewHeight } from "csx";
import { globalVariables } from "@library/styles/globalStyleVars";
import {
    absolutePosition,
    borders,
    componentThemeVariables,
    debugHelper,
    longWordEllipsis,
    paddings,
    placeholderStyles,
    singleLineEllipsis,
    srOnly,
    styleFactory,
    textInputSizing,
    unit,
} from "@library/styles/styleHelpers";
import { layoutVariables } from "@library/styles/layoutStyles";
import { formElementsVariables } from "@library/components/forms/formElementStyles";
import { standardAnimations } from "@library/styles/animationHelpers";
import { shadowHelper } from "@library/styles/shadowHelpers";

export function richEditorVariables(theme?: object) {
    const globalVars = globalVariables(theme);
    const elementaryColor = globalVars.elementaryColors;
    const varsFormElements = formElementsVariables(theme);
    const themeVars = componentThemeVariables(theme, "richEditor");
    const animations = standardAnimations();

    const colors = {
        bg: globalVars.mainColors.bg,
        outline: globalVars.mainColors.primary.fade(0.6),
        ...themeVars.subComponentStyles("colors"),
    };

    const spacing = {
        paddingLeft: 36,
        paddingRight: 36,
        paddingTop: 12,
        paddingBottom: 12,
        embedMenu: 0,
        ...themeVars.subComponentStyles("spacing"),
    };

    const sizing = {
        minHeight: 200,
        ...themeVars.subComponentStyles("sizing"),
    };

    const menuButton = {
        size: globalVars.icon.sizes.default,
        ...themeVars.subComponentStyles("menuButton"),
    };

    const floatingButton = {
        size: 28,
        offset: -varsFormElements.border.width + 1,
        ...themeVars.subComponentStyles("floatingButton"),
    };

    const insertLink = {
        width: 287,
        leftPadding: 9,
        ...themeVars.subComponentStyles("insertLink"),
    };

    const flyout = {
        padding: {
            top: 12,
            right: 12,
            bottom: 12,
            left: 12,
        },
        maxHeight: viewHeight(100),
        height: menuButton.size,
        ...themeVars.subComponentStyles("flyout"),
    };

    const nub = {
        width: 12,
        ...themeVars.subComponentStyles("nub"),
    };

    const menu = {
        borderWidth: 1,
        offset: nub.width,
        ...themeVars.subComponentStyles("menu"),
    };

    const pilcrow = {
        offset: 9,
        fontSize: 14,
        animation: {
            duration: ".3s",
            name: animations.fadeIn,
            timing: "ease-out",
            iterationCount: 1,
        },
        ...themeVars.subComponentStyles("pilcrow"),
    };

    const emojiGroup = {
        paddingLeft: 3,
        offset: -(varsFormElements.border.width + menu.borderWidth * 2),
        ...themeVars.subComponentStyles("emojiGroup"),
    };

    const embedMenu = {
        padding: 0,
        mobile: {
            border: {
                color: globalVars.mainColors.primary,
            },
            transition: {
                duration: ".15s",
                timing: "ease-out",
            },
        },
        ...themeVars.subComponentStyles("embedMenu"),
    };

    const embedButton = {
        offset: -varsFormElements.border.width,
        ...themeVars.subComponentStyles("embedButton"),
    };

    const text = {
        offset: 0,
        ...themeVars.subComponentStyles("text"),
    };

    const title = {
        height: globalVars.fonts.size.title + globalVars.gutter.half,
        fontSize: globalVars.fonts.size.title,
        placeholder: {
            color: globalVars.mixBgAndFg(0.5),
        },
        ...themeVars.subComponentStyles("titleInput"),
    };

    const scrollContainer = {
        overshoot: 48,
        ...themeVars.subComponentStyles("scrollContainer"),
    };

    return {
        colors,
        spacing,
        sizing,
        menuButton,
        floatingButton,
        insertLink,
        flyout,
        nub,
        menu,
        pilcrow,
        emojiGroup,
        embedButton,
        text,
        title,
        embedMenu,
        scrollContainer,
    };
}

export function richEditorClasses(theme?: object) {
    const globalVars = globalVariables(theme);
    const mediaQueries = layoutVariables(theme).mediaQueries();
    const style = styleFactory("richEditor");
    const formElementVars = formElementsVariables(theme);
    const vars = richEditorVariables(theme);

    const root = style({
        position: "relative",
        display: "block",
        $nest: {
            "&.isDisabled": {
                $nest: {
                    "&, &.richEditor-button": {
                        cursor: important("progress"),
                    },
                },
            },
            "&.inheritHeight": {
                $nest: {
                    ".richEditor-text, richEditor-textWrap, richEditor-frame": {
                        display: "flex",
                        flexDirection: "column",
                        flexGrow: 1,
                    },
                },
            },
            ".ql-clipboard": {
                ...srOnly(),
                position: "fixed", // Fixed https://github.com/quilljs/quill/issues/1374#issuecomment-415333651
            },
            ".richEditor-nextInput, .iconButton, .richEditor-button": {
                ...singleLineEllipsis(),
                position: "relative",
                appearance: "none",
                border: 0,
                padding: 0,
                background: "none",
                textAlign: "left",
            },
            ".Close-x": {
                display: "block",
                opacity: globalVars.states.icon.opacity,
                cursor: "pointer",
            },
        },
    });

    const scrollContainer = style("scrollContainer", {
        position: "relative",
        overflow: "auto",
        height: percent(100),
        width: calc(`100% + ${unit(vars.scrollContainer.overshoot * 2)}`),
        marginLeft: unit(-vars.scrollContainer.overshoot),
        padding: {
            left: unit(vars.scrollContainer.overshoot),
            right: unit(vars.scrollContainer.overshoot),
        },
    });

    const frame = style("frame", {
        position: "relative",
        backgroundColor: vars.colors.bg,
        height: "auto",
        padding: 0,
        $nest: {
            "&.isMenuInset": {
                overflow: "initial",
                position: "relative",
            },
        },
    });

    const menu = style("menu", {
        display: "inline-block",
        position: "relative",
    });

    const paragraphMenu = style("paragraphMenu", {
        position: "absolute",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        top: unit(vars.pilcrow.offset),
        left: unit(vars.spacing.paddingLeft - globalVars.icon.sizes.default + 2),
        transform: `translateX(-100%)`,
        height: unit(vars.floatingButton.size),
        width: unit(globalVars.icon.sizes.default),
        animationName: vars.pilcrow.animation.name,
        animationDuration: vars.pilcrow.animation.duration,
        animationTimingFunction: vars.pilcrow.animation.timing,
        animationIterationCount: vars.pilcrow.animation.iterationCount,
        zIndex: 1,
        $nest: {
            ".richEditor-button&.isActive:hover": {
                cursor: "default",
            },
            "&.isMenuInset": {
                transform: "none",
            },
        },
    });

    const floatingButton = style("floatingButton", {
        width: unit(vars.floatingButton.size),
        maxWidth: unit(vars.floatingButton.size),
        minWidth: unit(vars.floatingButton.size),
        height: unit(vars.floatingButton.size),
    });

    const text = style("text", {
        position: "relative",
        minHeight: unit(vars.sizing.minHeight),
        whiteSpace: important("pre-wrap"),
        outline: 0,
    });

    const menuItems = style("menuItems", {
        "-ms-overflow-style": "-ms-autohiding-scrollbar",
        position: "relative",
        display: "flex",
        alignItems: "flex-start",
        justifyContent: "flex-start",
        flexWrap: "nowrap",
        listStyle: "none",
        padding: 0,
        margin: 0,
        zIndex: 1,
        $nest: {
            ".richEditor-menuItem": {
                display: "block",
                padding: 0,
                margin: 0,
                $nest: {
                    ".richEditor-button, &.richEditor-button": {
                        width: unit(vars.menuButton.size),
                        fontSize: unit((vars.menuButton.size * 24) / 39),
                        lineHeight: unit(vars.menuButton.size),
                        $nest: {
                            "&.emojiChar-🇺🇳": {
                                fontSize: unit(10),
                            },
                        },
                    },
                    "&:first-child .richEditor-embedButton": {
                        borderBottomLeftRadius: unit(globalVars.border.radius),
                    },
                    "&.isRightAligned": {
                        marginLeft: "auto",
                    },
                },
            },
        },
    });

    const button = style("button", {
        display: "block",
        userSelect: "none",
        cursor: "pointer",
        $nest: {
            "&.richEditor-formatButton, &.richEditor-embedButton": {
                height: unit(vars.menuButton.size),
                color: "inherit",
            },
            "&.emojiGroup": {
                display: "block",
                width: unit(vars.menuButton.size),
                height: unit(vars.menuButton.size),
                textAlign: "center",
                $nest: {
                    "&.isSelected": {
                        opacity: 1,
                    },
                },
            },
            "&:not(:disabled)": {
                cursor: "pointer",
            },
            "&:hover": {
                opacity: 1,
                cursor: "pointer",
            },
            "&:hover .Close-X, &:hover .richEditorButton-icon": {
                opacity: 1,
            },
            "&:focus": {
                opacity: 1,
                zIndex: 2,
            },
            "&:focus .Close-X, &:focus .richEditorButton-icon": {
                opacity: 1,
            },
            "&.isActive": {
                opacity: 1,
            },
            "&.isActive .Close-X, .isActive .richEditorButton-icon": {
                opacity: 1,
            },
            "&.isOpen": {
                opacity: 1,
            },
            "&.richEditor-formatButton:focus": {
                opacity: 1,
            },
        },
    });

    const menuItem = style("menuItem", {
        display: "block",
        padding: 0,
        margin: 0,
        $nest: {
            ".richEditor-button, &.richEditor-button": {
                width: unit(vars.menuButton.size),
                height: unit(vars.menuButton.size),
                maxWidth: unit(vars.menuButton.size),
                fontSize: unit((vars.menuButton.size * 24) / 39),
                lineHeight: unit(vars.menuButton.size),
                $nest: {
                    "&.emojiChar-🇺🇳": {
                        fontSize: unit(14),
                    },
                },
            },
            "&.isRightAligned": {
                marginLeft: "auto",
            },
        },
    });

    const upload = style("upload", {
        display: important("none"),
    });

    const embedBar = style("embedBar", {
        display: "block",
        width: percent(100),
        padding: unit(vars.embedMenu.padding),
        marginTop: unit(formElementVars.border.width),
    });

    const icon = style("icon", {
        display: "block",
        margin: "auto",
        height: unit(globalVars.icon.sizes.default),
        width: unit(globalVars.icon.sizes.default),
        opacity: globalVars.states.icon.opacity,
    });

    const close = style("close", {
        $nest: {
            "&, &.Close": {
                position: "relative",
                display: "block",
                width: unit(vars.menuButton.size),
                height: unit(vars.menuButton.size),
                lineHeight: unit(vars.menuButton.size),
                verticalAlign: "bottom",
                top: "auto",
                right: "auto",
                textAlign: "center",
                userSelect: "none",
                background: "transparent",
                cursor: "pointer",
                opacity: globalVars.states.icon.opacity,
                $nest: {
                    "&:hover, &:focus, &.focus-visible, &:active": {
                        opacity: 1,
                        cursor: "pointer",
                    },
                },
            },
        },
    });

    return {
        root,
        scrollContainer,
        menu,
        paragraphMenu,
        floatingButton,
        text,
        menuItems,
        upload,
        embedBar,
        menuItem,
        frame,
        button,
        icon,
        close,
    };
}

export function insertLinkClasses(theme?: object) {
    const vars = richEditorVariables(theme);
    const style = styleFactory("insertLink");

    const root = style({
        display: "flex",
        flexWrap: "nowrap",
        alignItems: "center",
        maxWidth: unit(vars.insertLink.width),
        width: percent(100),
        paddingLeft: unit(vars.insertLink.leftPadding),
    });

    const input = style("input", {
        $nest: {
            "&, &.InputBox": {
                border: important("0"),
                marginBottom: important("0"),
                flexGrow: 1,
                maxWidth: calc(`100% - ${unit(vars.menuButton.size)}`),
            },
        },
    });

    return { root };
}

export function richEditorFlyoutClasses(theme?: object) {
    const vars = richEditorVariables(theme);
    const style = styleFactory("richEditorFlyout");
    const shadows = shadowHelper(theme);
    const globalVars = globalVariables(theme);

    const root = style({
        ...shadows.dropDown(),
        ...borders(),
        position: "relative",
        overflow: "hidden",
        backgroundColor: globalVars.mainColors.bg.toString(),
        zIndex: 6,
        $nest: {
            "& .InputBox": {
                width: percent(100),
                boxSizing: "border-box",
            },
            "& .richEditor-close": {
                position: "absolute",
                top: 0,
                right: 0,
            },
            "& .Footer": {
                display: "flex",
            },
        },
    });

    const header = style("header", {
        ...paddings({
            top: unit(vars.flyout.padding.top / 2),
            right: unit(vars.flyout.padding.right),
            bottom: unit(vars.flyout.padding.bottom / 2),
            left: unit(vars.flyout.padding.left),
        }),
    });

    const footer = style("footer", {
        ...paddings(vars.flyout.padding),
        $nest: {
            "&.insertEmoji-footer": {
                padding: 0,
            },
        },
    });

    const title = style("title", {
        ...longWordEllipsis(),
        margin: 0,
        maxWidth: calc(`100% - ${unit(vars.menuButton.size)}`),
        minHeight: unit(vars.menuButton.size - vars.flyout.padding.top),
        fontSize: percent(100),
        lineHeight: "inherit",
        color: globalVars.mainColors.fg.toString(),
        $nest: {
            "&:focus": {
                outline: 0,
            },
        },
    });

    return { root, header, footer, title };
}

export function insertMediaClasses(theme?: object) {
    const globalVars = globalVariables(theme);
    const mediaQueries = layoutVariables(theme).mediaQueries();
    const vars = richEditorVariables(theme);
    const formElementVars = formElementsVariables(theme);
    const style = styleFactory("insertMedia");

    const root = style({});

    return { root };
}

export function richEditorFormClasses(theme?: object) {
    const globalVars = globalVariables(theme);
    const mediaQueries = layoutVariables(theme).mediaQueries();
    const vars = richEditorVariables(theme);
    const formElementVars = formElementsVariables(theme);
    const style = styleFactory("richEditorForm");

    const root = style({});

    const frame = style("frame", {
        width: calc(`100% + ${unit(globalVars.gutter.half)}`),
        marginLeft: unit(-globalVars.gutter.quarter),
    });

    const textWrap = style("textWrap", {
        ...paddings({
            top: 0,
            bottom: 0,
            right: unit(globalVars.gutter.quarter),
            left: unit(globalVars.gutter.quarter),
        }),
    });

    const title = style("title", {
        $nest: {
            "&.inputText, &&": {
                ...textInputSizing(
                    vars.title.height,
                    vars.title.fontSize,
                    globalVars.gutter.half,
                    formElementVars.colors.fg,
                    formElementVars.border.fullWidth,
                ),
                $nest: {
                    "&:active, &:focus, &.focus-visible": {
                        boxShadow: "none",
                    },
                    ...placeholderStyles({
                        lineHeight: "inherit",
                        padding: "inherit",
                        color: formElementVars.colors.placeholder.toString(),
                    }),
                },
            },
        },
    });

    const editor = style("editor", {
        borderTopLeftRadius: 0,
        borderTopRightRadius: 0,
        marginTop: unit(-formElementVars.border.width),
        display: "flex",
        flexDirection: "column",
    });

    const scrollContainer = style("scrollContainer", {
        paddingTop: unit(globalVars.gutter.half),
    });

    const scrollFrame = style("scrollFrame", {
        ...absolutePosition.bottomLeft(),
        width: percent(100),
        height: calc(`100% - ${formElementVars.border.width + formElementVars.sizing.height}`),
    });

    const body = style("body", {
        paddingTop: unit(globalVars.overlay.fullPageHeadingSpacer),
        flexGrow: 1,
    });

    const inlineMenuItems = style("inlineMenuItems", {
        borderBottom: `${formElementVars.border.width} solid ${formElementVars.border.color.toString()}`,
    });

    return {
        root,
        frame,
        textWrap,
        title,
        editor,
        scrollContainer,
        scrollFrame,
        body,
        inlineMenuItems,
    };
}
