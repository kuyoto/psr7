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

namespace Kuyoto\Psr7\Unit;

use Kuyoto\Psr7\ServerRequest;
use Kuyoto\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * Provides a unit test for ServerRequest.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class ServerRequestTest extends TestCase
{
    /**
     * @var ServerRequest the server request instance
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->request = new ServerRequest('GET', '/', [], fopen('php://temp', 'rw'));

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->request = null;

        parent::tearDown();
    }

    /**
     * ServerRequestTest::testServerParamsAreEmptyByDefault().
     */
    public function testServerParamsAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getServerParams());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testQueryParamsAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getQueryParams());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testQueryParamsMutatorReturnsCloneWithChanges(): void
    {
        $value = ['foo' => 'bar'];

        $request = $this->request->withQueryParams($value);

        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getQueryParams());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testCookiesAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getCookieParams());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testCookiesMutatorReturnsCloneWithChanges(): void
    {
        $value = ['foo' => 'bar'];

        $request = $this->request->withCookieParams($value);

        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getCookieParams());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testUploadedFilesAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getUploadedFiles());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testParsedBodyIsEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getParsedBody());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testParsedBodyMutatorReturnsCloneWithChanges(): void
    {
        $value = ['foo' => 'bar'];

        $request = $this->request->withParsedBody($value);

        $this->assertNotSame($this->request, $request);
        $this->assertSame($value, $request->getParsedBody());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testAttributesAreEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getAttributes());
    }

    /**
     * ServerRequestTest::testQueryParamsAreEmptyByDefault().
     */
    public function testSingleAttributesWhenEmptyByDefault(): void
    {
        $this->assertEmpty($this->request->getAttribute('does-not-exist'));
    }

    /**
     * ServerRequestTest::testAllowsRemovingAttributeWithNullValue().
     */
    public function testAllowsRemovingAttributeWithNullValue(): void
    {
        $request = $this->request;
        $request = $request->withAttribute('boo', null);
        $request = $request->withoutAttribute('boo');

        $this->assertSame([], $request->getAttributes());
    }

    /**
     * ServerRequestTest::testAllowsRemovingNonExistentAttribute().
     */
    public function testAllowsRemovingNonExistentAttribute(): void
    {
        $request = $this->request->withoutAttribute('boo');

        $this->assertSame([], $request->getAttributes());
    }

    /**
     * ServerRequestTest::testNestedUploadedFiles().
     */
    public function testNestedUploadedFiles(): void
    {
        $request = $this->request;

        $uploadedFiles = [
            [
                new UploadedFile('php://temp', 0, UPLOAD_ERR_OK),
                new UploadedFile('php://temp', 0, UPLOAD_ERR_OK),
            ],
        ];

        $request = $request->withUploadedFiles($uploadedFiles);

        $this->assertSame($uploadedFiles, $request->getUploadedFiles());
    }
}
