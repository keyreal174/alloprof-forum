/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { colorOut, ColorValues } from "@library/styles/styleHelpersColors";
import { BorderRadiusProperty, BorderStyleProperty, BorderWidthProperty } from "csstype";
import { NestedCSSProperties, TLength } from "typestyle/lib/types";
import { unit } from "@library/styles/styleHelpers";
import { globalVariables, IGlobalBorderStyles } from "@library/styles/globalStyleVars";
import merge from "lodash/merge";
import { ColorHelper } from "csx";
import { getValueIfItExists } from "@library/forms/borderStylesCalculator";

export enum BorderType {
    BORDER = "border",
    NONE = "none",
    SHADOW = "shadow",
    SHADOW_AS_BORDER = "shadow_as_border", // Note that is applied on a different element
}

export interface ISimpleBorderStyle {
    color?: ColorValues | ColorHelper;
    width?: BorderWidthProperty<TLength>;
    style?: BorderStyleProperty;
    radius?: IRadiusValue;
}

export interface IBorderRadiusOptions {
    fallbackRadii?: object;
    debug?: boolean;
    isImportant?: boolean;
}

export interface IBordersWithRadius extends ISimpleBorderStyle {
    radius?: radiusValue;
}

export type radiusValue = BorderRadiusProperty<TLength> | string;

export type IRadiusValue = IBorderRadiusValue | IRadiusShorthand | IBorderRadiusOutput;

interface IRadiusFlex {
    radius?: IRadiusValue;
}

export interface IRadiusShorthand {
    all?: IBorderRadiusValue;
    top?: IBorderRadiusValue;
    bottom?: IBorderRadiusValue;
    left?: IBorderRadiusValue;
    right?: IBorderRadiusValue;
}

export interface IBorderRadiusOutput {
    borderTopRightRadius?: IBorderRadiusValue;
    borderTopLeftRadius?: IBorderRadiusValue;
    borderBottomRightRadius?: IBorderRadiusValue;
    borderBottomLeftRadius?: IBorderRadiusValue;
}

export interface IMixedRadiusDeclaration extends IRadiusShorthand, IBorderRadiusOutput {}

type IRadiusInput = IMixedRadiusDeclaration | IRadiusValue;

export type IBorderRadiusValue = BorderRadiusProperty<TLength> | number | string | undefined;

export interface IBorderStyles extends ISimpleBorderStyle, IRadiusFlex {
    all?: ISimpleBorderStyle & IRadiusFlex;
    topBottom?: ISimpleBorderStyle & IRadiusFlex;
    leftRight?: ISimpleBorderStyle & IRadiusFlex;
    top?: ISimpleBorderStyle & IRadiusFlex;
    bottom?: ISimpleBorderStyle & IRadiusFlex;
    left?: ISimpleBorderStyle & IRadiusFlex;
    right?: ISimpleBorderStyle & IRadiusFlex;
}

export interface IMixedBorderStyles extends IBorderStyles, ISimpleBorderStyle {}

const typeIsStringOrNumber = (variable: unknown): variable is number | string => {
    if (variable !== null) {
        const type = typeof variable;
        return type === "string" || type === "number";
    } else {
        return false;
    }
};

const setAllRadii = (radius: BorderRadiusProperty<TLength>, options?: IBorderRadiusOptions) => {
    return {
        borderTopRightRadius: unit(radius, options),
        borderBottomRightRadius: unit(radius, options),
        borderBottomLeftRadius: unit(radius, options),
        borderTopLeftRadius: unit(radius, options),
    };
};

export const EMPTY_BORDER: Partial<ISimpleBorderStyle> = {
    color: undefined,
    width: undefined,
    style: undefined,
    radius: undefined,
};

export const EMPTY_BORDER_RADIUS = {
    borderTopRightRadius: undefined,
    borderBottomRightRadius: undefined,
    borderBottomLeftRadius: undefined,
    borderTopLeftRadius: undefined,
};

/**
 * Main utility function for generation proper border radiuses. Supports numerous shorthand properties.
 *
 * @param radii
 * @param options
 */
