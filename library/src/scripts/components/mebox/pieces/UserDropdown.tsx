/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import { uniqueIDFromPrefix } from "@library/componentIDs";
import DropDown from "@library/components/dropdown/DropDown";
import { t } from "@library/application";
import FrameHeader from "@library/components/frame/FrameHeader";
import FrameBody from "@library/components/frame/FrameBody";
import FramePanel from "@library/components/frame/FramePanel";
import FrameFooter from "@library/components/frame/FrameFooter";
import Button, { ButtonBaseClass } from "@library/components/forms/Button";
import LinkAsButton from "@library/components/LinkAsButton";
import Frame from "@library/components/frame/Frame";
import { settings } from "@library/components/icons/common";
import { IUserFragment } from "@library/@types/api/users";
import { UserPhotoSize, UserPhoto } from "@library/components/mebox/pieces/UserPhoto";
import { logError } from "@library/utility";
import { connect } from "react-redux";
import UsersModel, { IInjectableUserState } from "@library/users/UsersModel";
import get from "lodash/get";

export interface ILinkSection {}

export interface IUserDropDownProps extends IInjectableUserState {
    className?: string;
    userInfo: IUserFragment;
    linkSections: ILinkSection[];
}

interface IState {
    hasUnread: false;
    open: boolean;
}

/**
 * Implements Messages Drop down for header
 */
export class UserDropDown extends React.Component<IUserDropDownProps, IState> {
    private id = uniqueIDFromPrefix("notificationsDropDown");

    public constructor(props) {
        super(props);
        this.state = {
            hasUnread: false,
            open: false,
        };
    }

    public render() {
        const userInfo: IUserFragment = get(this.props.userInfo, "currentUser.data", {
            name: null,
            userID: null,
            photoUrl: null,
        });
        return (
            <DropDown
                id={this.id}
                name={t("My Account")}
                buttonClassName={"vanillaHeader-account"}
                renderLeft={true}
                buttonContents={
                    <UserPhoto
                        userInfo={userInfo}
                        open={this.state.open}
                        className="headerDropDown-user"
                        size={UserPhotoSize.SMALL}
                    />
                }
                onVisibilityChange={this.setOpen}
            >
                <Frame>
                    <FrameHeader className="isShadowed" title={t("Notifications")}>
                        <LinkAsButton
                            title={t("Notification Preferences")}
                            className="headerDropDown-headerButton headerDropDown-messages button-pushRight"
                            to={`/profile/preferences/${userInfo.userID}`}
                            baseClass={ButtonBaseClass.TEXT}
                        >
                            {settings()}
                        </LinkAsButton>
                    </FrameHeader>
                    <FrameBody className="isSelfPadded">
                        <FramePanel>{t("Messages Here")}</FramePanel>
                    </FrameBody>
                    <FrameFooter className="isShadowed">
                        <LinkAsButton
                            className="headerDropDown-footerButton headerDropDown-allButton button-pushLeft"
                            to={"/kb/messages"}
                            baseClass={ButtonBaseClass.TEXT}
                        >
                            {t("All Messages")}
                        </LinkAsButton>
                        <Button
                            onClick={this.handleAllRead}
                            disabled={this.state.hasUnread}
                            baseClass={ButtonBaseClass.TEXT}
                        >
                            {t("Mark All Read")}
                        </Button>
                    </FrameFooter>
                </Frame>
            </DropDown>
        );
    }

    private handleAllRead = e => {
        alert("Todo!");
    };

    private setOpen = open => {
        this.setState({
            open,
        });
    };

    /**
     * @inheritdoc
     */
    public componentDidCatch(error, info) {
        logError(error, info);
    }
}

const withRedux = connect(UsersModel.mapStateToProps);
export default withRedux(UserPhoto);
