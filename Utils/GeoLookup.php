<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Utils;

use GeoIp2\Database\Reader;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Throwable;
use Yireo\SalesBlock2\Logger\Debugger;
use Yireo\SalesBlock2ByGeo\Config\Config;

class GeoLookup implements ResetAfterRequestInterface
{
    /**
     * @var array<string, array{country: string, continent: string}>
     */
    private array $locations = [];

    private ?Reader $reader = null;

    public function __construct(
        private readonly Config $config,
        private readonly DatabasePath $databasePath,
        private readonly Debugger $debugger
    ) {
    }

    /**
     * @return array{country: string, continent: string} Empty strings when unknown
     */
    public function lookup(string $ip): array
    {
        $unknown = ['country' => '', 'continent' => ''];
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return $unknown;
        }

        if (isset($this->locations[$ip])) {
            return $this->locations[$ip];
        }

        try {
            $location = $this->lookupWithDatabase($ip) ?? $this->lookupWithPhpExtension($ip) ?? $unknown;
        } catch (Throwable $exception) {
            $this->debugger->debug('GeoIP lookup failed: ' . $exception->getMessage());
            $location = $unknown;
        }

        $this->locations[$ip] = $location;

        return $location;
    }

    public function isDatabaseAvailable(): bool
    {
        try {
            $this->databasePath->resolve($this->config->getCountryDatabase());
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function _resetState(): void
    {
        $this->locations = [];
        $this->closeReader();
    }

    public function __destruct()
    {
        $this->closeReader();
    }

    /**
     * @return array{country: string, continent: string}|null
     */
    private function lookupWithDatabase(string $ip): ?array
    {
        $reader = $this->getReader();
        if (!$reader) {
            return null;
        }

        $record = $reader->country($ip);

        return [
            'country' => strtoupper((string)$record->country->isoCode),
            'continent' => strtoupper((string)$record->continent->code),
        ];
    }

    /**
     * @return array{country: string, continent: string}|null
     */
    private function lookupWithPhpExtension(string $ip): ?array
    {
        if (!function_exists('geoip_country_code_by_name')) {
            return null;
        }

        $continent = function_exists('geoip_continent_code_by_name') ? geoip_continent_code_by_name($ip) : '';

        return [
            'country' => strtoupper((string)geoip_country_code_by_name($ip)),
            'continent' => strtoupper((string)$continent),
        ];
    }

    private function getReader(): ?Reader
    {
        if ($this->reader) {
            return $this->reader;
        }

        $database = $this->config->getCountryDatabase();
        if ($database === '') {
            return null;
        }

        try {
            $this->reader = new Reader($this->databasePath->resolve($database));
        } catch (Throwable $exception) {
            $this->debugger->debug('GeoIP database could not be opened: ' . $exception->getMessage());
            return null;
        }

        return $this->reader;
    }

    private function closeReader(): void
    {
        if (!$this->reader) {
            return;
        }

        try {
            $this->reader->close();
        } catch (Throwable $exception) {
            $this->debugger->debug('GeoIP database could not be closed: ' . $exception->getMessage());
        }

        $this->reader = null;
    }
}
