/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { formElementsVariables } from "@library/forms/formElementStyles";
import { globalVariables } from "@library/styles/globalStyleVars";
import {
    allButtonStates,
    borders,
    colorOut,
    offsetLightness,
    flexHelper,
    modifyColorBasedOnLightness,
    unit,
    userSelect,
    absolutePosition,
    pointerEvents,
    singleBorder,
    sticky,
    BorderType,
} from "@library/styles/styleHelpers";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { ColorHelper, percent, px, quote, viewHeight, url, translate, rgba } from "csx";
import backLinkClasses from "@library/routing/links/backLinkStyles";
import { NestedCSSProperties } from "typestyle/lib/types";
import { iconClasses } from "@library/icons/iconClasses";
import { shadowHelper } from "@library/styles/shadowHelpers";
import { IButtonType } from "@library/forms/styleHelperButtonInterface";
import { buttonResetMixin, ButtonTypes } from "@library/forms/buttonStyles";
import generateButtonClass from "@library/forms/styleHelperButtonGenerator";
import { media } from "typestyle";
import { LogoAlignment } from "./TitleBar";
import { searchBarClasses } from "@library/features/search/searchBarStyles";

export const titleBarVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const formElementVars = formElementsVariables();
    const makeThemeVars = variableFactory("titleBar");

    const sizing = makeThemeVars("sizing", {
        height: 48,
        spacer: 12,
        mobile: {
            height: 44,
            width: formElementVars.sizing.height,
        },
    });

    const colors = makeThemeVars("colors", {
        fg: globalVars.mainColors.bg,
        bg: globalVars.mainColors.primary,
        bgImage: null as string | null,
    });

    const fullBleed = makeThemeVars("fullBleed", {
        enabled: false,
        startingOpacity: 0,
        endingOpacity: 0.15, // Scale of 0 -> 1 where 1 is opaque.
        bgColor: colors.bg,
    });

    // Fix up the ending opacity so it is always darker than the starting one.
    fullBleed.endingOpacity = Math.max(fullBleed.startingOpacity, fullBleed.endingOpacity);

    const guest = makeThemeVars("guest", {
        spacer: 8,
    });

    const border = makeThemeVars("border", {
        type: BorderType.NONE,
    });

    const buttonSize = globalVars.buttonIcon.size;
    const button = makeThemeVars("button", {
        borderRadius: globalVars.border.radius,
        size: buttonSize,
        guest: {
            minWidth: 86,
        },
        mobile: {
            fontSize: 16,
            width: buttonSize,
        },
        state: {
            bg: globalVars.mainColors.statePrimary,
        },
    });

    const navAlignment = makeThemeVars("navAlignment", {
        alignment: "left" as "left" | "center",
    });

    const generatedColors = makeThemeVars("generatedColors", {
        state: offsetLightness(colors.bg, 0.04), // Default state color change
    });

    const linkButtonDefaults: IButtonType = {
        name: ButtonTypes.TITLEBAR_LINK,
        colors: {
            bg: rgba(0, 0, 0, 0),
            fg: colors.fg,
        },
        fonts: {
            color: colors.fg,
        },
        sizing: {
            minWidth: unit(globalVars.icon.sizes.large),
            minHeight: unit(globalVars.icon.sizes.large),
        },
        padding: {
            side: 6,
        },
        borders: {
            style: "none",
            color: rgba(0, 0, 0, 0),
        },
        hover: {
            colors: {
                bg: generatedColors.state,
            },
        },
        focus: {
            colors: {
                bg: generatedColors.state,
            },
        },
        focusAccessible: {
            colors: {
                bg: generatedColors.state,
            },
        },
        active: {
            colors: {
                bg: generatedColors.state,
            },
        },
    };
    const linkButton: IButtonType = makeThemeVars("linkButton", linkButtonDefaults);

    const count = makeThemeVars("count", {
        size: 18,
        fontSize: 10,
        fg: globalVars.mainColors.bg,
        bg: globalVars.mainColors.primary,
    });

    const dropDownContents = makeThemeVars("dropDownContents", {
        minWidth: 350,
        maxHeight: viewHeight(90),
    });

    const endElements = makeThemeVars("endElements", {
        flexBasis: buttonSize * 4,
        mobile: {
            flexBasis: button.mobile.width - 20,
        },
    });

    const compactSearch = makeThemeVars("compactSearch", {
        bg: fullBleed.enabled ? colors.bg.fade(0.2) : globalVars.mainColors.secondary,
        fg: colors.fg,
        mobile: {
            width: button.mobile.width,
        },
    });

    const buttonContents = makeThemeVars("buttonContents", {
        state: {
            bg: button.state.bg,
        },
    });

    const signIn = makeThemeVars("signIn", {
        fg: colors.fg,
        bg: modifyColorBasedOnLightness(globalVars.mainColors.primary, 0.1, true),
        hover: {
            bg: modifyColorBasedOnLightness(globalVars.mainColors.primary, 0.2, true),
        },
    });

    const resister = makeThemeVars("register", {
        fg: colors.bg,
        bg: colors.fg,
        borderColor: colors.bg,
        states: {
            bg: colors.fg.fade(0.9),
        },
    });

    const mobileDropDown = makeThemeVars("mobileDropdown", {
        height: px(sizing.mobile.height),
    });

    const meBox = makeThemeVars("meBox", {
        sizing: {
            buttonContents: formElementVars.sizing.height,
        },
    });

    const bottomRow = makeThemeVars("bottomRow", {
        bg: modifyColorBasedOnLightness(colors.bg, 0.1).desaturate(0.2, true),
    });

    // Note that the logo defined here is the last fallback. If set through the dashboard, it will overwrite these values.
    const logo = makeThemeVars("logo", {
        doubleLogoStrategy: "visible" as "hidden" | "visible" | "fade-in",
        offsetRight: globalVars.gutter.size,
        maxWidth: 200,
        heightOffset: sizing.height / 3,
        tablet: {},
        desktop: {}, // add "url" if you want to set in theme
        mobile: {}, // add "url" if you want to set in theme
    });

    const mobileLogo = makeThemeVars("mobileLogo", {
        justifyContent: LogoAlignment.CENTER,
    });

    const breakpoints = makeThemeVars("breakpoints", {
        compact: 800,
    });

    const mediaQueries = () => {
        const full = (styles: NestedCSSProperties, useMinWidth: boolean = true) => {
            return media(
                {
                    minWidth: px(breakpoints.compact + 1),
                },
                styles,
            );
        };

        const compact = (styles: NestedCSSProperties) => {
            return media(
                {
                    maxWidth: px(breakpoints.compact),
                },
                styles,
            );
        };

        return {
            full,
            compact,
        };
    };

    return {
        fullBleed,
        border,
        sizing,
        colors,
        signIn,
        resister,
        guest,
        button,
        linkButton,
        count,
        dropDownContents,
        endElements,
        compactSearch,
        buttonContents,
        mobileDropDown,
        meBox,
        bottomRow,
        logo,
        mediaQueries,
        breakpoints,
        navAlignment,
        mobileLogo,
    };
});

