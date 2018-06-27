/**
 * @author Adam (charrondev) Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import { setData, getData, escapeHTML } from "@dashboard/dom";
import uniqueId from "lodash/uniqueId";
import { IEmbedData, renderEmbed, FOCUS_CLASS } from "@dashboard/embeds";
import FocusableEmbedBlot from "../abstract/FocusableEmbedBlot";
import ErrorBlot from "./ErrorBlot";
import { t } from "@dashboard/application";
import { logError } from "@dashboard/utility";
import LoadingBlot from "@rich-editor/quill/blots/embeds/LoadingBlot";
import { Blot } from "quill/core";

const DATA_KEY = "__embed-data__";

interface ILoaderData {
    type: "image" | "link";
    link?: string;
    loaded?: boolean;
}

interface IEmbedUnloadedValue {
    loaderData: ILoaderData;
    dataPromise: Promise<IEmbedData>;
}

interface IEmbedLoadedValue {
    loaderData: ILoaderData;
    data: IEmbedData;
}

const WARNING_HTML = title => `
<svg class="embedLinkLoader-failIcon" title="${title}" aria-label="${title}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
    <title>${title}</title>
    <circle cx="8" cy="8" r="8" style="fill: #f5af15"/>
    <circle cx="8" cy="8" r="7.5" style="fill: none;stroke: #000;stroke-opacity: 0.122"/>
    <path d="M11,10.4V8h2v2.4L12.8,13H11.3Zm0,4h2v2H11Z" transform="translate(-4 -4)" style="fill: #fff"/>
</svg>`;

export type IEmbedValue = IEmbedLoadedValue | IEmbedUnloadedValue;

/**
 * The primary entrypoint for rendering embeds in Quill.
 *
 * If you're trying to render an embed, you likely want to use the {EmbedInsertionModule}.
 */
export default class ExternalEmbedBlot extends FocusableEmbedBlot {
    public static blotName = "embed-external";
    public static className = "embed-external";
    public static tagName = "div";
    public static readonly LOADING_VALUE = { loading: true };

    /**
     * Create the initial HTML for the external embed.
     *
     * An embed always starts with a loader (even if its only there for a second).
     */
    public static create(value: IEmbedValue): HTMLElement {
        return LoadingBlot.create(value);
    }

    /**
     * Get the loading value, otherwise get the primary value.
     */
    public static value(element: Element) {
        const isLoader = element.classList.contains(LoadingBlot.className);
        if (isLoader) {
            return LoadingBlot.value(element);
        } else {
            const value = getData(element, DATA_KEY, false);
            return value;
        }
    }

    /**
     * Create a successful embed element.
     *
     * @throws {Error} If the rendering fails
     */
    public static async createEmbedFromData(data: IEmbedData): Promise<Element> {
        const rootNode = FocusableEmbedBlot.create(data);
        const embedNode = document.createElement("div");
        const descriptionNode = document.createElement("span");
        rootNode.classList.add("js-embed");
        rootNode.classList.remove(FOCUS_CLASS);
        descriptionNode.innerHTML = t("richEditor.externalEmbed.description");
        descriptionNode.classList.add("sr-only");
        descriptionNode.id = uniqueId("richEditor-embed-description-");

        embedNode.classList.add(FOCUS_CLASS);
        embedNode.setAttribute("aria-label", "External embed content - " + data.type);
        embedNode.setAttribute("aria-describedby", descriptionNode.id);

        rootNode.appendChild(embedNode);
        rootNode.appendChild(descriptionNode);

        await renderEmbed(embedNode, data);
        return rootNode;
    }

    /**
     * Create an warning state for the embed element. This occurs when the data fetching has succeeded,
     * but the browser rendering has not.
     *
     * In other words, the blot has all of the data in needs to render in another browser, but not the
     * current one.
     *
     * A usual case for this is having tracking protection on in Firefox (twitter + instagram scripts blocked) .
     *
     * @param linkText - The text of the link that failed to be embeded.
     */
    public static createEmbedWarningFallback(linkText: string) {
        const div = FocusableEmbedBlot.create();
        div.classList.remove(FOCUS_CLASS);
        div.classList.add("js-embed");
        div.classList.add("embedLinkLoader");
        div.classList.add("embedLinkLoader-error");
        div.classList.add(FOCUS_CLASS);

        const sanitizedText = escapeHTML(linkText);

        // In the future this message should point to a knowledge base article.
        const warningTitle = t("This embed could not be loaded in your browser.");
        div.innerHTML = `<a href="#" class="embedLinkLoader-link">${sanitizedText}&nbsp;${WARNING_HTML(
            warningTitle,
        )}</a>`;
        return div;
    }

    private loadCallback?: () => void;

    /**
     * This should only ever be called internally (or through Parchment.create())
     *
     * @param domNode - The node to attach the blot to.
     * @param value - The value the embed is being created with.
     * @param needsSetup - Whether or not replace with a final form. This should be false only for internal use.
     */
    constructor(domNode, value: IEmbedValue, needsSetup = true) {
        super(domNode);
        if (needsSetup) {
            void this.replaceLoaderWithFinalForm(value);
        }
    }

    /**
     * Replace the embed's loader with it's final state. This could take the form of a registered embed,
     * or an error state.
     *
     * @see @dashboard/embeds
     */
    public async replaceLoaderWithFinalForm(value: IEmbedValue) {
        let finalBlot: ExternalEmbedBlot | ErrorBlot;

        let data: IEmbedData | null = null;
        if ("data" in value) {
            data = value.data;
        } else {
            try {
                data = await value.dataPromise;
            } catch (e) {
                logError(e);
                this.replaceWith(new ErrorBlot(ErrorBlot.create(e)));
                if (this.loadCallback) {
                    this.loadCallback();
                    this.loadCallback = undefined;
                }
                return;
            }
        }

        let embedElement: Element;
        const newValue: IEmbedValue = {
            data,
            loaderData: {
                ...value.loaderData,
                loaded: true,
            },
        };

        try {
            embedElement = await ExternalEmbedBlot.createEmbedFromData(data);
        } catch (e) {
            logError(e);
            embedElement = ExternalEmbedBlot.createEmbedWarningFallback(data.url);
        }

        setData(embedElement, DATA_KEY, newValue);
        finalBlot = new ExternalEmbedBlot(embedElement, newValue, false);
        this.replaceWith(finalBlot);
        if (this.loadCallback) {
            this.loadCallback();
            this.loadCallback = undefined;
        }
    }

    /**
     * Register a callback for when the blot has been finalized.
     */
    public registerLoadCallback(callback: () => void) {
        this.loadCallback = callback;
    }
}
