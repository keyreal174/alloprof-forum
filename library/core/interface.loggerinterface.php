<?php
/**
 * Logger Interface
 *
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Core
 * @since 2.2
 */

/**
 * Describes a logger instance
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementers is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interface LoggerInterface {
    /**
     * System is unusable.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function emergency($message, array $context = []);

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function alert($message, array $context = []);

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function critical($message, array $context = []);

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function error($message, array $context = []);

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function warning($message, array $context = []);

    /**
     * Normal but significant events.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function notice($message, array $context = []);

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function info($message, array $context = []);

    /**
     * Detailed debug information.
     *
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function debug($message, array $context = []);

    /**
     * Log with an arbitrary level.
     *
     * @param string $level One of the constants on the {@link Logger} class.
     * @param string $message The message to log.
     * @param array $context Additional data to pass to the log entry.
     */
    public function log($level, $message, array $context = []);
}
