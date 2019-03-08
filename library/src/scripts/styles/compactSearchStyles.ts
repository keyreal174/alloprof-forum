/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { globalVariables } from "@library/styles/globalStyleVars";
import { unit } from "@library/styles/styleHelpers";
import { formElementsVariables } from "@library/components/forms/formElementStyles";
import { vanillaHeaderVariables } from "@library/styles/vanillaHeaderStyles";
import { percent, px } from "csx";
import { styleFactory, useThemeCache } from "@library/styles/styleUtils";

export const compactSearchClasses = useThemeCache(() => {
    const globalVars = globalVariables();
    const formElementVars = formElementsVariables();
    const vanillaHeaderVars = vanillaHeaderVariables();
    const style = styleFactory("compactSearch");

    const root = style({
        $nest: {
            ".searchBar": {
                flexGrow: 1,
            },
            ".searchBar-valueContainer.suggestedTextInput-inputText": {
                height: unit(formElementVars.sizing.height),
                backgroundColor: vanillaHeaderVars.colors.bg.darken(0.05).toString(),
                border: 0,
            },
            ".searchBar__placeholder": {
                color: globalVars.mainColors.bg.toString(),
            },
            ".searchBar__control": {
                opacity: 0.8,
                $nest: {
                    "&.searchBar__control--isFocused": {
                        opacity: 1,
                    },
                },
            },
            "&.isOpen": {
                width: percent(100),
            },
            "&.isCentered": {
                margin: "auto",
            },
            ".suggestedTextInput-inputText": {
                borderTopRightRadius: unit(globalVars.border.radius),
                borderBottomRightRadius: unit(globalVars.border.radius),
            },
        },
    });

    const contents = style("contents", {
        display: "flex",
        alignItems: "center",
        flexWrap: "nowrap",
    });

    const close = style("close", {
        color: "inherit",
        whiteSpace: "nowrap",
        fontWeight: globalVars.fonts.weights.semiBold,
    });

    const cancelContents = style("cancelContents", {
        padding: px(4),
    });
    return { root, contents, close, cancelContents };
});
