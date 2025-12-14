<?php
if ( ! defined('ABSPATH') ) {
    exit;
}

class WPSG_GalleryItems {

    protected $service;
    protected $page = 'wpsg-admin';
    protected $view = 'gallery_items';
    protected $album_id;

    public function __construct() {

        // ambil album_id dari request
        $this->album_id = isset($_GET['album_id'])
            ? absint($_GET['album_id'])
            : 0;

        // service (nanti kita isi benerannya)
        // $this->service = new WPSG_GalleryItemService();

    }

    public function render() {

        if ( ! $this->album_id ) {
            $this->render_empty_state();
            return;
        }

        $action = $_GET['action'] ?? 'list';

        switch ( $action ) {
            case 'add':
                $this->render_form();
                break;

            default:
                $this->render_list();
                break;
        }
    }

    protected function render_list() {

        echo '<div class="wrap">';

        echo '<h1 class="wp-heading-inline">Isi Album</h1>';

        echo '<a class="page-title-action" href="' .
            admin_url("admin.php?page={$this->page}&view={$this->view}&album_id={$this->album_id}&action=add")
        . '">Tambah Item</a>';

        echo '<a class="button" style="margin-left:10px;" href="' .
            admin_url("admin.php?page={$this->page}&view=galleries")
        . '">Kembali ke Album</a>';

        echo '<hr class="wp-header-end">';

        // sementara dummy
        echo '<p><em>Belum ada item dalam album ini.</em></p>';

        echo '</div>';
    }

    protected function render_empty_state() {
        echo '<div class="wrap">';
        echo '<h2>Album tidak ditemukan</h2>';
        echo '<p>Album ID tidak valid atau tidak diberikan.</p>';
        echo '</div>';
    }

    protected function render_form() {

        echo '<div class="wrap">';

        echo '<h1>Tambah Item Album</h1>';

        echo '<form method="post" action="">';

        wp_nonce_field('wpsg_save_gallery_item');

        echo '<input type="hidden" name="album_id" value="' . esc_attr($this->album_id) . '">';

        echo '<table class="form-table">';
        echo '<tr>
                <th scope="row"><label>Media</label></th>
                <td>
                    <em>(media picker nanti di sini)</em>
                </td>
              </tr>';

        echo '<tr>
                <th scope="row"><label>Caption</label></th>
                <td>
                    <textarea name="caption" class="large-text" rows="3"></textarea>
                </td>
              </tr>';
        echo '</table>';

        submit_button('Simpan Item');

        echo '</form>';

        echo '</div>';
    }
}
