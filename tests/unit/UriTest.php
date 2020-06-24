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

use Kuyoto\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Provides a unit test for Uri.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class UriTest extends TestCase
{
    /**
     * UriTest::testCanSerializeToString().
     */
    public function testCanSerializeToString(): void
    {
        $url = 'https://user:pass@local.example.com:3001/foo?bar=baz#quz';

        $uri = new Uri($url);

        $this->assertSame($url, (string) $uri);
    }

    /**
     * UriTest::testWithSchemeReturnsNewInstanceWithNewScheme().
     */
    public function testWithSchemeReturnsNewInstanceWithNewScheme(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('http');

        $this->assertNotSame($uri, $new);
        $this->assertSame('http', $new->getScheme());
        $this->assertSame('http://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * UriTest::testWithSchemeReturnsSameInstanceWithSameScheme().
     */
    public function testWithSchemeReturnsSameInstanceWithSameScheme(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withScheme('https');

        $this->assertSame($uri, $new);
        $this->assertSame('https', $new->getScheme());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * UriTest::testWithUserInfoReturnsNewInstanceWithProvidedUser().
     */
    public function testWithUserInfoReturnsNewInstanceWithProvidedUser(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('matthew');

        $this->assertNotSame($uri, $new);
        $this->assertSame('matthew', $new->getUserInfo());
        $this->assertSame('https://matthew@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * UriTest::testWithUserInfoReturnsNewInstanceWithProvidedUserAndPassword().
     */
    public function testWithUserInfoReturnsNewInstanceWithProvidedUserAndPassword(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('matthew', 'laminas');

        $this->assertNotSame($uri, $new);
        $this->assertSame('matthew:laminas', $new->getUserInfo());
        $this->assertSame('https://matthew:laminas@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * UriTest::testWithUserInfoReturnsSameInstanceIfUserAndPasswordAreSameAsBefore().
     */
    public function testWithUserInfoReturnsSameInstanceIfUserAndPasswordAreSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withUserInfo('user', 'pass');

        $this->assertSame($uri, $new);
        $this->assertSame('user:pass', $new->getUserInfo());
        $this->assertSame('https://user:pass@local.example.com:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * UriTest::testWithHostReturnsNewInstanceWithProvidedHost().
     */
    public function testWithHostReturnsNewInstanceWithProvidedHost(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('getlaminas.org');

        $this->assertNotSame($uri, $new);
        $this->assertSame('getlaminas.org', $new->getHost());
        $this->assertSame('https://user:pass@getlaminas.org:3001/foo?bar=baz#quz', (string) $new);
    }

    /**
     * UriTest::testWithHostReturnsSameInstanceWithProvidedHostIsSameAsBefore().
     */
    public function testWithHostReturnsSameInstanceWithProvidedHostIsSameAsBefore(): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withHost('local.example.com');

        $this->assertSame($uri, $new);
        $this->assertSame('local.example.com', $new->getHost());
    }

    /**
     * UriTest::testWithPortReturnsNewInstanceWithProvidedPort().
     *
     * @param mixed $port the uri port
     *
     * @dataProvider provideValidPorts
     */
    public function testWithPortReturnsNewInstanceWithProvidedPort($port): void
    {
        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $new = $uri->withPort($port);

        $this->assertNotSame($uri, $new);
        $this->assertEquals($port, $new->getPort());
        $this->assertSame(
            sprintf('https://user:pass@local.example.com%s/foo?bar=baz#quz', $port === null ? '' : ':'.$port),
            (string) $new
        );
    }

    /**
     * UriTest::provideValidPorts().
     */
    public function provideValidPorts(): array
    {
        return [
            'null' => [null],
            'int' => [3000],
        ];
    }

    /**
     * UriTest::testWithPortReturnsNewInstanceWithProvidedPort().
     *
     * @param mixed $port the uri port
     *
     * @dataProvider provideInvalidPorts
     */
    public function testWithPortRaisesExceptionForInvalidPorts($port): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $uri->withPort($port);
    }

    /**
     * UriTest::provideInvalidPorts().
     */
    public function provideInvalidPorts(): array
    {
        return [
            'true' => [true],
            'false' => [false],
            'string' => ['string'],
            'float' => [55.5],
            'array' => [[3000]],
            'too-small' => [-1],
            'too-big' => [65536],
            'string-int' => ['3000'],
        ];
    }

    /**
     * UriTest::testWithQueryRaisesExceptionForInvalidQueryStrings().
     *
     * @param mixed $query the uri query string
     *
     * @dataProvider provideInvalidQueryStrings
     */
    public function testWithQueryRaisesExceptionForInvalidQueryStrings($query): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $uri = new Uri('https://user:pass@local.example.com:3001/foo?bar=baz#quz');
        $uri->withQuery($query);
    }

    /**
     * UriTest::provideInvalidQueryStrings().
     */
    public function provideInvalidQueryStrings(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'array' => [['baz=bat']],
            'object' => [(object) ['baz=bat']],
        ];
    }

    /**
     * UriTest::testRetrievingAuthorityReturnsExpectedValues().
     *
     * @param string $url      the url
     * @param string $expected the expected url authority info
     *
     * @dataProvider provideAuthorityInfo
     */
    public function testRetrievingAuthorityReturnsExpectedValues($url, $expected): void
    {
        $uri = new Uri($url);
        $this->assertSame($expected, $uri->getAuthority());
    }

    /**
     * UriTest::provideAuthorityInfo().
     */
    public function provideAuthorityInfo(): array
    {
        return [
            'host-only' => ['http://foo.com/bar', 'foo.com'],
            'host-port' => ['http://foo.com:3000/bar', 'foo.com:3000'],
            'user-host' => ['http://me@foo.com/bar', 'me@foo.com'],
            'user-host-port' => ['http://me@foo.com:3000/bar', 'me@foo.com:3000'],
        ];
    }

    /**
     * UriTest::testMutatingWithUnsupportedSchemeRaisesAnException().
     *
     * @param mixed $scheme the uri scheme
     *
     * @dataProvider provideInvalidSchemes
     */
    public function testMutatingWithUnsupportedSchemeRaisesAnException($scheme): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $uri = new Uri('http://example.com');
        $uri->withScheme($scheme);
    }

    /**
     * UriTest::provideInvalidSchemes().
     */
    public function provideInvalidSchemes(): array
    {
        return [
            'mailto' => ['mailto'],
            'ftp' => ['ftp'],
            'telnet' => ['telnet'],
            'ssh' => ['ssh'],
            'git' => ['git'],
        ];
    }
}
