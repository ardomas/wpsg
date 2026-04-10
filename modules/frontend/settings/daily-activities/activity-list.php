<?php

$site_id = wpsg_get_network_id();

$repo = new WPSG_DailyActivitiesService();

$page_title = "Kegiatan Harian Anak";

?>
<div class="container pb-4">
    <div class="row">
        <div class="col-12">
            <div class="row mb-2">
                <div class="col-12 col-sm-8 text-start">
                    <h2>Daftar <?php echo $page_title; ?></h2>
                </div>
                <div class="col-12 col-sm-4 text-end"><?php
                        echo fe_generate_href_button([
                            'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null, 'act'=>wpsg_encrypt('add') ], 
                            'class'=>'btn-process mx-1',
                            'text'=>'Tambah',
                            'icon'=>'fas fa-plus fa-fw'
                        ]);
                        echo fe_generate_href_button([
                            'url_params'=>[ 'sid' => $_GET['sid'] ], 
                            'exclude_keys'=>[], 
                            'class'=>'btn-process',
                            'text'=>'Kembali', 
                            'icon'=>'fas fa-reply fa-fw'
                        ]);
                ?></div>
            </div>
        </div>
        <div class="col-12">
            <div class="row wpsg-grid-hover wpsg-grid-border gap-1"><?php
                $temp_list = $repo->get_list();
                $data_list = [];
                foreach( $temp_list as $item ){
                    $key = str_replace(':','.',$item['time_start']) . '.' . str_replace(':','.',$item['time_end']) . '.' . str_pad( $item['sort_order'], 3, '0', STR_PAD_LEFT );
                    $data_list[$key] = $item;
                }
                ksort( $data_list );
                $num = 0;
                foreach( $data_list as $key => $item ){
                    $encr_data_id = wpsg_encrypt( $item['id'] );
                    $time_1 = explode(':',$item['time_start']);
                    $time_2 = explode(':',$item['time_end'  ]);
                    $str_time_1 = Date( 'H:i', mktime( $time_1[0], $time_1[1], $time_1[2], 0, 0, 0 ) );
                    if( $item['time_start'] < '08:00' ) { $str_time_1 = '~'; }
                    $str_time_2 = Date( 'H:i', mktime( $time_2[0], $time_2[1], $time_2[2], 0, 0, 0 ) );
                    if( $item['time_end'  ] > '17:00:01' ) { $str_time_2 = '~'; }


                    ?><div class="row wpsg-grid-data wpsg-data-row p-2" data-id= "<?php echo $encr_data_id; ?>">
                        <div class="col-12 col-sm-3 col-md-2 text-nowrap">
                            <div class="row">
                                <div class="col-3 col-sm-4 col-md-6 text-center"><?php echo $str_time_1; ?></div>
                                <div class="col-3 col-sm-4 col-md-6 text-center"><?php echo $str_time_2; ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-9 col-md-10" style="font-weight: 600;"><?php echo esc_html( $item['title'] ); ?></div>
                        <div class="col-12" style="font-weight: 300; font-style: italic;"><?php
                            echo esc_html( $item['description'] ) ?? '- tidak ada keterangan -';
                        ?></div>
                    </div><?php
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