/**
 * Compatibility styles, using the color variables.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
import { globalVariables } from "@library/styles/globalStyleVars";
import { colorOut } from "@library/styles/styleHelpersColors";
import { cssOut } from "@dashboard/compatibilityStyles/index";
import { allLinkStates, fonts, margins, paddings, singleBorder, unit } from "@library/styles/styleHelpers";
import { forumLayoutVariables } from "@dashboard/compatibilityStyles/forumLayoutStyles";
import { metaContainerStyles } from "@vanilla/library/src/scripts/styles/metasStyles";
import { searchBarClasses, searchBarVariables } from "@library/features/search/searchBarStyles";
import { searchResultsVariables } from "@library/features/search/searchResultsStyles";
import { percent } from "csx";

export const searchPageCSS = () => {
    const globalVars = globalVariables();
    const layoutVars = forumLayoutVariables();

    cssOut(`.DataList.DataList-Search .Item.Item-Search .Img.PhotoWrap`, {
        top: unit(layoutVars.cell.paddings.vertical),
        left: unit(layoutVars.cell.paddings.horizontal),
    });

    cssOut(
        `
         #search-results .Breadcrumbs a,
         #search-results .MessageList a,
         #search-results .DataTableWrap a,
         #search-results .Container .Frame-contentWrap .ChildCategories a,
        .DataList#search-results a,
        .DataList-Search#search-results .MItem-Author,
        .DataList-Search#search-results .MItem-Author a,
        .DataList-Search#search-results a,
        .DataList-Search .MItem-Author a
        `,
        {
            textDecoration: "none",
            color: colorOut(globalVars.mainColors.fg),
            fontSize: unit(globalVars.meta.text.fontSize),
        },
    );

    cssOut(
        `
          .DataList.DataList-Search#search-results .Item.Item-Search h3 a,
      `,
        {
            textDecoration: "none",
            ...fonts({
                color: globalVars.mainColors.fg,
                size: globalVars.fonts.size.large,
                weight: globalVars.fonts.weights.semiBold,
                lineHeight: globalVars.lineHeights.condensed,
            }),
            ...allLinkStates({
                hover: {
                    color: colorOut(globalVars.links.colors.hover),
                },
                accessibleFocus: {
                    color: colorOut(globalVars.links.colors.accessibleFocus),
                },
                focus: {
                    color: colorOut(globalVars.links.colors.focus),
                },
                active: {
                    color: colorOut(globalVars.links.colors.active),
                },
                visited: {
                    color: colorOut(globalVars.links.colors.visited),
                },
            }),
        },
    );

    cssOut(`.Item.Item-Search .Meta .Bullet`, {
        display: "none",
    });

    cssOut(`#search-results .DataList.DataList-Search .Item.Item-Search .Media-Body .Meta`, {
        ...metaContainerStyles(),
        $nest: {
            "& .Bullet": {
                display: "none",
            },
        },
    });
    cssOut(`#search-results .DataList.DataList-Search .Item.Item-Search .Media-Body .Summary`, {
        $nest: {
            "& .Bullet": {
                display: "none",
            },
        },
    });

    cssOut(`#search-results .DataList.DataList-Search .Breadcrumbs`, {
        overflow: "visible",
    });

    cssOut(`#search-results .DataList.DataList-Search .Item-Body.Media`, {
        margin: 0,
    });

    cssOut(`#search-results .DataList.DataList-Search + .PageControls.Bottom`, {
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",

        $nest: {
            "& .Gloss": {
                margin: 0,
                minHeight: 0,
                minWidth: 0,
            },
            "& .Pager": {
                float: "none",
                marginRight: "auto",
            },
        },
    });

    cssOut(`#search-results .DataList.DataList-Search .Crumb`, {
        ...margins({
            right: -6,
            left: -6,
        }),
    });

    // Search result styles
    const searchResultsStyles = searchBarClasses().searchResultsStyles;
    const searchResultsVars = searchResultsVariables();

    cssOut(`body.Section-SearchResults .MenuItems.MenuItems-Input.ui-autocomplete`, {
        position: "relative",
        ...paddings({
            vertical: 0,
        }),
    });

    // li
    cssOut(`body.Section-SearchResults .MenuItems.MenuItems-Input.ui-autocomplete .ui-menu-item`, {
        position: "relative",
        padding: 0,
        margin: 0,
        $nest: {
            "& + .ui-menu-item": {
                borderTop: singleBorder({
                    color: searchResultsVars.separator.fg,
                    width: searchResultsVars.separator.width,
                }),
            },
        },
    });

    // a
    cssOut(`body.Section-SearchResults .MenuItems.MenuItems-Input.ui-autocomplete .ui-menu-item a`, {
        ...searchResultsStyles.option,
        display: "flex",
        flexWrap: "wrap",
        alignItems: "center",
        justifyContent: "flex-start",
        $nest: {
            "& .Title": {
                ...searchResultsStyles.title,
                display: "block",
                width: percent(100),
                marginBottom: ".15em",
            },
            "& .Aside": {
                display: "inline-block",
                float: "none",
                ...searchResultsStyles.meta,
            },
            "& .Aside .Date": {
                display: "inline",
                ...searchResultsStyles.meta,
            },
            "& .Gloss": {
                ...searchResultsStyles.excerpt,
                display: "block",
                paddingLeft: 0,
                marginTop: 0,
                width: percent(100),
            },
        },
    });
};
