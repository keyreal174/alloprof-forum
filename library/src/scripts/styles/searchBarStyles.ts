/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { formElementsVariables } from "@library/components/forms/formElementStyles";
import { buttonVariables } from "@library/styles/buttonStyles";
import { globalVariables } from "@library/styles/globalStyleVars";
import { layoutVariables } from "@library/styles/layoutStyles";
import { componentThemeVariables, debugHelper, toStringColor, unit } from "@library/styles/styleHelpers";
import { styleFactory, useThemeCache } from "@library/styles/styleUtils";
import { vanillaHeaderVariables } from "@library/styles/vanillaHeaderStyles";
import { calc, important, percent, px } from "csx";

export const searchBarVariables = useThemeCache(() => {
    const formElementVars = formElementsVariables();
    const themeVars = styleFactory("searchBar");

    const search = themeVars("search", {
        minWidth: 109,
    });

    const searchIcon = themeVars("searchIcon", {
        gap: 32,
        height: 13,
        width: 13,
    });

    const sizing = themeVars("sizing", {
        height: 40,
    });

    const placeholder = themeVars("placeholder", {
        color: formElementVars.colors.placeholder,
    });

    const heading = themeVars("heading", {
        margin: 5,
    });

    return {
        search,
        searchIcon,
        sizing,
        placeholder,
        heading,
    };
});

export const searchBarClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const vars = searchBarVariables();
    const vanillaHeaderVars = vanillaHeaderVariables();
    const debug = debugHelper("searchBar");
    const buttonVars = buttonVariables();
    const formElementVars = formElementsVariables();
    const mediaQueries = layoutVariables().mediaQueries();
    const style = styleFactory("searchBar");

    const root = style(
        {
            cursor: "pointer",
            $nest: {
                "& .suggestedTextInput-inputText": {
                    borderRight: 0,
                    borderTopRightRadius: 0,
                    borderBottomRightRadius: 0,
                    $nest: {
                        "&.inputText": {
                            paddingTop: 0,
                            paddingBottom: 0,
                        },
                    },
                },

                "& .suggestedTextInput-clear": {
                    $nest: {
                        "&, &.buttonIcon": {
                            border: "none",
                            boxShadow: "none",
                            color: globalVars.mainColors.primary.toString(),
                        },
                    },
                },

                "& .searchBar__placeholder": {
                    color: toStringColor(globalVars.border.color),
                    margin: "auto",
                },

                "& .suggestedTextInput-valueContainer": {
                    $nest: {
                        ".inputBlock-inputText": {
                            height: "auto",
                        },
                    },
                },
                "& .searchBar-submitButton": {
                    position: "relative",
                    borderTopLeftRadius: 0,
                    borderBottomLeftRadius: 0,
                    marginLeft: -1,
                    minWidth: unit(vars.search.minWidth),
                    flexBasis: unit(vars.search.minWidth),
                    minHeight: unit(vars.sizing.height),
                    $nest: {
                        "&:hover, &:focus": {
                            zIndex: 1,
                        },
                    },
                },
                "& .searchBar__control": {
                    display: "flex",
                    flex: 1,
                    border: 0,
                    backgroundColor: "transparent",
                    height: percent(100),
                    maxWidth: calc(`100% - ${unit(vars.sizing.height)}`),
                    $nest: {
                        "&.searchBar__control--is-focused": {
                            boxShadow: "none",
                            $nest: {
                                "&.inputText": {
                                    borderTopRightRadius: 0,
                                    borderBottomRightRadius: 0,
                                    borderColor: buttonVars.standard.focus.borderColor.toString(),
                                },
                            },
                        },
                    },
                },
                "& .searchBar__value-container": {
                    overflow: "auto",
                    $nest: {
                        "& > div": {
                            width: percent(100),
                        },
                    },
                },
                "& .searchBar__indicators": {
                    display: "none",
                },
                "& .searchBar__input": {
                    width: percent(100),
                    display: important("block"),
                    $nest: {
                        input: {
                            width: important(percent(100).toString()),
                            lineHeight: globalVars.lineHeights.base,
                        },
                    },
                },
                "& .searchBar__menu-list": {
                    maxHeight: calc(`100vh - ${unit(vanillaHeaderVars.sizing.height)}`),
                },
            },
        },
        mediaQueries.oneColumn({
            $nest: {
                "& .searchBar-submitButton": {
                    minWidth: 0,
                },
            },
        }),
    );

    const results = style("results", {
        $nest: {
            ".suggestedTextInput__placeholder": {
                color: toStringColor(formElementVars.colors.placeholder),
            },
            ".suggestedTextInput-noOptions": {
                padding: px(12),
            },
            ".suggestedTextInput-option": {
                width: percent(100),
                padding: px(12),
                textAlign: "left",
                display: "block",
                color: "inherit",
                $nest: {
                    "&:hover, &:focus, &.isFocused": {
                        color: "inherit",
                        backgroundColor: globalVars.states.hover.color.toString(),
                    },
                },
            },
            ".suggestedTextInput-menu": {
                borderRadius: unit(globalVars.border.radius),
                marginTop: unit(-formElementVars.border.width),
                marginBottom: unit(-formElementVars.border.width),
            },
            ".suggestedTextInput-item": {
                $nest: {
                    "& + .suggestedTextInput-item": {
                        borderTop: `solid 1px ${globalVars.border.color.toString()}`,
                    },
                },
            },
        },
    });

    const valueContainer = style("valueContainer", {
        display: "flex",
        alignItems: "center",
        $nest: {
            "&&&": {
                display: "flex",
                flexWrap: "nowrap",
                alignItems: "center",
                justifyContent: "flex-start",
                paddingLeft: unit(vars.searchIcon.gap),
            },
        },
    });

    const actionButton = style("actionButton", {
        marginLeft: "auto",
        marginRight: -(globalVars.buttonIcon.offset + 3), // the "3" is to offset the pencil
        opacity: 0.8,
        $nest: {
            "&:hover": {
                opacity: 1,
            },
        },
    });

    const label = style("label", {
        display: "flex",
        alignItems: "center",
        justifyContent: "flex-start",
    });

    const clear = style("clear", {
        position: "relative",
        display: "flex",
        boxSizing: "border-box",
        height: unit(vars.sizing.height),
        width: unit(vars.sizing.height),
        color: vanillaHeaderVars.colors.fg.toString(),
    });

    const form = style("form", {
        display: "block",
    });

    const content = style("content", {
        display: "flex",
        alignItems: "flex-start",
        justifyContent: "flex-start",
        position: "relative",
        minHeight: unit(vars.sizing.height),
    });

    // special selector
    const heading = style("heading", {
        $nest: {
            "&&": {
                marginBottom: unit(vars.heading.margin),
            },
        },
    });

    const iconContainer = style("iconContainer", {
        position: "absolute",
        top: 0,
        bottom: 0,
        left: "2px",
        height: percent(100),
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        width: unit(vars.searchIcon.gap),
        color: toStringColor(vars.search.fg),
    });
    const icon = style("icon", {
        width: unit(vars.searchIcon.width),
        height: unit(vars.searchIcon.height),
    });

    return {
        root,
        valueContainer,
        actionButton,
        label,
        clear,
        form,
        content,
        heading,
        iconContainer,
        icon,
        results,
    };
});
