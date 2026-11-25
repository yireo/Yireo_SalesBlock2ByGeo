# Yireo SalesBlock2ByGeo for Magento 2

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-salesblock2-by-geo)

</div>
<!-- badges.specs.end -->

This module is a helper-module for the [Yireo_SalesBlock2](https://www.yireo.com/software/magento-extensions/salesblock2) extension, that allows you to block orders from being placed, based on specific rules defined in the Magento Admin Panel.

This specific module allows you to match by a specific geo location. 

### Installation
To install this module, use the following commands. First, install this module using composer. Note that this step will fail if the `Yireo_SalesBlock2` is not installed yet.
 
    composer require yireo/magento2-salesblock2-by-geo
    
Once this module is installed via composer, you can enable it:

    bin/magento module:enable Yireo_SalesBlock2ByGeo
    bin/magento setup:upgrade

There are no further steps to take. The `Yireo_SalesBlock2` module automatically picks up on things.

### Usage
When creating a rule within the SalesBlock extension, this submodule adds a new geolocation rule to block
sales by geolocation. Within the **Match** field, you can add one or more of the following values:

- A two-letter country code, like `NL` ([ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2))
- A three-letter country code, like `NLD` ([ISO 3166-1 alpha-3](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3))
- A two-letter continent code with the prefix `continent:`, like `continent:EU` (`AF`, `AN`, `AS`, `EU`, `NA`, `OC`, `SA`)
- An exact IP address

Multiple entries are to be separated by commas.

When the location of a visitor can't be determined (for instance a private IP, a missing database or a lookup error), a geo rule does not match. The error is written to the SalesBlock log when logging is enabled.

The rule message that is shown to the customer can use the variables `%ip%` and `%country%`. When the rule has no frontend label, a generic message is shown.

#### Upgrading from 2.x
- A two-letter value is now always a country code. Continent rules need the prefix `continent:` (for instance `continent:EU` instead of `EU`), because some continent codes (`AF`, `AS`, `NA`, `SA`) are also country codes.
- The message variable `%geo%` has been renamed to `%ip%`.
- The GeoIP v2 database file must be located in the `var/` folder of Magento, and is configured with a path relative to `var/`.

### GeoIP support
You either need to add a GeoIP2 database to your system and configure this extension to use it (preferred), or install GeoIP support into your PHP installation (usually by installing the legacy PHP extension `ext-geoip`, which might no longer be available for your PHP version).

For the database, register for an account with MaxMind and download the country database (`GeoLite2-Country.mmdb` or `GeoIP2-Country.mmdb`). Upload it to the `var/` folder of Magento (for instance `var/geoip/GeoLite2-Country.mmdb`) and configure the path relative to `var/` (`geoip/GeoLite2-Country.mmdb`) in the Magento Admin Panel under **Stores > Configuration > Yireo > Yireo SalesBlock**. Do not place the database in the `pub/` folder.

### Client IP detection and reverse proxies
This module reads the client IP from `REMOTE_ADDR` only. The `X-Forwarded-For` header can be set to any value by a client, so it is ignored by default, even when Magento core is configured to use it.

When Magento runs behind a reverse proxy (load balancer, CDN, Varnish), add the IP addresses of your proxies to the setting **Trusted proxies**. When a request comes from a trusted proxy, the rightmost IP in the `X-Forwarded-For` header that is not a trusted proxy is used as the client IP.

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
