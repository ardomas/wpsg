<?php
/**
 * WPSG - Locales Module
 * Initializes the date and currency formatting tools.
 *
 * @package WPSG\Modules\Locales
 */

namespace WPSG\Modules\Locales;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ------------------------------------------------------------
 * Load Required Classes
 * ------------------------------------------------------------
 */
// require_once __DIR__ . '/includes/tools/locales/class-locale-base.php';
// require_once __DIR__ . '/includes/tools/locales/class-locale-date.php';
// require_once __DIR__ . '/includes/tools/locales/class-currency.php';


/**
 * ------------------------------------------------------------
 * Module Initialization
 * ------------------------------------------------------------
 */

add_action( 'plugins_loaded', function() {

    // Initialize date formatter if needed
    if ( method_exists( \WPSG_DateFormatter::class, 'init' ) ) {
        \WPSG_DateFormatter::init();
    }

    // Initialize currency formatter if needed
    if ( method_exists( \WPSG_CurrencyFormatter::class, 'init' ) ) {
        \WPSG_CurrencyFormatter::init();
    }
});


/**
 * ------------------------------------------------------------
 * Global Helper Functions
 * ------------------------------------------------------------
 * These helpers make the module usable anywhere without
 * requiring developers to load class names manually.
 * ------------------------------------------------------------
 */

if ( ! function_exists( 'wpsg_format_date' ) ) {
    /**
     * Format a date using WPSG's Locales module.
     *
     * @param mixed  $date    Date string, timestamp, or DateTime object.
     * @param string $format  Optional output format.
     * @return string
     */
    function wpsg_format_date( $date, $format = null ) {
        return \WPSG_DateFormatter::format( $date, $format );
    }
}

if ( ! function_exists( 'wpsg_format_currency' ) ) {
    /**
     * Format a number into currency format.
     *
     * @param float|int|string $number
     * @param string           $currency
     * @return string
     */
    function wpsg_format_currency( $number, $currency = 'IDR' ) {
        return \WPSG_CurrencyFormatter::format( $number, $currency );
    }
}

