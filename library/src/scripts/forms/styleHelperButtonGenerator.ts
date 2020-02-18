/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { buttonGlobalVariables, ButtonPresets, buttonResetMixin, buttonSizing } from "@library/forms/buttonStyles";
import { formElementsVariables } from "@library/forms/formElementStyles";
import { IButtonType } from "@library/forms/styleHelperButtonInterface";
import { borders } from "@library/styles/styleHelpersBorders";
import { colorOut } from "@library/styles/styleHelpersColors";
import { fonts } from "@library/styles/styleHelpersTypography";
import { styleFactory } from "@library/styles/styleUtils";
import { ColorHelper, percent } from "csx";
import merge from "lodash/merge";
import { NestedCSSProperties } from "typestyle/lib/types";
import { globalVariables } from "@library/styles/globalStyleVars";
import cloneDeep from "lodash/cloneDeep";

export const generateButtonStyleProperties = (buttonTypeVars: IButtonType, setZIndexOnState = false) => {
    const globalVars = globalVariables();
    const formElVars = formElementsVariables();
    const buttonGlobals = buttonGlobalVariables();
    const zIndex = setZIndexOnState ? 1 : undefined;
    const buttonDimensions = buttonTypeVars.sizing || false;

    const state = buttonTypeVars.state ?? {};
    const colors = buttonTypeVars.colors ?? {
        bg: globalVars.mainColors.bg,
        fg: globalVars.mainColors.fg,
    };

    // Make sure we have the second level, if it was empty
    buttonTypeVars = merge(
        {
            preset: ButtonPresets.ADVANCED,
            colors,
            state,
            hover: state,
            focus: state,
            active: state,
            borders: state,
            focusAccessible: state,
        },
        buttonTypeVars,
    );

    // Check for preset, defaults to "advanced" where everything is explicit.
    if (buttonTypeVars.preset === ButtonPresets.OUTLINE) {
        buttonTypeVars = merge(
            {
                borders: {
                    ...globalVars.borderType.formElements.buttons,
                    color: (colors.fg as ColorHelper).mix(colors.bg as ColorHelper, 0.24),
                },
            },
            buttonTypeVars,
        );

        window.console.log("ButtonPresets.OUTLINE: ", buttonTypeVars);
    } else if (buttonTypeVars.preset === ButtonPresets.SOLID) {
        buttonTypeVars = merge(
            {
                borders: {
                    ...globalVars.borderType.formElements.buttons,
                    color: colors.bg,
                },
            },
            buttonTypeVars,
        );

        window.console.log("ButtonPresets.SOLID: ", buttonTypeVars);
    }

    // Remove debug and fallback
    const defaultBorder = borders(buttonTypeVars.borders, globalVariables().border);

    const hoverBorder =
        buttonTypeVars.hover && buttonTypeVars.hover.borders
            ? merge(cloneDeep(defaultBorder), borders(buttonTypeVars.hover.borders))
            : {};

    const activeBorder =
        buttonTypeVars.active && buttonTypeVars.active.borders
            ? merge(cloneDeep(defaultBorder), borders(buttonTypeVars.active.borders))
            : {};

    const focusBorder =
        buttonTypeVars.focus && buttonTypeVars.focus.borders
            ? merge(cloneDeep(defaultBorder), borders(buttonTypeVars.focus && buttonTypeVars.focus.borders))
            : defaultBorder;

    const focusAccessibleBorder =
        buttonTypeVars.focusAccessible && buttonTypeVars.focusAccessible.borders
            ? merge(cloneDeep(defaultBorder), borders(buttonTypeVars.focusAccessible.borders))
            : {};

    const result: NestedCSSProperties = {
        ...buttonResetMixin(),
        textOverflow: "ellipsis",
        overflow: "hidden",
        width: "auto",
        maxWidth: percent(100),
        backgroundColor: colorOut(
            buttonTypeVars.colors && buttonTypeVars.colors.bg ? buttonTypeVars.colors.bg : buttonGlobals.colors.bg,
        ),
        ...fonts({
            size: buttonGlobals.font.size,
            ...buttonTypeVars.fonts,
            color:
                buttonTypeVars.colors && buttonTypeVars.colors.fg ? buttonTypeVars.colors.fg : buttonGlobals.colors.fg,
            weight: buttonGlobals.font.weight,
        }),
        ...defaultBorder,
        ...buttonSizing(
            buttonDimensions && buttonDimensions.minHeight !== undefined
                ? buttonDimensions.minHeight
                : buttonGlobals.sizing.minHeight,
            buttonDimensions && buttonDimensions.minWidth !== undefined
                ? buttonDimensions.minWidth
                : buttonGlobals.sizing.minWidth,
            buttonTypeVars.fonts && buttonTypeVars.fonts.size !== undefined
                ? buttonTypeVars.fonts.size
                : buttonGlobals.font.size,
            buttonTypeVars.padding && buttonTypeVars.padding.side !== undefined
                ? buttonTypeVars.padding.side
                : buttonGlobals.padding.side,
            formElVars,
        ),
        display: "inline-flex",
        alignItems: "center",
        position: "relative",
        textAlign: "center",
        whiteSpace: "nowrap",
        verticalAlign: "middle",
        justifyContent: "center",
        touchAction: "manipulation",
        cursor: "pointer",
        $nest: {
            "&:not([disabled])": {
                $nest: {
                    "&:not(.focus-visible)": {
                        outline: 0,
                    },
                    "&:hover": {
                        zIndex,
                        color: colorOut(
                            buttonTypeVars.hover && buttonTypeVars.hover.colors && buttonTypeVars.hover.colors.fg
                                ? buttonTypeVars.hover.colors.fg
                                : undefined,
                        ),
                        backgroundColor: colorOut(
                            buttonTypeVars.hover && buttonTypeVars.hover.colors && buttonTypeVars.hover.colors.bg
                                ? buttonTypeVars.hover.colors.bg
                                : undefined,
                        ),
                        ...hoverBorder,
                    },
                    "&:focus": {
                        zIndex,
                        color: colorOut(
                            buttonTypeVars.focus!.colors && buttonTypeVars.focus!.colors.fg
                                ? buttonTypeVars.focus!.colors.fg
                                : undefined,
                        ),
                        backgroundColor: colorOut(
                            buttonTypeVars.focus!.colors && buttonTypeVars.focus!.colors.bg
                                ? buttonTypeVars.focus!.colors.bg
                                : undefined,
                        ),
                        ...focusBorder,
                    },
                    "&:active": {
                        zIndex,
                        color: colorOut(
                            buttonTypeVars.active!.colors && buttonTypeVars.active!.colors.fg
                                ? buttonTypeVars.active!.colors.fg
                                : undefined,
                        ),
                        backgroundColor: colorOut(
                            buttonTypeVars.active!.colors && buttonTypeVars.active!.colors.bg
                                ? buttonTypeVars.active!.colors.bg
                                : undefined,
                        ),
                        ...activeBorder,
                    },
                    "&.focus-visible": {
                        zIndex,
                        color: colorOut(
                            buttonTypeVars.focusAccessible!.colors && buttonTypeVars.focusAccessible!.colors.fg
                                ? buttonTypeVars.focusAccessible!.colors.fg
                                : undefined,
                        ),
                        backgroundColor: colorOut(
                            buttonTypeVars.focusAccessible!.colors && buttonTypeVars.focusAccessible!.colors.bg
                                ? buttonTypeVars.focusAccessible!.colors.bg
                                : undefined,
                        ),
                        ...focusAccessibleBorder,
                    },
                },
            },
            "&[disabled]": {
                opacity: formElVars.disabled.opacity,
            },
        },
    };

    return result;
};
const generateButtonClass = (buttonTypeVars: IButtonType, setZIndexOnState = false) => {
    const style = styleFactory(`button-${buttonTypeVars.name}`);
    return style(generateButtonStyleProperties(buttonTypeVars, setZIndexOnState));
};

export default generateButtonClass;
