/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import {allLinkStates, colorOut, debugHelper, margins, unit} from "@library/styles/styleHelpers";
import { useThemeCache } from "@library/styles/styleUtils";
import { px } from "csx";
import { style } from "typestyle";
import { layoutVariables } from "@library/layout/layoutStyles";

export const siteNavAdminLinksClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const debug = debugHelper("siteNavAdminLinks");
    const mediaQueries = layoutVariables().mediaQueries();

    const root = style(
        {
            display: "block",
            ...margins({
                all: 0,
            }),
            ...debug.name(),
        },
        mediaQueries.oneColumn({
            ...margins({
                vertical: 16,
            }),
        }),
    );

    const item = style({
        display: "block",
        color: colorOut(globalVars.mainColors.fg),
        ...debug.name("item"),
    });

    const divider = style({
        borderBottom: `solid 1px ${globalVars.mixBgAndFg(0.5)}`,
        marginBottom: px(25),
        ...debug.name("i"),
    });

    const link = style({
        fontWeight: globalVars.fonts.weights.semiBold,
        ...allLinkStates({
            noState: {
                color: colorOut(globalVars.mainColors.fg),
            },
            hover: {
                color: colorOut(globalVars.mainColors.primary),
            },
            focus: {
                color: colorOut(globalVars.mainColors.secondary),
            },
        }),
        ...debug.name("link"),
    });

    const linkIcon = style({
        marginRight: px(6),
        ...debug.name("linkIcon"),
    });

    return { root, item, divider, link, linkIcon };
});
