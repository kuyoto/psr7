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

namespace Kuyoto\Psr7\Integration;

use Http\Psr7Test\RequestIntegrationTest;
use Kuyoto\Psr7\Request;
use Kuyoto\Psr7\Stream;
use Kuyoto\Psr7\Uri;
use Psr\Http\Message\StreamInterface;

/**
 * Provides an integration test for Request.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class RequestTest extends RequestIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        return new Request($method, '', [], fopen('php://temp', 'r+'));
    }

    /**
     * {@inheritdoc}
     */
    protected function buildUri($uri)
    {
        if (class_exists(Uri::class)) {
            return new Uri($uri);
        }

        return parent::buildUri($uri);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildStream($data)
    {
        $type = gettype($data);

        switch (strtolower($type)) {
            case 'resource':
                return new Stream($data);
            case 'object':
                if ($data instanceof StreamInterface) {
                    return $data;
                }
                // no-break
                // no break
            default:
                return new Stream(fopen('php://temp', 'r'));
        }

        return parent::buildStream($data);
    }
}
