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

use Http\Psr7Test\UploadedFileIntegrationTest;
use Kuyoto\Psr7\UploadedFile;

/**
 * Provides an integration test for UploadedFile.
 *
 * @category Library
 *
 * @author   Tolulope Kuyoro <nifskid1999@gmail.com>
 * @license  https://github.com/kuyoto/psr7/blob/master/LICENSE.md (MIT License)
 *
 * @internal
 * @coversNothing
 */
class UploadedFileTest extends UploadedFileIntegrationTest
{
    /**
     * @var string the path to a file
     */
    protected $path;

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        self::removeDirectory('.tmp');

        parent::tearDownAfterClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->path = $this->createAndGetPath(dirname(__DIR__).'/_files/uploads/foo.txt');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->path = null;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new UploadedFile($this->path, filesize($this->path), UPLOAD_ERR_OK);
    }

    /**
     * Creates an returns the path.
     *
     * @param string $path The path
     *
     * @return string The path
     */
    protected function createAndGetPath(string $path): string
    {
        if (!file_exists(dirname($path))) {
            @mkdir(dirname($path));
        }

        $handle = fopen($path, 'w+');

        fwrite($handle, 'an upload file');

        fclose($handle);

        return $path;
    }

    /**
     * Removes a directory recursively.
     *
     * @param string $directory The directory to remove
     */
    protected static function removeDirectory(string $directory): bool
    {
        if (is_link($directory)) {
            return unlink($directory);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return true;
        }

        $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        /** @var \SplFileinfo $file The instance of the SplFileinfo object. */
        foreach ($ri as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }

        return @rmdir($directory);
    }
}
