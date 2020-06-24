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

use Kuyoto\Psr7\Request;
use Kuyoto\Psr7\Stream;
use Kuyoto\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Provides a unit test for Request.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class RequestTest extends TestCase
{
    /**
     * @var Request The request instance
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->request = new Request('GET', '/', [], fopen('php://temp', 'wb+'));

        parent::setUp();
    }

    /**
     * RequestTest::testMethodIsGetByDefault().
     */
    public function testMethodIsGetByDefault(): void
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    /**
     * RequestTest::testMethodMutatorReturnsCloneWithChangedMethod().
     */
    public function testMethodMutatorReturnsCloneWithChangedMethod(): void
    {
        $request = $this->request->withMethod('POST');

        $this->assertNotSame($this->request, $request);
        $this->assertEquals('POST', $request->getMethod());
    }

    /**
     * RequestTest::testWithInvalidMethod().
     *
     * @param mixed $method an invalid HTTP method
     *
     * @dataProvider provideInvalidMethod
     */
    public function testWithInvalidMethod($method): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->request->withMethod($method);
    }

    /**
     * RequestTest::provideInvalidMethod().
     */
    public function provideInvalidMethod(): array
    {
        return [
            [null],
            [''],
        ];
    }

    /**
     * RequestTest::testWithUriReturnsNewInstanceWithNewUri().
     */
    public function testWithUriReturnsNewInstanceWithNewUri(): void
    {
        $request = $this->request->withUri(new Uri('https://example.com:10082/foo/bar?baz=bat'));

        $this->assertNotSame($this->request, $request);

        $request2 = $request->withUri(new Uri('/baz/bat?foo=bar'));

        $this->assertNotSame($this->request, $request2);
        $this->assertNotSame($request, $request2);
        $this->assertSame('/baz/bat?foo=bar', (string) $request2->getUri());
    }

    /**
     * RequestTest::testConstructorCanAcceptAllMessageParts().
     */
    public function testConstructorCanAcceptAllMessageParts(): void
    {
        $uri = new Uri('http://example.com/');
        $body = new Stream(fopen('php://memory', 'rw'));
        $headers = [
            'x-foo' => ['bar'],
        ];

        $request = new Request('POST', $uri, $headers, $body);

        $this->assertSame($uri, $request->getUri());
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame($body, $request->getBody());

        $testHeaders = $request->getHeaders();

        foreach ($headers as $key => $value) {
            $this->assertArrayHasKey($key, $testHeaders);
            $this->assertSame($value, $testHeaders[$key]);
        }
    }

    /**
     * RequestTest::testDefaultStreamIsWritable().
     */
    public function testDefaultStreamIsWritable(): void
    {
        $request = clone $this->request;

        $request->getBody()->write('test');

        $this->assertSame('test', (string) $request->getBody());
    }

    /**
     * RequestTest::testRequestTargetIsSlashWhenNoUriPresent().
     */
    public function testRequestTargetIsSlashWhenNoUriPresent(): void
    {
        $request = $this->request;

        $this->assertSame('/', $request->getRequestTarget());
    }

    /**
     * RequestTest::testRequestTargetIsSlashWhenUriHasNoPathOrQuery().
     */
    public function testRequestTargetIsSlashWhenUriHasNoPathOrQuery(): void
    {
        $request = $this->request->withUri(new Uri('http://example.com'));

        $this->assertSame('/', $request->getRequestTarget());
    }

    /**
     * RequestTest::testCanProvideARequestTarget().
     *
     * @param mixed $requestTarget the request target
     *
     * @dataProvider provideValidRequestTargets
     */
    public function testCanProvideARequestTarget($requestTarget): void
    {
        $request = $this->request->withRequestTarget($requestTarget);

        $this->assertSame($requestTarget, $request->getRequestTarget());
    }

    /**
     * RequestTest::provideValidRequestTargets().
     */
    public function provideValidRequestTargets(): array
    {
        return [
            'asterisk-form' => ['*'],
            'authority-form' => ['api.example.com'],
            'absolute-form' => ['https://api.example.com/users'],
            'absolute-form-query' => ['https://api.example.com/users?foo=bar'],
            'origin-form-path-only' => ['/users'],
            'origin-form' => ['/users?id=foo'],
        ];
    }

    /**
     * RequestTest::testRequestTargetCannotContainWhitespace().
     */
    public function testRequestTargetCannotContainWhitespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = $this->request;

        $request->withRequestTarget('foo bar baz');
    }

    /**
     * RequestTest::testGetHeadersContainsHostHeaderIfUriWithHostIsPresent().
     */
    public function testGetHeadersContainsHostHeaderIfUriWithHostIsPresent(): void
    {
        $request = $this->request->withUri(new Uri('http://example.com'));

        $headers = $request->getHeaders();

        $this->assertArrayHasKey('Host', $headers);
        $this->assertContains('example.com', $headers['Host']);
    }

    /**
     * RequestTest::testGetHeadersContainsNoHostHeaderIfUriWithHostIsDeleted().
     */
    public function testGetHeadersContainsNoHostHeaderIfUriWithHostIsDeleted(): void
    {
        $request = $this->request->withUri(new Uri('http://example.com'))->withoutHeader('host');

        $headers = $request->getHeaders();

        $this->assertArrayNotHasKey('Host', $headers);
    }

    /**
     * RequestTest::testGetHostHeaderReturnsUriHostWhenPresent().
     */
    public function testGetHostHeaderReturnsUriHostWhenPresent(): void
    {
        $request = $this->request->withUri(new Uri('http://example.com'));

        $this->assertSame(['example.com'], $request->getHeader('host'));
    }
}
