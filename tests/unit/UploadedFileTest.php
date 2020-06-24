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
use Kuyoto\Psr7\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * Provides a unit test for UploadedFile.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class UploadedFileTest extends TestCase
{
    /**
     * @var string the temporary file created
     */
    private $tmpFile;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->tmpFile = null;

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        if (is_string($this->tmpFile) && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }

        parent::tearDown();
    }

    /**
     * UploadedFileTest::testRaisesExceptionOnInvalidStreamOrFile().
     *
     * @param string $file the file path
     *
     * @dataProvider provideInvalidStreams
     */
    public function testRaisesExceptionOnInvalidStreamOrFile($file): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UploadedFile(new Stream($file), 0, UPLOAD_ERR_OK);
    }

    /**
     * UploadedFileTest::provideInvalidStreams().
     */
    public function provideInvalidStreams(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'array' => [['filename']],
            'object' => [(object) ['filename']],
        ];
    }

    /**
     * UploadedFileTest::testRaisesExceptionOnInvalidErrorStatus().
     *
     * @param mixed $status an invalid upload error status
     *
     * @dataProvider provideInvalidErrorStatuses
     */
    public function testRaisesExceptionOnInvalidErrorStatus($status): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Upload file error status must be an integer value and one of the "UPLOAD_ERR_*" constants.');

        new UploadedFile(new Stream(fopen('php://temp', 'wb+')), 0, $status, null, null);
    }

    /**
     * UploadedFileTest::provideInvalidErrorStatuses().
     */
    public function provideInvalidErrorStatuses(): array
    {
        return [
            'negative' => [-1],
            'too-big' => [9],
        ];
    }

    /**
     * UploadedFileTest::testValidSize().
     */
    public function testValidSize(): void
    {
        $uploaded = new UploadedFile(new Stream(fopen('php://temp', 'wb+')), 123, UPLOAD_ERR_OK);

        $this->assertSame(123, $uploaded->getSize());
    }

    /**
     * UploadedFileTest::testValidClientFilename().
     */
    public function testValidClientFilename(): void
    {
        $file = new UploadedFile(new Stream(fopen('php://temp', 'wb+')), 0, UPLOAD_ERR_OK, 'boo.txt');

        $this->assertSame('boo.txt', $file->getClientFilename());
    }

    /**
     * UploadedFileTest::testValidNullClientFilename().
     */
    public function testValidNullClientFilename(): void
    {
        $file = new UploadedFile(new Stream(fopen('php://temp', 'wb+')), 0, UPLOAD_ERR_OK);

        $this->assertSame(null, $file->getClientFilename());
    }

    /**
     * UploadedFileTest::testValidClientMediaType().
     */
    public function testValidClientMediaType(): void
    {
        $file = new UploadedFile(new Stream(fopen('php://temp', 'wb+')), 0, UPLOAD_ERR_OK, 'foobar.baz', 'mediatype');

        $this->assertSame('mediatype', $file->getClientMediaType());
    }

    /**
     * UploadedFileTest::testGetStreamReturnsOriginalStreamObject().
     */
    public function testGetStreamReturnsOriginalStreamObject(): void
    {
        $stream = new Stream(fopen('php://temp', 'wb+'));
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->assertSame($stream, $upload->getStream());
    }

    /**
     * UploadedFileTest::testGetStreamReturnsWrappedPhpStream().
     */
    public function testGetStreamReturnsWrappedPhpStream(): void
    {
        $stream = fopen('php://temp', 'wb+');
        $upload = new UploadedFile(new Stream($stream), 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();

        $this->assertSame($stream, $uploadStream);
    }

    /**
     * UploadedFileTest::testMovesFileToDesignatedPath().
     */
    public function testMovesFileToDesignatedPath(): void
    {
        file_put_contents('php://temp', 'Foo bar!');

        $stream = new Stream(fopen('php://temp', 'wb+'));

        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'fluct');

        $upload->moveTo($to);

        $this->assertTrue(file_exists($to));
        $this->assertSame((string) $stream, file_get_contents($to));
    }

    /**
     * UploadedFileTest::testMoveCannotBeCalledMoreThanOnce().
     */
    public function testMoveCannotBeCalledMoreThanOnce(): void
    {
        $stream = new Stream(fopen('php://temp', 'wb+'));
        $stream->write('Foo bar!');

        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'fluct');
        $upload->moveTo($to);

        $this->assertTrue(file_exists($to));
        $this->expectException(\RuntimeException::class);

        $upload->moveTo($to);
    }

    /**
     * UploadedFileTest::testCannotRetrieveStreamAfterMove().
     */
    public function testCannotRetrieveStreamAfterMove(): void
    {
        $stream = new Stream(fopen('php://temp', 'wb+'));
        $stream->write('Foo bar!');

        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);

        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'fluct');

        $upload->moveTo($to);

        $this->assertTrue(file_exists($to));
        $this->expectException(\RuntimeException::class);

        $upload->getStream();
    }
}
