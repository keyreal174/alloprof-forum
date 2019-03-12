/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { layoutVariables } from "@library/styles/layoutStyles";
import { debugHelper, flexHelper, unit } from "@library/styles/styleHelpers";
import { useThemeCache } from "@library/styles/styleUtils";
import { vanillaHeaderVariables } from "@library/styles/vanillaHeaderStyles";
import { style } from "typestyle";
import { formElementsVariables } from "@library/components/forms/formElementStyles";

export const meBoxClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const formVars = formElementsVariables();
    const vanillaHeaderVars = vanillaHeaderVariables();
    const debug = debugHelper("meBox");
    const mediaQueries = layoutVariables().mediaQueries();
    const flex = flexHelper();

    const root = style(
        {
            ...debug.name(),
            display: "flex",
            alignItems: "center",
            height: unit(vanillaHeaderVars.sizing.height),
        },
        mediaQueries.oneColumn({
            height: unit(vanillaHeaderVars.sizing.mobile.height),
        }),
    );

    const buttonContent = style({
        ...flex.middle(),
        width: unit(formVars.sizing.height),
        maxWidth: unit(formVars.sizing.height),
        flexBasis: unit(formVars.sizing.height),
        height: unit(vanillaHeaderVars.meBox.sizing.buttonContents),
    });

    return { root, buttonContent };
});
