<?php

$data_service = new WPSG_IndicatorsService();

$site_id = wpsg_get_network_id();
$id = isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? wpsg_decrypt( $_GET['id'] ) : 0;

$data_cats = $data_service->get_categories();
$data_indi = $data_service->get( $id );

if( !isset( $can_edit_indicator ) ){
    $can_edit_indicator = false;
}

$read_or_write = '';
$access_select = '';
if( ! $can_edit_indicator ){
    $read_or_write = 'readonly="readonly"';
    $access_select = 'disabled';
}

if( count( $data_cats )==0 ){

    ?><div class="form">
        <div class="container wpsg-boxed border-1 my-5 p-5">
            <h3 class="label label-warning">Halt!</h3>
            <p>Pembuatan data indikator tidak bisa dilanjutkan karena data kategori untuk indikator masih kosong.</p>
        </div>
    </div><?php

} else {

    ?>

    <div class="d-none">
        <input type="hidden" name="action" value="wpsg_save_indicator_data">
        <input type="hidden" name="indicator_id" value="<?php echo $id; ?>"/>
        <?php wp_nonce_field('wpsg_save_indicator_data','wpsg_indicator_data_nonce'); ?>
    </div>

    <div class="row">

        <div class="col-12">
            <div class="row my-3 g-2">
                <label for="title">Indikator</label>
                <input type="text" name="title" id="title" class="form-control" <?php echo $read_or_write; ?> value="<?php echo $data_indi['title']; ?>">
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-6">
            <div class="row mb-3 g-2">
                <div class="col-12">
                    <label class="control-label">Kelompok Usia (dalam bulan)</label>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <div class="input-group">
                            <label class="input-group-prepend input-group-text control-label" style="width: 25%; min-width: 60px; max-width: 80px;" for="age_min_month">Min</label>
                            <input type="number" name="age_min_month" id="age_min_month" class="form-control text-center" <?php echo $read_or_write; ?> value="<?php echo $data_indi['age_min_month']; ?>">
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <div class="input-group">
                            <label class="input-group-prepend input-group-text control-label" style="width: 25%; min-width: 60px; max-width: 80px;" for="age_max_month">Max</label>
                            <input type="number" name="age_max_month" id="age_max_month" class="form-control text-center" <?php echo $read_or_write; ?> value="<?php echo $data_indi['age_max_month']; ?>">
                        </div>
                    </div>
                </div>                
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-6">
            <div class="row mb-3 g-2">
                <label for="category_id">Domain/Aspek</label>
                <select name="category_id" id="category_id" <?php echo $read_or_write . ' ' . $access_select; ?> class="form-control control-label">
                    <option value="">-- unset --</option><?php
                    foreach( $data_cats as $cat ){
                        $selected = $data_indi['category_id'] == $cat['id'] ? 'selected' : '';
                        echo '<option value="'.$cat['id'].'" '.$selected.'>'.$cat['name'].'</option>';
                    }
                ?></select>
            </div>
        </div>
        <div class="col-12">
            <div class="row mb-3 g-2">
                <label for="description">Deskripsi</label>
                <textarea name="description" id="description" <?php echo $read_or_write; ?> class="form-control"><?php echo $data_indi['description']; ?></textarea>
            </div>
        </div>
    </div>
    <div class="row"><?php 

        if( $can_edit_indicator ) {

            ?><div class="container">
                <div class="row my-5">
                        <div class="col-6 text-start"><?php
                            if( $id != 0 ){
                                ?><button type="button" class="btn btn-danger" id="indicator-btn-delete" 
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
                <script type="text/javascript" lang="javascript">
                    document.addEventListener('DOMContentLoaded',()=>{
                        var indicator_id = '<?php echo $id; ?>';
                        console.log( indicator_id );
                        document.getElementById('indicator-btn-delete').addEventListener('click',()=>{
                            if( window.confirm('Are you sure, You want to delete this data') ){
                                let data_url = document.getElementById('indicator-btn-delete').getAttribute('data-url');
                                data_url += '&id' + '=<?php echo wpsg_encrypt( $id ); ?>';
                                window.location = data_url;
                                // console.log( data_url );
                                
                            }
                        });
                    });
                </script>
            </div><?php

        }

    ?></div><?php

}