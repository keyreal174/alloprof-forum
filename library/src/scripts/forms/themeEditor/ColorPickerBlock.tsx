/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import ColorPicker, { IColorPicker } from "@library/forms/themeEditor/ColorPicker";
import ThemeBuilderBlock, { IThemeBuilderBlock } from "@library/forms/themeEditor/ThemeBuilderBlock";
import { uniqueIDFromPrefix } from "@library/utility/idUtils";
import { ColorHelper } from "csx";
import React, { useMemo } from "react";

export interface IPresetColorPicker extends Omit<Omit<IColorPicker, "inputID">, "labelID"> {
    defaultValue?: ColorHelper;
}

export interface IPresetThemeEditorInputBlock extends Omit<Omit<IThemeBuilderBlock, "children">, "labelID"> {}
type VoidFunction = () => void;
export interface IColorPickerBlock {
    colorPicker: IPresetColorPicker;
    inputBlock: IPresetThemeEditorInputBlock;
    handleChange?: VoidFunction;
}

export default function ColorPickerBlock(props: IColorPickerBlock) {
    const inputID = useMemo(() => {
        return uniqueIDFromPrefix("themeBuilderColorPickerInput");
    }, []);
    const labelID = useMemo(() => {
        return uniqueIDFromPrefix("themeBuilderColorPickerLabel");
    }, []);

    return (
        <ThemeBuilderBlock
            {...{
                ...props.inputBlock,
                inputID,
                labelID,
            }}
        >
            <ColorPicker
                {...{
                    ...props.colorPicker,
                    inputID,
                    labelID,
                }}
            />
        </ThemeBuilderBlock>
    );
}
