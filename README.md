# Yireo SalesBlock2ByGeo for Magento 2
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
