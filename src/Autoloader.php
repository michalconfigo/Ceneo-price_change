<?php

/**
 * Includes the composer Autoloader used for packages and classes in the src/ directory.
 */

namespace Ceneo;

defined( 'ABSPATH' ) || exit;

/**
 * Autoloader class.
 *
 */
class Autoloader
{

    /**
     * Static-only class.
     */
    private function __construct()
    {
    }

    /**
     * Require the autoloader and return the result.
     *
     * If the autoloader is not present, let's log the failure and display a nice admin notice.
     *
     * @return boolean
     */
    public static function init()
    {
        $autoloader = dirname(__DIR__) . '/vendor/autoload.php';


        $autoloader_result = require $autoloader;
        if (!$autoloader_result) {
            return false;
        }

        return $autoloader_result;
    }
}
