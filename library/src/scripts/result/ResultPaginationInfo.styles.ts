/**
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { useThemeCache, styleFactory } from "@library/styles/styleUtils";
import { fonts } from "@library/styles/styleHelpers";
import { globalVariables } from "@library/styles/globalStyleVars";

export const resultPaginationInfoClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const style = styleFactory("resultPaginationInfo");

    const root = style("pagination", {
        marginLeft: "auto",
        ...fonts(globalVars.meta.text),
    });

    return { root };
});
