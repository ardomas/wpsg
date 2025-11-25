<?php
/**
 * WPSG Announcements
 * Clean, modular, WordPress-style class for handling announcement data
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_AnnouncementsData {

    /**
     * Post type key
     */
    const POST_TYPE = 'wpsg_announcement';

    /**
     * Register hooks
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_type' ] );
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'save_post', [ __CLASS__, 'save_meta' ] );
    }

    /**
     * Register custom post type
     */
    public static function register_post_type() {
        $labels = [
            'name'          => 'Announcements',
            'singular_name' => 'Announcement',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => true,
            'show_ui'      => true,
            'menu_icon'    => 'dashicons-megaphone',
            'supports'     => [ 'title', 'editor', 'excerpt', 'thumbnail' ],
            'has_archive'  => true,
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'wpsg_announcement_details',
            'Announcement Details',
            [ __CLASS__, 'render_meta_box' ],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render meta box content
     */
    public static function render_meta_box( $post ) {
        wp_nonce_field( 'wpsg_announcement_save_meta', 'wpsg_announcement_nonce' );

        $details = get_post_meta( $post->ID, '_wpsg_announcement_details', true );
        $details = is_array( $details ) ? $details : [];

        $defaults = [
            'start_date'  => '',
            'end_date'    => '',
            'start_time'  => '',
            'end_time'    => '',
            'location'    => [ 'address' => '', 'gmap' => '' ],
            'speakers'    => [],
            'organizers'  => [],
            'contacts'    => [],
        ];

        $data = wp_parse_args( $details, $defaults );

        include __DIR__ . '/views/meta-box-announcement.php';
    }

    /**
     * Save metadata
     */
    public static function save_meta( $post_id ) {
        if ( ! isset( $_POST['wpsg_announcement_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['wpsg_announcement_nonce'], 'wpsg_announcement_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Collect fields
        $fields = [
            'start_date', 'end_date', 'start_time', 'end_time'
        ];

        $details = [];
        foreach ( $fields as $field ) {
            $details[ $field ] = isset( $_POST[ $field ] ) ? sanitize_text_field( $_POST[ $field ] ) : '';
        }

        // Location (address + gmap)
        $details['location'] = [
            'address' => sanitize_text_field( $_POST['location_address'] ?? '' ),
            'gmap'    => esc_url_raw( $_POST['location_gmap'] ?? '' ),
        ];

        // Speakers
        $details['speakers'] = [];
        if ( ! empty( $_POST['speakers'] ) && is_array( $_POST['speakers'] ) ) {
            foreach ( $_POST['speakers'] as $sp ) {
                $details['speakers'][] = [
                    'name'      => sanitize_text_field( $sp['name'] ?? '' ),
                    'company'   => sanitize_text_field( $sp['company'] ?? '' ),
                    'position'  => sanitize_text_field( $sp['position'] ?? '' ),
                ];
            }
        }

        // Organizers
        $details['organizers'] = [];
        if ( ! empty( $_POST['organizers'] ) && is_array( $_POST['organizers'] ) ) {
            foreach ( $_POST['organizers'] as $org ) {
                $details['organizers'][] = [
                    'name'        => sanitize_text_field( $org['name'] ?? '' ),
                    'is_main'     => ! empty( $org['is_main'] ) ? 1 : 0,
                    'description' => sanitize_text_field( $org['description'] ?? '' ),
                ];
            }
        }

        // Contacts
        $details['contacts'] = [];
        if ( ! empty( $_POST['contacts'] ) && is_array( $_POST['contacts'] ) ) {
            foreach ( $_POST['contacts'] as $ct ) {
                $details['contacts'][] = [
                    'name'   => sanitize_text_field( $ct['name'] ?? '' ),
                    'number' => sanitize_text_field( $ct['number'] ?? '' ),
                ];
            }
        }

        update_post_meta( $post_id, '_wpsg_announcement_details', $details );
    }
}

WPSG_AnnouncementsData::init();