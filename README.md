# Yireo SalesBlock2ByGeo for Magento 2

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-salesblock2-by-geo)
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
sales by geolocation. Within the **Match** field, you can one or more of the following values:

- A two-letter identifier for a continent ([ref](https://www.php.net/manual/en/function.geoip-continent-code-by-name.php))
- A two-letter identifier for a country ([ref](https://dev.maxmind.com/geoip/legacy/codes/iso3166/))
- A three-letter identifier for a country ([ref](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-3))

Multiple entries are to be separated by commas.

### GeoIP support
You either need to install GeoIP support into your PHP installation (usually by installing a PHP extension `ext-geoip`) or you need to add GeoIP databases on your system and configure this extension to use them. For installing the PHP extension, refer to your PHP installation. Please note that the GeoIP extension might no longer be available for your PHP version.

As for the Geo databases, register for an account with Maxmind and download the database (`*.mmdb`) for either cities or countries or both. Upload them some where on your server and configure this extension in the Magento Admin Panel to point to that path.

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SalesBlock2ByGeo/compile.yml?label=compile)
<!-- badges.test.end -->
