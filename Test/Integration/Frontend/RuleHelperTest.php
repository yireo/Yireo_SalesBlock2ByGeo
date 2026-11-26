<?php declare(strict_types=1);

namespace Yireo\SalesBlock2ByGeo\Test\Integration\Frontend;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\SalesBlock2\Helper\Rule;
use Yireo\SalesBlock2\RuleMatch\RuleMatch;
use Yireo\SalesBlock2\Test\Integration\RuleProvider;
use Yireo\SalesBlock2ByGeo\Utils\CurrentIp;

class RuleHelperTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Setup dependencies
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testFindRuleWithAtLeastOneRuleThatMatchesByEmail()
    {
        /** @var Session $customerSession */
        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->loginById(1);
        $customer = $customerSession->getCustomer();

        $this->setConfigValue('salesblock/settings/enabled', 1);
        $this->getRuleProvider()->createRule('geo', 'US', true);

        /** @var Rule $ruleHelper */
        $ruleHelper = $this->objectManager->get(Rule::class);
        $rules = $ruleHelper->getRules();
        $this->assertNotEmpty($rules);

        try {
            $match = $ruleHelper->findMatch();
            $this->assertInstanceOf(RuleMatch::class, $match);
        } catch (NotFoundException $e) {
            $this->fail('No match found: ' . $e->getMessage());
        }
    }

    public function testSpoofedForwardedForHeaderIsIgnored(): void
    {
        /** @var HttpRequest $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $server = $request->getServer();
        $server->set('REMOTE_ADDR', '203.0.113.5');
        $server->set('HTTP_X_FORWARDED_FOR', '8.8.8.8');
        $request->setServer($server);

        $this->assertSame('203.0.113.5', $this->objectManager->create(CurrentIp::class)->getValue());
    }

    private function getRuleProvider(): RuleProvider
    {
        return $this->objectManager->get(RuleProvider::class);
    }

    private function setConfigValue(string $configPath, $value)
    {
        $this->objectManager->get(MutableScopeConfigInterface::class)->setValue($configPath, $value);
    }
}
