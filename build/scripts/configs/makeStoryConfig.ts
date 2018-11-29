/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { Configuration } from "webpack";
import { makeBaseConfig } from "./makeBaseConfig";
import EntryModel from "../utility/EntryModel";
// tslint:disable
const TSDocgenPlugin = require("react-docgen-typescript-webpack-plugin");
const merge = require("webpack-merge");

/**
 * Create the storybook configuration.
 *
 * @param section - The section of the app to build. Eg. forum | admin | knowledge.
 */
export async function makeStoryConfig(baseStorybookConfig: any, entryModel: EntryModel) {
    const baseConfig: Configuration = await makeBaseConfig(entryModel, "storybook");
    baseConfig.mode = "development";
    baseConfig.optimization = {
        splitChunks: false,
    };
    // baseConfig.plugins!.push(new TSDocgenPlugin());
    return merge(baseStorybookConfig, baseConfig as any);
}
