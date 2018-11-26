/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import ReactDOM from "react-dom";
import MeBox from "@library/components/mebox/MeBox";
import { dummyLogoData } from "./mebox/state/dummyLogoData";
import { dummyNotificationsData } from "@library/components/mebox/state/dummyNotificationsData";
import { dummyMessagesData } from "@library/components/mebox/state/dummyMessagesData";
import { dummyGuestNavigationData, dummyNavigationData } from "./mebox/state/dummyNavigationData";
import { Devices, IDeviceProps } from "@library/components/DeviceChecker";
import { withDevice } from "@library/contexts/DeviceContext";
import { dummyUserDropDownData } from "@library/components/mebox/state/dummyUserDropDownData";
import UsersModel, { IInjectableUserState } from "@library/users/UsersModel";
import classNames from "classnames";
import Container from "@library/components/layouts/components/Container";
import { PanelWidgetHorizontalPadding } from "@library/components/layouts/PanelLayout";
import HeaderLogo from "@library/components/mebox/pieces/HeaderLogo";
import VanillaHeaderNav from "@library/components/mebox/pieces/VanillaHeaderNav";
import CompactSearch from "@library/components/mebox/pieces/CompactSearch";
import CompactMeBox from "@library/components/mebox/pieces/CompactMeBox";
import { connect } from "react-redux";
import { INotificationsProps } from "@library/components/mebox/pieces/NotificationsContents";
import TabHandler from "@library/TabHandler";

interface IProps extends IDeviceProps, IInjectableUserState {
    container?: Element; // Element containing header. Should be the default most if not all of the time.
    className?: string;
    homePage?: boolean;
}

interface IState {
    openSearch: boolean;
    showingSuggestions: boolean;
}

/**
 * Implements Vanilla Header component. Note that this component uses a react portal.
 * That means the exact location in the page is not that important, since it will
 * render in a specific div in the default-master.
 */
export class VanillaHeader extends React.Component<IProps, IState> {
    private resultsRef: React.RefObject<HTMLDivElement> = React.createRef();
    public state = {
        openSearch: false,
        showingSuggestions: false,
    };
    public render() {
        const currentUser = this.props.currentUser.data;
        const isMobile = this.props.device === Devices.MOBILE;
        const isGuest = currentUser && UsersModel && currentUser.userID === UsersModel.GUEST_ID;
        const countClass = "vanillaHeader-count";
        const buttonClass = "vanillaHeader-button";

        const notificationProps = {
            data: dummyNotificationsData.data,
            userSlug: currentUser!.name,
            count: 108,
            countClass: classNames(countClass, "vanillaHeader-notificationsCount"),
        };

        const messagesProps = {
            ...dummyMessagesData,
            buttonClass,
            countClass: classNames(countClass, "vanillaHeader-messagesCount"),
        };

        return ReactDOM.createPortal(
            <header className={classNames("vanillaHeader", this.props.className)}>
                <Container>
                    <PanelWidgetHorizontalPadding>
                        <div className="vanillaHeader-bar">
                            {!this.state.openSearch && (
                                <HeaderLogo
                                    {...dummyLogoData}
                                    className="vanillaHeader-headerLogo hasRightMargin"
                                    logoClassName="vanillaHeader-logo"
                                />
                            )}
                            {!this.state.openSearch &&
                                !isMobile && (
                                    <VanillaHeaderNav
                                        {...dummyNavigationData}
                                        className="vanillaHeader-nav"
                                        linkClassName="vanillaHeader-navLink"
                                        linkContentClassName="vanillaHeader-navLinkContent"
                                    />
                                )}

                            <CompactSearch
                                className="vanillaHeader-compactSearch"
                                open={this.state.openSearch}
                                onOpenSearch={this.openSearch}
                                onCloseSearch={this.closeSearch}
                                cancelButtonClassName="vanillaHeader-searchCancel"
                                buttonClass="vanillaHeader-button"
                                resultsRef={this.resultsRef}
                                showingSuggestions={this.state.showingSuggestions}
                                onOpenSuggestions={this.setOpenSuggestions}
                                onCloseSuggestions={this.setCloseSuggestions}
                            />
                            {!isGuest && (
                                <React.Fragment>
                                    {!isMobile &&
                                        !this.state.openSearch && (
                                            <MeBox
                                                notificationsProps={notificationProps as INotificationsProps}
                                                messagesProps={messagesProps as any}
                                                counts={dummyUserDropDownData}
                                                buttonClassName="vanillaHeader-button"
                                                contentClassName="vanillaHeader-dropDownContents"
                                            />
                                        )}
                                    {isMobile &&
                                        !this.state.openSearch && (
                                            <CompactMeBox
                                                notificationsProps={notificationProps as INotificationsProps}
                                                messagesProps={messagesProps as any}
                                                counts={dummyUserDropDownData}
                                                buttonClass="vanillaHeader-button"
                                                userPhotoClass="headerDropDown-user"
                                            />
                                        )}
                                </React.Fragment>
                            )}
                            {isGuest && (
                                <VanillaHeaderNav
                                    {...dummyGuestNavigationData}
                                    linkClassName="vanillaHeader-navLink"
                                    linkContentClassName="vanillaHeader-navLinkContent"
                                    className="vanillaHeader-nav vanillaHeader-guestNav"
                                />
                            )}
                        </div>
                    </PanelWidgetHorizontalPadding>
                </Container>
                <div ref={this.resultsRef} className="vanillaHeader-results" />
            </header>,
            this.props.container || document.getElementById("vanillaHeader")!,
        );
    }

    public openSearch = () => {
        this.setState({
            openSearch: true,
        });
    };

    public closeSearch = () => {
        this.setState({
            openSearch: false,
        });
    };

    /**
     * Keep track of visibility of suggestions
     */
    public setOpenSuggestions = () => {
        this.setState({
            showingSuggestions: true,
        });
    };

    /**
     * Keep track of visibility of suggestions
     */
    public setCloseSuggestions = () => {
        this.setState({
            showingSuggestions: false,
        });
    };
}

const withRedux = connect(UsersModel.mapStateToProps);
export default withRedux(withDevice(VanillaHeader));
