<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla;

use Garden\SafeCurl\SafeCurl;
use InvalidArgumentException;
use RuntimeException;
use Gdn_Upload;
use Vanilla\Formatting\Quill\Nesting\InvalidNestingException;

/**
 * Value object representing a file uploaded through an HTTP request.
 */
class UploadedFile {

    /** @var string */
    private $clientFileName;

    /** @var string */
    private $clientMediaType;

    /** @var int|null */
    private $clientHeight = null;

    /** @var int|null */
    private $clientWidth = null;

    /** @var int */
    private $error;

    /** @var string */
    private $file;

    /** @var string */
    private $moved = false;

    /** @var string|null */
    private $persistedUrl = null;

    /** @var int */
    private $size;

    /** @var  Gdn_Upload */
    private $uploadModel;

    /** @var array Constraints for the image resizer. */
    private $imageConstraints;

    /**
     * UploadedFile constructor.
     *
     * @param Gdn_Upload $uploadModel
     * @param string $file
     * @param int $size
     * @param int $error
     * @param string|null $clientFileName
     * @param string|null $clientMediaType
     */
    public function __construct(Gdn_Upload $uploadModel, $file, $size, $error, $clientFileName = null, $clientMediaType = null) {
        $this->uploadModel = $uploadModel;

        $this->setSize($size);
        $this->setError($error);
        $this->setClientFileName($clientFileName);
        $this->setClientMediaType($clientMediaType);

        if ($this->getError() === UPLOAD_ERR_OK) {
            $this->setFile($file);
        }
    }


