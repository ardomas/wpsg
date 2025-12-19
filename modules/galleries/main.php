<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once WPSG_DIR . 'includes/repositories/class-wpsg-galleries-repository.php';
require_once WPSG_DIR . 'includes/services/class-wpsg-galleries-service.php';

class WPSG_Galleries {

    private $service;
    private $page;
    private $view;

    public function __construct() {
        $this->page = 'wpsg-admin';
        $this->view = 'galleries';
        $this->service = new WPSG_GalleriesService();
    }

    public function delete_data() {
        $url_back = admin_url( "admin.php?page={$this->page}&view={$this->view}&action=list" );
        if( isset( $_GET['album_id'] ) ){
            $album_id = $_GET['album_id'];

            ?><div class='notice notice-error'><?php

            if( !is_null( $album_id ) && trim($album_id)!='' && $album_id!='0' ){
                if( $this->service->delete_album( $album_id ) ){
                    ?><p>Data sudah di dihapus</p><?php
                } else {
                    // Bisa tambahkan message error
                    ?><p>Terjadi kesalahan saat menghapus data.</p><?php
                }

            }

            ?><p>Silakan <a href="<?php echo $url_back; ?>">Kembali ke daftar</a></p></div><?php

        }
    }

    public function render_page() {

        $site_id   = wpsg_get_network_id();
        $profiles  = wpsg_get_profile_data();
        $site_data = $profiles['profile_identity'];
        $albums = $this->service->get_album_list(['site_id'=>$site_id,'limit'=>50]); // ambil 50 album terakhir

        ?><div class="wrap">
            <h2 class="wp-heading-inline">Galeri <?php echo( $site_data['full_name'] ); ?></h2>
            <a class="button button-primary" href="?page=<?php echo $this->page; ?>&view=<?php echo $this->view; ?>&action=add">Add Album</a>

            <div class="wpsg wpsg-form">
                <div class="wpsg-form-field">
                    <div class="row"><?php
                    if( !empty($albums) ){
                        $view_item = 'gallery_items';
                        foreach( $albums as $album ){
                            $act_fill   = admin_url( "admin.php?page={$this->page}&view={$view_item}&action=list&album_id={$album['id']}" );
                            $act_edit   = admin_url( "admin.php?page={$this->page}&view={$this->view}&action=edit&album_id={$album['id']}" );
                            $act_delete = admin_url( "admin.php?page={$this->page}&view={$this->view}&action=delete&album_id={$album['id']}" );
                            $thumbnail_url = $album['thumbnail_id'] 
                                ? wp_get_attachment_url($album->thumbnail_id) 
                                : '';
                            ?><div class="wpsg-boxed">
                                <div class="wpsg-row">
                                    <div class="col-8">

                                        <div class="wpsg-form-field">
                                            <div class="wpsg-row">
                                                <div class="col-2 wpsg-form-field" style="text-align: center;"><?php
                                                    if( $thumbnail_url!='' ){
                                                        ?><img src="<?php echo esc_url($thumbnail_url); ?>" width="80"><?php
                                                    } else {
                                                        ?><center>[thumbnail]</center><?php
                                                    }
                                                ?></div>
                                                <div class="col-10">
                                                    <div class="wpsg-row">
                                                        <b><a href="<?php echo $act_fill; ?>"><?php echo esc_html($album['title']); ?></a></b>
                                                    </div>
                                                    <div class="wpsg-row">
                                                        <div class="wpsg-form-field"><?php
                                                        echo nl2br( esc_html( $album['description'] ) );
                                                    ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-4">
                                        <table class="table" style="width: 100%;">
                                            <tbody>
                                                <tr>
                                                    <td colspan="2">
                                                        <div class="wpsg-row">
                                                            <div class="col" style="text-align: left;">
                                                                <a class="btn btn-action" href="<?php echo $act_fill; ?>" title="Isi Album"><i class="dashicons dashicons-images-alt2"></i> Contents</a>
                                                            </div>
                                                            <div class="col" style="text-align: right;">
                                                                <a class="btn btn-action" href="<?php echo $act_edit; ?>" title="Edit Album"><i class="dashicons dashicons-edit"></i> Edit</a>
                                                                <a class="btn btn-action btn-danger" href="<?php echo $act_delete; ?>" title="Hapus Album"><i class="dashicons dashicons-trash"></i> Delete</a>
                                                            </div>
                                                        </div>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right;">Created at :</td>
                                                    <td style="text-align: left;"><?php echo esc_html($album['created_at']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right;">Last Update :</td>
                                                    <td style="text-align: left;"><?php echo esc_html($album['updated_at']); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><?php
                        }
                    } else {
                        ?><div class="row">GAK ADA DATA</div><?php
                    }
                    ?></div>
                </div>
            </div>
        </div><?php

    }
    public function render_form() {
        $data = [];
        if( isset( $_GET['album_id'] ) ){
            $id   = $_GET['album_id'];
            $data = (array) $this->service->get_album( $id );
        }
        $this->render_form__( $data );
    }

    private function render_form__($data = []) {

        $this->handle_post();

        $id = $data['id'] ?? '';
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        // $thumbnail_id = $data['thumbnail_id'] ?? '';

        ?><form method="post" action="">

            <div class="wpsg-rows">
                <?php

                $subtitle = 'New Data';
                if( $id ){
                    echo '<input type="hidden" name="album_id" value="' . esc_attr($id) . '">';
                    $subtitle = 'Update Data';
                }

                ?>

                <h2 class="wp-heading-inline"><?php echo $subtitle; ?></h2>
                <a class="button" href="<?php
                    echo admin_url( "admin.php?page={$this->page}&view={$this->view}&action=list" );
                ?>">Back to List</a>

            </div>

            <div class="wpsg-rows">
                <div class="wpsg-form-field">
                    <label>Judul</label>
                    <input type="text" name="title" id="title" value="<?php echo esc_attr($title); ?>" class="regular-text"/>
                </div>
            </div>

            <div class="wpsg-rows">
                <div class="wpsg-form-field">
                    <label>Deskripsi</label>
                    <textarea name="description" id="description" class="large-text" rows="5"><?php echo esc_textarea($description); ?></textarea>
                </div>
            </div>
            <div class="wpsg-rows">
                <p class="submit"><input type="submit" name="submit_album" id="submit" class="button button-primary" value="Save Album"></p>
            </div>

        </form><?php

    }

    private function handle_post() {
        if ( isset($_POST['submit_album']) ) {

            // Ambil data dari form, bersihkan
            $data = [
                'id'          => $_POST['album_id'] ?? null,
                'title'       => sanitize_text_field($_POST['title'] ?? ''),
                'description' => sanitize_textarea_field($_POST['description'] ?? ''),
                'updated_at'  => current_time('mysql')
            ];
            if( !isset( $_POST['album_id'] ) ){
                $data ['created_at'] = current_time('mysql');
            }

            // Jika update, sertakan ID
            if ( !empty($_POST['album_id']) ) {
                $data['id'] = intval($_POST['album_id']);
            }

            // Panggil service untuk set album
            // $service = new WPSG_AlbumMediaService();
            $result = $this->service->set_album($data);

            if ($result) {
                // Redirect ke list agar refresh dan mencegah double submit
                // wp_redirect(admin_url("admin.php?page={$this->page}&view={$this->view}&action=list&status=success"));
                $url_back = admin_url( "admin.php?page={$this->page}&view={$this->view}&action=list" );
                ?><div class='notice notice-error'>
                    <p>Data sudah di simpan, silakan <a href="<?php echo $url_back; ?>">Kembali ke daftar</a></p>
                </div><?php
                exit;
            } else {
                // Bisa tambahkan message error
                ?><div class="notice notice-error"><p>Terjadi kesalahan saat menyimpan data.</p></div><?php
            }
        }
    }

    private function submit_post() {

        if ( isset($_POST['submit_album']) ) {
            // Tangkap data dari form
            $album_data = [
                'id'          => $_POST['album_id'] ?? null,
                'site_id'     => wpsg_get_network_id(),
                'title'       => sanitize_text_field($_POST['title']),
                'description' => sanitize_textarea_field($_POST['description']),
                'created_at'  => current_time('mysql'),
                'updated_at'  => current_time('mysql')
            ];

            // Simpan melalui service
            $saved_id = $this->service->set($album_data);

            if ($saved_id) {
                echo '<div class="notice notice-success"><p>Album berhasil disimpan. ID: ' . esc_html($saved_id) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Gagal menyimpan album.</p></div>';
            }
        }

    }

}
