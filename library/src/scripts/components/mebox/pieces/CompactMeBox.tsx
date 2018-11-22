/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import { uniqueIDFromPrefix } from "@library/componentIDs";
import { t } from "@library/application";
import { IUserFragment } from "@library/@types/api/users";
import { UserPhoto, UserPhotoSize } from "@library/components/mebox/pieces/UserPhoto";
import { connect } from "react-redux";
import UsersModel, { IInjectableUserState } from "@library/users/UsersModel";
import get from "lodash/get";
import classNames from "classnames";
import Tabs from "@library/components/tabs/Tabs";
import Modal from "@library/components/modal/Modal";
import ModalSizes from "@library/components/modal/ModalSizes";
import Button, { ButtonBaseClass } from "@library/components/forms/Button";
import CloseButton from "@library/components/CloseButton";
import { user } from "@library/components/icons/header";
import UserDropdownContents from "@library/components/mebox/pieces/UserDropdownContents";
import NotificationsToggle from "@library/components/mebox/pieces/NotificationsToggle";
import MessagesToggle from "@library/components/mebox/pieces/MessagesToggle";
import MessagesDropDown from "@library/components/mebox/pieces/MessagesDropDown";
import NotificationsDropDown from "@library/components/mebox/pieces/NotificationsDropDown";
import { IMeBoxProps } from "@library/components/mebox/MeBox";

export interface IUserDropDownProps extends IInjectableUserState, IMeBoxProps {
    buttonClass?: string;
    userPhotoClass?: string;
}

interface IState {
    open: boolean;
}

/**
 * Implements User Drop down for header
 */
export class CompactMeBox extends React.Component<IUserDropDownProps, IState> {
    private id = uniqueIDFromPrefix("compactMeBox");
    private buttonRef: React.RefObject<HTMLButtonElement> = React.createRef();

    public state = {
        open: false,
    };

    public render() {
        const userInfo: IUserFragment = get(this.props, "currentUser.data", {
            name: null,
            userID: null,
            photoUrl: null,
        });

        const { counts } = this.props;
        const countClass = this.props.countsClass;
        const buttonClass = this.props.buttonClass;

        return (
            <div className={classNames("compactMeBox", this.props.className)}>
                <Button
                    title={t("My Account")}
                    className={classNames("compactMeBox-openButton", this.props.buttonClass)}
                    onClick={this.open}
                    buttonRef={this.buttonRef}
                    baseClass={ButtonBaseClass.CUSTOM}
                >
                    <UserPhoto
                        userInfo={userInfo}
                        open={this.state.open}
                        className="meBox-user"
                        size={UserPhotoSize.SMALL}
                        forceIcon={true}
                    />
                </Button>
                {this.state.open && (
                    <Modal
                        size={ModalSizes.PSEUDO_DROP_DOWN}
                        label={t("Article Revisions")}
                        elementToFocusOnExit={this.buttonRef.current!}
                        className="compactMeBox-modal"
                        exitHandler={this.close}
                    >
                        <div className="compactMeBox-contents">
                            <CloseButton onClick={this.close} />
                            <Tabs
                                label={t("My Account Tabx")}
                                buttonClass={buttonClass}
                                tabs={[
                                    {
                                        buttonContent: user(false, "userPhoto-photo"),
                                        openButtonContent: user(true, "userPhoto-photo"),
                                        panelContent: <UserDropdownContents counts={counts} />,
                                    },
                                    {
                                        buttonContent: (
                                            <NotificationsToggle
                                                open={false}
                                                count={this.props.notificationsProps.count}
                                                countClass={this.props.notificationsProps.countClass}
                                            />
                                        ),
                                        openButtonContent: (
                                            <NotificationsToggle
                                                open={true}
                                                count={this.props.notificationsProps.count}
                                                countClass={this.props.notificationsProps.countClass}
                                            />
                                        ),
                                        panelContent: (
                                            <NotificationsDropDown
                                                {...this.props.notificationsProps}
                                                countClass={countClass}
                                            />
                                        ),
                                    },
                                    {
                                        buttonContent: (
                                            <MessagesToggle
                                                open={false}
                                                count={this.props.messagesProps.count}
                                                countClass={this.props.messagesProps.countClass}
                                            />
                                        ),
                                        openButtonContent: (
                                            <MessagesToggle
                                                open={true}
                                                count={this.props.messagesProps.count}
                                                countClass={this.props.messagesProps.countClass}
                                            />
                                        ),
                                        panelContent: (
                                            <MessagesDropDown
                                                count={this.props.messagesProps.count}
                                                countClass={this.props.countsClass}
                                                data={this.props.messagesProps.data}
                                            />
                                        ),
                                    },
                                ]}
                            />
                        </div>
                    </Modal>
                )}
            </div>
        );
    }

    private open = () => {
        this.setState({
            open: true,
        });
    };
    private close = () => {
        this.setState({
            open: false,
        });
    };
}

const withRedux = connect(UsersModel.mapStateToProps);
export default withRedux(CompactMeBox);
