<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class WPSG_Gallery_Shortcode {

    protected $service;

    public function __construct() {
        $this->service = new WPSG_GalleryService();
        add_shortcode('wpsg_gallery', [$this, 'render']);
        add_filter('query_vars', [$this, 'register_query_var']);
    }

    public function register_query_var($vars) {
        $vars[] = 'album';
        return $vars;
    }

    public function render($atts) {
        $album_id = get_query_var('album');

        if ($album_id) {
            return $this->render_album_detail((int) $album_id);
        }

        return $this->render_album_list();
    }

    protected function render_album_list() {

        $albums = $this->service->get_all_albums();

        if (empty($albums)) {
            return '<p>Belum ada gallery.</p>';
        }

        ob_start();
        echo '<div class="wpsg-gallery-list">';

        foreach ($albums as $album) {

            $items = $this->service->get_items_by_album(
                $album->id,
                5
            );

            $url = add_query_arg(
                'album',
                $album->id,
                get_permalink()
            );
            ?>
            <div class="wpsg-album">
                <h3>
                    <a href="<?php echo esc_url($url); ?>">
                        <?php echo esc_html($album->title); ?>
                    </a>
                </h3>

                <div class="wpsg-thumbs">
                    <?php foreach ($items as $item): ?>
                        <?php echo wp_get_attachment_image(
                            $item->media_id,
                            'thumbnail'
                        ); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }

        echo '</div>';
        return ob_get_clean();
    }

    protected function render_album_detail($album_id) {

        $album = $this->service->get_album($album_id);
        $items = $this->service->get_items_by_album($album_id);

        if (! $album) {
            return '<p>Album tidak ditemukan.</p>';
        }

        $back_url = remove_query_arg('album');

        ob_start();
        ?>
        <div class="wpsg-album-detail">

            <a class="wpsg-back" href="<?php echo esc_url($back_url); ?>">
                ← Kembali ke Gallery
            </a>

            <h2><?php echo esc_html($album->title); ?></h2>

            <div class="wpsg-gallery-grid">
                <?php foreach ($items as $item): ?>
                    <?php echo wp_get_attachment_image(
                        $item->media_id,
                        'medium'
                    ); ?>
                <?php endforeach; ?>
            </div>

        </div>
        <?php

        return ob_get_clean();
    }


}
