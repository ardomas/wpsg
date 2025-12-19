<?php
if ( ! defined('ABSPATH') ) {
    exit;
}

class WPSG_GalleryItems {

    protected $service;
    protected $page = 'wpsg-admin';
    protected $view = 'gallery_items';
    protected $album_id;
    protected $item_id;

    public function __construct() {

        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        // ambil album_id dari request
        $this->album_id = isset($_GET['album_id']) ? absint($_GET['album_id']) : 0;
        $this->item_id  = isset($_GET['item_id'] ) ? absint($_GET['item_id'] ) : null;

        // service (nanti kita isi benerannya)
        $this->service = new WPSG_GalleriesService();

    }

    public function enqueue_assets($hook) {

        // Batasi hanya di halaman plugin kita
        if ( ! isset($_GET['page']) || $_GET['page'] !== 'wpsg-admin' ) {
            return;
        }

        if ( ($_GET['view'] ?? '') !== 'gallery_items' ) {
            return;
        }

        $action = $_GET['action'] ?? 'list';
        if ( ! in_array($action, ['add', 'edit'], true) ) {
            return;
        }

        wp_enqueue_media();
    }

    public function render_reload($album_id){
        $this->album_id = $album_id;
        $_GET['action'] = 'list';
        $this->render();
    }

    public function render() {

        $this->handle_post();

        if ( ! $this->album_id ) {
            $this->render_empty_state();
            return;
        }

        $action = $_GET['action'] ?? 'list';

        switch ( $action ) {
            case 'add' :
            case 'edit':
                $this->render_form();
                break;
            case "delete":
                $this->delete_item();
                break;
            default:
                echo $this->render_list();
                break;
        }
    }

    protected function handle_post() {

        if ( ! isset($_POST['album_id']) ) {
            return;
        }

        if ( ! isset($_POST['_wpnonce']) || 
            ! wp_verify_nonce($_POST['_wpnonce'], 'wpsg_save_gallery_item') ) {
            return;
        }

        // Pastikan service sudah ada
        if ( ! $this->service ) {
            return;
        }

        $album_id = absint($_POST['album_id']);
        $post_id  = absint($_POST['post_id'] ?? 0);
        $item_id  = absint($_POST['item_id'] ?? 0);
        $caption  = sanitize_textarea_field($_POST['caption'] ?? '');

        if ( ! $album_id || ! $post_id ) {
            return;
        }

        $data = [
            'album_id' => $album_id,
            'post_id'  => $post_id,
            'caption'  => $caption,
            'created_at' => current_time('mysql'),
        ];
        // akan ditambahkan id untuk edit
        if( $item_id!=0 ){
            $data['id'] = $item_id;
        };

        // 🔹 Service yang bekerja
        if( $this->service->set_item($data) ){
            $url_back = admin_url( "admin.php?page={$this->page}&view={$this->view}&action=list&album_id={$album_id}" );
            // 🔁 Redirect agar tidak double submit
            ?><div class='notice notice-error'>
                <p>Data sudah di simpan, silakan <a href="<?php echo $url_back; ?>">Kembali ke daftar</a></p>
            </div><?php
            exit;
        } else {
            ?><p>GAGAL</p><?php
        }

    }

    protected function render_list() {

        $items = $this->service->get_items_by_album($this->album_id);

        ob_start();
        ?><div class="wrap">
            <h1 class="wp-heading-inline">Album's content</h1>
            <a class="btn page-title-action" href="<?php
                   echo admin_url("admin.php?page={$this->page}&view={$this->view}&album_id={$this->album_id}&action=add"); 
                ?>">Add new data</a><a class="btn page-title-action" href="<?php
                   echo admin_url("admin.php?page={$this->page}&view=galleries");
                ?>">Back to List</a>
            <hr class="wp-header-end">
            <!-- sementara dummy -->
            <?php
                if( empty( $items ) ) {
                    $this->render_empty_state();
                    return;
                } else {
                    ?><div id="wpsg-gallery-items-container" class="wpsg-row"><?php
                        $nrow_loop = 0;
                        foreach( $items as $item ){
                            $this->render_single_item( $item );
                            $nrow_loop++;
                            if( $nrow_loop==4 ){
                                $nrow_loop=0;
                                echo '</div><div class="wpsg-row">';
                            }
                        }
                    ?></div><?php
                }
            ?>
            <script>

                function reloadGalleryItems(galleryId) {
                    let str_url = "<?php echo admin_url("admin.php?page={$this->page}&view={$this->view}&action=list");  ?>";
                    window.location = str_url + '&album_id=' + galleryId;
                    /*
                    jQuery.post(ajaxurl, {
                        action: 'wpsg_get_gallery_items',
                        album_id: galleryId,
                        // nonce: WPSG.nonce
                    }, function (res) {
                        if (res.success) {
                            jQuery('#wpsg-gallery-items-container').html(res.data.html);
                        }
                    });
                    */
                }

                jQuery(document).ready(function($){

                    $('.wpsg-delete-item').on('click', function(e){
                        e.preventDefault();

                        if (!confirm('Delete this item?')) {
                            return;
                        }

                        const btn    = $(this);
                        const itemId = btn.data('item-id');
                        const nonce  = btn.data('nonce');
                        const itemEl = btn.closest('.wpsg-gallery-item');

                        $.post(ajaxurl, {
                            action: 'wpsg_delete_gallery_item',
                            item_id: itemId,
                            nonce: nonce
                        }, function(response){
                            if (response.success) {
                                itemEl.fadeOut(300, function(){
                                    $(this).remove();
                                    console.log( '<?php echo $this->album_id; ?>' );
                                    reloadGalleryItems( '<?php echo $this->album_id; ?>' );
                                });
                            } else {
                                alert(response.data?.message || 'Delete failed');
                            }
                        });

                    });

                });
            </script>

        </div><?php

        return ob_get_clean();

    }

