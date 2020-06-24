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

namespace Kuyoto\Psr7\Factory;

use Kuyoto\Psr7\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-17 stream factory implementation.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 */
final class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rw+');

        if (!is_resource($resource)) {
            throw new \RuntimeException('Unable to open temporary file stream.');
        }

        if ($content !== '') {
            fwrite($resource, $content);
            fseek($resource, 0);
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if ($filename === '') {
            throw new \RuntimeException('The filename cannot be empty');
        }

        $resource = @fopen($filename, $mode);

        if (!is_resource($resource)) {
            throw new \RuntimeException(sprintf('Unable create resource from file "%s"', $filename));
        }

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new \InvalidArgumentException('Invalid stream provided; must be a stream resource');
        }

        return new Stream($resource);
    }
}
