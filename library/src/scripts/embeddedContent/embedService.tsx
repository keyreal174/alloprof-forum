/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { mountReact } from "@library/dom/domUtils";
import { CodePenEmbed } from "@library/embeddedContent/CodePenEmbed";
import { FileEmbed } from "@library/embeddedContent/FileEmbed";
import { GiphyEmbed } from "@library/embeddedContent/GiphyEmbed";
import { ImgurEmbed } from "@library/embeddedContent/ImgurEmbed";
import { LinkEmbed } from "@library/embeddedContent/LinkEmbed";
import { QuoteEmbed } from "@library/embeddedContent/QuoteEmbed";
import { VideoEmbed } from "@library/embeddedContent/VideoEmbed";
import { TwitterEmbed } from "@library/embeddedContent/TwitterEmbed";
import { logWarning } from "@vanilla/utils";
import React from "react";

// Methods
export interface IBaseEmbedProps {
    // Stored data.
    embedType: string;
    url: string;
    name?: string;
    // Frontend only
    inEditor?: boolean;
    // onRenderComplete: () => void;
}

type EmbedComponentType = React.ComponentType<IBaseEmbedProps>;

const registeredEmbeds = new Map<string, EmbedComponentType>();

export function registerEmbed(embedType: string, EmbedComponent: EmbedComponentType) {
    registeredEmbeds.set(embedType, EmbedComponent);
}

export function getEmbedForType(embedType: string): EmbedComponentType | null {
    return registeredEmbeds.get(embedType) || null;
}

export function mountEmbed(mountPoint: HTMLElement, data: IBaseEmbedProps, inEditor: boolean, callback?: () => void) {
    const type = data.embedType || null;
    if (type === null) {
        logWarning(`Found embed with data`, data, `and no type on element`, mountPoint);
        return;
    }
    const EmbedClass = getEmbedForType(type);
    if (EmbedClass === null) {
        logWarning(
            `Attempted to mount embed type ${type} on element`,
            mountPoint,
            `but could not find registered embed.`,
        );
        return;
    }

    mountReact(<EmbedClass {...data} inEditor={inEditor} />, mountPoint, callback);
}

export function mountAllEmbeds(root: HTMLElement = document.body) {
    const mountPoints = root.querySelectorAll("[data-embedjson]");
    for (const mountPoint of mountPoints) {
        const parsedData = JSON.parse(mountPoint.getAttribute("data-embedjson") || "{}");
        mountEmbed(mountPoint as HTMLElement, parsedData, false);
    }
}

// Default embed registration
registerEmbed("codepen", CodePenEmbed);
registerEmbed("file", FileEmbed);
registerEmbed("giphy", GiphyEmbed);
registerEmbed("imgur", ImgurEmbed);
registerEmbed("link", LinkEmbed);
registerEmbed("quote", QuoteEmbed);
registerEmbed("twitch", VideoEmbed);
registerEmbed("twitter", TwitterEmbed);
registerEmbed("vimeo", VideoEmbed);
registerEmbed("wistia", VideoEmbed);
registerEmbed("youtube", VideoEmbed);