export const titleBarClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = titleBarVariables();
    const formElementVars = formElementsVariables();
    const mediaQueries = vars.mediaQueries();
    const flex = flexHelper();
    const style = styleFactory("titleBar");

    const getBorderVars = (): NestedCSSProperties => {
        switch (vars.border.type) {
            case BorderType.BORDER:
                return {
                    borderBottom: singleBorder(),
                };
            case BorderType.SHADOW:
                return {
                    boxShadow: shadowHelper().makeShadow(),
                };
            case BorderType.NONE:
            default:
                return {};
        }
    };

    const root = style({
        maxWidth: percent(100),
        color: colorOut(vars.colors.fg),
        position: "relative",
        ...getBorderVars(),
        $nest: {
            "& .searchBar__control": {
                color: vars.colors.fg.toString(),
                cursor: "pointer",
            },
            "&& .suggestedTextInput-clear.searchBar-clear": {
                $nest: {
                    "&:hover": {
                        color: vars.colors.fg.toString(),
                    },
                    "&:active": {
                        color: vars.colors.fg.toString(),
                    },
                    "&:focus": {
                        color: vars.colors.fg.toString(),
                    },
                },
            },
            "& .searchBar__placeholder": {
                color: vars.colors.fg.fade(0.8).toString(),
                cursor: "pointer",
            },
            [`& .${backLinkClasses().link}`]: {
                $nest: {
                    "&, &:hover, &:focus, &:active": {
                        color: colorOut(vars.colors.fg),
                    },
                },
            },
            [`& .${searchBarClasses().valueContainer}`]: {
                backgroundColor: colorOut(vars.compactSearch.bg),
            },
            [`& .${searchBarClasses().valueContainer} .searchBar__input`]: {
                color: colorOut(vars.compactSearch.fg),
            },
        },
        ...mediaQueries.compact({
            height: px(vars.sizing.mobile.height),
        }).$nest,
    });

    const bg1 = style("bg1", {
        willChange: "opacity",
        ...absolutePosition.fullSizeOfParent(),
        backgroundColor: colorOut(vars.colors.bg),
    });

    const bg2 = style("bg2", {
        willChange: "opacity",
        ...absolutePosition.fullSizeOfParent(),
        backgroundColor: colorOut(vars.colors.bg),
    });

    const bgImage = style("bgImage", {
        ...absolutePosition.fullSizeOfParent(),
        objectFit: "cover",
    });

    const negativeSpacer = style(
        "negativeSpacer",
        {
            marginTop: px(-vars.sizing.height),
            paddingTop: px(vars.sizing.height),
        },
        mediaQueries.compact({
            marginTop: px(-vars.sizing.mobile.height),
            paddingTop: px(vars.sizing.mobile.height),
        }),
    );

    const spacer = style(
        "spacer",
        {
            height: px(vars.sizing.height),
        },
        mediaQueries.compact({
            height: px(vars.sizing.mobile.height),
        }),
    );

    const bar = style(
        "bar",
        {
            display: "flex",
            justifyContent: "flex-start",
            flexWrap: "nowrap",
            alignItems: "center",
            height: px(vars.sizing.height),
            width: percent(100),
            $nest: {
                "&.isHome": {
                    justifyContent: "space-between",
                },
            },
        },
        mediaQueries.compact({ height: px(vars.sizing.mobile.height) }),
    );

    const logoContainer = style(
        "logoContainer",
        {
            display: "inline-flex",
            alignSelf: "center",
            color: colorOut(vars.colors.fg),
            marginRight: unit(vars.logo.offsetRight),
            justifyContent: vars.mobileLogo.justifyContent,
            $nest: {
                "&&": {
                    color: colorOut(vars.colors.fg),
                },
                "&.focus-visible": {
                    $nest: {
                        "&.headerLogo-logoFrame": {
                            outline: `5px solid ${vars.buttonContents.state.bg}`,
                            background: colorOut(vars.buttonContents.state.bg),
                            borderRadius: vars.button.borderRadius,
                        },
                    },
                },
            },
        },
        mediaQueries.compact({
            height: px(vars.sizing.mobile.height),
            marginRight: unit(0),
        }),
    );

    const logoFlexBasis = style("logoFlexBasis", {
        flexBasis: vars.endElements.flexBasis,
    });

    const meBox = style("meBox", {
        justifyContent: "flex-end",
    });

    const nav = style(
        "nav",
        {
            display: "flex",
            flexWrap: "wrap",
            height: px(vars.sizing.height),
            color: "inherit",
            flexGrow: 1,
            justifyContent: vars.navAlignment.alignment === "left" ? "flex-start" : "center",
            $nest: {
                "&.titleBar-guestNav": {
                    flex: "initial",
                },
            },
        },
        mediaQueries.compact({ height: px(vars.sizing.mobile.height) }),
    );

    const locales = style(
        "locales",
        {
            height: px(vars.sizing.height),
            $nest: {
                "&.buttonAsText": {
                    $nest: {
                        "&:hover": {
                            color: "inherit",
                        },
                        "&:focus": {
                            color: "inherit",
                        },
                    },
                },
            },
        },
        mediaQueries.compact({ height: px(vars.sizing.mobile.height) }),
    );

    const messages = style("messages", {
        color: vars.colors.fg.toString(),
    });

    const notifications = style("notifications", {
        color: "inherit",
    });

    const compactSearch = style(
        "compactSearch",
        {
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            marginLeft: "auto",
            minWidth: unit(formElementVars.sizing.height),
            flexBasis: px(formElementVars.sizing.height),
            maxWidth: percent(100),
            height: unit(vars.sizing.height),
            $nest: {
                "&.isOpen": {
                    flex: 1,
                },
            },
        },
        mediaQueries.compact({ height: px(vars.sizing.mobile.height) }),
    );

    const compactSearchResults = style("compactSearchResults", {
        position: "absolute",
        top: unit(formElementVars.sizing.height),
        width: percent(100),
        $nest: {
            "&:empty": {
                display: "none",
            },
        },
    });

    const extraMeBoxIcons = style("extraMeBoxIcons", {
        display: "flex",
        alignItems: "center",
        justifyContent: "flex-end",
        marginLeft: "auto",
        $nest: {
            [`& + .${compactSearch}`]: {
                marginLeft: 0,
            },
            li: {
                listStyle: "none",
            },
        },
    });

    const topElement = style(
        "topElement",
        {
            color: vars.colors.fg.toString(),
            padding: `0 ${px(vars.sizing.spacer / 2)}`,
            margin: `0 ${px(vars.sizing.spacer / 2)}`,
            borderRadius: px(vars.button.borderRadius),
        },
        mediaQueries.compact({
            fontSize: px(vars.button.mobile.fontSize),
        }),
    );

    const localeToggle = style(
        "localeToggle",
        {
            height: px(vars.sizing.height),
        },
        mediaQueries.compact({
            height: px(vars.sizing.mobile.height),
        }),
    );

    const languages = style("languages", {
        marginLeft: "auto",
    });

    const button = style(
        "button",
        {
            ...buttonResetMixin(),
            height: px(vars.button.size),
            minWidth: px(vars.button.size),
            maxWidth: percent(100),
            padding: px(0),
            color: colorOut(vars.colors.fg),
            $nest: {
                "&&": {
                    ...allButtonStates(
                        {
                            active: {
                                color: colorOut(vars.colors.fg),
                                $nest: {
                                    "& .meBox-buttonContent": {
                                        backgroundColor: colorOut(vars.buttonContents.state.bg),
                                    },
                                },
                            },
                            hover: {
                                color: colorOut(vars.colors.fg),
                                $nest: {
                                    "& .meBox-buttonContent": {
                                        backgroundColor: colorOut(vars.buttonContents.state.bg),
                                    },
                                },
                            },
                            accessibleFocus: {
                                outline: 0,
                                color: colorOut(vars.colors.fg),
                                $nest: {
                                    "& .meBox-buttonContent": {
                                        borderColor: colorOut(vars.colors.fg),
                                        backgroundColor: colorOut(vars.buttonContents.state.bg),
                                    },
                                },
                            },
                        },
                        {
                            "& .meBox-buttonContent": {
                                ...borders({
                                    radius: 0,
                                    width: 1,
                                    color: rgba(0, 0, 0, 0),
                                }),
                            },
                            "&.isOpen": {
                                color: colorOut(vars.colors.fg),
                                $nest: {
                                    "& .meBox-buttonContent": {
                                        backgroundColor: colorOut(vars.buttonContents.state.bg),
                                    },
                                    "&:focus": {
                                        color: colorOut(vars.colors.fg),
                                    },
                                    "&.focus-visible": {
                                        color: colorOut(vars.colors.fg),
                                    },
                                },
                            },
                        },
                    ),
                },
            },
        },
        mediaQueries.compact({
            height: px(vars.sizing.mobile.height),
            width: px(vars.sizing.mobile.width),
            minWidth: px(vars.sizing.mobile.width),
        }),
    );

    const linkButton = generateButtonClass(vars.linkButton);

    const buttonOffset = style("buttonOffset", {
        transform: `translateX(6px)`,
    });

    const centeredButtonClass = style("centeredButtonClass", {
        ...flex.middle(),
    });

    const searchCancel = style("searchCancel", {
        ...buttonResetMixin(),
        ...userSelect(),
        height: px(formElementVars.sizing.height),
        $nest: {
            "&.focus-visible": {
                $nest: {
                    "&.meBox-buttonContent": {
                        borderRadius: px(vars.button.borderRadius),
                        backgroundColor: vars.buttonContents.state.bg.toString(),
                    },
                },
            },
        },
    });

    const tabButtonActive = {
        color: globalVars.mainColors.primary.toString(),
        $nest: {
            ".titleBar-tabButtonContent": {
                color: vars.colors.fg.toString(),
                backgroundColor: colorOut(modifyColorBasedOnLightness(vars.colors.fg, 1)),
                borderRadius: px(vars.button.borderRadius),
            },
        },
    };

    const tabButton = style("tabButton", {
        display: "block",
        height: percent(100),
        padding: px(0),
        $nest: {
            "&:active": tabButtonActive,
            "&:hover": tabButtonActive,
            "&:focus": tabButtonActive,
        },
    });

    const dropDownContents = style("dropDownContents", {
        $nest: {
            "&&&": {
                minWidth: unit(vars.dropDownContents.minWidth),
                maxHeight: unit(vars.dropDownContents.maxHeight),
            },
        },
    });

    const count = style("count", {
        height: px(vars.count.size),
        fontSize: px(vars.count.fontSize),
        backgroundColor: vars.count.bg.toString(),
        color: vars.count.fg.toString(),
    });

    const rightFlexBasis = style(
        "rightFlexBasis",
        {
            display: "flex",
            height: px(vars.sizing.height),
            flexWrap: "nowrap",
            justifyContent: "flex-end",
            alignItems: "center",
            flexBasis: vars.endElements.flexBasis,
        },
        mediaQueries.compact({
            flexShrink: 1,
            flexBasis: px(vars.endElements.mobile.flexBasis),
            height: px(vars.sizing.mobile.height),
        }),
    );

    const leftFlexBasis = style("leftFlexBasis", {
        ...flex.middleLeft(),
        flexShrink: 1,
        flexBasis: px(vars.endElements.mobile.flexBasis),
    });

    const signIn = style("signIn", {
        marginLeft: unit(vars.guest.spacer),
        marginRight: unit(vars.guest.spacer),
        $nest: {
            "&&&": {
                color: colorOut(vars.signIn.fg),
                borderColor: colorOut(vars.colors.fg),
            },
        },
    });

    const register = style("register", {
        marginLeft: unit(vars.guest.spacer),
        marginRight: unit(vars.guest.spacer),
        backgroundColor: colorOut(vars.resister.bg),
        $nest: {
            "&&": {
                // Ugly solution, but not much choice until: https://github.com/vanilla/knowledge/issues/778
                ...allButtonStates({
                    allStates: {
                        borderColor: colorOut(vars.resister.borderColor, true),
                        color: colorOut(vars.resister.fg),
                    },
                    noState: {
                        backgroundColor: colorOut(vars.resister.bg, true),
                    },
                    hover: {
                        color: colorOut(vars.resister.fg),
                        backgroundColor: colorOut(vars.resister.states.bg, true),
                    },
                    focus: {
                        color: colorOut(vars.resister.fg),
                        backgroundColor: colorOut(vars.resister.states.bg, true),
                    },
                    active: {
                        color: colorOut(vars.resister.fg),
                        backgroundColor: colorOut(vars.resister.states.bg, true),
                    },
                }),
            },
        },
    });

    const clearButtonClass = style("clearButtonClass", {
        opacity: 0.7,
        $nest: {
            "&&": {
                color: colorOut(vars.colors.fg),
            },
            "&:hover, &:focus": {
                opacity: 1,
            },
        },
    });

    const guestButton = style("guestButton", {
        minWidth: unit(vars.button.guest.minWidth),
        borderRadius: unit(vars.button.borderRadius),
    });

    const desktopNavWrap = style("desktopNavWrap", {
        position: "relative",
        flexGrow: 1,
        $nest: addGradientsToHintOverflow(globalVars.gutter.half * 4, vars.colors.bg) as any,
    });

    const logoCenterer = style("logoCenterer", {
        ...absolutePosition.middleOfParent(true),
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
    });

    const hamburger = style("hamburger", {
        marginRight: unit(12),
        $nest: {
            "&&": {
                ...allButtonStates({
                    allStates: {
                        color: colorOut(vars.colors.fg),
                    },
                }),
            },
        },
    });

    const isSticky = style("isSticky", {
        ...sticky(),
        top: 0,
        zIndex: 10,
    });

    const logoAnimationWrap = style("logoAnimationWrap", {
        display: "inline-flex",
        alignItems: "center",
    });

    return {
        root,
        bg1,
        bg2,
        bgImage,
        negativeSpacer,
        spacer,
        bar,
        logoContainer,
        meBox,
        nav,
        locales,
        messages,
        notifications,
        compactSearch,
        topElement,
        localeToggle,
        languages,
        button,
        buttonOffset,
        linkButton,
        searchCancel,
        tabButton,
        dropDownContents,
        count,
        extraMeBoxIcons,
        rightFlexBasis,
        leftFlexBasis,
        signIn,
        register,
        centeredButtonClass,
        compactSearchResults,
        clearButtonClass,
        guestButton,
        logoFlexBasis,
        desktopNavWrap,
        logoCenterer,
        hamburger,
        isSticky,
        logoAnimationWrap,
    };
});

