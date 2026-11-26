<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Unit\Utils;

use InvalidArgumentException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2ByGeo\Utils\DatabasePath;

class DatabasePathTest extends TestCase
{
    /**
     * @var string
     */
    private $rootDir = '';

    protected function setUp(): void
    {
        $this->rootDir = realpath(sys_get_temp_dir()) . '/salesblock2bygeo-' . uniqid();
        mkdir($this->rootDir . '/var/geoip', 0777, true);
        touch($this->rootDir . '/var/geoip/country.mmdb');
        touch($this->rootDir . '/var/geoip/country.txt');
        touch($this->rootDir . '/outside.mmdb');
    }

    protected function tearDown(): void
    {
        foreach (['var/geoip/country.mmdb', 'var/geoip/country.txt', 'outside.mmdb'] as $file) {
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

    public function testResolveAbsolutePathOutsideVar(): void
    {
        $path = $this->rootDir . '/outside.mmdb';
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
            'missing absolute path' => ['/nonexistent/salesblock2bygeo/country.mmdb'],
            'wrong extension' => ['geoip/country.txt'],
            'missing file' => ['geoip/missing.mmdb'],
            'null byte' => ["geoip/country\0.mmdb"],
            'backslash' => ['geoip\\country.mmdb'],
            'stream wrapper' => ['phar://geoip/country.mmdb'],
        ];
    }

    /**
     * @dataProvider getInvalidPaths
     * @param string $path
     */
    public function testResolveRejectsInvalidPath(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getDatabasePath()->resolve($path);
    }

    /**
     * @return DatabasePath
     */
    private function getDatabasePath(): DatabasePath
    {
        $directory = $this->createMock(ReadInterface::class);
        $directory->method('getAbsolutePath')->willReturn($this->rootDir . '/var/');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryRead')->willReturn($directory);

        return new DatabasePath($filesystem, new File());
    }
}
