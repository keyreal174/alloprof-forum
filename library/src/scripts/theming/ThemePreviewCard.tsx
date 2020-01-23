/**
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React, { useRef, useState } from "react";
import { themeCardClasses } from "./themeCardStyles";
import Button from "@library/forms/Button";
import { t } from "@library/utility/appUtils";
import { globalVariables } from "@library/styles/globalStyleVars";
import { colorOut } from "@library/styles/styleHelpersColors";
import { titleBarVariables } from "@library/headers/titleBarStyles";
import ButtonLoader from "@library/loaders/ButtonLoader";
import { useFocusWatcher } from "@vanilla/react-utils";
import classNames from "classnames";
import DropDown, { FlyoutType } from "@library/flyouts/DropDown";
import DropDownItemButton from "@library/flyouts/items/DropDownItemButton";
import DropDownItemSeparator from "@library/flyouts/items/DropDownItemSeparator";
import { ToolTip, ToolTipIcon } from "@library/toolTip/ToolTip";
import { WarningIcon } from "@library/icons/common";
import { iconClasses } from "@library/icons/iconClasses";
import { ButtonTypes } from "@library/forms/buttonStyles";
import DropDownItem from "@library/flyouts/items/DropDownItem";
import LinkAsButton from "@library/routing/LinkAsButton";
import DropDownItemLink from "@library/flyouts/items/DropDownItemLink";

type VoidFunction = () => void;
type ClickHandlerOrUrl = string | VoidFunction;

interface IProps {
    name?: string;
    previewImage?: string;
    globalBg?: string;
    globalFg?: string;
    globalPrimary?: string;
    titleBarBg?: string;
    titleBarFg?: string;
    headerImg?: string;
    onApply?: VoidFunction;
    isApplyLoading?: boolean;
    onPreview?: VoidFunction;
    onCopy?: ClickHandlerOrUrl;
    onEdit?: ClickHandlerOrUrl;
    onDelete?: ClickHandlerOrUrl;
    isActiveTheme: boolean;
    noActions?: boolean;
    canCopy?: boolean;
    canDelete?: boolean;
    canEdit?: boolean;
    canCopyCustom?: boolean;
}

export default function ThemePreviewCard(props: IProps) {
    const tiles = [1, 2, 3, 4];
    const vars = globalVariables();
    const titleVars = titleBarVariables();

    const {
        globalBg = colorOut(vars.mainColors.bg),
        globalPrimary = colorOut(vars.mainColors.primary),
        globalFg = colorOut(vars.mainColors.fg),
        titleBarBg = colorOut(titleVars.colors.bg),
        titleBarFg = colorOut(titleVars.colors.fg),
    } = props;
    const classes = themeCardClasses();
    const titlebarStyle = {
        backgroundColor: titleBarBg,
    };

    const titleBarLinks = {
        backgroundColor: titleBarFg,
    };

    const containerStyle = {
        backgroundColor: `${globalBg}`,
    };

    const headerStyle = {
        backgroundColor: globalPrimary,
    };

    const [hasFocus, setHasFocus] = useState(false);
    const containerRef = useRef<HTMLDivElement | null>(null);
    useFocusWatcher(containerRef, setHasFocus);

    return (
        <div
            ref={containerRef}
            style={containerStyle}
            className={classNames(
                hasFocus && classes.isFocused,
                props.noActions ? classes.noActions : classes.container,
            )}
            tabIndex={0}
            title={props.name}
        >
            <div className={classes.menuBar}>
                {[0, 1, 2].map(key => (
                    <span key={key} className={classes.dots}></span>
                ))}
            </div>
            {props.previewImage ? (
                <img className={classes.previewImage} src={props.previewImage} />
            ) : (
                <div className={classes.wrapper}>
                    <div style={titlebarStyle} className={classes.titlebar}>
                        <ul className={classes.titleBarNav}>
                            {[0, 1, 2].map(key => (
                                <li key={key} style={titleBarLinks} className={classes.titleBarLinks}></li>
                            ))}
                        </ul>
                    </div>
                    <div style={headerStyle} className={classes.header}>
                        <div className={classes.title}></div>
                        <div className={classes.search}>
                            <span className={classes.bar}></span>
                            <span className={classes.search_btn}>
                                <span className={classes.searchText}></span>
                            </span>
                        </div>
                    </div>
                    <div className={classes.content}>
                        <ul className={classes.contentList} style={containerStyle}>
                            {tiles.map((val, key) => (
                                <li key={key} className={classes.contentListItem}>
                                    <div className={classes.contentTile}>
                                        <div className={classes.tileImg} style={{ borderColor: globalPrimary }}></div>
                                        <div className={classes.tileHeader}></div>
                                        <div className={classes.tileContent}>
                                            <p className={classes.text1}></p>
                                            <p className={classes.text2}></p>
                                            <p className={classes.text3}></p>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            )}
            {!props.noActions && (
                <div className={props.noActions ? classes.noOverlay : classes.overlay}>
                    {(props.canEdit || props.canDelete) && (
                        <div className={classes.actionDropdown}>
                            <DropDown buttonBaseClass={ButtonTypes.ICON} flyoutType={FlyoutType.LIST} renderLeft={true}>
                                {props.canEdit && props.onEdit && (
                                    <LinkOrButton isDropdown onClick={props.onEdit}>
                                        {t("Edit")}
                                    </LinkOrButton>
                                )}
                                {props.canCopyCustom && props.onCopy && (
                                    <LinkOrButton isDropdown onClick={props.onCopy}>
                                        {t("Copy")}
                                    </LinkOrButton>
                                )}
                                <DropDownItemSeparator />
                                {props.canDelete && props.isActiveTheme ? (
                                    <DropDownItemButton onClick={props.onDelete} disabled={props.isActiveTheme}>
                                        <span className={classNames("selectBox-itemLabel", classes.itemLabel)}>
                                            Delete
                                        </span>
                                        <span className={classNames("sc-only")}>
                                            <ToolTip
                                                label={
                                                    "This theme cannot be deleted because it is the currently applied theme"
                                                }
                                            >
                                                <ToolTipIcon>
                                                    <span>
                                                        <WarningIcon
                                                            className={classNames(iconClasses().errorFgColor)}
                                                        />
                                                    </span>
                                                </ToolTipIcon>
                                            </ToolTip>
                                        </span>
                                    </DropDownItemButton>
                                ) : (
                                    <DropDownItemButton name={t("Delete")} onClick={props.onDelete} />
                                )}
                            </DropDown>
                        </div>
                    )}
                    <div className={classes.actionButtons}>
                        <Button
                            className={classes.buttons}
                            onClick={() => {
                                containerRef.current?.focus();
                                props.onApply?.();
                            }}
                        >
                            {props.isApplyLoading ? <ButtonLoader /> : t("Apply")}
                        </Button>
                        <Button
                            className={classes.buttons}
                            onClick={() => {
                                containerRef.current?.focus();
                                props.onPreview?.();
                            }}
                        >
                            {t("Preview")}
                        </Button>
                        {props.canCopy && props.onCopy && (
                            <LinkOrButton onClick={props.onCopy}>{t("Copy")}</LinkOrButton>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

function LinkOrButton(props: { onClick: ClickHandlerOrUrl; children: React.ReactNode; isDropdown?: boolean }) {
    const classes = themeCardClasses();
    if (typeof props.onClick === "string") {
        if (props.isDropdown) {
            return <DropDownItemLink to={props.onClick}>{props.children}</DropDownItemLink>;
        } else {
            return (
                <LinkAsButton className={classes.buttons} to={props.onClick}>
                    {props.children}
                </LinkAsButton>
            );
        }
    } else {
        if (props.isDropdown) {
            return <DropDownItemButton onClick={props.onClick}>{props.children}</DropDownItemButton>;
        } else {
            return (
                <Button className={classes.buttons} onClick={props.onClick}>
                    {props.children}
                </Button>
            );
        }
    }
}
