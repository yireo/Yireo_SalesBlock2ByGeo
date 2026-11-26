<?php declare(strict_types=1);

/**
 * Yireo SalesBlock2ByGeo for Magento
 *
 * @package     Yireo_SalesBlock2ByGeo
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\SalesBlock2ByGeo\Utils;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Yireo\SalesBlock2ByGeo\Config\Config;

class CurrentIp
{
    /**
     * @var string
     */
    private $ip = '';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param RequestInterface $request
     * @param Config $config
     */
    public function __construct(
        RequestInterface $request,
        Config $config
    ) {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get the current IP address
     *
     * @return string
     */
    public function getValue(): string
    {
        if ($this->ip !== '') {
            return $this->ip;
        }

        return $this->detectIp();
    }

    /**
     * Only trust X-Forwarded-For when the direct peer is a configured trusted proxy
     *
     * @return string
     */
    private function detectIp(): string
    {
        $remoteAddress = $this->normalize($this->getServerValue('REMOTE_ADDR'));
        if ($remoteAddress === '') {
            return '';
        }

        $trustedProxies = $this->config->getTrustedProxies();
        if (!in_array($remoteAddress, $trustedProxies, true)) {
            return $remoteAddress;
        }

        $forwardedFor = $this->getServerValue('HTTP_X_FORWARDED_FOR');
        $forwardedIps = array_reverse(explode(',', $forwardedFor));
        foreach ($forwardedIps as $forwardedIp) {
            $forwardedIp = $this->normalize($forwardedIp);
            if ($forwardedIp === '') {
                break;
            }

            if (!in_array($forwardedIp, $trustedProxies, true)) {
                return $forwardedIp;
            }
        }

        return $remoteAddress;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getServerValue(string $name): string
    {
        if (!$this->request instanceof HttpRequest) {
            return '';
        }

        return (string)$this->request->getServer($name);
    }

    /**
     * @param string $ip
     * @return string
     */
    private function normalize(string $ip): string
    {
        $ip = trim($ip, " \f\n\r\t\v\x00");
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return '';
        }

        return $ip;
    }
}
