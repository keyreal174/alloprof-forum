import chalk from "chalk";

/**
 * Log something to STDOUT. Use this instead of console.log();
 *
 * @param contents - What to print out.
 */
export function print(contents: string) {
    if (process.env.NODE_ENV !== "test") {
        // tslint:disable-next-line:no-console
        console.log(contents);
    }
}

/**
 * Log something to STDOUT only if the verbose option is set. Use this instead of console.log();
 *
 * @param contents - What to print out.
 */
export function printVerbose(contents: string) {
    // @ts-ignore
    const isVerbose = global.verbose || false;

    if (isVerbose) {
        print(contents);
    }
}

/**
 * Log an error to STDERR. Colored red if ANSI codes are supported.
 *
 * @param error - The error or string to print out.
 */
export function printError(error: any) {
    // tslint:disable-next-line:no-console
    console.error(chalk.bold.red(error.toString()));
    throw error;
}

/**
 * Log an error to STDERR. Colored red if ANSI codes are supported.
 *
 * @param error - The error or string to print out.
 */
export function fail(error: any) {
    // tslint:disable-next-line:no-console
    console.error(chalk.bold.red(error.toString()));
    process.exit(1);
}
