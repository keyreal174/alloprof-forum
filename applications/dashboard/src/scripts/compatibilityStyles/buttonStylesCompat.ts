/**
 * Compatibility styles, using the color variables.
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { cssOut, trimTrailingCommas } from "@dashboard/compatibilityStyles/index";
import { buttonGlobalVariables, buttonUtilityClasses, buttonVariables } from "@library/forms/buttonStyles";
import { generateButtonStyleProperties } from "@library/forms/styleHelperButtonGenerator";
import {
    absolutePosition,
    borders,
    colorOut,
    importantUnit,
    offsetLightness,
    paddings,
    unit,
} from "@library/styles/styleHelpers";
import { globalVariables } from "@library/styles/globalStyleVars";
import { formElementsVariables } from "@library/forms/formElementStyles";
import { important, percent, rgba } from "csx";
import { ButtonTypes } from "@library/forms/buttonTypes";

export const buttonCSS = () => {
    const globalVars = globalVariables();
    const formElementVars = formElementsVariables();

    // @mixin Button
    mixinButton(".Button-Options", ButtonTypes.ICON_COMPACT);
    mixinButton(".DataList a.Delete.Delete.Delete", ButtonTypes.ICON_COMPACT);
    mixinButton(".MessageList a.Delete.Delete.Delete", ButtonTypes.ICON_COMPACT);

    mixinButton(".Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".FormTitleWrapper .Buttons .Button", ButtonTypes.PRIMARY);
    mixinButton(".FormWrapper .Buttons .Button", ButtonTypes.PRIMARY);
    mixinButton(".FormWrapper .file-upload-browse", ButtonTypes.PRIMARY);
    mixinButton(".FormTitleWrapper .Buttons .Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".FormTitleWrapper .file-upload-browse", ButtonTypes.PRIMARY);
    mixinButton(".FormWrapper .Buttons .Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".Button-Controls .Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".BigButton:not(.Danger)", ButtonTypes.PRIMARY);
    mixinButton(".NewConversation.NewConversation", ButtonTypes.PRIMARY);
    mixinButton(".groupToolbar .Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".BoxButtons .ButtonGroup.Multi .Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".Section-Members .Group-RemoveMember.Group-RemoveMember", ButtonTypes.PRIMARY);
    mixinButton(".Section-Members .Buttons .Group-RemoveMember.Group-RemoveMember", ButtonTypes.PRIMARY);
    mixinButton(".Section-Members .Buttons .Group-Leader.Group-Leader", ButtonTypes.STANDARD);
    mixinButton(".group-members-filter-box .Button.search", ButtonTypes.PRIMARY);
    mixinButton("#Form_Ban", ButtonTypes.PRIMARY);
    mixinButton(".Popup #UserBadgeForm button", ButtonTypes.PRIMARY);
    mixinButton(".Button.Handle", ButtonTypes.PRIMARY);
    mixinButton("div.Popup .Body .Button.Primary", ButtonTypes.PRIMARY);
    mixinButton(".ButtonGroup.Multi .Button.Handle", ButtonTypes.PRIMARY);
    mixinButton(".ButtonGroup.Multi .Button.Handle .Sprite.SpDropdownHandle", ButtonTypes.PRIMARY);
    mixinButton(".AdvancedSearch .InputAndButton .bwrap .Button", ButtonTypes.PRIMARY);

    const buttonBorderRadius = parseInt(globalVars.borderType.formElements.buttons.toString(), 10);
    const borderOffset = globalVars.border.width * 2;
    const handleSize = formElementVars.sizing.height - borderOffset;

    if (buttonBorderRadius && buttonBorderRadius > 0) {
        cssOut(`.Frame .ButtonGroup.Multi.NewDiscussion .Button.Handle .SpDropdownHandle::before`, {
            marginTop: unit((formElementVars.sizing.height * 2) / 36), // center vertically
            marginRight: unit(buttonBorderRadius * 0.035), // offset based on border radius. No radius will give no offset.
            maxHeight: unit(handleSize),
            height: unit(handleSize),
            lineHeight: unit(handleSize),
            maxWidth: unit(handleSize),
            minWidth: unit(handleSize),
        });
    }

    cssOut(`.FormWrapper .file-upload-browse, .FormTitleWrapper .file-upload-browse`, {
        marginRight: unit(0),
    });

    cssOut(`.Group-Box .BlockColumn .Buttons a:first-child`, {
        marginRight: unit(globalVars.gutter.quarter),
    });

    cssOut(`.Frame .ButtonGroup.Multi .Button.Handle .Sprite.SpDropdownHandle`, {
        height: unit(handleSize),
        maxHeight: unit(handleSize),
        width: unit(handleSize),
        background: important("transparent"),
        backgroundColor: important("none"),
        ...borders({
            color: rgba(0, 0, 0, 0),
        }),
        maxWidth: unit(handleSize),
        minWidth: unit(handleSize),
    });

    cssOut(`.Frame .ButtonGroup.Multi.NewDiscussion .Button.Handle.Handle`, {
        position: "absolute",
        top: unit(0),
        right: unit(formElementVars.border.width),
        bottom: unit(0),
        minWidth: importantUnit(handleSize),
        maxWidth: importantUnit(handleSize),
        maxHeight: importantUnit(handleSize),
        minHeight: importantUnit(handleSize),
        height: importantUnit(handleSize),
        width: importantUnit(handleSize),
        borderTopRightRadius: unit(buttonBorderRadius),
        borderBottomRightRadius: unit(buttonBorderRadius),
        display: "block",
    });

    cssOut(`.Frame .ButtonGroup.Multi.Open .Button.Handle`, {
        backgroundColor: colorOut(globalVars.mainColors.secondary),
        width: unit(formElementVars.sizing.height),
    });

    cssOut(`.Frame .ButtonGroup.Multi.NewDiscussion .Sprite.SpDropdownHandle`, {
        ...absolutePosition.fullSizeOfParent(),
        padding: important(0),
        border: important(0),
        borderRadius: important(0),
        minWidth: unit(handleSize),
    });

    cssOut(`.ButtonGroup.Multi.NewDiscussion`, {
        position: "relative",
        maxWidth: percent(100),
        boxSizing: "border-box",
        $nest: {
            "& .Button.Primary": {
                maxWidth: percent(100),
                width: percent(100),
                ...paddings({
                    horizontal: formElementVars.sizing.height,
                }),
            },
            "& .Button.Handle": {
                ...absolutePosition.middleRightOfParent(),
                width: unit(formElementVars.sizing.height),
                maxWidth: unit(formElementVars.sizing.height),
                minWidth: unit(formElementVars.sizing.height),
                height: unit(formElementVars.sizing.height),
                padding: 0,
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                border: important(0),
                borderTopLeftRadius: important(0),
                borderBottomLeftRadius: important(0),
            },
            "& .Button.Handle .SpDropdownHandle::before": {
                padding: important(0),
            },
        },
    });

    cssOut(`.ButtonGroup.Multi > .Button:first-child`, {
        borderTopLeftRadius: unit(buttonBorderRadius),
    });

    // Standard
    mixinButton(".Button", ButtonTypes.STANDARD);
    mixinButton(".DataList .Item-Col .Options .OptionsLink", ButtonTypes.STANDARD);
    mixinButton(".MessageList .Item-Col .Options .OptionsLink", ButtonTypes.STANDARD);
    mixinButton(".PrevNextPager .Previous", ButtonTypes.STANDARD);
    mixinButton(".PrevNextPager .Next", ButtonTypes.STANDARD);
    mixinButton("div.Popup .Button.change-picture-new", ButtonTypes.STANDARD);
    mixinButton("body.Section-BestOf .FilterMenu a", ButtonTypes.STANDARD);
    mixinButton(".group-members-filter-box .Button", ButtonTypes.STANDARD);
    mixinButton("body.Section-Profile .ProfileOptions .Button-EditProfile", ButtonTypes.STANDARD);
    mixinButton("body.Section-Profile .ProfileOptions .MemberButtons", ButtonTypes.STANDARD);
    mixinButton("body.Section-Profile .ProfileOptions .ProfileButtons-BackToProfile", ButtonTypes.STANDARD);
    mixinButton(".Button.Close", ButtonTypes.STANDARD);
    mixinButton(".viewPollResults", ButtonTypes.STANDARD);

    cssOut(".Panel-main .ApplyButton", {
        width: "auto",
    });

    cssOut(`.AdvancedSearch .InputAndButton .bwrap.bwrap`, {
        ...absolutePosition.topRight(),
    });

    cssOut(`.AdvancedSearch .InputAndButton .bwrap .Button`, {
        minWidth: "auto",
        borderTopLeftRadius: important(0),
        borderBottomLeftRadius: important(0),
    });

    cssOut(`.AdvancedSearch .InputAndButton .bwrap .Button .Sprite.SpSearch`, {
        width: "auto",
        height: "auto",
    });
};

export const mixinButton = (selector: string, buttonType: ButtonTypes = ButtonTypes.STANDARD) => {
    const vars = buttonVariables();
    selector = trimTrailingCommas(selector);

    if (buttonType === ButtonTypes.PRIMARY) {
        cssOut(selector, generateButtonStyleProperties({ buttonTypeVars: vars.primary }));
    } else if (buttonType === ButtonTypes.STANDARD) {
        cssOut(selector, generateButtonStyleProperties({ buttonTypeVars: vars.standard }));
    } else if (buttonType === ButtonTypes.ICON_COMPACT) {
        cssOut(selector, buttonUtilityClasses().iconMixin(buttonGlobalVariables().sizing.compactHeight));
    } else {
        new Error(`No support yet for button type: ${buttonType}`);
    }
};
