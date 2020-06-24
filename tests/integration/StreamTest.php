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

use Http\Psr7Test\StreamIntegrationTest;
use Kuyoto\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

/**
 * Provides an integration test for Stream.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class StreamTest extends StreamIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createStream($data)
    {
        return $this->buildStream($data);
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
                return new Stream(fopen('php://temp', 'wb+'));
        }

        return parent::buildStream($data);
    }
}
