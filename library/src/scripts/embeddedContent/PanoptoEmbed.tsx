/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { ensureScript } from "@vanilla/dom-utils";
import { EmbedContent } from "@library/embeddedContent/EmbedContent";
import { IBaseEmbedProps } from "@library/embeddedContent/embedService";
import React, { useLayoutEffect } from "react";
import { useThrowError } from "@vanilla/react-utils";
import { EmbedContainer } from "@library/embeddedContent/EmbedContainer";

interface IProps extends IBaseEmbedProps {
    sessionId: string;
    domain: string;
    width: number;
    height: number;
}

const PANOPTO_SCRIPT = "https://developers.panopto.com/scripts/embedapi.min.js";

/**
 * A class for rendering Twitter embeds.
 */
export function PanoptoEmbed(props: IProps): JSX.Element {
    const throwError = useThrowError();

    useLayoutEffect(() => {
        void convertPanoptoEmbeds().catch(throwError);
    });

    return (
        <>
            <EmbedContainer>
                <EmbedContent type={props.embedType}>
                    <div
                        className="panopto-media"
                        data-sessionid={props.sessionId}
                        data-domain={props.domain}
                        data-url={props.url}
                        data-height={props.height}
                        data-width={props.width}
                    >
                        <div id={"player-" + props.sessionId}></div>
                    </div>
                </EmbedContent>
            </EmbedContainer>
        </>
    );
}

/**
 * Convert all of the Panopto embeds in the page.
 */
async function convertPanoptoEmbeds() {
    const panoptoEmbeds = Array.from(document.querySelectorAll(".panopto-media"));

    if (panoptoEmbeds.length > 0) {
        await ensureScript(PANOPTO_SCRIPT);
        panoptoEmbeds.map(contentElement => {
            renderPanoptoEmbed(contentElement as HTMLElement);
        });
    }
}

/**
 * Render a single Panopto embed.
 */
async function renderPanoptoEmbed(element: HTMLElement) {
    const sessionId = element.getAttribute("data-sessionid");
    if (sessionId == null) {
        throw new Error("Attempted to embed a Panopto video but the sessionId could not be found.");
    }

    const domain = element.getAttribute("data-domain");
    if (domain == null) {
        throw new Error("Attempted to embed a Panopto video but the domain could not be found.");
    }

    const height = element.getAttribute("data-height");
    const width = element.getAttribute("data-width");

    let embedApi = new window.EmbedApi("player-" + sessionId, {
        width: width,
        height: height,
        serverName: domain,
        sessionId: sessionId,
    });

    embedApi.loadVideo();
}