export const standardizeBorderRadius = (radii: IRadiusInput, options?: IBorderRadiusOptions): IBorderRadiusOutput => {
    const output: IBorderRadiusOutput = {};
    const { debug } = options || {};

    if (typeof radii === "object" && Object.keys(radii).length === 0) {
        return output;
    }

    if (typeIsStringOrNumber(radii)) {
        // direct value
        const value = unit(radii as number | string);
        return {
            borderTopRightRadius: unit(value, options),
            borderBottomRightRadius: unit(value, options),
            borderBottomLeftRadius: unit(value, options),
            borderTopLeftRadius: unit(value, options),
        };
    }

    // Otherwise we need to check all of the values.
    const all = getValueIfItExists(radii, "all", getValueIfItExists(radii, "radius"));
    const top = getValueIfItExists(radii, "top");
    const bottom = getValueIfItExists(radii, "bottom");
    const left = getValueIfItExists(radii, "left");
    const right = getValueIfItExists(radii, "right");

    if (typeIsStringOrNumber(all)) {
        merge(output, {
            borderTopRightRadius: unit(all, options),
            borderBottomRightRadius: unit(all, options),
            borderBottomLeftRadius: unit(all, options),
            borderTopLeftRadius: unit(all, options),
        });
    }

    if (top !== undefined) {
        const isShorthand = typeIsStringOrNumber(top);

        if (isShorthand) {
            const value = !isShorthand ? unit(top, options) : top;
            merge(output, {
                borderTopRightRadius: unit(value, options),
                borderTopLeftRadius: unit(value, options),
            });
        } else {
            merge(
                output,
                right !== undefined ? { borderTopRightRadius: unit(right, options) } : {},
                left !== undefined ? { borderTopLeftRadius: unit(left, options) } : {},
            );
        }
    }

    if (bottom !== undefined) {
        const isShorthand = typeIsStringOrNumber(bottom);

        if (isShorthand) {
            const value = !isShorthand ? unit(bottom, options) : bottom;
            merge(output, {
                borderBottomRightRadius: unit(value, options),
                borderBottomLeftRadius: unit(value, options),
            });
        } else {
            merge(
                output,
                right !== undefined ? { borderBottomRightRadius: unit(right, options) } : {},
                left !== undefined ? { borderBottomLeftRadius: unit(left, options) } : {},
            );
        }
    }

    if (left !== undefined) {
        const isShorthand = typeIsStringOrNumber(left);

        if (isShorthand) {
            const value = !isShorthand ? unit(left, options) : left;
            merge(output, {
                borderTopLeftRadius: unit(value, options),
                borderBottomLeftRadius: unit(value, options),
            });
        } else {
            const topStyles = top !== undefined ? { borderTopLeftRadius: unit(top, options) } : {};
            const bottomStyles = bottom !== undefined ? { borderBottomLeftRadius: unit(bottom, options) } : {};
            merge(
                output,
                !typeIsStringOrNumber(topStyles) ? topStyles : {},
                !typeIsStringOrNumber(bottomStyles) ? bottomStyles : {},
            );
        }
    }
    if (right !== undefined) {
        const isShorthand = typeIsStringOrNumber(right);

        if (isShorthand) {
            const value = !isShorthand ? unit(right, options) : right;
            merge(output, {
                borderTopRightRadius: unit(value, options),
                borderBottomRightRadius: unit(value, options),
            });
        } else {
            const topStyles = top !== undefined ? { borderTopRightRadius: unit(top, options) } : {};
            const bottomStyles = bottom !== undefined ? { borderBottomRightRadius: unit(bottom, options) } : {};
            merge(
                output,
                !typeIsStringOrNumber(topStyles) ? topStyles : {},
                !typeIsStringOrNumber(bottomStyles) ? bottomStyles : {},
            );
        }
    }

    const borderTopRightRadius = getValueIfItExists(radii, "borderTopRightRadius");
    if (borderTopRightRadius !== undefined) {
        merge(output, {
            borderTopRightRadius: unit(borderTopRightRadius, options),
        });
    }
    const borderTopLeftRadius = getValueIfItExists(radii, "borderTopLeftRadius");
    if (borderTopLeftRadius !== undefined) {
        merge(output, {
            borderTopLeftRadius: unit(borderTopLeftRadius, options),
        });
    }
    const borderBottomRightRadius = getValueIfItExists(radii, "borderBottomRightRadius");
    if (borderBottomRightRadius !== undefined) {
        merge(output, {
            borderBottomRightRadius: unit(borderBottomRightRadius, options),
        });
    }
    const borderBottomLeftRadius = getValueIfItExists(radii, "borderBottomLeftRadius");
    if (borderBottomLeftRadius !== undefined) {
        merge(output, {
            borderBottomLeftRadius: unit(borderBottomLeftRadius, options),
        });
    }

    return output;
};

