/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { styleFactory, useThemeCache } from "@library/styles/styleUtils";
import { unit } from "@library/styles/styleHelpers";
import { percent } from "csx";
import { richEditorVariables } from "@rich-editor/editor/richEditorVariables";

export const inlineToolbarClasses = useThemeCache((legacyMode: boolean = false) => {
    const vars = richEditorVariables();
    const style = styleFactory("inlineToolbar");

    const offsetForNub = vars.menu.offset / 2;
    const root = style({
        $nest: {
            "&.isUp": {
                $nest: {
                    ".richEditor-nubPosition": {
                        transform: `translateY(${!legacyMode ? "-1px" : "9px"}) translateX(-50%)`,
                        alignItems: "flex-end",
                        bottom: percent(100),
                    },
                    ".richEditor-nub": {
                        transform: `translateY(-50%) rotate(135deg)`,
                        marginBottom: unit(offsetForNub),
                    },
                },
            },
            "&.isDown": {
                transform: `translateY(50%)`,
                $nest: {
                    ".richEditor-nub": {
                        transform: `translateY(50%) rotate(-45deg)`,
                        marginTop: unit(offsetForNub),
                        boxShadow: "none",
                    },
                },
            },
        },
    });
    return { root };
});
