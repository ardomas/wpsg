<?php

$data_service = new WPSG_IndicatorCategoriesService();

$site_id = wpsg_get_network_id();
$id = isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? wpsg_decrypt( $_GET['id'] ) : 0;


$data = empty( $id ) ? $data_service->blank_data() : $data_service->get( $id );

if( !isset( $can_edit_category ) ){
    $can_edit_category = false;
}

$read_or_write = '';
$access_select = '';
if( ! $can_edit_category ){
    $read_or_write = 'readonly="readonly"';
    $access_select = 'disabled';
}

?>
    <div class="d-none">
        <input type="hidden" name="action" value="wpsg_save_indicator_category_data">
        <input type="hidden" name="category_id" value="<?php echo $id; ?>"/>
        <?php wp_nonce_field('wpsg_save_indicator_category_data','wpsg_indicator_category_data_nonce'); ?>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row my-3 g-2">
                    <label for="name">Domain</label>
                    <input type="text" name="name" id="name" class="form-control" <?php echo $read_or_write; ?> value="<?php echo $data['name']; ?>">
                </div>
            </div>

            <div class="col-12">
                <div class="row mb-3 g-2">
                    <label for="description">Deskripsi</label>
                    <textarea name="description" id="description" <?php echo $read_or_write; ?> class="form-control"><?php echo $data['description']; ?></textarea>
                </div>
            </div>

        </div>
    </div>

<?php

if( $can_edit_category ){

    ?><div class="container">
        <div class="row my-5">
            <div class="col-6 text-start"><?php
                if( $id != 0 ){
                    ?><button type="button" class="btn btn-danger" id="indicator-category-btn-delete" 
                        data-url="<?php echo esc_url( remove_query_arg( ['act'] ) . '&act=' . wpsg_encrypt( 'delete' ) ); ?>">
                        <i class="fas fa-trash-alt fa-fw"></i>
                        <span class="d-none d-md-inline">Hapus</span>
                    </button><?php
                }
            ?></div>
            <div class="col-6 text-end">
                <button type="submit" class="btn btn-process">
                    <i class="fas fa-floppy-disk fa-fw"></i>
                    <span class="d-none d-md-inline">Simpan</span>
                </button>
            </div>
        </div>
    </div>
    <script type="text/javascript" lang="javascript">

        document.addEventListener('DOMContentLoaded',()=>{
            var category_id = '<?php echo $id; ?>';
            console.log( category_id );
            document.getElementById('indicator-category-btn-delete').addEventListener('click',()=>{
                if( window.confirm('Are you sure, You want to delete this data') ){
                    let data_url = document.getElementById('indicator-category-btn-delete').getAttribute('data-url');
                    data_url += '&id' + '=<?php echo wpsg_encrypt( $id ); ?>';
                    window.location = data_url;
                    // console.log( data_url );
                    
                }
            });
        });

    </script><?php

}
