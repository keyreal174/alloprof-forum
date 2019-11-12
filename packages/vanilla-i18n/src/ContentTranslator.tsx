/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import React from "react";
import { logError } from "@vanilla/utils";

export interface IContentTranslatorProps {
    isLoading?: boolean;
    isFullScreen?: boolean;
    properties: ITranslationProperty[];
    resource: string;
    afterSave: () => void; // You probably want to re-fetch your resource here, then close the modal.
    onDismiss: () => void; // You probably just want to close the modal.
}

// Subtypes
export enum TranslationPropertyType {
    TEXT = "text",
    TEXT_MULTILINE = "",
}

export interface ITranslationProperty {
    recordType: string; // Ex. knowledgeCategory
    recordID?: number; // Ex. 425
    recordKey?: string; // Ex. Garden.Description
    sourceText: string; // Ex. "Howdy Stranger"
    propertyName: string; // Ex. name
    propertyValidation: ITranslationPropertyValidation;
    propertyType: TranslationPropertyType;
}

export interface ITranslationPropertyValidation {
    minLength?: number;
    maxLength?: number;
}

export const NullContentTranslator: React.FC<IContentTranslatorProps> = props => {
    logError("Rendering a Null Content translator. Be sure to check `shouldDisplay` before rendering.");
    return null;
};
