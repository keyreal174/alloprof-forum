/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPLv2
 */

/**
 * @type {boolean} The current debug setting.
 * @private
 */
let _debug = false;

/**
 * Get or set the debug flag.
 *
 * @param newValue - The new value of debug.
 * @returns the current debug setting.
 */
export function debug(newValue?: boolean): boolean {
    if (newValue !== undefined) {
        _debug = newValue;
    }

    return _debug;
}

type NormalCallback = (...args: any[]) => any;
type PromiseCallback = (...args: any[]) => Promise<any>;

export type PromiseOrNormalCallback = NormalCallback | PromiseCallback;

/**
 * Resolve an array of functions that return promises sequentially.
 *
 * @param promiseFunctions - The functions to execute.
 *
 * @returns An array of all results in sequential order.
 *
 * @example
 * const urls = ['/url1', '/url2', '/url3']
 * const functions = urls.map(url => () => fetch(url))
 * resolvePromisesSequentially(funcs)
 *   .then(console.log)
 *   .catch(console.error)
 */
export function resolvePromisesSequentially(promiseFunctions: PromiseOrNormalCallback[]): Promise<any[]> {
    if (!Array.isArray(promiseFunctions)) {
        throw new Error("First argument needs to be an array of Promises");
    }

    return new Promise((resolve, reject) => {
        let count = 0;
        let results = [];

        function iterationFunction(previousPromise, currentPromise) {
            return previousPromise
                .then(result => {
                    if (count++ !== 0) {
                        results = results.concat(result);
                    }

                    return currentPromise(result, results, count);
                })
                .catch(err => reject(err));
        }

        promiseFunctions = promiseFunctions.concat(() => Promise.resolve());

        promiseFunctions.reduce(iterationFunction, Promise.resolve(false)).then(() => {
            resolve(results);
        });
    });
}

/**
 * Log something to console.
 *
 * This only prints in debug mode.
 *
 * @param value - The value to log.
 */
export function log(...value: any[]) {
    if (_debug) {
        // tslint:disable-next-line:no-console
        console.log(...value);
    }
}

/**
 * Log an error to console.
 *
 * @param value - The value to log.
 */
export function logError(...value: any[]) {
    // tslint:disable-next-line:no-console
    console.error(...value);
}

/**
 * Log a warning to console.
 *
 * @param value - The value to log.
 */
export function logWarning(...value: any[]) {
    // tslint:disable-next-line:no-console
    console.warn(...value);
}

/**
 * A simple, fast method of hashing a string. Similar to Java's hash function.
 * https://stackoverflow.com/a/7616484/1486603
 *
 * @param str - The string to hash.
 *
 * @returns The hash code returned.
 */
export function hashString(str: string): number {
    function hashReduce(prevHash, currVal) {
        // tslint:disable-next-line:no-bitwise
        return (prevHash << 5) - prevHash + currVal.charCodeAt(0);
    }
    return str.split("").reduce(hashReduce, 0);
}

interface IClass {
    new (): any;
}

export function isInstanceOfOneOf(needle: any, haystack: IClass[]) {
    for (const classItem of haystack) {
        if (needle instanceof classItem) {
            return true;
        }
    }

    return false;
}

export function simplifyFraction(numerator: number, denominator: number) {
    const findGCD = (a, b) => {
        return b ? findGCD(b, a % b) : a;
    };
    const gcd = findGCD(numerator, denominator);

    numerator = numerator / gcd;
    denominator = denominator / gcd;

    return {
        numerator,
        denominator,
        shorthand: denominator + ":" + numerator,
    };
}

interface IMentionMatch {
    match: string;
    rawMatch: string;
}

/**
 * Custom matching to allow quotation marks in the matching string as well as spaces.
 * Spaces make things more complicated.
 *
 * @param subtext - The string to be tested.
 * @param shouldStartWithSpace - Should the pattern include a test for a whitespace prefix?
 * @returns Matching string if successful.  Null on failure to match.
 */
export function matchAtMention(subtext: string, shouldStartWithSpace: boolean = false): IMentionMatch | null {
    // Split the string at the lines to allow for a simpler regex.
    const lines = subtext.split("\n");
    const lastLine = lines[lines.length - 1];

    // If you change this you MUST change the regex in src/scripts/__tests__/legacy.test.js !!!
    /**
     * Put together the non-excluded characters.
     *
     * @param {boolean} excludeWhiteSpace - Whether or not to exclude whitespace characters.
     *
     * @returns {string} A Regex string.
     */
    function nonExcludedCharacters(excludeWhiteSpace) {
        let excluded =
            "[^" +
            '"' + // Quote character
            "\\u0000-\\u001f\\u007f-\\u009f" + // Control characters
            "\\u2028"; // Line terminator

        if (excludeWhiteSpace) {
            excluded += "\\s";
        }

        excluded += "]";
        return excluded;
    }

    let regexStr =
        "@" + // @ Symbol triggers the match
        "(" +
        // One or more non-greedy characters that aren't excluded. White is allowed, but a starting quote is required.
        '"(' +
        nonExcludedCharacters(false) +
        '+?)"?' +
        "|" + // Or
        // One or more non-greedy characters that aren't exluded. Whitespace is excluded.
        "(" +
        nonExcludedCharacters(true) +
        '+?)"?' +
        ")" +
        "(?:\\n|$)"; // Newline terminates.

    // Determined by at.who library
    if (shouldStartWithSpace) {
        regexStr = "(?:^|\\s)" + regexStr;
    }
    const regex = new RegExp(regexStr, "gi");
    const match = regex.exec(lastLine);
    if (match) {
        return {
            rawMatch: match[0],
            match: match[2] || match[1], // Return either of the matching groups (quoted or unquoted).
        };
    }

    // No match
    return null;
}
/**
 * Re-exported from sprintf-js https://www.npmjs.com/package/sprintf-js
 */
// export const sprintf = sprintfJs.sprintf;
