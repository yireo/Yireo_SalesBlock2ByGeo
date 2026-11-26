<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Unit\Utils;

use Magento\Framework\App\Request\Http as HttpRequest;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2ByGeo\Config\Config;
use Yireo\SalesBlock2ByGeo\Utils\CurrentIp;

class CurrentIpTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string[], string}>
     */
    public static function getRequests(): array
    {
        return [
            'remote address' => ['203.0.113.5', '', [], '203.0.113.5'],
            'spoofed header without trusted proxies' => ['203.0.113.5', '8.8.8.8', [], '203.0.113.5'],
            'spoofed header from untrusted peer' => ['203.0.113.5', '8.8.8.8', ['10.0.0.1'], '203.0.113.5'],
            'header from trusted proxy' => ['10.0.0.1', '198.51.100.7', ['10.0.0.1'], '198.51.100.7'],
            'spoofed leftmost entry behind trusted proxy' => [
                '10.0.0.1',
                '8.8.8.8, 198.51.100.7',
                ['10.0.0.1'],
                '198.51.100.7',
            ],
            'chain of trusted proxies' => [
                '10.0.0.1',
                '198.51.100.7, 10.0.0.2',
                ['10.0.0.1', '10.0.0.2'],
                '198.51.100.7',
            ],
            'invalid entry stops the chain' => ['10.0.0.1', '198.51.100.7, foo', ['10.0.0.1'], '10.0.0.1'],
            'only trusted proxies in header' => ['10.0.0.1', '10.0.0.1', ['10.0.0.1'], '10.0.0.1'],
            'trusted proxy without header' => ['10.0.0.1', '', ['10.0.0.1'], '10.0.0.1'],
            'ipv6' => ['2001:db8::1', '', [], '2001:db8::1'],
            'invalid remote address' => ['foo', '8.8.8.8', [], ''],
            'empty remote address' => ['', '8.8.8.8', [], ''],
        ];
    }

    /**
     * @dataProvider getRequests
     * @param string $remoteAddress
     * @param string $forwardedFor
     * @param string[] $trustedProxies
     * @param string $expected
     */
    public function testGetValue(
        string $remoteAddress,
        string $forwardedFor,
        array $trustedProxies,
        string $expected
    ): void {
        $currentIp = new CurrentIp($this->getRequest($remoteAddress, $forwardedFor), $this->getConfig($trustedProxies));
        $this->assertSame($expected, $currentIp->getValue());
    }

    public function testSetIpOverridesDetection(): void
    {
        $currentIp = new CurrentIp($this->getRequest('203.0.113.5', ''), $this->getConfig([]));
        $this->assertSame('203.0.113.5', $currentIp->getValue());

        $currentIp->setIp('198.51.100.7');
        $this->assertSame('198.51.100.7', $currentIp->getValue());

        $currentIp->setIp('');
        $this->assertSame('203.0.113.5', $currentIp->getValue());
    }

    /**
     * @param string $remoteAddress
     * @param string $forwardedFor
     * @return HttpRequest
     */
    private function getRequest(string $remoteAddress, string $forwardedFor): HttpRequest
    {
        $request = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getServer'])
            ->getMock();
        $serverValues = ['REMOTE_ADDR' => $remoteAddress, 'HTTP_X_FORWARDED_FOR' => $forwardedFor];
        $request->method('getServer')->willReturnCallback(
            function (string $name) use ($serverValues) {
                return $serverValues[$name] ?? null;
            }
        );

        return $request;
    }

    /**
     * @param string[] $trustedProxies
     * @return Config
     */
    private function getConfig(array $trustedProxies): Config
    {
        $config = $this->createMock(Config::class);
        $config->method('getTrustedProxies')->willReturn($trustedProxies);

        return $config;
    }
}
