<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

function wpsg_media_gallery_shortcode( $atts ) {

    // Parameter: default type = all
    $atts = shortcode_atts([
        'type' => 'all', // image | video | audio | all
        'limit' => 50,   // berapa banyak ditampilkan
    ], $atts );

    // Tentukan MIME type berdasarkan parameter
    $mime = [];
    if ( $atts['type'] === 'image' ) {
        $mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    } elseif ( $atts['type'] === 'video' ) {
        $mime = ['video/mp4', 'video/webm'];
    } elseif ( $atts['type'] === 'audio' ) {
        $mime = ['audio/mpeg', 'audio/wav'];
    }

    // Query attachment
    $args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    if ( ! empty( $mime ) ) {
        $args['post_mime_type'] = $mime;
    }

    $media = get_posts( $args );

    if ( empty( $media ) ) {
        return "<p>No media found.</p>";
    }

    // Output HTML sederhana
    ob_start();
    ?>
    <div class="wpsg-media-gallery">
        <?php foreach ( $media as $item ): ?>
            <div class="wpsg-media-item">
                <?php
                if ( strpos( $item->post_mime_type, 'image' ) === 0 ) {
                    echo wp_get_attachment_image( $item->ID, 'medium' );
                } elseif ( strpos( $item->post_mime_type, 'video' ) === 0 ) {
                    echo wp_video_shortcode([ 'src' => wp_get_attachment_url( $item->ID ) ]);
                } elseif ( strpos( $item->post_mime_type, 'audio' ) === 0 ) {
                    echo wp_audio_shortcode([ 'src' => wp_get_attachment_url( $item->ID ) ]);
                } else {
                    echo "<a href='". esc_url( wp_get_attachment_url( $item->ID ) ) ."'>Download</a>";
                }
                ?>

                <p class="wpsg-media-caption">
                    <?php echo esc_html( $item->post_title ); ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
