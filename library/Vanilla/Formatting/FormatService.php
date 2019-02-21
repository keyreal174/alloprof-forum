<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Formatting;

use Garden\Container\Container;
use Garden\Container\ContainerException;
use Vanilla\Contracts\Formatting\FormatInterface;
use Vanilla\Formatting\Exception\FormatterNotFoundException;
use Vanilla\Formatting\Formats\NotFoundFormat;

/**
 * Simple service for calling out to formatters registered in FormatFactory.
 */
class FormatService {

    /** @var array */
    private $formats = [];

    /**
     * Parse attachment data from a message.
     *
     * @param string $content
     * @param string $format
     * @return Attachment[]
     */
    public function parseAttachments(string $content, string $format): array {
        $formatter = $this->getFormatter($format);

        $result = $formatter->parseAttachments($content);
        return $result;
    }

    /**
     * Register a format type and the class name handles it.
     *
     * @param string $formatKey
     * @param FormatInterface $format
     */
    public function registerFormat(string $formatKey, FormatInterface $format) {
        $formatKey = strtolower($formatKey);
        $this->formats[$formatKey] = $format;
    }

    /**
     * Get an instance of a formatter.
     *
     * @param string $formatKey The key of the format to fetch.
     * @param bool $throw Whether or not to throw an exception if the format couldn't be found.
     *
     * @return FormatInterface
     * @throws FormatterNotFoundException If $throw === true &&  the formatter that was requested could not be found.
     */
    public function getFormatter(string $formatKey, $throw = false): FormatInterface {
        $formatKey = strtolower($formatKey);
        $format = $this->formats[$formatKey] ?? null;
        $errorMessage = "Unable to find a formatter for the formatKey $formatKey.";
        if (!$format) {
            if ($throw) {
                throw new FormatterNotFoundException($errorMessage);
            } else {
                return new NotFoundFormat($formatKey);
            }
        }

        return $format;
    }
}
