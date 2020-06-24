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

use Http\Psr7Test\ServerRequestIntegrationTest;
use Kuyoto\Psr7\ServerRequest;
use Kuyoto\Psr7\UploadedFile;

/**
 * Provides an integration test for ServerRequest.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class ServerRequestTest extends ServerRequestIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    protected $skippedTests = [
        'testGetCookieParams' => '',
        'testWithCookieParams' => '',
    ];

    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        return new ServerRequest($method, '', [], fopen('php://temp', 'r+'), $_SERVER);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildUploadableFile($data)
    {
        if (class_exists(UploadedFile::class)) {
            return new UploadedFile($data, strlen($data), UPLOAD_ERR_OK);
        }

        return parent::buildUploadableFile($data);
    }
}
