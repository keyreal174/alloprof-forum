import React from "react";
import { UserPhoto, UserPhotoSize } from "@library/headers/mebox/pieces/UserPhoto";
import { IUserFragment } from "@library/@types/api/users";
import { visibility } from "@library/styles/styleHelpersVisibility";
import DropDownItemButton from "@library/flyouts/items/DropDownItemButton";
import { dropdownSwitchButtonClasses } from "@library/flyouts/dropDownSwitchButtonStyles";
import { CheckCompactIcon } from "@library/icons/common";
import { t } from "@vanilla/i18n/src";
import classNames from "classnames";
import ButtonLoader from "@library/loaders/ButtonLoader";
import DateTime from "@library/content/DateTime";
import { ITheme } from "@library/theming/themeReducer";

interface IProps {
    revision: ITheme;
    isSelected?: boolean;
    userInfo: IUserFragment;
    onClick?: (event: any) => void;
    disabled?: boolean;
    isActive?: boolean;
}

export function ThemeRevisionItem(props: IProps) {
    const { revision, isSelected, userInfo, isActive } = props;
    const visibilityClasses = visibility();
    const classes = dropdownSwitchButtonClasses();

    const checkStatus = isSelected ? (
        <>
            <CheckCompactIcon aria-hidden={true} />
            <span className={visibilityClasses.visuallyHidden}>{t("on")}</span>
        </>
    ) : (
        <>
            <span className={visibilityClasses.visuallyHidden}>{t("off")}</span>
        </>
    );

    const content = (
        <>
            <div style={{ display: "flex", alignItems: "center" }}>
                <UserPhoto userInfo={userInfo} size={UserPhotoSize.MEDIUM} />
                <div style={{ margin: "10px", width: "250px" }}>
                    <span style={{ display: "block" }} className={classes.itemLabel}>
                        {revision.name}
                    </span>
                    <span style={{ display: "block" }} className={classes.itemLabel}>
                        <DateTime timestamp={revision.dateInserted} />
                    </span>
                    {isActive && <span>active</span>}
                </div>
                <span className={classNames(classes.checkContainer, "sc-only")}>{checkStatus}</span>
            </div>
        </>
    );

    return (
        <DropDownItemButton onClick={props.onClick} role={"switch"} aria-checked={isSelected} disabled={props.disabled}>
            {content}
        </DropDownItemButton>
    );
}
