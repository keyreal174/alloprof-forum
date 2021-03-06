/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import classNames from "classnames";
import EmbedInsertionModule from "@rich-editor/quill/EmbedInsertionModule";
import { IWithEditorProps } from "@rich-editor/editor/context";
import { withEditor } from "@rich-editor/editor/withEditor";
import { isFileImage } from "@vanilla/utils";
import { richEditorClasses } from "@rich-editor/editor/richEditorStyles";
import { IconForButtonWrap } from "@rich-editor/editor/pieces/IconForButtonWrap";
import { AttachmentIcon, ImageIcon } from "@library/icons/editorIcons";
import { getMeta } from "@library/utility/appUtils";
import { visibility } from "@library/styles/styleHelpersVisibility";
import { t } from "@vanilla/i18n/src";
import ScreenReaderContent from "@library/layout/ScreenReaderContent";

interface IProps extends IWithEditorProps {
    disabled?: boolean;
    type: "file" | "image";
    allowedMimeTypes: string[];
    legacyMode: boolean;
}

export class EditorUploadButton extends React.Component<IProps, { uploadCount: number }> {
    public state = {
        uploadCount: 0,
    };
    private inputRef: React.RefObject<HTMLInputElement> = React.createRef();

    public render() {
        const classesRichEditor = richEditorClasses(this.props.legacyMode);

        const text = this.props.type === "image" ? t("Upload Image") : t("Upload File");

        return (
            <button
                className={classNames(
                    "richEditor-button",
                    "richEditor-embedButton",
                    "richEditor-buttonUpload",
                    'builtin',
                    classesRichEditor.button,
                )}
                type="button"
                disabled={this.props.disabled}
                onClick={this.onFakeButtonClick}
                title={text}
            >
                <ScreenReaderContent>{text}</ScreenReaderContent>
                <IconForButtonWrap icon={this.icon} />
                <input
                    key={this.state.uploadCount}
                    ref={this.inputRef}
                    onChange={this.onInputChange}
                    className={classNames("richEditor-upload", classesRichEditor.upload)}
                    multiple
                    type="file"
                    accept={this.inputAccepts}
                />
            </button>
        );
    }

    /**
     * Determine if a particular mime type is an image mimeType.
     */
    private isMimeTypeImage = (mimeType: string) => mimeType.startsWith("image/");

    /**
     * Get the icon to display for the input.
     */
    private get icon(): JSX.Element {
        switch (this.props.type) {
            case "file":
                return <AttachmentIcon />;
            case "image":
                return (
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="1" y="1" width="22" height="22" rx="2" stroke="black" stroke-width="2"/>
                        <path d="M0.922852 13.5637L5.11815 9.36836C5.70393 8.78258 6.65368 8.78258 7.23947 9.36836L20.948 23.0769" stroke="black" stroke-width="2"/>
                        <path d="M12.3867 15.0228L15.8414 11.5681C16.6224 10.787 17.8888 10.787 18.6698 11.5681L23.0766 15.9749" stroke="black" stroke-width="2"/>
                    </svg>
                );
        }
    }

    /**
     * Get an "accepts" mimeTypes string for the file upload input.
     */
    private get inputAccepts(): string {
        switch (this.props.type) {
            case "file": {
                const types = this.props.allowedMimeTypes.filter((type) => !this.isMimeTypeImage(type));
                return types.join(", ");
            }
            case "image": {
                const types = this.props.allowedMimeTypes.filter(this.isMimeTypeImage);
                return types.join(",");
            }
        }
    }

    /**
     * Pass through our fake button to be a click on the file upload (which can't be styled).
     */
    private onFakeButtonClick = (event: React.MouseEvent<any>) => {
        if (this.inputRef && this.inputRef.current) {
            this.inputRef.current.click();
        }
    };

    /**
     * Handle the change of the file upload input.
     */
    private onInputChange = () => {
        const files =
            this.inputRef && this.inputRef.current && this.inputRef.current.files && this.inputRef.current.files;
        const embedInsertion =
            this.props.quill && (this.props.quill.getModule("embed/insertion") as EmbedInsertionModule);
        const maxUploads = getMeta("upload.maxUploads", 20);

        if (files && embedInsertion) {
            // Increment the upload count to reset the input.
            this.setState({ uploadCount: this.state.uploadCount + 1 });
            const filesArray = Array.from(files);
            if (filesArray.length >= maxUploads) {
                const error = new Error(`Can't upload more than ${maxUploads} files at once.`);
                embedInsertion.createErrorEmbed(error);
                throw error;
            }

            filesArray.forEach((file) => {
                if (this.props.type === "image" && isFileImage(file)) {
                    embedInsertion.createImageEmbed(file);
                } else {
                    embedInsertion.createFileEmbed(file);
                }
            });
        }
    };
}

export default withEditor<IProps>(EditorUploadButton);
