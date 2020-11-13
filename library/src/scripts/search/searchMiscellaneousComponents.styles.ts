/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { useThemeCache, styleFactory, variableFactory } from "@library/styles/styleUtils";
import { fonts, unit, margins } from "@library/styles/styleHelpers";

export const searchMiscellaneousComponentsVariables = useThemeCache(() => {
    const makeThemeVars = variableFactory("searchComponents");
    const spacing = makeThemeVars("spacing", {
        margin: 12,
    });
    return {
        spacing,
    };
});

export const searchMiscellaneousComponentsClasses = useThemeCache(() => {
    const style = styleFactory("searchMiscellaneousComponents");
    const globalVars = globalVariables();
    const vars = searchMiscellaneousComponentsVariables();

    const root = style("root", {
        display: "flex",
        alignItems: "center",
        justifyContent: "flex-start",
        marginBottom: unit(vars.spacing.margin),
    });

    const sort = style("sort", {
        display: "flex",
        ...margins({
            all: 0,
            right: globalVars.gutter.size,
        }),
        flexGrow: 1,
    });

    const sortLabel = style("sortLabel", {
        alignSelf: "center",
        marginRight: unit(6),
        ...fonts({
            color: globalVars.meta.text.color,
            weight: globalVars.fonts.weights.normal,
        }),
    });

    return {
        root,
        sort,
        sortLabel,
    };
});
