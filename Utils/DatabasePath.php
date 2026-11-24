<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Utils;

use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;

class DatabasePath
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly File $driver
    ) {
    }

    /**
     * Resolve a configured database path (relative to var/) to an absolute path inside var/
     *
     * @throws InvalidArgumentException
     */
    public function resolve(string $path): string
    {
        $path = trim($path, " \f\n\r\t\v\x00");
        if ($path === '') {
            throw new InvalidArgumentException('No GeoIP database configured');
        }

        if (str_contains($path, "\0") || str_contains($path, '..') || str_contains($path, '\\')) {
            throw new InvalidArgumentException('The GeoIP database path contains invalid characters');
        }

        if (!preg_match('/\.mmdb$/i', $path)) {
            throw new InvalidArgumentException('The GeoIP database file must have the extension .mmdb');
        }

        $driver = $this->driver;
        $varDirectory = rtrim($this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath(), '/');
        if (str_starts_with($path, '/')) {
            if (!str_starts_with($path, $varDirectory . '/')) {
                throw new InvalidArgumentException('The GeoIP database file must be located in the var/ folder');
            }

            $path = substr($path, strlen($varDirectory) + 1);
        }

        $realVarDirectory = $driver->getRealPath($varDirectory);
        $realPath = $driver->getRealPath($varDirectory . '/' . $path);
        if (!is_string($realVarDirectory) || !is_string($realPath) || !$this->isReadableFile($driver, $realPath)) {
            throw new InvalidArgumentException('The GeoIP database file does not exist or is not readable');
        }

        if (!str_starts_with($realPath, $realVarDirectory . '/')) {
            throw new InvalidArgumentException('The GeoIP database file must be located in the var/ folder');
        }

        return $realPath;
    }

    private function isReadableFile(DriverInterface $driver, string $path): bool
    {
        try {
            return $driver->isFile($path) && $driver->isReadable($path);
        } catch (FileSystemException) {
            return false;
        }
    }
}
