/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU GPL v2
 */

const path = require("path");
const webpackConfig = require("./webpack.test.config");
const VANILLA_ROOT = path.resolve(path.join(__dirname, "../../"));

const TEST_FILE_ROOTS = process.env.TEST_FILE_ROOTS || ["applications/*", "plugins/*"];

const files = [];
const preprocessors = {};

TEST_FILE_ROOTS.forEach(fileRoot => {
    const { normalize, join } = path;
    const tsPath = normalize(join(fileRoot, "src/scripts/**/*.test.ts"));
    const tsxPath = normalize(join(fileRoot, "src/scripts/**/*.test.tsx"));
    const setupPath = normalize(join(fileRoot, "src/scripts/__tests__/setup.ts"));

    files.push(setupPath);
    preprocessors[tsPath] = ["webpack", "sourcemap"];
    preprocessors[tsxPath] = ["webpack", "sourcemap"];
    preprocessors[setupPath] = ["webpack", "sourcemap"];
});

module.exports = config => {
    config.set({
        preprocessors,
        files,
        // base path, that will be used to resolve files and exclude
        basePath: VANILLA_ROOT,
        frameworks: ["mocha", "chai"],
        reporters: ["mocha"],
        // reporter options
        mochaReporter: {
            output: "minimal",
            showDiff: true,
        },
        logLevel: config.LOG_INFO,
        port: 9876, // karma web server port
        colors: true,
        mime: {
            "text/x-typescript": ["ts"],
        },
        browsers: ["ChromeHeadlessNoSandbox"],
        autoWatch: true,
        webpackMiddleware: {
            // webpack-dev-middleware configuration
            // i. e.
            stats: "errors-only",
        },
        webpack: webpackConfig,
        singleRun: false, // Karma captures browsers, runs the tests and exits
        concurrency: Infinity,
        // you can define custom flags
        customLaunchers: {
            ChromeHeadlessNoSandbox: {
                base: "ChromeHeadless",
                flags: ["--no-sandbox"],
            },
            ChromeDebug: {
                base: "Chrome",
                flags: ["--remote-debugging-port=9333"],
            },
        },
    });
};
