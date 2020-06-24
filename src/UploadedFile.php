<?php

/**
 * PSR-7 (https://github.com/kuyoto/psr7).
 *
 * PHP version 7
 *
 * @category  Library
 *
 * @author    Tolulope Kuyoro <nifskid1999@gmail.com>
 * @copyright 2020 Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license   https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */

declare(strict_types=1);

namespace Kuyoto\Psr7;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * PHP UploadedFile implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class UploadedFile implements UploadedFileInterface
{
    /**
     * @var array a map of upload errors
     */
    private const ERRORS = [
        UPLOAD_ERR_OK => 1,
        UPLOAD_ERR_INI_SIZE => 1,
        UPLOAD_ERR_FORM_SIZE => 1,
        UPLOAD_ERR_PARTIAL => 1,
        UPLOAD_ERR_NO_FILE => 1,
        UPLOAD_ERR_NO_TMP_DIR => 1,
        UPLOAD_ERR_CANT_WRITE => 1,
        UPLOAD_ERR_EXTENSION => 1,
    ];

    /**
     * @var string the client filename
     */
    private $clientFilename;

    /**
     * @var string the client media type
     */
    private $clientMediaType;

    /**
     * @var int the associated error
     */
    private $error;

    /**
     * @var string the original file
     */
    private $file;

    /**
     * @var StreamInterface The stream.
     */
    private $body;

    /**
     * @var bool is the file moved
     */
    private $moved = false;

    /**
     * @var int the file size
     */
    private $size;

    /**
     * Constructor.
     *
     * @param StreamInterface|string $streamOrFile    the stream or path to the file
     * @param null|int               $size            the file size in bytes
     * @param null|string            $clientFilename  the file name
     * @param null|string            $clientMediaType the file media type
     * @param int                    $errorStatus     the UPLOAD_ERR_XXX code representing the status of the upload
     *
     * @throws \InvalidArgumentException on an invalid upload error status code
     * @throws \InvalidArgumentException on an invalid stream or file path
     */
    public function __construct($streamOrFile, int $size, int $errorStatus, ?string $clientFilename = null, ?string $clientMediaType = null)
    {
        if (!isset(self::ERRORS[$errorStatus])) {
            throw new \InvalidArgumentException('Upload file error status must be an integer value and one of the "UPLOAD_ERR_*" constants.');
        }

        $this->size = $size;
        $this->error = $errorStatus;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;

        if ($this->error === UPLOAD_ERR_OK) {
            if (is_string($streamOrFile)) {
                $this->file = $streamOrFile;
            } elseif ($streamOrFile instanceof StreamInterface) {
                $this->body = $streamOrFile;
            } else {
                throw new \InvalidArgumentException('Invalid stream or file provided');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->moved) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }

        if ($this->body instanceof StreamInterface) {
            return $this->body;
        }

        return new Stream(fopen($this->file, 'r'));
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath)
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->moved) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }

        if (!is_string($targetPath) || $targetPath === '') {
            throw new \InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }

        if ($this->file !== null) {
            $this->moved = (PHP_SAPI === 'cli') ? rename($this->file, $targetPath) : move_uploaded_file($this->file, $targetPath);
        } else {
            $dest = new Stream(fopen($targetPath, 'w'));
            $source = $this->getStream();

            if ($source->isSeekable()) {
                $source->rewind();
            }

            // Copy the contents of a stream into another stream until end-of-file.
            while (!$source->eof()) {
                if (!$dest->write($source->read(1048576))) {
                    break;
                }
            }

            $this->moved = true;
        }

        if ($this->moved === false) {
            throw new \RuntimeException(sprintf('Uploaded file could not be moved to %s', $targetPath));
        }
    }
}
