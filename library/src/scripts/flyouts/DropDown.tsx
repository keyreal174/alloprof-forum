/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { frameHeaderClasses } from "@library/layout/frame/frameStyles";
import Heading from "@library/layout/Heading";
import DropDownContents from "@library/flyouts/DropDownContents";
import { ButtonTypes } from "@library/forms/buttonStyles";
import { getRequiredID } from "@library/utility/idUtils";
import FlexSpacer from "@library/layout/FlexSpacer";
import { dropDownClasses } from "@library/flyouts/dropDownStyles";
import SmartAlign from "@library/layout/SmartAlign";
import CloseButton from "@library/navigation/CloseButton";
import FlyoutToggle from "@library/flyouts/FlyoutToggle";
import classNames from "classnames";
import { dropDownMenu } from "@library/icons/common";

export interface IProps {
    id?: string;
    name?: string;
    children: React.ReactNode;
    className?: string;
    renderAbove?: boolean; // Adjusts the flyout position vertically
    renderLeft?: boolean; // Adjusts the flyout position horizontally
    describedBy?: string;
    contentsClassName?: string;
    buttonContents?: React.ReactNode;
    buttonClassName?: string;
    buttonBaseClass?: ButtonTypes;
    disabled?: boolean;
    toggleButtonClassName?: string;
    setExternalButtonRef?: (ref: React.RefObject<HTMLButtonElement>) => void;
    onVisibilityChange?: (isVisible: boolean) => void;
    openAsModal?: boolean;
    title?: string;
    paddedList?: boolean;
}

export interface IState {
    selectedText: string;
}

/**
 * Creates a drop down menu
 */
export default class DropDown extends React.Component<IProps, IState> {
    private id;
    public static defaultProps = {
        openAsModal: false,
    };
    public constructor(props) {
        super(props);
        this.id = getRequiredID(props, "dropDown");
        this.state = {
            selectedText: "",
        };
    }

    public setSelectedText(selectedText) {
        this.setState({
            selectedText,
        });
    }

    public get selectedText(): string {
        return this.state.selectedText;
    }

    public render() {
        const { title } = this.props;
        const classesDropDown = dropDownClasses();
        const classesFrameHeader = frameHeaderClasses();
        const classes = dropDownClasses();
        return (
            <FlyoutToggle
                id={this.id}
                className={classNames(this.props.className)}
                buttonBaseClass={this.props.buttonBaseClass || ButtonTypes.CUSTOM}
                name={this.props.name}
                buttonContents={this.props.buttonContents || dropDownMenu()}
                buttonClassName={this.props.buttonClassName}
                selectedItemLabel={this.selectedText}
                disabled={this.props.disabled}
                setExternalButtonRef={this.props.setExternalButtonRef}
                toggleButtonClassName={this.props.toggleButtonClassName}
                onVisibilityChange={this.props.onVisibilityChange}
                openAsModal={!!this.props.openAsModal}
            >
                {params => {
                    return (
                        <DropDownContents
                            {...params}
                            id={this.id + "-handle"}
                            parentID={this.id}
                            className={classNames(
                                this.props.contentsClassName,
                                this.props.paddedList ? classesDropDown.paddedList : "",
                            )}
                            onClick={this.doNothing}
                            renderLeft={!!this.props.renderLeft}
                            renderAbove={!!this.props.renderAbove}
                            openAsModal={this.props.openAsModal}
                        >
                            {title ? (
                                <header className={classNames("frameHeader", classesFrameHeader.root)}>
                                    <FlexSpacer
                                        className={classNames("frameHeader-leftSpacer", classesFrameHeader.leftSpacer)}
                                    />
                                    <SmartAlign>
                                        <Heading
                                            title={title}
                                            className={classNames(
                                                "dropDown-title",
                                                classesDropDown.title,
                                                classes.title,
                                            )}
                                        />
                                    </SmartAlign>
                                    <div
                                        className={classNames(
                                            "frameHeader-closePosition",
                                            classesFrameHeader.closePosition,
                                            classesFrameHeader.action,
                                        )}
                                    >
                                        <CloseButton
                                            className="frameHeader-close"
                                            onClick={params.closeMenuHandler}
                                            baseClass={ButtonTypes.CUSTOM}
                                        />
                                    </div>
                                </header>
                            ) : null}
                            <ul className={classNames("dropDownItems", classes.items)}>{this.props.children}</ul>
                        </DropDownContents>
                    );
                }}
            </FlyoutToggle>
        );
    }

    private doNothing = e => {
        e.stopPropagation();
    };
}
