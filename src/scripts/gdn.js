/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

// import * as utility from "@core/utility";

/** The gdn object may be set in an inline script in the head of the document. */
const gdn = window['gdn'] || {};

if (!('meta' in gdn)) {
    gdn.meta = {};
}

if (!('permissions' in gdn)) {
    gdn.permissions = {};
}

if (!('translations' in gdn)) {
    gdn.translations = {};
}

// Wrap like this because we can't import utility (cyclical dependency)/
if (gdn.meta["debug"]) {
    // eslint-disable-next-line no-console
    console.log(gdn);
}

export default gdn;