    private function render_single_item(object $item): void
    {
        $thumb = wp_get_attachment_image(
            $item->post_id,
            'thumbnail',
            false,
            [
                'class' => 'wpsg-gallery-thumb'
            ]
        );

        $url_edit   = admin_url('admin.php?page=wpsg-admin&view=gallery_items&album_id=' . $item->album_id . '&action=edit&item_id='   . $item->id );

        ?><div class="col-3">
            <div class="wpsg-gallery-item" data-item-id="<?php echo esc_attr($item->id); ?>">
                <div class="wpsg-form-field">
                    <div class="wpsg-thumb"><?php
                        echo $thumb ?: '<div class="wpsg-thumb-placeholder"></div>';
                    ?></div>
                    <div class="wpsg-meta">
                        <!-- <span class="wpsg-item-id">#<?php echo esc_html($item->id); ?></span><br/> -->
                        <span><?php echo esc_html($item->caption); ?></span>
                        <div class="wpsg-action">
                            <div class="wpsg-row">
                                <a class="btn btn-action change wpsg-edit-item" href="<?php echo $url_edit; ?>" title="Edit Data">
                                    <i class="dashicons dashicons-edit"></i> Edit
                                </a>
                                <a class="btn btn-action delete wpsg-delete-item" href="#" title="Delete Data"
                                   data-item-id="<?php echo esc_attr($item->id); ?>"
                                   data-nonce="<?php echo esc_attr( wp_create_nonce('wpsg_delete_gallery_item') ); ?>">
                                    <i class="dashicons dashicons-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><?php

    }

    protected function render_empty_state() {
        ?><div class="wrap">
            <p><?php echo esc_html__('No media items yet.', 'wpsg'); ?>.</p>
        </div><?php
    }

    protected function render_form() {

        $back_to_main_list = admin_url("admin.php?page={$this->page}&view=galleries");
        $back_to_item_list = admin_url("admin.php?page={$this->page}&view=gallery_items&action=list&album_id=" . $this->album_id);
        $form_action_title = 'Add Item';
        $data = [
            'album_id' => $this->album_id,
            'post_id'  => null,
            'caption'  => ''
        ];
        if( $this->item_id ){
            $form_action_title = 'Edit Image';
            $data = (array) $this->service->get_item( $this->item_id );
            if( isset( $data['post_id'] ) ){
                $data_img = wp_get_attachment_image($data['post_id']);
            }
        }

        ?><div class="wrap">

            <h2 class="wp-heading-inline"><?php echo $form_action_title; ?></h2>
            <a class="btn page-title-action" href="<?php
                echo $back_to_item_list;
            ?>">Back to Album</a>
            <a class="btn page-title-action" href="<?php
                echo $back_to_main_list;
            ?>">Back to Main List</a>

            <form method="post" action="">
                <?php wp_nonce_field('wpsg_save_gallery_item'); ?>
                <input type="hidden" name="album_id" value="<?php echo esc_attr($this->album_id); ?>"/>
                <input type="hidden" name="item_id" value="<?php echo esc_attr($this->item_id); ?>"/>
                <div class="wpsg wpsg-form">
                    <div class="wpsg-boxed">
                        <div class="wpsg-row">
                            <div class="col-3">
                                <label>Media</label>
                                <div class="wpsg-form-field">
                                    <input type="hidden" name="post_id" id="wpsg_media_post_id" value="<?php echo $data['post_id']; ?>"/>
                                    <div id="wpsg-media-preview" style="margin-top:10px;"><?php
                                        echo wp_get_attachment_image( $data['post_id'] );
                                    ?></div>
                                    <button type="button" class="button" id="wpsg-select-media">
                                        Select Media
                                    </button>
                                </div>
                            </div>
                            <div class="col-6">                                
                                <label>Caption</label>
                                <div class="wpsg-form-field">
                                    <textarea name="caption" class="large-text" rows="3" style="width: 100%;"><?php
                                        echo esc_textarea( $data['caption'] );
                                    ?></textarea>
                                </div>
                                <?php submit_button('Save Data'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                jQuery(document).ready(function($){
                    let frame;

                    $('#wpsg-select-media').on('click', function(e){
                        e.preventDefault();

                        if (frame) {
                            frame.open();
                            return;
                        }

                        frame = wp.media({
                            title: 'Select Media',
                            button: { text: 'Use this media' },
                            multiple: false
                        });

                        frame.on('select', function(){
                            const attachment = frame.state().get('selection').first().toJSON();

                            $('#wpsg_media_post_id').val(attachment.id);

                            let preview = '';
                            if (attachment.type === 'image') {
                                preview = '<img src="' + attachment.sizes.thumbnail.url + '" style="width:200px;" />';
                            } else {
                                preview = '<strong>' + attachment.filename + '</strong>';
                            }

                            $('#wpsg-media-preview').html(preview);
                        });

                        frame.open();
                    });
                });
                </script>


            </form>
        </div><?php
    }
}
