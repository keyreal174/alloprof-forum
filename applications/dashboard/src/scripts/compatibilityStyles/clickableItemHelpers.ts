/**
 * Compatibility styles, using the color variables.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import {
    colorOut,
    ColorValues,
    ILinkColorOverwritesWithOptions,
    linkStyleFallbacks,
} from "@library/styles/styleHelpers";
import { cssOut, nestedWorkaround, trimTrailingCommas } from "@dashboard/compatibilityStyles/index";
import { globalVariables } from "@library/styles/globalStyleVars";
import { NestedCSSProperties } from "typestyle/lib/types";
import merge from "lodash/merge";
import { NestedCSSSelectors } from "typestyle/src/types";
import { important } from "csx";

export const EMPTY_STATE_COLORS = {
    default: undefined as undefined | ColorValues,
    hover: undefined as undefined | ColorValues,
    focus: undefined as undefined | ColorValues,
    clickFocus: undefined as undefined | ColorValues,
    keyboardFocus: undefined as undefined | ColorValues,
    active: undefined as undefined | ColorValues,
    visited: undefined as undefined | ColorValues,
    allStates: undefined as undefined | ColorValues,
};

export const EMPTY_STATE_STYLES = {
    default: undefined as undefined | NestedCSSProperties,
    hover: undefined as undefined | NestedCSSProperties,
    focus: undefined as undefined | NestedCSSProperties,
    clickFocus: undefined as undefined | NestedCSSProperties,
    keyboardFocus: undefined as undefined | NestedCSSProperties,
    active: undefined as undefined | NestedCSSProperties,
    visited: undefined as undefined | NestedCSSProperties,
    allStates: undefined as undefined | NestedCSSProperties,
};

// These need to be strings as they could be any styles.
export const clickStyleFallback = (
    specificOverwrite: undefined | NestedCSSProperties,
    defaultOverwrite: undefined | NestedCSSProperties,
) => {
    const mergedStyles = merge(specificOverwrite || {}, defaultOverwrite || {});
    return Object.keys(mergedStyles).length === 0 ? undefined : mergedStyles;
};

export const mixinClickInput = (selector: string, overwriteColors?: {}, overwriteSpecial?: {}) => {
    selector = trimTrailingCommas(selector);
    const selectors = selector.split(",");
    const linkColors = clickableItemStates(overwriteColors, overwriteSpecial);
    if (!selectors) {
        if (linkColors.color !== undefined) {
            cssOut(selector, {
                color: linkColors.color,
            });
        }
        nestedWorkaround(trimTrailingCommas(selector), linkColors.$nest);
    } else {
        selectors.map((s) => {
            if (linkColors.color !== undefined) {
                cssOut(selector, {
                    color: linkColors.color,
                });
            }
            nestedWorkaround(trimTrailingCommas(s), linkColors.$nest);
        });
    }
};

// The "special" here is non standard styles for links. The text colors have presets and have more complicated inheritance
export interface IClickableItemOptionalStates {
    default?: NestedCSSProperties;
    hover?: NestedCSSProperties;
    focus?: NestedCSSProperties;
    clickFocus?: NestedCSSProperties;
    keyboardFocus?: NestedCSSProperties;
    active?: NestedCSSProperties;
    visited?: NestedCSSProperties;
    allStates?: NestedCSSProperties;
}

export interface IClickableItemEnforcedStates {
    default: undefined;
    hover: undefined;
    focus: undefined;
    clickFocus: undefined;
    keyboardFocus: undefined;
    active: undefined;
    visited: undefined;
    allStates: undefined;
}

export interface IClickableItemOptions {
    disableTextDecoration?: boolean;
}

export const clickableItemStates = (
    overwriteColors?: ILinkColorOverwritesWithOptions,
    options?: IClickableItemOptions,
) => {
    const vars = globalVariables();
    const { disableTextDecoration } = options || { disableTextDecoration: false };
    // We want to default to the standard styles and only overwrite what we want/need
    const linkColors = vars.links.colors;

    overwriteColors = { ...EMPTY_STATE_COLORS, ...(overwriteColors ?? {}) };

    const mergedColors = {
        default: !overwriteColors.skipDefault
            ? linkStyleFallbacks(overwriteColors.default, undefined, linkColors.default)
            : undefined,
        hover: linkStyleFallbacks(overwriteColors.hover, overwriteColors.allStates, linkColors.hover),
        focus: linkStyleFallbacks(overwriteColors.focus, overwriteColors.allStates, linkColors.focus),
        clickFocus: linkStyleFallbacks(overwriteColors.clickFocus, overwriteColors.allStates, linkColors.focus),
        keyboardFocus: linkStyleFallbacks(
            overwriteColors.keyboardFocus,
            overwriteColors.allStates,
            linkColors.keyboardFocus,
        ),
        active: linkStyleFallbacks(overwriteColors.active, overwriteColors.allStates, linkColors.active),
        visited: linkStyleFallbacks(overwriteColors.visited, overwriteColors.allStates, linkColors.visited),
    };

    const textDecoration = disableTextDecoration ? important("none") : undefined;

    const styles = {
        default: {
            color: colorOut(mergedColors.default),
            textDecoration,
        },
        hover: {
            color: colorOut(mergedColors.hover),
            cursor: "pointer",
            textDecoration,
        },
        focus: {
            color: colorOut(mergedColors.focus),
            textDecoration,
        },
        clickFocus: {
            color: colorOut(mergedColors.focus),
            textDecoration,
        },
        keyboardFocus: {
            color: colorOut(mergedColors.keyboardFocus),
            textDecoration,
        },
        active: {
            color: colorOut(mergedColors.active),
            cursor: "pointer",
            textDecoration,
        },
        visited: mergedColors.visited
            ? {
                  color: colorOut(mergedColors.visited),
                  textDecoration,
              }
            : undefined,
    };

    const final = {
        color: styles.default.color as undefined | string,
        $nest: {
            "&&:hover": styles.hover,
            "&&:focus": {
                ...(styles.focus ?? {}),
                ...(styles.clickFocus ?? {}),
            },
            "&&.focus-visible": {
                ...(styles.focus ?? {}),
                ...(styles.keyboardFocus ?? {}),
            },
            "&&:active": styles.active,
            "&:visited": styles.visited ?? undefined,
        } as NestedCSSSelectors,
    } as NestedCSSProperties;

    return final;
};
