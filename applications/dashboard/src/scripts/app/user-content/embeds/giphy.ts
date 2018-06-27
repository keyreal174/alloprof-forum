/**
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0
 */

import { registerEmbed, IEmbedData, FOCUS_CLASS, IEmbedElements } from "@dashboard/embeds";

registerEmbed("giphy", giphyRenderer);

/**
 * Renders giphy embeds.
 */
export async function giphyRenderer(elements: IEmbedElements, data: IEmbedData) {
    const contentElement = elements.content;
    if (data.attributes.postID == null) {
        throw new Error("Giphy embed fail, the post could not be found");
    }
    const width = data.width + "px";
    contentElement.classList.add("embedGiphy");
    contentElement.style.width = data.width ? width : "100%";

    const paddingBottom = ((data.height || 1) / (data.width || 1)) * 100 + "%";
    const giphyWrapper = document.createElement("div");
    giphyWrapper.style.paddingBottom = paddingBottom;
    giphyWrapper.classList.add("embedExternal-ratio");

    const iframe = document.createElement("iframe");
    iframe.classList.add("giphy-embed");
    iframe.classList.add("embedGiphy-iframe");
    iframe.setAttribute("src", "https://giphy.com/embed/" + data.attributes.postID);

    giphyWrapper.appendChild(iframe);
    contentElement.appendChild(giphyWrapper);
}