export const borderRadii = (radii: IRadiusValue, options?: IBorderRadiusOptions) => {
    const { fallbackRadii = globalVariables().border.radius, isImportant = false, debug = false } = options || {};

    const output: IBorderRadiusOutput = {};

    if (typeIsStringOrNumber(fallbackRadii)) {
        merge(output, setAllRadii(fallbackRadii, { isImportant }));
    } else {
        merge(output, typeIsStringOrNumber(fallbackRadii) ? fallbackRadii : fallbackRadii);
    }

    const hasRadiusShorthand = typeIsStringOrNumber(radii);
    const hasRadiusShorthandFallback = typeIsStringOrNumber(fallbackRadii);

    // Make sure we have a value before overwriting.
    if (hasRadiusShorthand) {
        merge(output, setAllRadii(radii as any, { isImportant }));
    } else if (hasRadiusShorthandFallback) {
        merge(output, setAllRadii(fallbackRadii as any, { isImportant }));
    } else {
        // our fallback must be an object.
        merge(output, standardizeBorderRadius(fallbackRadii as any, { isImportant }));
    }
    merge(output, standardizeBorderRadius(radii as any, { isImportant }));
    return output as NestedCSSProperties;
};

const setAllBorders = (
    color: ColorValues,
    width: BorderWidthProperty<TLength>,
    style: BorderStyleProperty,
    radius?: IBorderRadiusOutput,
    debug = false as boolean | string,
) => {
    const output = {};

    if (color !== undefined) {
        merge(output, {
            borderTopColor: colorOut(color),
            borderRightColor: colorOut(color),
            borderBottomColor: colorOut(color),
            borderLeftColor: colorOut(color),
        });
    }

    if (width !== undefined) {
        merge(output, {
            borderTopWidth: unit(width),
            borderRightWidth: unit(width),
            borderBottomWidth: unit(width),
            borderLeftWidth: unit(width),
        });
    }

    if (style !== undefined) {
        merge(output, {
            borderTopStyle: style,
            borderRightStyle: style,
            borderBottomStyle: style,
            borderLeftStyle: style,
        });
    }

    if (radius !== undefined && typeof radius !== "object") {
        merge(output, setAllRadii(radius));
    }

    return output;
};

const singleBorderStyle = (
    borderStyles: ISimpleBorderStyle,
    fallbackVariables: IGlobalBorderStyles = globalVariables().border,
) => {
    if (!borderStyles) {
        return;
    }
    const { color, width, style } = borderStyles;
    const output: ISimpleBorderStyle = {};
    output.color = colorOut(borderStyles.color ? borderStyles.color : color) as ColorValues;
    output.width = unit(borderStyles.width ? borderStyles.width : width) as BorderWidthProperty<TLength>;
    output.style = borderStyles.style ? borderStyles.style : (style as BorderStyleProperty);

    if (Object.keys(output).length > 0) {
        return output;
    } else {
        return;
    }
};

