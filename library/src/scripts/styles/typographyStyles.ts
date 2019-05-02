/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { styleFactory, useThemeCache } from "@library/styles/styleUtils";
import { globalVariables } from "@library/styles/globalStyleVars";
import { color, percent } from "csx";
import { paddings, unit } from "@library/styles/styleHelpers";
import { containerVariables } from "@library/layout/components/containerStyles";
import { layoutVariables } from "@library/layout/panelLayoutStyles";
import { lineHeightAdjustment } from "@library/styles/textUtils";

export const typographyClasses = useThemeCache(() => {
    const style = styleFactory("typography");
    const globalVars = globalVariables();
    const vars = containerVariables();
    const mediaQueries = layoutVariables().mediaQueries();

    const pageTitle = style(
        "pageTitle",
        {
            fontSize: unit(globalVars.fonts.size.title),
            lineHeight: globalVars.lineHeights.condensed,
            $nest: lineHeightAdjustment(globalVars.lineHeights.condensed),
        },
        mediaQueries.oneColumn({
            fontSize: unit(globalVars.fonts.mobile.size.title),
        }),
    );
    const subTitle = style("subTitle", {
        fontSize: unit(globalVars.fonts.size.subTitle),
    });
    const componentSubTitle = style("componentSubTitle", {
        fontSize: unit(globalVars.fonts.size.large),
    });

    return { pageTitle, subTitle, componentSubTitle };
});
