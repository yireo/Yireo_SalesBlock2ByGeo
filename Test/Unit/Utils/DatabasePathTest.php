<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Unit\Utils;

use InvalidArgumentException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2ByGeo\Utils\DatabasePath;

class DatabasePathTest extends TestCase
{
    private string $rootDir = '';

    protected function setUp(): void
    {
        $this->rootDir = realpath(sys_get_temp_dir()) . '/salesblock2bygeo-' . uniqid();
        mkdir($this->rootDir . '/var/geoip', 0777, true);
        touch($this->rootDir . '/var/geoip/country.mmdb');
        touch($this->rootDir . '/var/geoip/country.txt');
        touch($this->rootDir . '/outside.mmdb');
        symlink($this->rootDir . '/outside.mmdb', $this->rootDir . '/var/geoip/link.mmdb');
    }

    protected function tearDown(): void
    {
        foreach (['var/geoip/link.mmdb', 'var/geoip/country.mmdb', 'var/geoip/country.txt', 'outside.mmdb'] as $file) {
            unlink($this->rootDir . '/' . $file);
        }

        rmdir($this->rootDir . '/var/geoip');
        rmdir($this->rootDir . '/var');
        rmdir($this->rootDir);
    }

    public function testResolveRelativePath(): void
    {
        $this->assertSame(
            $this->rootDir . '/var/geoip/country.mmdb',
            $this->getDatabasePath()->resolve('geoip/country.mmdb')
        );
    }

    public function testResolveAbsolutePathInsideVar(): void
    {
        $path = $this->rootDir . '/var/geoip/country.mmdb';
        $this->assertSame($path, $this->getDatabasePath()->resolve($path));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function getInvalidPaths(): array
    {
        return [
            'empty' => [''],
            'traversal' => ['../outside.mmdb'],
            'nested traversal' => ['geoip/../../outside.mmdb'],
            'absolute path outside var' => ['/etc/passwd.mmdb'],
            'wrong extension' => ['geoip/country.txt'],
            'missing file' => ['geoip/missing.mmdb'],
            'symlink outside var' => ['geoip/link.mmdb'],
            'null byte' => ["geoip/country\0.mmdb"],
            'backslash' => ['geoip\\country.mmdb'],
            'stream wrapper' => ['phar://geoip/country.mmdb'],
        ];
    }

    #[DataProvider('getInvalidPaths')]
    public function testResolveRejectsInvalidPath(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getDatabasePath()->resolve($path);
    }

    private function getDatabasePath(): DatabasePath
    {
        $directory = $this->createMock(ReadInterface::class);
        $directory->method('getAbsolutePath')->willReturn($this->rootDir . '/var/');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($directory);

        return new DatabasePath($filesystem, new File());
    }
}
