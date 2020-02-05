/**
 * Compatibility styles, using the color variables.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { cssRaw } from "typestyle";
import {
    absolutePosition,
    borders,
    buttonStates,
    colorOut,
    IActionStates,
    importantColorOut,
    importantUnit,
    IStateSelectors,
    negative,
    paddings,
    pointerEvents,
    setAllLinkColors,
    singleBorder,
    textInputSizingFromFixedHeight,
    unit,
    userSelect,
} from "@library/styles/styleHelpers";
import { globalVariables } from "@library/styles/globalStyleVars";
import { calc, important, percent } from "csx";
import { cssOut, nestedWorkaround, trimTrailingCommas } from "@dashboard/compatibilityStyles/index";
import { inputClasses, inputVariables } from "@library/forms/inputStyles";
import { formElementsVariables } from "@library/forms/formElementStyles";
import { forumLayoutVariables } from "./forumLayoutStyles";
import { NestedCSSProperties } from "typestyle/lib/types";
import { useThemeCache, variableFactory } from "@library/styles/styleUtils";

export const groupVariables = useThemeCache(() => {
    const globalVars = globalVariables();
    const makeThemeVars = variableFactory("groups");

    const banner = makeThemeVars("banner", {
        height: 200,
    });

    const logo = makeThemeVars("logo", {
        height: 140,
    });

    return {
        banner,
        logo,
    };
});

export const groupsCSS = () => {
    const vars = groupVariables();
    const globalVars = globalVariables();
    const inputVars = inputVariables();
    const formVars = formElementsVariables();
    const layoutVars = forumLayoutVariables();
    const mainColors = globalVars.mainColors;
    const fg = colorOut(mainColors.fg);
    const bg = colorOut(mainColors.bg);
    const primary = colorOut(mainColors.primary);
    const metaFg = colorOut(globalVars.meta.colors.fg);
    const mediaQueries = layoutVars.mediaQueries();

    cssOut(`.Group-Banner`, {
        height: unit(vars.banner.height),
    });

    cssOut(`.groupToolbar`, {
        marginTop: unit(32),
    });

    cssOut(
        `
        .ButtonGroup.Open .Button.GroupOptionsTitle::before,
        .Button.GroupOptionsTitle::before,
        .Button.GroupOptionsTitle:active::before,
        .Button.GroupOptionsTitle:focus::before
        `,
        {
            color: "inherit",
            marginRight: unit(6),
        },
    );

    cssOut(`.Group-Header.NoBanner .Group-Header-Info`, {
        paddingLeft: unit(0),
    });

    cssOut(`.Group-Header`, {
        display: "flex",
        flexDirection: "row",
        alignItems: "center",
        flexWrap: "wrap",
    });

    cssOut(`a.ChangePicture`, {
        ...absolutePosition.fullSizeOfParent(),
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        opacity: 0,
    });

    // cssOut(`body.Section-Group .Group-Banner`, {
    //     ...absolutePosition.fullSizeOfParent(),
    // });

    cssOut(`.DataTableContainer.Group-Box.ApplicantList .PageControls .H`, {
        position: "relative",
    });

    cssOut(`body.Section-Event .Group-Banner`, {
        flexGrow: 1,
        width: percent(100),
    });

    cssOut(`.Photo.PhotoWrap.PhotoWrapLarge.Group-Icon-Big-Wrap`, {
        width: unit(vars.logo.height),
        height: unit(vars.logo.height),
        flexBasis: unit(vars.logo.height),
        top: unit(vars.banner.height - vars.logo.height / 2),
        background: "transparent",
        zIndex: 1,
        $nest: {
            "&:hover .ChangePicture": {
                opacity: 1,
            },
        },
    });

    cssOut(`.Groups .DataTable .Item td, .DataTable .Item td`, {
        borderBottom: singleBorder(),
        ...paddings({
            ...layoutVars.cell.paddings,
        }),
        backgroundColor: "transparent",
    });

    cssOut(`.Groups .DataTable .Item:first-child td, .DataTable .Item:first-child td`, {
        borderTop: singleBorder(),
    });

    cssOut(`.GroupOptions`, {
        top: calc(`100% + ${unit(globalVars.gutter.size)}`),
    });

    cssOut(`.PhotoWrap:hover a.ChangePicture`, {
        opacity: 0,
        backgroundColor: importantColorOut(globalVars.mainColors.bg.fade(0.5)),
        color: colorOut(globalVars.mainColors.fg),
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
    });

    cssOut(`.GroupWrap .DataTable .Title-Icon`, {
        color: colorOut(globalVars.meta.colors.fg),
    });

    cssOut(
        `.Groups .DataTable .Excerpt, .Groups .DataTable .CategoryDescription, .DataTable .Excerpt, .DataTable .CategoryDescription`,
        {
            color: colorOut(globalVars.mainColors.fg),
            fontSize: unit(globalVars.fonts.size.medium),
        },
    );

    cssOut(
        `.DataList a, .DataList-Search a, .Breadcrumbs a, .MessageList a, .DataTableWrap a, .Container .Frame-contentWrap .ChildCategories a`,
        {
            fontSize: unit(globalVars.fonts.size.medium),
        },
    );

    cssOut(`.Groups .Name.Group-Name .Options .Button`, {
        minWidth: 0,
        marginLeft: unit(globalVars.gutter.size),
    });

    cssOut(`.DataTableContainer.Group-Box`, {
        marginTop: unit(globalVars.gutter.size * 3),
    });

    cssOut(
        `
        .Group-Box .PageControls
        `,
        {
            position: "relative",
            flexDirection: "row",
        },
    );

    cssOut(
        `.Group-Box .PageControls .H`,
        {
            position: "absolute",
            top: 0,
            left: 0,
            right: 0,
            margin: "auto",
        },
        mediaQueries.xs({
            position: "relative",
            top: "auto",
            left: "auto",
            right: "auto",
        }),
    );

    cssOut(`.GroupWrap .DataTable .Buttons`, {
        display: "flex",
        flexWrap: "wrap",
        justifyContent: "flex-end",
    });
};
