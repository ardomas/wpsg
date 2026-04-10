<?php

$site_id = wpsg_get_network_id();

$user = wp_get_current_user();
$page_title = "Indikator Perkembangan Anak";

$repo_indi = new WPSG_IndicatorsRepository();
$repo_cats = new WPSG_IndicatorCategoriesRepository();

$data_cats_temp = $repo_cats->get_list();
$data_cats = [];
foreach( $data_cats_temp as $item_cat ){
    $key = $item_cat['id'];
    $data_cats[$key] = $item_cat;
}

$data_indi_temp = $repo_indi->get_list();
$data_indi = [];
foreach( $data_indi_temp as $item_indi ){
    $mo_1 = $item_indi['age_min_month'];
    $mo_2 = $item_indi['age_max_month'];
    $key  = str_pad($mo_1,2,'0',STR_PAD_LEFT) . '-' . str_pad($mo_2,2,'0',STR_PAD_LEFT);
    if( !isset( $data_indi[$key] ) ){
        $data_indi[$key] = [];
    }
    $data_indi[$key][$item_indi['id']] = $item_indi;
}

?><div class="container pb-4">
    <div class="row">
        <div class="col-12">
            <div class="row mb-2">
                <div class="col-12 col-sm-8 text-start">
                    <h2>Daftar <?php echo $page_title; ?></h2>
                </div>
                <div class="col-12 col-sm-4 text-end">
                    <div class="row"><div class="col-12 d-inline"><?php
                        echo fe_generate_href_button([
                            'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null, 's2'=>$_GET['s2'] ?? null, 'act'=>wpsg_encrypt('add') ], 
                            'class'=>'btn-process mx-1',
                            'text'=>'Tambah',
                            'icon'=>'fas fa-plus fa-fw'
                        ]);
                        echo fe_generate_href_button([
                            'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null ], 
                            'exclude_keys'=>[], 
                            'class'=>'btn-process',
                            'text'=>'Kembali', 
                            'icon'=>'fas fa-reply fa-fw'
                        ]);
                ?></div></div></div>
            </div>
        </div>
        <div class="col-12">
            <div class="row wpsg-grid-hover wpsg-grid-border gap-1"><?php

            foreach( $data_indi as $ageid=>$sub_data ){
                    foreach( $sub_data as $key=>$item_data ){
                        $encr_data_id = wpsg_encrypt( $item_data['id'] );
                        ?><div class="row wpsg-grid-data wpsg-data-row p-2" data-id= "<?php echo $encr_data_id; ?>">
                            <div class="col-12 col-sm-12 col-md-6"><strong><?php echo $item_data['title']; ?></strong></div>
                            <div class="col-12 col-sm-6 col-md-3"><?php echo $item_data['age_min_month'] . ' - ' . $item_data['age_max_month']; ?> bulan</div>
                            <div class="col-12 col-sm-6 col-md-3"><?php echo $data_cats[ $item_data['category_id'] ]['name']; ?></div>
                            <?php
                            if( !empty( $item_data['description'] ) ){
                                ?><div class="col-12 italic"><i><?php echo $item_data['description']; ?></i></div><?php
                            }
                            ?>
                        </div><?php
                    }
            }

            ?></div>    
        </div>
    </div>
</div>
<script type="text/javascript" lang="javascript">
    (function(){
        document.addEventListener('DOMContentLoaded', function(){

            let currentRow = null;
            let usr_action = '<?php echo wpsg_encrypt( 'edit' ); ?>';

            var generate_action=(()=>{
                let param_gets = document.querySelectorAll('[name="data-from-get-param"]');
                let params = [];
                param_gets.forEach((x)=>{ 
                    params[ x.getAttribute( 'key-id' ) ] = x.getAttribute( 'key-value' );
                });
                params['act'] = usr_action;
                params['id']  = currentRow.getAttribute('data-id');

                let params_join = Object.entries(params).map( ([key,val])=>{ return key + '=' + val; } ).join('&');

                let str_base_url = window.origin + '/<?php echo fe_get_app_url(); ?>/?' + params_join;
                //
                window.location = str_base_url;
                //
            });

            document.querySelectorAll('.wpsg-data-row').forEach((row)=>{
                row.addEventListener('click',(e)=>{
                    let obj = e.target.closest( '.wpsg-data-row' );
                    currentRow = row;
                    generate_action();
                });
            });

        });
    })();
</script>