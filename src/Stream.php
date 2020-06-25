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

/**
 * PHP stream implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class Stream implements StreamInterface
{
    /**
     * @var array an array of resource modes
     */
    private static $modes = [
        'readable' => [
            'r' => true,
            'w+' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'rb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'rt' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a+' => true,
        ],
        'writable' => [
            'w' => true,
            'w+' => true,
            'rw' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'wb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a' => true,
            'a+' => true,
        ],
    ];

    /**
     * @var resource the stream resource
     */
    private $body;

    /**
     * @var bool Is this stream readable?
     */
    private $readable = false;

    /**
     * @var bool Is this stream writable?
     */
    private $writable = false;

    /**
     * @var bool Is this stream seekable?
     */
    private $seekable = false;

    /**
     * @var null|int the size of the stream if known
     */
    private $size;

    /**
     * Constructor.
     *
     * @param resource $body a stream resource
     *
     * @throws \InvalidArgumentException on an invalid stream resource
     */
    public function __construct($body)
    {
        if (!is_resource($body)) {
            throw new \InvalidArgumentException('Invalid stream provided; must be a stream resource');
        }

        $this->body = $body;

        $metadata = stream_get_meta_data($this->body);

        $this->seekable = $metadata['seekable'];
        $this->readable = isset(static::$modes['readable'][$metadata['mode']]);
        $this->writable = isset(static::$modes['writable'][$metadata['mode']]);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Creates a new PSR-7 stream.
     *
     * @param null|resource|StreamInterface|string $body the body
     *
     * @throws \InvalidArgumentException
     */
    public static function create($body = ''): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (is_string($body) || $body === null) {
            $resource = fopen('php://temp', 'rw+');
            fwrite($resource, (string) $body);
            $body = $resource;
        }

        if (is_resource($body)) {
            return new self($body);
        }

        throw new \InvalidArgumentException('First argument to Stream::create() must be a string, resource or StreamInterface.');
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->body)) {
            $resource = $this->detach();
            fclose($resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->body;
        $this->body = null;
        $this->size = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return !$this->body || feof($this->body);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!isset($this->body)) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        $contents = stream_get_contents($this->body);

        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return stream_get_meta_data($this->body);
        }

        $metadata = stream_get_meta_data($this->body);

        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if ($this->body === null) {
            return null;
        }

        $stats = fstat($this->body);

        if (isset($stats['size'])) {
            return $stats['size'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        if ($length < 0) {
            throw new \InvalidArgumentException('Length parameter cannot be negative');
        }

        $result = fread($this->body, $length);

        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (fseek($this->body, $offset, $whence) === -1) {
            $message = sprintf('Unable to seek to stream position %s with whence ', var_export($whence, true));

            throw new \RuntimeException($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        $result = ftell($this->body);

        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        $this->size = null;

        $result = fwrite($this->body, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }
}