    /**
     * Extract a name based on the URL of the file.
     *
     * @param string $url
     * @return string|null
     */
    private static function extractNameFromUrl(string $url): ?string {
        $keys = parse_url($url); // parse the url
        $path = explode("/", $keys['path']); // splitting the path
        $last = end($path) ?: null; // get the value of the last element
        return $last;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @throws RuntimeException Because it is not implemented yet.
     */
    public function getStream() {
        throw new RuntimeException(self::class.'::'.__FUNCTION__.' is not supported.');
    }

    /**
     * Get the upload path of the final file.
     *
     * @param string $persistSubDirectory
     * @param string $nameFormat
     * @return string
     */
    public function generatePersistedUploadPath(string $persistSubDirectory = '', string $nameFormat = '%s'): string {
        $persistDirectory = PATH_UPLOADS;
        if ($persistSubDirectory) {
            $persistDirectory .= "/${persistDirectory}";
        }
        $ext = strtolower(pathinfo($this->getClientFilename(), PATHINFO_EXTENSION));
        $baseName = basename($this->getClientFilename(), ".${ext}");
        $baseName = sprintf($nameFormat, $baseName);
        $uploadPath = FileUtils::generateUniqueUploadPath($ext, true, $baseName, $persistDirectory);
        return $uploadPath;
    }

    /**
     * Save the uploaded file to a persistent location.
     *
     * @param bool $copy Whether or not to copy the file instead of moving it.
     * @param string $persistSubDirectory
     * @param string $nameFormat
     *
     * @return $this For method chaining.
     */
    public function persistUpload(bool $copy = false, string $persistSubDirectory = '', string $nameFormat = '%s'): UploadedFile {
        $this->tryApplyImageProcessing();

        $result = $this->uploadModel->saveAs(
            $this->getFile(),
            $this->generatePersistedUploadPath($persistSubDirectory, $nameFormat),
            ["OriginalFilename" => $this->getClientFilename()],
            $copy
        );
        $this->setPersistedUrl($result['SaveName']);
        return $this;
    }

    /**
     * If the upload is an image, attempt to apply some processing to it.
     * This includes optional resizing and re-orienting, based on EXIF data.
     */
    private function tryApplyImageProcessing(): void {
        $file = $this->getFile();
        $size = getimagesize($file);

        if (empty($size)) {
            return;
            // we don't have an image.
        }

        [$width, $height] = $size;
        $options = [
            "crop" => false,
            "height" => $height ?? 0,
            "width" => $width ?? 0,
        ];

        if (\Gdn::config("ImageUpload.Limits.Enabled")) {
            if ($newWidth = filter_var(\Gdn::config("ImageUpload.Limits.Width"), FILTER_VALIDATE_INT)) {
                $options["width"] = $newWidth;
            }
            if ($newHeight = filter_var(\Gdn::config("ImageUpload.Limits.Height"), FILTER_VALIDATE_INT)) {
                $options["height"] = $newHeight;
            }
        }

        // Resize and re-orient the image as necessary.
        $resizer = new ImageResizer();
        $resizer->resize($file, null, $options);

        // Get the new details, after resizing and re-orienting the image.
        [$width, $height] = getimagesize($file);
        $this->setClientHeight($height);
        $this->setClientWidth($width);
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws InvalidArgumentException If the $targetPath specified is invalid.
     * @throws RuntimeException On any error during the move operation.
     * @throws RuntimeException On the second or subsequent call to the method.
     */
    public function moveTo($targetPath) {
        if ($this->moved) {
            throw new RuntimeException('This upload has already been moved.');
        }

        $directory = dirname($targetPath);
        if (!is_writable($directory)) {
            throw new InvalidArgumentException('The specified path is not writable.');
        }

        if (!is_uploaded_file($this->file)) {
            throw new RuntimeException("'{$this->file}' is not a valid upload.");
        }

        try {
            $this->uploadModel->saveAs($this->file, $targetPath);
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        $this->moved = true;
    }

    /**
     * Retrieve the file size.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * @return int One of the UPLOAD_ERR_XXX constants.
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Get the temporary filename associated with this uploaded file.
     *
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * @return string|null The filename sent by the client or null if none was provided.
     */
    public function getClientFilename() {
        return $this->clientFileName;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * @return string|null The media type sent by the client or null if none was provided.
     */
    public function getClientMediaType() {
        return $this->clientMediaType;
    }

    /**
     * Set the client-provided file name.
     *
     * @param string|null $clientFileName
     * @throws InvalidArgumentException If the value is invalid.
     */
    private function setClientFileName($clientFileName) {
        if ($clientFileName === null || is_string($clientFileName)) {
            $this->clientFileName = $clientFileName;
        } else {
            throw new InvalidArgumentException('Client file name must be a string or null.');
        }
    }

    /**
     * Set the client-provided media type.
     *
     * @param string|null $clientMediaType
     * @throws InvalidArgumentException If the value is invalid.
     */
    private function setClientMediaType($clientMediaType) {
        if ($clientMediaType === null || is_string($clientMediaType)) {
            $this->clientMediaType = $clientMediaType;
        } else {
            throw new InvalidArgumentException('Client media type must be a string or null.');
        }
    }

    /**
     * Set the error flag.
     *
     * @param int $error An error flag. Must be one of the UPLOAD_ERR_* constants.
     * @throws InvalidArgumentException If the value is invalid.
     */
    private function setError($error) {
        $validErrors = [
            UPLOAD_ERR_OK,
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION
        ];

        if (is_integer($error) && in_array($error, $validErrors)) {
            $this->error = $error;
        } else {
            throw new InvalidArgumentException('Error must be one of the UPLOAD_ERR_* constants.');
        }
    }

    /**
     * Set the temporary filename.
     *
     * @param string $file
     * @throws InvalidArgumentException If the value is invalid.
     */
    private function setFile($file) {
        if (is_string($file)) {
            $this->file = $file;
        } else {
            throw new InvalidArgumentException('File name must be a string.');
        }
    }

    /**
     * Set the file size.
     *
     * @param int $size The size of the file, in bytes.
     * @throws InvalidNestingException If the value is invalid.
     */
    private function setSize($size) {
        if (is_int($size)) {
            $this->size = $size;
        } else {
            throw new InvalidArgumentException('Size must be an integer.');
        }
    }

    /**
     * @return int|null
     */
    public function getClientHeight(): ?int {
        return $this->clientHeight;
    }

    /**
     * @param int|null $clientHeight
     */
    public function setClientHeight(?int $clientHeight): void {
        $this->clientHeight = $clientHeight;
    }

    /**
     * @return int|null
     */
    public function getClientWidth(): ?int {
        return $this->clientWidth;
    }

    /**
     * @param int|null $clientWidth
     */
    public function setClientWidth(?int $clientWidth): void {
        $this->clientWidth = $clientWidth;
    }

    /**
     * @return string|null
     */
    public function getPersistedUrl(): ?string {
        return $this->persistedUrl;
    }

    /**
     * @param string|null $persistedUrl
     */
    public function setPersistedUrl(?string $persistedUrl): void {
        $this->persistedUrl = $persistedUrl;
    }

    /**
     * Apply some image constraints for the resizer.
     *
     * @param array $imageConstraints
     *
     * @return $this
     */
    public function setImageConstraints(array $imageConstraints): UploadedFile {
        $this->imageConstraints = $imageConstraints;
        return $this;
    }
}
