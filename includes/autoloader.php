<?php
/**
 * Minimal PSR-4 Autoloader for WPSG
 */

if ( ! class_exists( 'WPSG_Autoloader' ) ) {

    class WPSG_Autoloader {

        /**
         * Base namespace prefix.
         * Example: WPSG\Data\Repositories\ClassName
         */
        protected static $prefix = 'WPSG\\';

        /**
         * Base directory for namespace.
         */
        protected static $base_dir;

        /**
         * Register autoloader
         */
        public static function register() {
            self::$base_dir = plugin_dir_path( __DIR__ ); // path to /includes

            // spl_autoload_register( [ __CLASS__, 'autoload' ] );

            spl_autoload_register(
                function($class) {

                // Hanya autoload class yang dimulai dengan WPSG_
                if (strpos($class, 'WPSG_') !== 0) {
                    return;
                }

                // Hilangkan prefix
                $short = substr($class, 5); // remove WPSG_

                // Tentukan folder tujuan berdasarkan suffix
                $folder = '';
                if (substr($short, -4) === 'Data') {
                    $folder = 'includes/data/';
                } elseif (substr($short, -10) === 'Repository') {
                    $folder = 'includes/repositories/';
                } elseif (substr($short, -7) === 'Service') {
                    $folder = 'includes/services/';
                } else {
                    // Default fallback (misal: helpers, utilitas)
                    $folder = 'includes/';
                }

                // Ubah CamelCase menjadi kebab-case
                $filename = strtolower(
                    preg_replace('/([a-z])([A-Z])/', '$1-$2', $short)
                );

                // Buat fullpath
                $filepath = WPSG_DIR . $folder . 'class-wpsg-' . $filename . '.php';

                // Load jika file ada
                if (file_exists($filepath)) {
                    require_once $filepath;
                }
            });

        }

        /**
         * Autoload callback
         */
        protected static function autoload( $class ) {

            // Only load classes under the WPSG namespace
            if ( strpos( $class, self::$prefix ) !== 0 ) {
                return;
            }

            // Remove prefix
            $relative_class = substr( $class, strlen( self::$prefix ) );

            // Replace namespace separators with directory separators
            $file = self::$base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }

    }
}
