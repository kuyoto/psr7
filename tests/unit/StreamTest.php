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

use Kuyoto\Psr7\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Provides a unit test for Stream.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class StreamTest extends TestCase
{
    /**
     * @var string the temporary file name
     */
    private $tmpnam;

    /**
     * @var Stream the stream instance
     */
    private $stream;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->tmpnam = null;
        $this->stream = new Stream(fopen('php://memory', 'wb+'));

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        if ($this->tmpnam && file_exists($this->tmpnam)) {
            unlink($this->tmpnam);
        }

        parent::tearDown();
    }

    /**
     * StreamTest::testCanInstantiateWithStreamIdentifier().
     */
    public function testCanInstantiateWithStreamIdentifier(): void
    {
        $this->assertInstanceOf(Stream::class, $this->stream);
    }

    /**
     * StreamTest::testCanInstantiteWithStreamResource().
     */
    public function testCanInstantiteWithStreamResource(): void
    {
        $resource = fopen('php://memory', 'wb+');

        $stream = new Stream($resource);

        $this->assertInstanceOf(Stream::class, $stream);
    }

    /**
     * StreamTest::testIsReadableReturnsFalseIfStreamIsNotReadable().
     */
    public function testIsReadableReturnsFalseIfStreamIsNotReadable(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');

        $stream = new Stream(fopen($this->tmpnam, 'w'));

        $this->assertFalse($stream->isReadable());
    }

    /**
     * StreamTest::testIsWritableReturnsFalseIfStreamIsNotWritable().
     */
    public function testIsWritableReturnsFalseIfStreamIsNotWritable(): void
    {
        $stream = new Stream(fopen('php://memory', 'r'));

        $this->assertFalse($stream->isWritable());
    }

    /**
     * StreamTest::testToStringRetrievesFullContentsOfStream().
     */
    public function testToStringRetrievesFullContentsOfStream(): void
    {
        $message = 'foo bar';

        $this->stream->write($message);

        $this->assertSame($message, (string) $this->stream);
    }

    /**
     * StreamTest::testDetachReturnsResource().
     */
    public function testDetachReturnsResource(): void
    {
        $resource = fopen('php://memory', 'wb+');

        $stream = new Stream($resource);

        $this->assertSame($resource, $stream->detach());
    }

    /**
     * StreamTest::testConstructorThrowsExceptionOnInvalidArgument().
     */
    public function testConstructorThrowsExceptionOnInvalidArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Stream(true);
    }

    /**
     * StreamTest::testCloseClosesResource().
     */
    public function testCloseClosesResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'wb+');

        $stream = new Stream($resource);
        $stream->close();

        $this->assertFalse(is_resource($resource));
    }

    /**
     * StreamTest::testCloseUnsetsResource().
     */
    public function testCloseUnsetsResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'wb+');

        $stream = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->detach());
    }

    /**
     * StreamTest::testCloseDoesNothingAfterDetach().
     */
    public function testCloseDoesNothingAfterDetach(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'wb+');

        $stream = new Stream($resource);
        $detached = $stream->detach();
        $stream->close();

        $this->assertTrue(is_resource($detached));
        $this->assertSame($resource, $detached);
    }

    /**
     * StreamTest::testSizeReportsNullWhenNoResourcePresent().
     */
    public function testSizeReportsNullWhenNoResourcePresent(): void
    {
        $this->stream->detach();

        $this->assertNull($this->stream->getSize());
    }

    /**
     * StreamTest::testTellReportsCurrentPositionInResource().
     */
    public function testTellReportsCurrentPositionInResource(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');

        file_put_contents($this->tmpnam, 'FOO BAR');

        $stream = new Stream(fopen($this->tmpnam, 'wb+'));
        $stream->seek(2);

        $this->assertSame(2, $stream->tell());
    }

    /**
     * StreamTest::testEofReportsFalseWhenNotAtEndOfStream().
     */
    public function testEofReportsFalseWhenNotAtEndOfStream(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');

        $stream = new Stream($resource);

        fseek($resource, 2);

        $this->assertFalse($stream->eof());
    }

    /**
     * StreamTest::testEofReportsTrueWhenAtEndOfStream().
     */
    public function testEofReportsTrueWhenAtEndOfStream(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $resource = fopen($this->tmpnam, 'wb+');

        $stream = new Stream($resource);

        while (!feof($resource)) {
            fread($resource, 4096);
        }

        $this->assertTrue($stream->eof());
    }

    /**
     * StreamTest::testGetSizeReturnsStreamSize().
     */
    public function testGetSizeReturnsStreamSize(): void
    {
        $resource = fopen(__FILE__, 'r');
        $expected = fstat($resource);

        $stream = new Stream($resource);

        $this->assertSame($expected['size'], $stream->getSize());
    }

    /**
     * StreamTest::testGetMetadataReturnsAllMetadataWhenNoKeyPresent().
     */
    public function testGetMetadataReturnsAllMetadataWhenNoKeyPresent(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'r+');

        $stream = new Stream($resource);

        $expected = stream_get_meta_data($resource);

        $this->assertSame($expected, $stream->getMetadata());
    }

    /**
     * StreamTest::testGetMetadataReturnsDataForSpecifiedKey().
     */
    public function testGetMetadataReturnsDataForSpecifiedKey(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'r+');

        $stream = new Stream($resource);

        $metadata = stream_get_meta_data($resource);

        $this->assertSame($metadata['uri'], $stream->getMetadata('uri'));
    }

    /**
     * StreamTest::testGetMetadataReturnsNullIfNoDataExistsForKey().
     */
    public function testGetMetadataReturnsNullIfNoDataExistsForKey(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'r+');

        $stream = new Stream($resource);

        $this->assertNull($stream->getMetadata('TOTALLY_MADE_UP'));
    }

    /**
     * StreamTest::testGetContentsShouldGetFullStreamContents().
     */
    public function testGetContentsShouldGetFullStreamContents(): void
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'fluct');
        $resource = fopen($this->tmpnam, 'r+');

        $stream = new Stream($resource);

        fwrite($resource, 'FooBar');

        // rewind, because current pointer is at end of stream!
        $stream->rewind();

        $this->assertSame('FooBar', $stream->getContents());
    }
}
