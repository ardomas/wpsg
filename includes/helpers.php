<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load stylesheet with integrity, crossorigin, and referrerpolicy attributes.
 */
function wpsg_enqueue_cdn_style( $handle, $attributes ) {

    $src = $attributes['src'] ?? null;
    $ver = $attributes['ver'] ?? 'N/A';

    if( is_null( $src ) ){
        return;
    }
    $integrity = $attributes['integrity'] ?? '';
    $origin    = $attributes['origin'   ] ?? 'anonymous';
    $policy    = $attributes['policy'   ] ?? 'no-referrer';

    wp_enqueue_style( $handle, $src, [], $ver );

    add_filter('style_loader_tag', function($html, $tag_handle) use ($handle, $integrity, $origin, $policy) {

        if ($tag_handle !== $handle) {
            return $html;
        }

        return preg_replace(
            '/rel=[\'"]stylesheet[\'"]/',
            "rel='stylesheet' integrity='{$integrity}' crossorigin='{$origin}' referrerpolicy='{$policy}'",
            $html
        );

    }, 10, 2);
}

/**
 * Load script with integrity, crossorigin, and referrerpolicy attributes.
 */
function wpsg_enqueue_cdn_script( $handle, $attributes, $in_footer = true ) {

    $src = $attributes['src'] ?? null;
    $ver = $attributes['ver'] ?? 'N/A';

    if ( is_null( $src ) ) {
        return;
    }

    $integrity = $attributes['integrity'] ?? '';
    $origin    = $attributes['origin']    ?? 'anonymous';
    $policy    = $attributes['policy']    ?? 'no-referrer';

    wp_enqueue_script( $handle, $src, [], $ver, $in_footer );

    add_filter('script_loader_tag', function( $tag, $tag_handle ) use ( $handle, $integrity, $origin, $policy ) {

        if ( $tag_handle !== $handle ) {
            return $tag;
        }

        // Inject integrity, crossorigin, and referrerpolicy
        $tag = str_replace(
            '<script ',
            "<script integrity='{$integrity}' crossorigin='{$origin}' referrerpolicy='{$policy}' ",
            $tag
        );

        return $tag;

    }, 10, 2 );
}

function wpsg_enqueue_picocss(){
    $attr_style = [
        'src' => 'https://cdnjs.cloudflare.com/ajax/libs/picocss/2.1.1/pico.min.css',
        'ver' => '2.1.1',
        'integrity' =>'sha512-+4kjFgVD0n6H3xt19Ox84B56MoS7srFn60tgdWFuO4hemtjhySKyW4LnftYZn46k3THUEiTTsbVjrHai+0MOFw==',
        'origin'    => 'anonymous',
        'policy'    => 'no-referrer',
    ];
    wpsg_enqueue_cdn_style( 'wpsg-pico-css', $attr_style  );
}

/**
 * Load Font Awesome via CDN with SRI.
 */
function wpsg_enqueue_fontawesome() {

    $attr_style = [
        'src'=>'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        'ver'=>'6.7.2',
        'integrity' => 'sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==',
        'origin'    => 'anonymous',
        'policy'    => 'no-referrer',
    ];
    $attr_script = [
        'src'   => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js',
        'ver'   => '6.7.2',
        'integrity' => 'sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==',
        'origin'    => 'anonymous',
        'policy'    => 'no-referrer',
    ];

    wpsg_enqueue_cdn_style( 'wpsg-fontawesome-css', $attr_style  );
    wpsg_enqueue_cdn_script( 'wpsg-fontawesome-js', $attr_script, false );

}