export const titleBarLogoClasses = useThemeCache(() => {
    const vars = titleBarVariables();
    const style = styleFactory("titleBarLogo");
    const logoHeight = px(vars.sizing.height - vars.logo.heightOffset);

    const logoFrame = style("logoFrame", { display: "inline-flex", alignSelf: "center" });

    const logo = style("logo", {
        display: "block",
        maxHeight: logoHeight,
        maxWidth: unit(vars.logo.maxWidth),
        width: "auto",
        $nest: {
            "&.isCentred": {
                margin: "auto",
            },
            [`.${iconClasses().vanillaLogo}`]: {
                height: logoHeight,
                width: "auto",
            },
        },
    });

    const mobileLogo = style("mobileLogo", {
        justifyContent: vars.mobileLogo.justifyContent,
    });

    const isCenter = style("isCenter", {
        position: "absolute",
        left: percent(50),
        transform: translate(`-50%`, `-50%`),
    });

    return {
        logoFrame,
        logo,
        mobileLogo,
        isCenter,
    };
});

export const addGradientsToHintOverflow = (width: number | string, color: ColorHelper) => {
    const gradient = (direction: "right" | "left") => {
        return `linear-gradient(to ${direction}, ${colorOut(color.fade(0))} 0%, ${colorOut(
            color.fade(0.3),
        )} 20%, ${colorOut(color)} 90%)`;
    };
    return {
        "&:after": {
            ...absolutePosition.topRight(),
            background: gradient("right"),
        },
        "&:before": {
            ...absolutePosition.topLeft(),
            background: gradient("left"),
        },
        "&:before, &:after": {
            ...pointerEvents(),
            content: quote(``),
            height: percent(100),
            width: unit(width),
            zIndex: 1,
        },
    };
};
