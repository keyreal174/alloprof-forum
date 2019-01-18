/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React, { MouseEvent, ChangeEvent } from "react";
import EmbedInsertionModule from "@rich-editor/quill/EmbedInsertionModule";
import { withEditor, IWithEditorProps } from "@rich-editor/components/context";
import { isFileImage } from "@library/utility";
import { image, attachment } from "@library/components/icons/editorIcons";

interface IProps extends IWithEditorProps {
    disabled?: boolean;
    type: "file" | "image";
    allowedMimeTypes: string[];
}

export class EditorUploadButton extends React.Component<IProps, {}> {
    private inputRef: React.RefObject<HTMLInputElement> = React.createRef();

    public render() {
        return (
            <button
                className="richEditor-button richEditor-embedButton richEditor-buttonUpload"
                type="button"
                aria-pressed="false"
                disabled={this.props.disabled}
                onClick={this.onFakeButtonClick}
            >
                {this.icon}
                <input
                    ref={this.inputRef}
                    onChange={this.onInputChange}
                    className="richEditor-upload"
                    type="file"
                    accept={this.inputAccepts}
                />
            </button>
        );
    }

    private isMimeTypeImage = (mimeType: string) => mimeType.startsWith("image/");

    private get icon(): React.ReactNode {
        switch (this.props.type) {
            case "file":
                return attachment();
            case "image":
                return image();
        }
    }

    private get inputAccepts(): string {
        switch (this.props.type) {
            case "file": {
                const types = this.props.allowedMimeTypes.filter(type => !this.isMimeTypeImage(type));
                return types.join(", ");
            }
            case "image": {
                const types = this.props.allowedMimeTypes.filter(this.isMimeTypeImage);
                return types.join(",");
            }
        }
    }

    private onFakeButtonClick = (event: MouseEvent<any>) => {
        if (this.inputRef && this.inputRef.current) {
            this.inputRef.current.click();
        }
    };

    private onInputChange = () => {
        // Grab the first file.
        const file =
            this.inputRef && this.inputRef.current && this.inputRef.current.files && this.inputRef.current.files[0];
        const embedInsertion =
            this.props.quill && (this.props.quill.getModule("embed/insertion") as EmbedInsertionModule);

        if (file && embedInsertion) {
            if (this.props.type === "image" && isFileImage(file)) {
                embedInsertion.createImageEmbed(file);
            } else {
                embedInsertion.createFileEmbed(file);
            }
        }
    };
}

export default withEditor<IProps>(EditorUploadButton);
