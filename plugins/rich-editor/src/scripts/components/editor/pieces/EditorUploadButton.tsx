/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import React, { MouseEvent, ChangeEvent } from "react";
import { hasPermission } from "@dashboard/permissions";
import * as Icons from "@rich-editor/components/icons";
import EmbedInsertionModule from "@rich-editor/quill/EmbedInsertionModule";
import { withEditor, IEditorContextProps } from "@rich-editor/components/context";
import { uploadImage } from "@dashboard/apiv2";
import { isFileImage } from "@dashboard/utility";

interface IProps extends IEditorContextProps {}

export class UploadButton extends React.Component<IProps, {}> {
    private inputRef: React.RefObject<HTMLInputElement> = React.createRef();

    public render() {
        return (
            <button
                className="richEditor-button richEditor-embedButton richEditor-buttonUpload js-fakeFileUpload"
                type="button"
                aria-pressed="false"
                onClick={this.onFakeButtonClick}
            >
                <Icons.image />
                <input
                    ref={this.inputRef}
                    onChange={this.onInputChange}
                    className="js-fileUpload richEditor-upload"
                    type="file"
                    accept="image/gif, image/jpeg, image/jpg, image/png"
                />
            </button>
        );
    }

    private onFakeButtonClick = (event: MouseEvent<any>) => {
        if (this.inputRef && this.inputRef.current) {
            this.inputRef.current.click();
        }
    };

    private onInputChange = (event: ChangeEvent<any>) => {
        // Grab the first file.
        const file =
            this.inputRef && this.inputRef.current && this.inputRef.current.files && this.inputRef.current.files[0];
        const embedInsertion =
            this.props.quill && (this.props.quill.getModule("embed/insertion") as EmbedInsertionModule);

        if (file && isFileImage(file) && embedInsertion) {
            const imagePromise = uploadImage(file);
            embedInsertion.createEmbed(imagePromise);
        }
    };
}

export default withEditor<IProps>(UploadButton);
