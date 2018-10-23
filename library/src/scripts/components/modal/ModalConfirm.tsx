/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import { t } from "@library/application";
import Button from "@library/components/forms/Button";
import { Frame, FrameBody, FrameFooter, FrameHeader, FramePanel } from "@library/components/frame";
import SmartAlign from "@library/components/SmartAlign";
import ModalSizes from "@library/components/modal/ModalSizes";
import { getRequiredID } from "@library/componentIDs";
import Modal from "@library/components/modal/Modal";
import ButtonLoader from "@library/components/ButtonLoader";

interface IProps {
    title: string; // required for accessibility
    srOnlyTitle?: boolean;
    className?: string;
    onCancel: () => void;
    onConfirm: () => void;
    children: React.ReactNode;
    isConfirmLoading?: boolean;
}

interface IState {
    id: string;
}

/**
 * Basic confirm dialogue.
 */
export default class ModalConfirm extends React.Component<IProps, IState> {
    public static defaultProps = {
        srOnlyTitle: false,
    };

    private cancelRef;
    private id;

    constructor(props) {
        super(props);
        this.cancelRef = React.createRef();
        this.id = getRequiredID(props, "confirmModal");
    }

    public get titleID() {
        return this.id + "-title";
    }

    public render() {
        const { onCancel, onConfirm, srOnlyTitle, isConfirmLoading, title, children } = this.props;
        return (
            <Modal
                size={ModalSizes.SMALL}
                elementToFocus={this.cancelRef.current}
                exitHandler={onCancel}
                titleID={this.titleID}
            >
                <Frame>
                    <FrameHeader titleID={this.titleID} closeFrame={onCancel} srOnlyTitle={srOnlyTitle!}>
                        {title}
                    </FrameHeader>
                    <FrameBody>
                        <FramePanel>
                            <SmartAlign className="frameBody-contents">{children}</SmartAlign>
                        </FramePanel>
                    </FrameBody>
                    <FrameFooter>
                        <Button ref={this.cancelRef} onClick={onCancel}>
                            {t("Cancel")}
                        </Button>
                        <Button onClick={onConfirm} className="buttonPrimary" disabled={isConfirmLoading}>
                            {isConfirmLoading ? <ButtonLoader /> : t("Ok")}
                        </Button>
                    </FrameFooter>
                </Frame>
            </Modal>
        );
    }
}
