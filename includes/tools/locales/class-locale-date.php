<?php
// File: includes/tools/locales/class-locale-date.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_DateFormatter
 *
 * Provides date formatting helpers and shortcode.
 */
class WPSG_DateFormatter {

    /**
     * Initialize date-related hooks & shortcodes.
     */
    public static function init() {
        // Shortcode: [wpsg_date date="2025-11-17" format="long"]
        add_shortcode( 'wpsg_date', array( __CLASS__, 'shortcode_date' ) );

        // Optionally expose a filter so other modules can hook formatting rules
        add_filter( 'wpsg_date_format', array( __CLASS__, 'get_wp_date_format' ), 10, 2 );
    }

    /**
     * Main formatting function.
     *
     * @param mixed  $date   Date string, timestamp, or DateTime
     * @param string $format 'long'|'short'|'iso' or custom date format
     * @return string
     */
    public static function format( $date = '', $format = 'long' ) {
        // If empty, use current time
        if ( empty( $date ) ) {
            $dt = new DateTime();
        } else {
            $dt = WPSG_LocalesBase::parse_date( $date );
            if ( ! $dt ) {
                // fallback: return original input sanitized
                return esc_html( (string) $date );
            }
        }

        // Resolve format
        $format = (string) $format;
        $format_str = apply_filters( 'wpsg_date_format', $format, $date );

        // Predefined format keywords
        if ( 'iso' === strtolower( $format_str ) ) {
            return esc_html( $dt->format( 'Y-m-d' ) );
        }

        if ( 'short' === strtolower( $format_str ) ) {
            // e.g. 17/11/2025
            $out = $dt->format( 'd/m/Y' );
            return esc_html( $out );
        }

        if ( 'long' === strtolower( $format_str ) ) {
            // Prefer using WordPress date_i18n so month names localized by WP
            $timestamp = $dt->getTimestamp();
            // WordPress date_i18n format: 'j F Y' -> 17 November 2025
            $out = date_i18n( 'j F Y', $timestamp );
            return esc_html( $out );
        }

        // If custom format provided (PHP date format)
        try {
            $out = $dt->format( $format_str );
            return esc_html( $out );
        } catch ( Exception $e ) {
            return esc_html( $dt->format( 'Y-m-d' ) );
        }
    }

    /**
     * Shortcode handler
     *
     * Usage: [wpsg_date date="2025-11-17" format="long"]
     *
     * @param array $atts
     * @return string
     */
    public static function shortcode_date( $atts ) {
        $atts = shortcode_atts( array(
            'date'   => '',
            'format' => 'long',
        ), $atts, 'wpsg_date' );

        return self::format( $atts['date'], $atts['format'] );
    }

    /**
     * Provide default mapping for format
     * Other modules can change via filter 'wpsg_date_format'
     *
     * @param string $format
     * @param mixed  $date
     * @return string
     */
    public static function get_wp_date_format( $format, $date ) {
        // keep as passed (no change) — placeholder for extension
        return $format;
    }
}

// Auto init
add_action( 'init', array( 'WPSG_DateFormatter', 'init' ) );
