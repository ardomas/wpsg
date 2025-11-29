<?php
// File: includes/tools/locales/class-locale-currency.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_CurrencyFormatter
 *
 * Number & currency formatting helpers and shortcode.
 */
class WPSG_CurrencyFormatter {

    /**
     * Initialize number/currency hooks.
     */
    public static function init() {
        // Shortcode: [wpsg_number value="12345.67" decimals="2" currency="IDR" show_symbol="1"]
        add_shortcode( 'wpsg_number', array( __CLASS__, 'shortcode_number' ) );
    }

    /**
     * Format number according to Indonesian conventions by default:
     * thousands separator = '.', decimal separator = ','
     *
     * @param float|int $value
     * @param int       $decimals
     * @param bool      $use_currency
     * @param string    $currency  e.g. 'IDR', 'USD'
     * @return string
     */
    public static function format( $value = 0, $decimals = 0, $use_currency = false, $currency = 'IDR' ) {
        // ensure numeric
        $num = (float) str_replace( ',', '.', $value );

        // Default locale formatting (Indonesia)
        $thousands_sep = '.';
        $decimal_sep = ',';

        // Use PHP number_format but then replace decimal separator
        $formatted = number_format( $num, $decimals, '.', ',' ); // produce like 12,345.67

        // swap to desired separators (thousands '.' and decimal ',')
        // first replace thousands separator comma -> temporary token
        $formatted = str_replace( ',', '{_TMP_}', $formatted );
        // replace decimal point . -> actual decimal separator
        $formatted = str_replace( '.', $decimal_sep, $formatted );
        // replace tmp with thousands separator
        $formatted = str_replace( '{_TMP_}', $thousands_sep, $formatted );

        if ( $use_currency ) {
            $symbol = self::get_currency_symbol( $currency );
            // Place symbol before number for IDR; for others might vary
            if ( 'IDR' === strtoupper( $currency ) ) {
                return esc_html( $symbol . ' ' . $formatted );
            }
            // fallback: symbol + number
            return esc_html( $symbol . ' ' . $formatted );
        }

        return esc_html( $formatted );
    }

    /**
     * Shortcode handler.
     *
     * @param array $atts
     * @return string
     */
    public static function shortcode_number( $atts ) {
        $atts = shortcode_atts( array(
            'value'       => 0,
            'decimals'    => 0,
            'currency'    => 'IDR',
            'show_symbol' => 1,
        ), $atts, 'wpsg_number' );

        $use_currency = (int) $atts['show_symbol'] === 1;
        return self::format( $atts['value'], (int) $atts['decimals'], $use_currency, $atts['currency'] );
    }

    /**
     * Minimal currency symbol mapper.
     *
     * @param string $currency
     * @return string
     */
    public static function get_currency_symbol( $currency ) {
        $c = strtoupper( (string) $currency );
        $map = array(
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'SGD' => 'S$',
            'MYR' => 'RM',
            // extend as needed
        );

        return isset( $map[ $c ] ) ? $map[ $c ] : $c . ' ';
    }
}

// Auto init
add_action( 'init', array( 'WPSG_CurrencyFormatter', 'init' ) );
