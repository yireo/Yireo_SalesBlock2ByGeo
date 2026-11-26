<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Utils;

use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;

class DatabasePath
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var File
     */
    private $driver;

    /**
     * @param Filesystem $filesystem
     * @param File $driver
     */
    public function __construct(
        Filesystem $filesystem,
        File $driver
    ) {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
    }

    /**
     * Resolve a configured database path (absolute or relative to var/) to its real path
     *
     * @param string $path
     * @return string
     * @throws InvalidArgumentException
     */
    public function resolve(string $path): string
    {
        $path = trim($path, " \f\n\r\t\v\x00");
        if ($path === '') {
            throw new InvalidArgumentException('No GeoIP database configured');
        }

        if (strpos($path, "\0") !== false
            || strpos($path, '..') !== false
            || strpos($path, '\\') !== false
            || strpos($path, '://') !== false
        ) {
            throw new InvalidArgumentException('The GeoIP database path contains invalid characters');
        }

        if (!preg_match('/\.mmdb$/i', $path)) {
            throw new InvalidArgumentException('The GeoIP database file must have the extension .mmdb');
        }

        if (substr($path, 0, 1) !== '/') {
            $varDirectory = rtrim($this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath(), '/');
            $path = $varDirectory . '/' . $path;
        }

        $realPath = $this->driver->getRealPath($path);
        if (!is_string($realPath) || !$this->isReadableFile($realPath)) {
            throw new InvalidArgumentException('The GeoIP database file does not exist or is not readable');
        }

        return $realPath;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isReadableFile(string $path): bool
    {
        try {
            return $this->driver->isFile($path) && $this->driver->isReadable($path);
        } catch (FileSystemException $exception) {
            return false;
        }
    }
}