export const borders = (
    detailedStyles?: IBorderStyles | ISimpleBorderStyle | IMixedBorderStyles | undefined,
    options?: {
        fallbackBorderVariables?: IGlobalBorderStyles;
        debug?: boolean | string;
    },
): NestedCSSProperties => {
    const { fallbackBorderVariables = globalVariables().border, debug = false } = options || {};
    const output = {} as any;
    const style = getValueIfItExists(detailedStyles, "style", fallbackBorderVariables.style);
    const color = getValueIfItExists(detailedStyles, "color", fallbackBorderVariables.color);
    const width = getValueIfItExists(detailedStyles, "width", fallbackBorderVariables.width);
    const radius = getValueIfItExists(detailedStyles, "radius", fallbackBorderVariables.radius);
    const defaultsAll = setAllBorders(color, width, style, radius, debug);

    merge(output, defaultsAll);

    // Now we are sure to not have simple styles anymore.
    detailedStyles = detailedStyles as IBorderStyles;
    if (!detailedStyles) {
        detailedStyles = fallbackBorderVariables;
    }

    const all = getValueIfItExists(detailedStyles, "all");
    if (all !== undefined) {
        const allStyles = singleBorderStyle(all, fallbackBorderVariables);
        if (allStyles !== undefined) {
            output.borderTopWidth = allStyles?.width ?? width;
            output.borderTopStyle = getValueIfItExists(allStyles, "style", style);
            output.borderTopColor = getValueIfItExists(allStyles, "color", color);
            output.borderTopRightRadius = getValueIfItExists(all, "radius", radius);
            output.borderBottomRightRadius = getValueIfItExists(all, "radius", radius);
            output.borderBottomLeftRadius = getValueIfItExists(all, "radius", radius);
            output.borderTopLeftRadius = getValueIfItExists(all, "radius", radius);
        }
    }

    const top = getValueIfItExists(detailedStyles, "top");
    if (top !== undefined) {
        const topStyles = singleBorderStyle(top, fallbackBorderVariables);
        if (topStyles !== undefined) {
            output.borderTopWidth = getValueIfItExists(topStyles, "width", width);
            output.borderTopStyle = getValueIfItExists(topStyles, "style", style);
            output.borderTopColor = getValueIfItExists(topStyles, "color", color);
            output.borderTopLeftRadius = getValueIfItExists(top, "radius", radius);
            output.borderTopRightRadius = getValueIfItExists(top, "radius", radius);
        }
    }

    const right = getValueIfItExists(detailedStyles, "right");

    if (right !== undefined) {
        const rightStyles = singleBorderStyle(right, fallbackBorderVariables);
        if (rightStyles !== undefined) {
            output.borderRightWidth = getValueIfItExists(rightStyles, "width", width);
            output.borderRightStyle = getValueIfItExists(rightStyles, "style", style);
            output.borderRightColor = getValueIfItExists(rightStyles, "color", color);

            output.borderBottomRightRadius = getValueIfItExists(right, "radius", radius);
            output.borderTopRightRadius = getValueIfItExists(right, "radius", radius);
        }
    }

    const bottom = getValueIfItExists(detailedStyles, "bottom");
    if (bottom !== undefined) {
        const bottomStyles = singleBorderStyle(bottom, fallbackBorderVariables);
        if (bottomStyles !== undefined) {
            output.borderBottomWidth = getValueIfItExists(bottomStyles, "width", width);
            output.borderBottomStyle = getValueIfItExists(bottomStyles, "style", style);
            output.borderBottomColor = getValueIfItExists(bottomStyles, "color", color);
            output.borderBottomLeftRadius = getValueIfItExists(bottom, "radius", radius);
            output.borderBottomRightRadius = getValueIfItExists(bottom, "radius", radius);
        }
    }

    const left = getValueIfItExists(detailedStyles, "left");

    if (left !== undefined) {
        const leftStyles = singleBorderStyle(left, fallbackBorderVariables);
        if (leftStyles !== undefined) {
            output.borderLeftWidth = getValueIfItExists(leftStyles, "width", width);
            output.borderLeftStyle = getValueIfItExists(leftStyles, "style", style);
            output.borderLeftColor = getValueIfItExists(leftStyles, "color", color);
            output.borderBottomLeftRadius = getValueIfItExists(left, "radius", radius);
            output.borderTopLeftRadius = getValueIfItExists(left, "radius", radius);
        }
    }

    return output;
};

export const singleBorder = (styles?: ISimpleBorderStyle) => {
    const vars = globalVariables();
    const borderStyles = styles !== undefined ? styles : {};
    return `${borderStyles.style ? borderStyles.style : vars.border.style} ${
        borderStyles.color ? colorOut(borderStyles.color) : colorOut(vars.border.color)
    } ${borderStyles.width ? unit(borderStyles.width) : unit(vars.border.width)}` as any;
};
