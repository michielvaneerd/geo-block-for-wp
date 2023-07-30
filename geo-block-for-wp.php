<?php

// Allowed contintent codes:
// - AF – Africa
// - AN – Antarctica
// - AS – Asia
// - EU – Europe
// - NA – North America
// - OC – Oceania
// - SA – South America

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

if (!class_exists('GeoBlockForWp')) {

    class GeoBlockForWp
    {

        private static function getIp()
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

        private static function myDie($customMessage = null)
        {
            wp_die(!empty($customMessage) ? $customMessage : (defined('GEO_BLOCK_FOR_WP_MESSAGE') ? GEO_BLOCK_FOR_WP_MESSAGE : 'Forbidden'), 'Forbidden', ['response' => 403]);
        }

        private static function optionallyDieOnGeneralException()
        {
            if (defined('GEO_BLOCK_FOR_WP_DIE_ON_EXCEPTION') && GEO_BLOCK_FOR_WP_DIE_ON_EXCEPTION) {
                self::myDie('Internal server error');
            }
        }

        private static function handle($countryOrContinentCodes, $currentCode)
        {
            if (in_array($currentCode, $countryOrContinentCodes)) {
                // We have a country or continent that is defined.
                if (GEO_BLOCK_FOR_WP_CHECK === 'BLOCK') {
                    self::myDie();
                }
            } else {
                // We have a country or continent that is NOT defined
                if (GEO_BLOCK_FOR_WP_CHECK === 'ALLOW') {
                    self::myDie();
                }
            }
        }

        public static function check()
        {
            if (!defined('GEO_BLOCK_FOR_WP_CHECK') || (!defined('GEO_BLOCK_FOR_WP_COUNTRIES') && !defined('GEO_BLOCK_FOR_WP_CONTINENTS'))) {
                return;
            }

            if (!in_array(GEO_BLOCK_FOR_WP_CHECK, ['ALLOW', 'BLOCK'])) {
                return;
            }

            $dbPath = defined('GEO_BLOCK_FOR_WP_DB_PATH') ? GEO_BLOCK_FOR_WP_DB_PATH : plugin_dir_path(__FILE__) . '/GeoLite2-Country.mmdb';

            if (!is_readable($dbPath)) {
                self::optionallyDieOnGeneralException();
                return;
            }

            require_once(plugin_dir_path(__FILE__) . '/lib/autoload.php');

            try {

                $reader = new Reader($dbPath);

                $ip = self::getIp();

                if (empty($ip)) {
                    if (defined('GEO_BLOCK_FOR_WP_DIE_ON_NO_IP') && GEO_BLOCK_FOR_WP_DIE_ON_NO_IP) {
                        self::myDie();
                    } else {
                        return;
                    }
                }

                $record = $reader->country($ip);
                //$record = $reader->country('178.85.217.98'); // NL
                //$record = $reader->country('155.23.45.23'); // US

                if (defined('GEO_BLOCK_FOR_WP_CONTINENTS') && !empty(GEO_BLOCK_FOR_WP_CONTINENTS)) {
                    self::handle(GEO_BLOCK_FOR_WP_CONTINENTS, $record->continent->code);
                } elseif (defined('GEO_BLOCK_FOR_WP_COUNTRIES') && !empty(GEO_BLOCK_FOR_WP_COUNTRIES)) {
                    self::handle(GEO_BLOCK_FOR_WP_COUNTRIES, $record->country->isoCode);
                }
            } catch (AddressNotFoundException $ex) {
                if (defined('GEO_BLOCK_FOR_WP_DIE_ON_ADDRESS_NOT_FOUND') && GEO_BLOCK_FOR_WP_DIE_ON_ADDRESS_NOT_FOUND) {
                    self::myDie();
                }
            } catch (\Exception $ex) {
                self::optionallyDieOnGeneralException();
            }
        }
    }

    GeoBlockForWp::check();
}
