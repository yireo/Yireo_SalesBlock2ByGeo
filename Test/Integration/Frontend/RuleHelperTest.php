<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Integration\Frontend;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2\Helper\Rule;
use Yireo\SalesBlock2\RuleMatch\RuleMatch;
use Yireo\SalesBlock2\Test\Integration\RuleProvider;
use Yireo\SalesBlock2ByGeo\Utils\CurrentIp;
use Yireo\SalesBlock2ByGeo\Utils\GeoLookup;

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class RuleHelperTest extends TestCase
{
    private const GB_IP = '81.2.69.160';
    private const PRIVATE_IP = '192.168.1.1';

    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->assertInstanceOf(ObjectManager::class, $objectManager);
        $this->objectManager = $objectManager;

        $geoLookup = $this->createMock(GeoLookup::class);
        $geoLookup->method('lookup')->willReturnCallback(
            fn (string $ip) => $ip === self::GB_IP
                ? ['country' => 'GB', 'continent' => 'EU']
                : ['country' => '', 'continent' => '']
        );
        $this->objectManager->addSharedInstance($geoLookup, GeoLookup::class);
        $this->objectManager->get(MutableScopeConfigInterface::class)->setValue('salesblock/settings/enabled', 1);
    }

    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function getRuleData(): array
    {
        return [
            'matching country' => [self::GB_IP, 'GB', true],
            'matching country lowercase' => [self::GB_IP, 'gb', true],
            'other country' => [self::GB_IP, 'US', false],
            'matching continent' => [self::GB_IP, 'continent:EU', true],
            'other continent' => [self::GB_IP, 'continent:NA', false],
            'continent code without prefix is a country' => [self::GB_IP, 'EU', false],
            'list with matching country' => [self::GB_IP, 'US, GB', true],
            'unknown location does not match' => [self::PRIVATE_IP, 'US', false],
            'unknown location does not match continent' => [self::PRIVATE_IP, 'continent:EU', false],
            'exact IP' => [self::PRIVATE_IP, self::PRIVATE_IP, true],
        ];
    }

    /**
     * @dataProvider getRuleData
     */
    public function testFindMatch(string $ip, string $pattern, bool $expectMatch): void
    {
        $this->objectManager->get(CurrentIp::class)->setIp($ip);
        $this->getRuleProvider()->createRule('geo', $pattern, true);

        $ruleHelper = $this->objectManager->get(Rule::class);
        $this->assertNotEmpty($ruleHelper->getRules());

        try {
            $match = $ruleHelper->findMatch();
        } catch (NotFoundException) {
            $match = null;
        }

        if (!$expectMatch) {
            $this->assertNull($match);
            return;
        }

        $this->assertInstanceOf(RuleMatch::class, $match);
        $this->assertStringNotContainsString($pattern, $match->getMessage());
    }

    public function testSpoofedForwardedForHeaderIsIgnored(): void
    {
        /** @var HttpRequest $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $server = $request->getServer();
        $server->set('REMOTE_ADDR', self::GB_IP);
        $server->set('HTTP_X_FORWARDED_FOR', '8.8.8.8');
        $request->setServer($server);

        $this->assertSame(self::GB_IP, $this->objectManager->create(CurrentIp::class)->getValue());
    }

    private function getRuleProvider(): RuleProvider
    {
        return $this->objectManager->get(RuleProvider::class);
    }
}
