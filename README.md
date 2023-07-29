# Geo block for WP plugin

A MU (Must Use) plugin for Wordpress that can be used to block or allow certain countries or continents based on the IP of the visitor.

## Installation

The installation consist of 2 parts:

1. Install the plugin
2. Install the MaxMind `GeoLite2-Country` database.

### Plugin installation

Create the `mu-plugins` directory if this doesn't exist yet:

    mkdir wp-content/mu-plugins

Move the `geo-block-for-wp` directory into the `mu-plugins` directory. So now you have:

    wp-content/mu-plugins/geo-block-for-wp

Now we should add an `index.php` file to the `mu-plugins` directory that will load the `geo-block-for-wp` plugin:

    wp-content/mu-plugins/index.php

The content of this file:

    <?php
    require_once(__DIR__ . '/geo-block-for-wp/geo-block-for-wp.php');

### MaxMind GeoLite2-Country database installation

Login or create an account on the website (this is free):

    https://www.maxmind.com/en/account/login

After logging in, go to `My account` and choose `Download files` under `GeoIP2 / GeoLite2`. Choose the `GeoLite2 Country` file. Verify that this file has an `.mmdb` extension. Place this file inside the `geo-block-for-wp` directory:

    wp-content/mu-plugins/geo-block-for-wp/GeoLite2-Country.mmdb

## Configuration

The last thing you need to do is to define the countries or continents and define if they _are_ or _aren't_ allowed to access your site. Note that this is different:

- If you want to only __allow__ the countries that you speficy, you need to set `GEO_BLOCK_FOR_WP_CHECK` to `ALLOW` in `wp-config.php`.
- If you want to only __block__ the countries that you speficy, you need to set `GEO_BLOCK_FOR_WP_CHECK` to `BLOCK` in `wp-config.php`.

For example if you want to specify all countries or continents that are allowed to access your website:

    define('GEO_BLOCK_FOR_WP_CHECK', 'ALLOW');

Now just add the countries or continents to the `wp-config.php` file.

Countries:
    
    define('GEO_BLOCK_FOR_WP_COUNTRIES', [
	    'NL'
        'BE'
    ]);

Continents:

    define('GEO_BLOCK_FOR_WP_CONTINENTS', [
        'AF',
        'NA'
    ]);

Note that you can only define countries __OR__ continents. If you define both, the continents will take precedence.

You can also set the message that users who don't have access will see by setting `GEO_BLOCK_FOR_WP_MESSAGE` in `wp-config.php`:

    define('GEO_BLOCK_FOR_WP_MESSAGE', 'Sorry you cannot access this website.');

### Exception configuration

What needs to be done when an exception occurs can be set as well in the `wp-config.php` file. You can set either `true` pr `false`. Setting to `true` means that the plugin will exit with an error message.
Setting to `false` (or just not defining this at all) means that the plugin will do nothing and hence the website will be displayed.

Exit or continue if the IP address of the visitor cannot be retrieved:

    define('GEO_BLOCK_FOR_WP_DIE_ON_NO_IP', true);

Exit or continue if the IP address of the visitor is not present in the database file - this can for example be the case if you are running locally and the IP address will be a local IP address:

    define('GEO_BLOCK_FOR_WP_DIE_ON_ADDRESS_NOT_FOUND', true);

Exit or continue on some other exception:

    define('GEO_BLOCK_FOR_WP_DIE_ON_EXCEPTION', true);
