<?php
// File: includes/tools/locales/class-locale-base.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WPSG_Locales_Base
 *
 * Base helpers and initialization for Locales module.
 */
class WPSG_Locales_Base {

    /**
     * Initialize module: register procedural helpers and shortcodes.
     */
    public static function init() {
        // Register procedural wrappers (if not exists)
        if ( ! function_exists( 'wpsg_format_date' ) ) {
            function wpsg_format_date( $date = '', $format = 'long' ) {
                return WPSG_Date_Formatter::format( $date, $format );
            }
        }

        if ( ! function_exists( 'wpsg_format_number' ) ) {
            function wpsg_format_number( $number = 0, $decimals = 0, $use_currency = false, $currency = 'IDR' ) {
                return WPSG_Currency_Formatter::format( $number, (int) $decimals, (bool) $use_currency, $currency );
            }
        }

        // Shortcodes are registered in their respective classes (date & currency)
        // This class can be extended later if needed.
    }

    public static function get_locale_config( $locale = null ) {
        if ( ! $locale ) {
            $locale = 'id'; // default, atau bisa dari get_locale, dst
        }
        $file = WPSG_DIR . 'includes/tools/locales/locales.json';
        if ( ! file_exists( $file ) ) return [];
        $locales = json_decode( file_get_contents( $file ), true );
        return $locales[ $locale ] ?? $locales['en'];
    }

    public static function get_config_value( $key, $locale = null, $default = '' ) {
        $config = self::get_locale_config( $locale );
        return isset( $config[ $key ] ) ? $config[ $key ] : $default;
    }

    /**
     * Return the default locale slug for formatting.
     * Could be extended to retrieve from settings.
     *
     * @return string
     */
    public static function get_locale() {
        // Default to Indonesian locale for the project, fallback to site's locale
        $site_locale = get_locale();
        // if the site is english but we want indonesia by default you can set 'id_ID'
        // Here we follow site locale unless overriden in future settings
        return $site_locale ? $site_locale : 'en_US';
    }

    /**
     * Safe parse of date input; returns DateTime or null.
     *
     * @param mixed $date
     * @return DateTime|null
     */
    public static function parse_date( $date ) {
        if ( empty( $date ) ) {
            return null;
        }

        // If it's already a DateTime
        if ( $date instanceof DateTime ) {
            return $date;
        }

        // If integer (timestamp)
        if ( is_numeric( $date ) ) {
            try {
                return ( new DateTime() )->setTimestamp( (int) $date );
            } catch ( Exception $e ) {
                return null;
            }
        }

        // Try to create DateTime from string
        try {
            // Let DateTime attempt parsing; WordPress's date_i18n may be used later
            return new DateTime( $date );
        } catch ( Exception $e ) {
            return null;
        }
    }

}
