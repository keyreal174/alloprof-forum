/**
 * Compatibility styles, using the color variables.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import {
    absolutePosition,
    colorOut,
    ColorValues,
    importantUnit,
    paddings,
    singleBorder,
    unit,
} from "@library/styles/styleHelpers";
import { globalVariables } from "@library/styles/globalStyleVars";
import { calc, important, percent } from "csx";
import { cssOut } from "@dashboard/compatibilityStyles/index";
import { forumLayoutVariables } from "@dashboard/compatibilityStyles/forumLayoutStyles";
import { forumVariables } from "@library/forms/forumStyleVars";
import { userPhotoMixins } from "@library/headers/mebox/pieces/userPhotoStyles";

export const tableCSS = () => {
    const vars = globalVariables();
    const layoutVars = forumLayoutVariables();
    const forumVars = forumVariables();
    const userPhotoSizing = forumVars.userPhoto.sizing;
    const mixins = userPhotoMixins(forumVars.userPhoto);
    const margin = 12;

    cssOut(
        `
        .Groups .DataTable .LatestPostTitle,
        .Groups .DataTable .UserLink.BlockTitle,
        .Groups .DataTable .BigCount .Meta,
        .Groups .DataTable .Block.Wrap .Meta,
        .DataTable .LatestPostTitle,
        .DataTable .UserLink.BlockTitle,
        .DataTable .BigCount .Meta,
        .DataTable .Block.Wrap .Meta
        `,
        {
            width: calc(`100% - ${unit(userPhotoSizing.medium + margin)}`),
            marginTop: 0,
        },
    );

    cssOut(
        `
        .Groups .DataTable .UserLink.BlockTitle,
        .DataTable .UserLink.BlockTitle,
        `,
        {
            fontWeight: vars.fonts.weights.normal,
        },
    );

    cssOut(
        `
        .Groups .DataTable tbody td.LatestPost .PhotoWrap,
        .Groups .DataTable tbody td.LastUser .PhotoWrap,
        .Groups .DataTable tbody td.FirstUser .PhotoWrap,
        .DataTable tbody td.LatestPost .PhotoWrap,
        .DataTable tbody td.LastUser .PhotoWrap,
        .DataTable tbody td.FirstUser .PhotoWrap
    `,
        {
            ...mixins.root,
            ...absolutePosition.topLeft(),
            width: unit(userPhotoSizing.medium),
            height: unit(userPhotoSizing.medium),
        },
    );

    cssOut(
        `
        .Groups .DataTable tbody td.LatestPost .PhotoWrap img,
        .Groups .DataTable tbody td.LastUser .PhotoWrap img,
        .Groups .DataTable tbody td.FirstUser .PhotoWrap img,
        .DataTable tbody td.LatestPost .PhotoWrap img,
        .DataTable tbody td.LastUser .PhotoWrap img,
        .DataTable tbody td.FirstUser .PhotoWrap img
    `,
        mixins.photo,
    );

    cssOut(
        `
        .DataTable thead td,
        .DataTableWrap.GroupWrap thead td,
    `,
        {
            ...paddings({
                vertical: vars.gutter.size,
                horizontal: importantUnit(vars.gutter.half),
            }),
        },
    );

    cssOut(
        `
        .Groups .DataTable tbody td.LatestPost a,
        .Groups .DataTable tbody td.LastUser a,
        .Groups .DataTable tbody td.FirstUser a,
        .DataTable tbody td.LatestPost a,
        .DataTable tbody td.LastUser a,
        .DataTable tbody td.FirstUser a
        `,
        {
            color: colorOut(vars.mainColors.fg),
            fontSize: unit(vars.meta.text.size),
            textDecoration: important("none"),
        },
    );

    cssOut(`.Groups .DataTable .Item td, .DataTable .Item td`, {
        borderBottom: singleBorder(),
        padding: 0,
    });

    cssOut(`.Groups .DataTable .Item:first-child td, .DataTable .Item:first-child td`, {
        borderTop: singleBorder(),
    });

    cssOut(`.Groups .DataTable td .Wrap, .DataTable td .Wrap`, {
        ...paddings({
            vertical: layoutVars.cell.paddings.vertical,
            left: calc(`${unit(layoutVars.cell.paddings.horizontal)} / 2`),
            right: calc(`${unit(layoutVars.cell.paddings.horizontal)} / 2`),
        }),
    });

    cssOut(
        `.Groups .DataTable .Excerpt, .Groups .DataTable .CategoryDescription, .DataTable .Excerpt, .DataTable .CategoryDescription`,
        {
            color: colorOut(vars.mainColors.fg),
            fontSize: unit(vars.fonts.size.medium),
        },
    );

    cssOut(".DataTable .userCardWrapper .flyouts", {
        width: percent(100),
    });

    cssOut(`.DataTable .DiscussionName .Title`, {
        width: calc(`100% - ${unit(vars.icon.sizes.default * 2 + vars.gutter.quarter)}`),
    });
};
