/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import { globalVariables } from "@library/styles/globalStyleVars";
import { singleBorder, toStringColor, unit, userSelect } from "@library/styles/styleHelpers";
import { memoizeTheme, styleFactory } from "@library/styles/styleUtils";
import { richEditorVariables } from "@rich-editor/styles/richEditorStyles/richEditorVariables";
import { translateX } from "csx";

export const nubClasses = memoizeTheme(() => {
    const globalVars = globalVariables();
    const vars = richEditorVariables();
    const style = styleFactory("nub");

    const root = style({
        position: "relative",
        display: "block",
        width: unit(vars.nub.width),
        height: unit(vars.nub.width),
        borderTop: singleBorder({
            width: vars.menu.borderWidth,
        }),
        borderRight: singleBorder({
            width: vars.menu.borderWidth,
        }),
        boxShadow: globalVars.overlay.dropShadow,
        background: toStringColor(vars.colors.bg),
    });

    const position = style("position", {
        position: "absolute",
        display: "flex",
        alignItems: "flex-start",
        justifyContent: "center",
        overflow: "hidden",
        width: unit(vars.nub.width * 2),
        height: unit(vars.nub.width * 2),
        ...userSelect(),
        transform: translateX("-50%"),
        pointerEvents: "none",
    });

    return { root, position };
});
