<?php

if (!defined('GEO_BLOCK_FOR_WP_CHECK') || !defined('GEO_BLOCK_FOR_WP_COUNTRIES')) {
    return;
}

if (!in_array(GEO_BLOCK_FOR_WP_CHECK, ['ALLOW', 'BLOCK'])) {
    return;
}

if (!is_readable(plugin_dir_path(__FILE__) . '/GeoLite2-Country.mmdb')) {
    return;
}

require_once(plugin_dir_path(__FILE__) . '/lib/autoload.php');

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

function geo_block_for_wp_get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function geo_block_for_wp_get_message()
{
    if (defined('GEO_BLOCK_FOR_WP_MESSAGE')) {
        return GEO_BLOCK_FOR_WP_MESSAGE;
    }
    return 'Forbidden';
}

$reader = new Reader(plugin_dir_path(__FILE__) . '/GeoLite2-Country.mmdb');

try {
    // 178.85.217.98 = NL
    // 155.23.45.23 = US
    $record = $reader->country(geo_block_for_wp_get_ip());
    //$record = $reader->country('155.23.45.23');
    //$record = $reader->country('155.23.45.23');

    if (in_array($record->country->isoCode, GEO_BLOCK_FOR_WP_COUNTRIES)) {
        // We have a country that is defined.
        if (GEO_BLOCK_FOR_WP_CHECK === 'BLOCK') {
            wp_die(geo_block_for_wp_get_message(), 'Forbidden', ['response' => 403]);
        } else {
            // We have defined allowed countries, so this is okay.
        }
    } else {
        // We have a country that is NOT defined
        if (GEO_BLOCK_FOR_WP_CHECK === 'ALLOW') {
            wp_die(geo_block_for_wp_get_message(), 'Forbidden', ['response' => 403]);
        } else {
            // We have defined blocked countries, so this is okay.
        }
    }
} catch (AddressNotFoundException $ex) {
    // Maybe development, so continue...
    // wp_die('Not allowed!');
} catch (\Exception $ex) {
    wp_die('Internal Server Error');
}
