/**
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { useThemeCache, variableFactory, styleFactory } from "@library/styles/styleUtils";
import { IThemeVariables } from "@library/theming/themeReducer";
import { globalVariables } from "@library/styles/globalStyleVars";
import { percent } from "csx";
import { unit } from "@library/styles/styleHelpers";
import { layoutVariables } from "@library/layout/panelLayoutStyles";

export const userCardVariables = useThemeCache((forcedVars?: IThemeVariables) => {
    const makeVars = variableFactory("inviteUserCard", forcedVars);
    const globalVars = globalVariables();

    const body = makeVars("body", {
        padding: {
            size: globalVars.gutter.size,
        },
    });

    const button = makeVars("button", {
        mobile: {
            width: percent(47),
        },
    });

    const buttonGroup = makeVars("buttonGroup", {
        padding: {
            top: globalVars.gutter.size,
            bottom: globalVars.gutter.size,
        },
    });

    const message = makeVars("message", {
        padding: {
            bottom: globalVars.gutter.size,
        },
    });

    return { body, button, buttonGroup, message };
});

export const userCardClasses = useThemeCache((props: { compact?: boolean } = {}) => {
    const style = styleFactory("inviteUserCard");
    const mediaQueries = layoutVariables().mediaQueries();
    const vars = userCardVariables();

    const body = style("body", {
        padding: vars.body.padding.size,
    });

    const button = style(
        "button",
        {},
        mediaQueries.oneColumnDown({
            $nest: {
                "&&": {
                    width: vars.button.mobile.width,
                },
            },
        }),
    );

    const buttonGroup = style(
        "buttonGroup",
        {
            display: "flex",
            justifyContent: "flex-end",
            paddingTop: vars.buttonGroup.padding.top,
            paddingBottom: vars.buttonGroup.padding.bottom,
            $nest: {
                "&>*:first-child": {
                    marginRight: unit(20),
                },
            },
        },
        mediaQueries.oneColumnDown({
            $nest: {
                "&&": {
                    justifyContent: "space-between",
                },
                "&>*:first-child": {
                    marginRight: unit(0),
                },
            },
        }),
    );

    const message = style("message", {
        paddingBottom: vars.message.padding.bottom,
    });

    const textbox = style("textbox", {});

    const users = style("users", {
        maxHeight: unit(100),
        overflowY: "scroll",
    });

    return { body, button, buttonGroup, message, textbox, users };
});
