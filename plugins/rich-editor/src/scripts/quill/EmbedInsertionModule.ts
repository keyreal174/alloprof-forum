/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import Module from "quill/core/module";
import Parchment from "parchment";
import Quill from "quill/core";
import api, { uploadFile } from "@library/apiv2";
import { getPastedFile, getDraggedFile } from "@library/dom";
import ExternalEmbedBlot, { IEmbedValue } from "@rich-editor/quill/blots/embeds/ExternalEmbedBlot";
import getStore from "@library/state/getStore";
import { getIDForQuill, insertBlockBlotAt } from "@rich-editor/quill/utility";
import { IStoreState } from "@rich-editor/@types/store";
import { isFileImage } from "@library/utility";

/**
 * A Quill module for managing insertion of embeds/loading/error states.
 */
export default class EmbedInsertionModule extends Module {
    private store = getStore<IStoreState>();

    constructor(public quill: Quill, options = {}, editorID) {
        super(quill, options);
        this.quill = quill;
        this.setupImageUploads();
    }

    private get state() {
        const id = getIDForQuill(this.quill);
        return this.store.getState().editor.instances[id];
    }

    /**
     * Initiate a media scrape, and insert the appropriate embed blots depending on response.
     *
     * @param url - The URL to scrape.
     */
    public scrapeMedia(url: string) {
        const formData = new FormData();
        formData.append("url", url);

        const scrapePromise = api.post("/media/scrape", formData).then(result => result.data);
        this.createEmbed({
            loaderData: {
                type: "link",
                link: url,
            },
            dataPromise: scrapePromise,
        });
    }

    /**
     * Create an async embed. The embed will be responsible for handling it's loading state and error states.
     */
    public createEmbed = (embedValue: IEmbedValue) => {
        const externalEmbed = Parchment.create("embed-external", embedValue) as ExternalEmbedBlot;
        const selection = this.state.lastGoodSelection || { index: this.quill.scroll.length(), length: 0 };
        insertBlockBlotAt(this.quill, selection.index, externalEmbed);
        this.quill.update(Quill.sources.USER);
        externalEmbed.focus();
    };

    private pasteHandler = (event: ClipboardEvent) => {
        const image = getPastedFile(event);
        if (image) {
            const imagePromise = uploadFile(image).then();
            this.createEmbed({ loaderData: { type: "image" }, dataPromise: imagePromise });
        }
    };

    private dragHandler = (event: DragEvent) => {
        const file = getDraggedFile(event);

        if (!file) {
            return;
        }

        if (isFileImage(file)) {
            this.createImageEmbed(file);
        } else {
            this.createFileEmbed(file);
        }
    };

    public createImageEmbed(file: File) {
        const imagePromise = uploadFile(file).then(data => {
            data.type = "image";
            return data;
        });
        this.createEmbed({ loaderData: { type: "image" }, dataPromise: imagePromise });
    }

    public createFileEmbed(file: File) {
        const filePromise = uploadFile(file).then(data => {
            return {
                url: data.url,
                type: "file",
                attributes: data,
            };
        });
        this.createEmbed({ loaderData: { type: "image" }, dataPromise: filePromise });
    }

    /**
     * Setup image upload listeners and handlers.
     */
    private setupImageUploads() {
        this.quill.root.addEventListener("drop", this.dragHandler, false);
        this.quill.root.addEventListener("paste", this.pasteHandler, false);
    }
}
