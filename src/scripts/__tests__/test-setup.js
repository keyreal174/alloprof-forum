/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

import { polyfillClosest } from "../bootstrap/polyfills";

// Because there is something weird happening in watch mode if this gets applied twice.
// Likely the JSDOM references get deleted and need to be referenced.
if (Element.prototype.closest) {
    delete Element.prototype.closest;
}

// Because JSDOM doesn't support this yet
// https://github.com/facebook/jest/issues/2028
polyfillClosest();
