/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { color, percent, px } from "csx";
import { mixBgAndFg } from "@library/styles/styleHelpers";
import { vanillaHeaderVariables } from "@library/components/headers/vanillaHeaderStyles";
import { media, style } from "typestyle";
import { globalVariables } from "@library/styles/globalStyleVars";
import { layoutStyles } from "@library/styles/layoutStyles";

export const mobileDropDownVariables = () => {
    const vanillaHeaderVars = vanillaHeaderVariables();

    const title = {
        letterSpacing: -0.26,
        maxWidth: `calc(100% - ${px(vanillaHeaderVars.endElements.flexBasis * 2)}`,
    };
    const chevron = {
        width: 8,
        height: 8,
        color: mixBgAndFg(0.7),
    };
    const header = {
        minHeight: 28,
    };
    return { title, chevron, header };
};

export const mobileDropDownClasses = () => {
    const vars = mobileDropDownVariables();
    const globalVars = globalVariables();
    const mediaQueries = layoutStyles().mediaQueries();

    const root = style({
        position: "relative",
        flexGrow: 1,
        overflow: "hidden",
    });

    const modal = style({
        $nest: {
            ".siteNav": {
                paddingLeft: px(globalVars.gutter.half),
            },
            "&.modal": {
                borderTopLeftRadius: 0,
                borderTopRightRadius: 0,
            },
        },
    });

    const panel = style({
        position: "relative",
        maxHeight: percent(100),
        padding: 0,
    });

    const content = style({
        position: "relative",
        maxHeight: percent(100),
    });

    const toggleButton = style(
        {
            display: "flex",
            alignItems: "center",
            flexDirection: "row",
            justifyContent: "space-between",
            flexWrap: "nowrap",
            userSelect: "none",
            width: percent(100),
            maxWidth: `calc(100% - ${globalVars.spacer}`,
            marginLeft: globalVars.spacer / 2,
            marginRight: globalVars.spacer / 2,
        },
        mediaQueries.xs({
            maxWidth: percent(100),
            margin: 0,
            padding: 0,
        }),
    );

    const buttonContents = style({
        display: "inline-block",
        position: "relative",
        paddingRight: vars.chevron.width * 2,
        overflow: "hidden",
        textOverflow: "ellipsis",
        maxWidth: percent(100),
    });

    const title = style(
        {
            display: "inline",
            letterSpacing: vars.title.letterSpacing,
            fontWeight: globalVars.fonts.weights.semiBold,
            textAlign: "center",
        },
        mediaQueries.xs({
            textAlign: "left",
        }),
    );

    const icon = style({
        position: "absolute",
        display: "block",
        top: 0,
        right: 0,
        bottom: 0,
        maxHeight: percent(100),
        maxWidth: percent(100),
        margin: `auto 0`,
        height: vars.chevron.height,
        width: vars.chevron.width,
    });

    const closeModalIcon = style({
        padding: 0,
        margin: "auto",
        color: vars.chevron.color.toString(),
        $nest: {
            "&:hover": { color: globalVars.mainColors.primary.toString() },
            "&:active": { color: globalVars.mainColors.primary.toString() },
            "&:focus": { color: globalVars.mainColors.primary.toString() },
        },
    });

    const closeModal = style({
        width: percent(100),
        height: percent(100),
    });

    const headerElementDimensions = {
        height: px(vars.header.minHeight),
        width: px(vars.header.minHeight),
    };

    const header = style({
        $nest: {
            ".frameHeaderWithAction-action": headerElementDimensions,
            ".frameHeader-closePosition": headerElementDimensions,
            ".frameHeader-close": headerElementDimensions,
            ".frameHeader-leftSpacer": {
                flexBasis: px(vars.header.minHeight),
                height: px(vars.header.minHeight),
                width: px(vars.header.minHeight),
            },
        },
    });

    return {
        root,
        modal,
        panel,
        content,
        toggleButton,
        buttonContents,
        title,
        icon,
        closeModalIcon,
        closeModal,
        header,
    };
};
