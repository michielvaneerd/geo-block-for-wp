<?php

// Allowed contintent codes:
// - AF – Africa
// - AN – Antarctica
// - AS – Asia
// - EU – Europe
// - NA – North America
// - OC – Oceania
// - SA – South America

if (!defined('GEO_BLOCK_FOR_WP_CHECK') || (!defined('GEO_BLOCK_FOR_WP_COUNTRIES') && !defined('GEO_BLOCK_FOR_WP_CONTINENTS'))) {
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

function geo_block_for_wp_die($customMessage = null)
{
    wp_die(!empty($customMessage) ? $customMessage : (defined('GEO_BLOCK_FOR_WP_MESSAGE') ? GEO_BLOCK_FOR_WP_MESSAGE : 'Forbidden'), 'Forbidden', ['response' => 403]);
}

function geo_block_for_wp_handle($countryOrContinentCodes, $currentCode)
{
    if (in_array($currentCode, $countryOrContinentCodes)) {
        // We have a country or continent that is defined.
        if (GEO_BLOCK_FOR_WP_CHECK === 'BLOCK') {
            geo_block_for_wp_die();
        } else {
            // We have defined allowed countries or continents, so this is okay.
        }
    } else {
        // We have a country or continent that is NOT defined
        if (GEO_BLOCK_FOR_WP_CHECK === 'ALLOW') {
            geo_block_for_wp_die();
        } else {
            // We have defined blocked countries or continents, so this is okay.
        }
    }
}

try {

    $reader = new Reader(plugin_dir_path(__FILE__) . '/GeoLite2-Country.mmdb');

    $ip = geo_block_for_wp_get_ip();

    if (empty($ip)) {
        if (defined('GEO_BLOCK_FOR_WP_DIE_ON_NO_IP') && GEO_BLOCK_FOR_WP_DIE_ON_NO_IP) {
            geo_block_for_wp_die();
        } else {
            return;
        }
    }

    $record = $reader->country($ip);
    //$record = $reader->country('178.85.217.98'); // NL
    //$record = $reader->country('155.23.45.23'); // US

    if (defined('GEO_BLOCK_FOR_WP_CONTINENTS') && !empty(GEO_BLOCK_FOR_WP_CONTINENTS)) {
        geo_block_for_wp_handle(GEO_BLOCK_FOR_WP_CONTINENTS, $record->continent->code);
    } elseif (defined('GEO_BLOCK_FOR_WP_COUNTRIES') && !empty(GEO_BLOCK_FOR_WP_COUNTRIES)) {
        geo_block_for_wp_handle(GEO_BLOCK_FOR_WP_COUNTRIES, $record->country->isoCode);
    }
} catch (AddressNotFoundException $ex) {
    if (defined('GEO_BLOCK_FOR_WP_DIE_ON_ADDRESS_NOT_FOUND') && GEO_BLOCK_FOR_WP_DIE_ON_ADDRESS_NOT_FOUND) {
        geo_block_for_wp_die();
    }
} catch (\Exception $ex) {
    if (defined('GEO_BLOCK_FOR_WP_DIE_ON_EXCEPTION') && GEO_BLOCK_FOR_WP_DIE_ON_EXCEPTION) {
        geo_block_for_wp_die('Internal server error');
    }
}
