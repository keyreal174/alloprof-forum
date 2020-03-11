/**
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { styleFactory, useThemeCache, variableFactory } from "@library/styles/styleUtils";
import { formElementsVariables } from "@library/forms/formElementStyles";
import { BackgroundColorProperty, FontWeightProperty, PaddingProperty, TextShadowProperty } from "csstype";
import { calc, important, percent, px, quote, rgba, translateX, translateY } from "csx";
import {
    absolutePosition,
    backgroundHelper,
    borders,
    centeredBackgroundProps,
    colorOut,
    EMPTY_BACKGROUND,
    EMPTY_BORDER,
    EMPTY_FONTS,
    EMPTY_SPACING,
    fonts,
    IFont,
    modifyColorBasedOnLightness,
    textInputSizingFromFixedHeight,
    unit,
} from "@library/styles/styleHelpers";
import { NestedCSSProperties, TLength } from "typestyle/lib/types";
import { widgetVariables } from "@library/styles/widgetStyleVars";
import { generateButtonStyleProperties } from "@library/forms/styleHelperButtonGenerator";
import { layoutVariables } from "@library/layout/panelLayoutStyles";
import { compactSearchVariables } from "@library/headers/mebox/pieces/compactSearchStyles";
import { margins, paddings } from "@library/styles/styleHelpersSpacing";
import { IButtonType } from "@library/forms/styleHelperButtonInterface";
import { media } from "typestyle";
import { containerVariables } from "@library/layout/components/containerStyles";
import { ButtonPresets } from "@library/forms/buttonStyles";
import { searchBarClasses, searchBarVariables } from "@library/features/search/searchBarStyles";
import { inputMixin } from "@library/forms/inputStyles";

export enum BannerAlignment {
    LEFT = "left",
    CENTER = "center",
}

export enum SearchBarPresets {
    NO_BORDER = "no border",
    BORDER = "border",
    UNIFIED_BORDER = "unified border", // wraps button, and will set button to "solid"
}

export const presetsBanner = useThemeCache(() => {
    const makeThemeVars = variableFactory(["presetsBanner"]);
    const button = makeThemeVars("button", { preset: ButtonPresets.TRANSPARENT });
    const input = makeThemeVars("input", { preset: SearchBarPresets.NO_BORDER });

    return {
        button,
        input,
    };
});

export const bannerVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory(["banner", "splash"]);
    const globalVars = globalVariables();
    const widgetVars = widgetVariables();
    const formElVars = formElementsVariables();
    const presets = presetsBanner();

    const options = makeThemeVars("options", {
        alignment: BannerAlignment.CENTER,
        hideDesciption: false,
        hideSearch: false,
    });
    const compactSearchVars = compactSearchVariables();

    const topPadding = 69;
    const horizontalPadding = unit(
        widgetVars.spacing.inner.horizontalPadding + globalVars.gutter.quarter,
    ) as PaddingProperty<TLength>;
    const spacing = makeThemeVars("spacing", {
        padding: {
            ...EMPTY_SPACING,
            top: topPadding as PaddingProperty<TLength>,
            bottom: topPadding as PaddingProperty<TLength>,
            horizontal: horizontalPadding,
        },
        paddingMobile: {
            ...EMPTY_SPACING,
            top: 0,
            bottom: globalVars.gutter.size,
            horizontal: horizontalPadding,
        },
    });

    const inputAndButton = makeThemeVars("inputAndButton", {
        borderRadius: compactSearchVars.inputAndButton.borderRadius,
    });

    // Main colors
    const colors = makeThemeVars("colors", {
        primary: globalVars.mainColors.primary,
        primaryContrast: globalVars.mainColors.primaryContrast,
        secondary: globalVars.mainColors.secondary,
        secondaryContrast: globalVars.mainColors.secondaryContrast,
        bg: globalVars.mainColors.bg,
        fg: globalVars.mainColors.fg,
        borderColor: globalVars.mixPrimaryAndFg(0.4),
    });

    const state = makeThemeVars("state", {
        colors: {
            fg: colors.secondaryContrast,
            bg: colors.secondary,
        },
        borders: {
            color: colors.bg,
        },
        fonts: {
            color: colors.secondaryContrast,
        },
    });

    const border = {
        width: globalVars.border.width,
        radius: globalVars.borderType.formElements.default.radius,
    };

    const backgrounds = makeThemeVars("backgrounds", {
        ...compactSearchVars.backgrounds,
    });

    const contentContainer = makeThemeVars("contentContainer", {
        minWidth: 550,
        padding: {
            ...spacing.padding,
            left: 0,
            right: 0,
        },
    });

    const imageElement = makeThemeVars("imageElement", {
        image: undefined as string | undefined,
        minWidth: 500,
        disappearingWidth: 500,
        padding: {
            ...EMPTY_SPACING,
            all: globalVars.gutter.size,
            right: 0,
        },
    });

    const outerBackground = makeThemeVars("outerBackground", {
        ...EMPTY_BACKGROUND,
        color: colors.primary.lighten("12%"),
        backgroundPosition: "50% 50%",
        backgroundSize: "cover",
    });

    const innerBackground = makeThemeVars("innerBackground", {
        bg: undefined,
        padding: {
            top: spacing.padding,
            right: spacing.padding,
            bottom: spacing.padding,
            left: spacing.padding,
        },
    });

    const text = makeThemeVars("text", {
        shadowMix: 1, // We want to get the most extreme lightness contrast with text color (i.e. black or white)
        innerShadowOpacity: 0.25,
        outerShadowOpacity: 0.75,
    });

    const textMixin = {
        ...EMPTY_FONTS,
        color: colors.primaryContrast,
        align: options.alignment,
        shadow: `0 1px 1px ${colorOut(
            modifyColorBasedOnLightness(colors.primaryContrast, text.shadowMix).fade(text.innerShadowOpacity),
        )}, 0 1px 25px ${colorOut(
            modifyColorBasedOnLightness(colors.primaryContrast, text.shadowMix).fade(text.outerShadowOpacity),
        )}` as TextShadowProperty,
    };

    const title = makeThemeVars("title", {
        maxWidth: 700,
        font: {
            ...textMixin,
            size: globalVars.fonts.size.largeTitle,
            weight: globalVars.fonts.weights.semiBold as FontWeightProperty,
        },
        fontMobile: {
            ...textMixin,
            size: globalVars.fonts.size.title,
        },
        margins: {
            ...EMPTY_SPACING,
            top: 14,
            bottom: 12,
        },
        text: "How can we help you?",
    });

    const description = makeThemeVars("description", {
        text: undefined as string | undefined,
        font: {
            ...textMixin,
            size: globalVars.fonts.size.large,
        },
        maxWidth: 400,
        margins: {
            ...EMPTY_SPACING,
            bottom: 12,
        },
    });

    const paragraph = makeThemeVars("paragraph", {
        margin: ".4em",
        text: {
            size: 24,
            weight: 300,
        },
    });

    if (presets.input.preset === SearchBarPresets.UNIFIED_BORDER) {
        presets.button.preset = ButtonPresets.SOLID; // Unified border currently only supports solid buttons.
    }

    const isSolidButton = presets.button.preset === ButtonPresets.SOLID;
    const isTransparentButton = presets.button.preset === ButtonPresets.TRANSPARENT;

    const inputHasNoBorder =
        presets.input.preset === SearchBarPresets.UNIFIED_BORDER || presets.input.preset === SearchBarPresets.NO_BORDER;

    const searchBar = makeThemeVars("searchBar", {
        preset: presets.button.preset,
        colors: {
            fg: colors.fg,
            bg: colors.bg,
        },
        sizing: {
            maxWidth: 705,
            height: 40,
        },
        font: {
            color: colors.fg,
            size: globalVars.fonts.size.large,
        },
        margin: {
            ...EMPTY_SPACING,
            top: 24,
        },
        marginMobile: {
            ...EMPTY_SPACING,
            top: 16,
        },
        shadow: {
            show: false,
            style: `0 1px 1px ${colorOut(
                modifyColorBasedOnLightness(colors.fg, text.shadowMix, true).fade(text.innerShadowOpacity),
            )}, 0 1px 25px ${colorOut(
                modifyColorBasedOnLightness(colors.fg, text.shadowMix, true).fade(text.outerShadowOpacity),
            )}` as TextShadowProperty,
        },
        border: {
            color: inputHasNoBorder ? colors.bg : colors.primary,
            leftColor: isTransparentButton ? colors.primaryContrast : colors.borderColor,
            radius: {
                left: border.radius,
                right: 0,
            },
            width: globalVars.border.width,
        },
        state: {
            border: {
                color: isSolidButton ? colors.fg : colors.primaryContrast,
            },
        },
    });

    let buttonBorderStyles = {
        color: colors.borderColor,
        width: 0,
        left: {
            ...EMPTY_BORDER,
            color: searchBar.border.color,
            width: searchBar.border.width,
        },
        right: {
            radius: border.radius,
        },
    };

    const bgColorActive = isTransparentButton ? backgrounds.overlayColor.fade(0.15) : colors.secondary;
    const activeBorderColor = isTransparentButton ? colors.primaryContrast : colors.bg;

    let buttonStateStyles = {
        colors: {
            fg: colors.secondaryContrast,
            bg: bgColorActive,
        },
        borders: {
            color: activeBorderColor,
        },
        fonts: {
            color: colors.primaryContrast,
        },
    };

    if (isTransparentButton) {
        buttonBorderStyles.color = colors.primaryContrast;
        buttonBorderStyles.width = globalVars.border.width;
    }

    const searchButtonBg = isTransparentButton ? rgba(0, 0, 0, 0) : colors.primary;

    const searchButton = makeThemeVars("searchButton", {
        name: "searchButton",
        preset: presets.button.preset,
        spinnerColor: colors.primaryContrast,
        sizing: {
            minHeight: searchBar.sizing.height,
        },
        colors: {
            bg: searchButtonBg,
            fg: colors.primaryContrast,
        },
        borders: buttonBorderStyles,
        fonts: {
            color: colors.primaryContrast,
            size: globalVars.fonts.size.large,
            weight: globalVars.fonts.weights.bold,
        },
        state: buttonStateStyles,
    } as IButtonType);

    if (isSolidButton) {
        buttonBorderStyles.color = searchButtonBg;
        if (searchButton.state && searchButton.state.borders) {
            searchButton.state.borders.color = isTransparentButton ? colors.primaryContrast : colors.primary;
        }
    }

    const buttonShadow = makeThemeVars("shadow", {
        color: modifyColorBasedOnLightness(colors.primaryContrast, text.shadowMix).fade(0.05),
        full: `0 1px 15px ${colorOut(modifyColorBasedOnLightness(colors.primaryContrast, text.shadowMix).fade(0.3))}`,
        background: modifyColorBasedOnLightness(colors.primaryContrast, text.shadowMix).fade(
            0.1,
        ) as BackgroundColorProperty,
    });

    const unifiedBannerOptions = makeThemeVars("unifiedBannerOptions", {
        border: {
            width: 2,
            color: colors.secondary,
        },
    });

    return {
        options,
        outerBackground,
        backgrounds,
        spacing,
        innerBackground,
        contentContainer,
        text,
        title,
        description,
        paragraph,
        state,
        searchBar,
        buttonShadow,
        searchButton,
        colors,
        inputAndButton,
        imageElement,
        border,
        isTransparentButton,
        unifiedBannerOptions,
    };
});

export const bannerClasses = useThemeCache(() => {
    const vars = bannerVariables();
    const style = styleFactory("banner");
    const formElementVars = formElementsVariables();
    const globalVars = globalVariables();
    const mediaQueries = layoutVariables().mediaQueries();
    const presets = presetsBanner();

    const isCentered = vars.options.alignment === "center";
    const searchButton = style("searchButton", {
        $nest: {
            "&.searchBar-submitButton": {
                ...generateButtonStyleProperties(vars.searchButton),
                left: -1,
            },
        },
    });

    const valueContainer = style("valueContainer", {
        $nest: {
            "&&.inputText": {
                ...textInputSizingFromFixedHeight(
                    vars.searchBar.sizing.height,
                    vars.searchBar.font.size,
                    vars.searchBar.border.width * 2,
                ),
                boxSizing: "border-box",
                paddingLeft: unit(searchBarVariables().searchIcon.gap),
                backgroundColor: colorOut(vars.searchBar.colors.bg),
                ...borders({
                    color: vars.searchBar.border.color,
                }),
                $nest: {
                    "&:active, &:hover, &:focus, &.focus-visible": {
                        ...borders(vars.searchBar.border),
                    },
                },
                borderColor: colorOut(vars.searchBar.border.color),
            },
            ".searchBar__control": {
                cursor: "text",
                position: "relative",
            },
            "& .searchBar__placeholder": {
                color: colorOut(vars.searchBar.font.color),
            },
        },
    } as NestedCSSProperties);

    const outerBackground = (url?: string) => {
        const finalUrl = url ?? vars.outerBackground.image ?? undefined;
        const finalVars = {
            ...vars.outerBackground,
            image: finalUrl,
        };

        return style("outerBackground", {
            position: "absolute",
            top: 0,
            left: 0,
            width: percent(100),
            height: calc(`100% + 2px`),
            transform: translateY(`-1px`), // Depending on how the browser rounds the pixels, there is sometimes a 1px gap above the banner
            ...centeredBackgroundProps(),
            display: "block",
            ...backgroundHelper(finalVars),
        });
    };

    const defaultBannerSVG = style("defaultBannerSVG", {
        ...absolutePosition.fullSizeOfParent(),
    });

    const backgroundOverlay = style("backgroundOverlay", {
        display: "block",
        position: "absolute",
        top: px(0),
        left: px(0),
        width: percent(100),
        height: percent(100),
        background: colorOut(vars.backgrounds.overlayColor),
    });

    const contentContainer = style(
        "contentContainer",
        {
            ...paddings(vars.contentContainer.padding),
            backgroundColor: vars.innerBackground.bg,
            minWidth: vars.contentContainer.minWidth,
        },
        media(
            {
                maxWidth: calc(
                    `${unit(vars.contentContainer.minWidth)} + ${unit(vars.contentContainer.padding.horizontal)} * 4`,
                ),
            },
            {
                width: percent(100),
                minWidth: "initial",
            },
        ),
        mediaQueries.oneColumnDown({
            ...paddings(vars.spacing.paddingMobile),
        }),
    );

    const text = style("text", {
        color: colorOut(vars.colors.primaryContrast),
    });

    const searchContainer = style(
        "searchContainer",
        {
            position: "relative",
            width: percent(100),
            maxWidth: unit(vars.searchBar.sizing.maxWidth),
            margin: isCentered ? "auto" : undefined,
            ...margins(vars.searchBar.margin),
            $nest: {
                "& .search-results": {
                    width: percent(100),
                    maxWidth: unit(vars.searchBar.sizing.maxWidth),
                    margin: "auto",
                    zIndex: 2,
                },
            },
        },
        mediaQueries.oneColumnDown({
            ...margins(vars.searchBar.marginMobile),
        }),
    );

    const icon = style("icon", {});
    const input = style("input", {});

    const buttonLoader = style("buttonLoader", {});

    const title = style(
        "title",
        {
            display: "block",
            ...fonts(vars.title.font),
            flexGrow: 1,
        },
        mediaQueries.oneColumnDown({
            ...fonts(vars.title.fontMobile),
        }),
    );

    const textWrapMixin: NestedCSSProperties = {
        display: "flex",
        flexWrap: "nowrap",
        alignItems: "center",
        maxWidth: unit(vars.searchBar.sizing.maxWidth),
        width: percent(100),
        marginLeft: isCentered ? "auto" : undefined,
        marginRight: isCentered ? "auto" : undefined,
        ...mediaQueries.oneColumnDown({
            maxWidth: percent(100),
        }),
    };

    const titleAction = style("titleAction", {});
    const titleWrap = style("titleWrap", { ...margins(vars.title.margins), ...textWrapMixin });

    const titleFlexSpacer = style("titleFlexSpacer", {
        display: isCentered ? "block" : "none",
        position: "relative",
        height: unit(formElementVars.sizing.height),
        width: unit(formElementVars.sizing.height),
        flexBasis: unit(formElementVars.sizing.height),
        transform: translateX(px(formElementVars.sizing.height - globalVars.icon.sizes.default / 2 - 13)),
        $nest: {
            ".searchBar-actionButton:after": {
                content: quote(""),
                ...absolutePosition.middleOfParent(),
                width: px(20),
                height: px(20),
                backgroundColor: colorOut(vars.buttonShadow.background),
                boxShadow: vars.buttonShadow.full,
            },
            ".searchBar-actionButton": {
                color: important("inherit"),
                $nest: {
                    "&:not(.focus-visible)": {
                        outline: 0,
                    },
                },
            },
            ".icon-compose": {
                zIndex: 1,
            },
        },
    });

    const descriptionWrap = style("descriptionWrap", { ...margins(vars.description.margins), ...textWrapMixin });

    const description = style("description", {
        display: "block",
        ...fonts(vars.description.font as IFont),
        flexGrow: 1,
    });

    let rightRadius = vars.border.radius as number | string;
    let leftRadius = vars.border.radius as number | string;

    if (
        vars.searchButton &&
        vars.searchButton.borders &&
        vars.searchButton.borders.right &&
        vars.searchButton.borders.right.radius
    ) {
        const radius = vars.searchButton.borders.right.radius as string | number;
        rightRadius = unit(radius) as any;
    }

    if (presets.button.preset === ButtonPresets.HIDE) {
        leftRadius = rightRadius;
    } else {
        if (vars.searchBar.border.radius && vars.searchBar.border.radius.left) {
            leftRadius = unit(vars.searchBar.border.radius.left) as any;
        }
    }

    const content = style("content", {
        boxSizing: "border-box",
        zIndex: 1,
        boxShadow: vars.searchBar.shadow.show ? vars.searchBar.shadow.style : undefined,
        borderTopLeftRadius: unit(leftRadius),
        borderBottomLeftRadius: unit(leftRadius),
        borderTopRightRadius: unit(rightRadius),
        borderBottomRightRadius: unit(rightRadius),
        height: unit(vars.searchBar.sizing.height),
        $nest: {
            "&.hasFocus .searchBar-valueContainer": {
                boxShadow: `0 0 0 1px ${colorOut(vars.colors.primary)} inset`,
            },
            "& .searchBar-valueContainer icon-clear": {
                color: colorOut(vars.searchBar.font.color),
            },
            [`& .${searchBarClasses().icon}, & .searchBar__input`]: {
                color: colorOut(vars.searchBar.font.color),
            },
        },
    });

    const imagePositioner = style("imagePositioner", {
        display: "flex",
        flexDirection: "row",
        flexWrap: "nowrap",
        alignItems: "center",
    });

    const makeImageMinWidth = (rootUnit, padding) =>
        calc(
            `${unit(rootUnit)} - ${unit(vars.contentContainer.minWidth)} - ${unit(
                vars.contentContainer.padding.left ?? vars.contentContainer.padding.horizontal,
            )} - ${unit(padding)}`,
        );

    const imageElementContainer = style(
        "imageElementContainer",
        {
            alignSelf: "stretch",
            minWidth: makeImageMinWidth(globalVars.content.width, containerVariables().spacing.padding.horizontal),
            flexGrow: 1,
            position: "relative",
            overflow: "hidden",
        },
        media(
            { maxWidth: globalVars.content.width },
            {
                minWidth: makeImageMinWidth("100vw", containerVariables().spacing.padding.horizontal),
            },
        ),
        layoutVariables()
            .mediaQueries()
            .oneColumnDown({
                minWidth: makeImageMinWidth("100vw", containerVariables().spacing.paddingMobile.horizontal),
            }),
        media(
            { maxWidth: 500 },
            {
                display: "none",
            },
        ),
    );

    const imageElement = style(
        "imageElement",
        {
            ...absolutePosition.middleRightOfParent(),
            minWidth: unit(vars.imageElement.minWidth),
            ...paddings(vars.imageElement.padding),
            objectPosition: "100% 50%",
            objectFit: "contain",
            marginLeft: "auto",
            right: 0,
        },
        media(
            {
                maxWidth: calc(
                    `${unit(vars.imageElement.minWidth)} + ${unit(vars.contentContainer.minWidth)} + ${unit(
                        vars.imageElement.padding.horizontal ?? vars.imageElement.padding.all,
                    )} * 2`,
                ),
            },
            { right: "initial", objectPosition: "0% 50%" },
        ),
    );

    const rootConditionalStyles =
        presets.input.preset === SearchBarPresets.UNIFIED_BORDER
            ? {
                  borderTopLeftRadius: unit(leftRadius),
                  borderBottomLeftRadius: unit(leftRadius),
                  borderTopRightRadius: unit(rightRadius),
                  borderBottomRightRadius: unit(rightRadius),
                  backgroundColor: colorOut(vars.unifiedBannerOptions.border.color),
                  boxShadow: `0 0 0 ${unit(vars.unifiedBannerOptions.border.width)} ${
                      vars.unifiedBannerOptions.border.color
                  }`,
              }
            : {};

    const root = style({
        position: "relative",
        backgroundColor: colorOut(vars.outerBackground.color),
        $nest: {
            [`& .${searchBarClasses().independentRoot}`]: rootConditionalStyles,
            "& .searchBar": {
                height: unit(vars.searchBar.sizing.height),
            },
        },
    });

    const iconContainer = style("iconContainer", {
        $nest: {
            "&&": {
                height: unit(vars.searchBar.sizing.height),
                outline: 0,
                border: 0,
                background: "transparent",
            },
        },
    });

    const resultsAsModal = style("resultsAsModalClasses", {
        $nest: {
            "&&": {
                top: unit(vars.searchBar.sizing.height),
            },
        },
    });

    return {
        root,
        outerBackground,
        contentContainer,
        text,
        icon,
        defaultBannerSVG,
        searchContainer,
        searchButton,
        input,
        buttonLoader,
        title,
        titleAction,
        titleFlexSpacer,
        titleWrap,
        description,
        descriptionWrap,
        content,
        valueContainer,
        iconContainer,
        resultsAsModal,
        backgroundOverlay,
        imageElementContainer,
        imageElement,
        imagePositioner,
    };
});
