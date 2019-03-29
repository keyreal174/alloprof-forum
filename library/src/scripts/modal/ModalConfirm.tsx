/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import classNames from "classnames";
import FrameHeader from "@library/layout/frame/FrameHeader";
import ButtonLoader from "@library/loaders/ButtonLoader";
import FramePanel from "@library/layout/frame/FramePanel";
import Frame from "@library/layout/frame/Frame";
import { getRequiredID } from "@library/utility/idUtils";
import ModalSizes from "@library/modal/ModalSizes";
import FrameBody from "@library/layout/frame/FrameBody";
import FrameFooter from "@library/layout/frame/FrameFooter";
import { t } from "@library/utility/appUtils";
import Button from "@library/forms/Button";
import { ButtonTypes } from "@library/forms/buttonStyles";
import SmartAlign from "@library/layout/SmartAlign";
import { frameBodyClasses, frameFooterClasses } from "@library/layout/frame/frameStyles";
import Modal from "@library/modal/Modal";
import { modalClasses } from "@library/modal/modalStyles";

interface IProps {
    title: string; // required for accessibility
    srOnlyTitle?: boolean;
    className?: string;
    onCancel?: () => void;
    onConfirm: () => void;
    confirmTitle?: string;
    children: React.ReactNode;
    isConfirmLoading?: boolean;
    elementToFocusOnExit: HTMLElement;
}

interface IState {
    cancelled: boolean;
}

/**
 * Basic confirm dialogue.
 */
export default class ModalConfirm extends React.Component<IProps, IState> {
    public static defaultProps: Partial<IProps> = {
        srOnlyTitle: false,
        confirmTitle: t("Ok"),
    };

    private cancelRef;
    private id;
    public state: IState = {
        cancelled: false,
    };

    constructor(props) {
        super(props);
        this.id = getRequiredID(props, "confirmModal");
        this.cancelRef = React.createRef();
    }

    public render() {
        if (this.state.cancelled) {
            return null;
        }
        const { onConfirm, srOnlyTitle, isConfirmLoading, title, children } = this.props;
        const onCancel = this.handleCancel;
        const classesFrameBody = frameBodyClasses();
        const classFrameFooter = frameFooterClasses();
        return (
            <Modal
                size={ModalSizes.SMALL}
                elementToFocus={this.cancelRef.current}
                exitHandler={onCancel}
                titleID={this.titleID}
                elementToFocusOnExit={this.props.elementToFocusOnExit}
            >
                <Frame>
                    <FrameHeader
                        titleID={this.titleID}
                        closeFrame={onCancel}
                        srOnlyTitle={srOnlyTitle!}
                        title={title}
                    />
                    <FrameBody>
                        <FramePanel>
                            <SmartAlign className={classNames("frameBody-contents", classesFrameBody.contents)}>
                                {children}
                            </SmartAlign>
                        </FramePanel>
                    </FrameBody>
                    <FrameFooter>
                        <Button
                            className={classFrameFooter.actionButton}
                            baseClass={ButtonTypes.TEXT}
                            buttonRef={this.cancelRef}
                            onClick={onCancel}
                        >
                            {t("Cancel")}
                        </Button>
                        <Button
                            className={classFrameFooter.actionButton}
                            onClick={onConfirm}
                            baseClass={ButtonTypes.TEXT_PRIMARY}
                            disabled={isConfirmLoading}
                        >
                            {isConfirmLoading ? <ButtonLoader /> : this.props.confirmTitle}
                        </Button>
                    </FrameFooter>
                </Frame>
            </Modal>
        );
    }

    private handleCancel = () => {
        this.setState({ cancelled: true });
        this.props.onCancel && this.props.onCancel();
    };

    public get titleID() {
        return this.id + "-title";
    }

    public componentDidMount() {
        this.forceUpdate();
    }
}
