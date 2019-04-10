/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import KeyboardModule from "quill/modules/keyboard";
import { isAllowedUrl, t } from "@library/utility/appUtils";
import { getRequiredID, IRequiredComponentID } from "@library/utility/idUtils";
import { IWithEditorProps, withEditor } from "@rich-editor/editor/context";
import EmbedInsertionModule from "@rich-editor/quill/EmbedInsertionModule";
import { forceSelectionUpdate } from "@rich-editor/quill/utility";
import Button from "@library/forms/Button";
import { embed } from "@library/icons/editorIcons";
import classNames from "classnames";
import { richEditorClasses } from "@rich-editor/editor/richEditorClasses";
import { ButtonTypes } from "@library/forms/buttonStyles";
import { insertMediaClasses } from "@rich-editor/flyouts/pieces/insertMediaClasses";
import { IconForButtonWrap } from "@rich-editor/editor/pieces/IconForButtonWrap";
import DropDown from "@library/flyouts/DropDown";
import { Devices, IDeviceProps, withDevice } from "@library/layout/DeviceContext";
import FrameHeader from "@library/layout/frame/FrameHeader";
import FrameBody from "@library/layout/frame/FrameBody";
import FrameFooter from "@library/layout/frame/FrameFooter";
import Frame from "@library/layout/frame/Frame";
import { style } from "typestyle";

interface IProps extends IWithEditorProps, IDeviceProps {
    disabled?: boolean;
    renderAbove?: boolean;
    renderLeft?: boolean;
    legacyMode: boolean;
    closeMenuHandler: () => void;
}

interface IState extends IRequiredComponentID {
    id: string;
    url: string;
    isInputValid: boolean;
}

export class EmbedFlyout extends React.PureComponent<IProps, IState> {
    private embedModule: EmbedInsertionModule;
    private initalFocusRef: React.RefObject<any> = React.createRef();

    public constructor(props) {
        super(props);
        this.embedModule = props.quill.getModule("embed/insertion");
        this.state = {
            id: getRequiredID(props, "embedPopover"),
            url: "",
            isInputValid: false,
        };
    }

    get titleID(): string {
        return this.state.id + "-title";
    }

    get descriptionID(): string {
        return this.state.id + "-description";
    }

    public render() {
        const title = t("Insert Media");
        const classesRichEditor = richEditorClasses(this.props.legacyMode);
        const legacyMode = this.props.legacyMode;
        const classesInsertMedia = insertMediaClasses();
        const isMobile = this.props.device === Devices.MOBILE;
        return (
            <>
                <DropDown
                    id={this.state.id}
                    name={t("Insert Media")}
                    buttonClassName={classNames(
                        "richEditor-button",
                        "richEditor-embedButton",
                        classesRichEditor.button,
                    )}
                    title={t("Insert Media")}
                    paddedList={true}
                    onClose={this.clearInput}
                    onVisibilityChange={forceSelectionUpdate}
                    disabled={this.props.disabled}
                    buttonContents={<IconForButtonWrap icon={embed()} />}
                    buttonBaseClass={ButtonTypes.CUSTOM}
                    renderAbove={!!this.props.renderAbove}
                    renderLeft={!!this.props.renderLeft}
                    selfPadded={true}
                >
                    <Frame>
                        <FrameBody>
                            <p class={style({ marginTop: 6, marginBottom: 6 })}>
                                {t("Paste the URL of the media you want.")}
                            </p>
                            <input
                                className={classNames("InputBox", classesInsertMedia.insert, {
                                    inputText: !this.props.legacyMode,
                                })}
                                placeholder={t("http://")}
                                value={this.state.url}
                                onChange={this.inputChangeHandler}
                                onKeyDown={this.buttonKeyDownHandler}
                                aria-labelledby={this.titleID}
                                aria-describedby={this.descriptionID}
                                ref={this.initalFocusRef}
                            />
                        </FrameBody>
                        <FrameFooter>
                            {legacyMode ? (
                                <input
                                    type="button"
                                    className={classNames("Button Primary", "insertMedia-insert")}
                                    value={"Insert"}
                                    disabled={!this.state.isInputValid}
                                    aria-label={"Insert Media"}
                                    onClick={this.buttonClickHandler}
                                />
                            ) : (
                                <Button
                                    className={classNames("insertMedia-insert", classesInsertMedia.button)}
                                    baseClass={ButtonTypes.TEXT_PRIMARY}
                                    disabled={!this.state.isInputValid}
                                    onClick={this.buttonClickHandler}
                                >
                                    {t("Insert")}
                                </Button>
                            )}
                        </FrameFooter>
                    </Frame>
                </DropDown>
            </>
        );
    }

    private clearInput = () => {
        this.setState({
            url: "",
        });
    };

    private submitUrl() {
        this.clearInput();
        this.embedModule.scrapeMedia(this.normalizeUrl(this.state.url));
    }

    /**
     * Handle key-presses for the link toolbar.
     */
    private buttonKeyDownHandler = (event: React.KeyboardEvent<any>) => {
        if (KeyboardModule.match(event.nativeEvent, "enter")) {
            event.preventDefault();
            event.stopPropagation();
            this.state.isInputValid && this.submitUrl();
        }
    };

    /**
     * Handle a submit button click..
     */
    private buttonClickHandler = (event: React.MouseEvent<any>) => {
        event.preventDefault();
        this.submitUrl();
    };

    /**
     * Control the inputs value.
     */
    private inputChangeHandler = (event: React.ChangeEvent<any>) => {
        const url = event.target.value;
        const isInputValid = isAllowedUrl(this.normalizeUrl(url));
        this.setState({ url, isInputValid });
    };

    /**
     * Normalize the URL with a prepended http if there isn't one.
     */
    private normalizeUrl(url: string) {
        const result = url.match(/^https?:\/\//) ? url : "http://" + url;
        return result;
    }
}

export default withDevice(withEditor<IProps>(EmbedFlyout));
