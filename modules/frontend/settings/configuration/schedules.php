<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

if( !isset( $page_title ) ){
    $page_title = 'Tidak ada judul, hubungi developer';
}

$config_keys = [
    'schedule-weekly' => [
        'title' => 'Operasional Harian',
        'short' => 'Harian',
    ],
    'schedule-locale' => [
        'title' => 'Hari Peringatan Nasional',
        'short' => 'Nasional',
        // 'target' => 'schedule.locale',
        // 'file'   => 'schedule.locale.php',
        'columns'=> [
            ['name'=>'code'   , 'type'=>'MONTH'],
            ['name'=>'title'  , 'type'=>'VARCHAR(100)'],
            ['name'=>'holiday', 'type'=>'CHECKBOX'],
        ]
    ],
    'schedule-global' => [
        'title' => 'Hari Peringatan Internasional',
        'short' => 'Internasional',
        // 'target' => 'schedule.global',
        // 'file'   => 'schedule.global.php',
        'columns'=> [
            ['name'=>'code'   , 'type'=>'MONTH'],
            ['name'=>'title'  , 'type'=>'TEXT'],
            ['name'=>'holiday', 'type'=>'CHECKBOX'],
        ]
    ],
];

?>
<div class="container pb-4">
    <div class="d-none"><code id="data-list-json"></code></div>
    <div class="row">
        <div class="col-12">
            <div class="row mb-2">
                <div class="col-12 col-sm-8 text-start">
                    <h2><?php echo $page_title; ?></h2>
                </div>
                <div class="col-12 col-sm-4 text-end"><?php
                        echo fe_render_href_button([
                            'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null ], 
                            'exclude_keys'=>[], 
                            'class'=>'btn-cancel',
                            'title'=>'Kembali', 
                            'icon'=>'fas fa-reply fa-fw'
                        ]);
                ?></div>
            </div>
        </div>
        <div class="col-12">
            <div class="row">

                <div class="col-12">
                    <ul class="nav nav-tabs nav-wpsg" id="myTab" role="tablist"><?php

                        $is_active = true;
                        foreach( $config_keys as $key => $item ){

                            $nav_active = '';
                            $nav_select = 'false';

                            if( $is_active ){
                                $nav_active = ' active';
                                $nav_select = 'true';
                                $is_active  = false;
                            }

                            ?><li class="nav-item" role="presentation"><?php
                                ?><button type="button" class="nav-link<?php echo $nav_active; ?>"<?php
                                    ?> id="<?php echo $key; ?>-tab"<?php
                                    ?> data-bs-toggle="tab"<?php
                                    ?> data-bs-target="#<?php echo trim( $key ); ?>-area"<?php
                                    ?> role="tab"<?php
                                    ?> aria-controls="<?php echo $key; ?>-tab"<?php
                                    ?> aria-selected="<?php echo ($is_active ? 'true' : 'false'); ?>"><?php
                                    ?><span class="d-inline d-sm-inline d-md-inline d-lg-none"><?php echo $item['short']; ?></span><?php
                                    ?><span class="d-none d-sm-none d-md-none d-lg-inline"><?php echo $item['title']; ?></span><?php
                                ?></button><?php
                            ?></li><?php

                        }

                    ?></ul>
                    <div class="tab-content wpsg-grid-border px-0" id="myTabContent"><?php

                        $is_active = true;
                        foreach( $config_keys as $key => $item ){

                            $file_name = $key . '.php';
                            $tab_class = '';
                            if( $is_active ){
                                $tab_class = ' show active';
                                $is_active = false;
                            }

                            ?><div class="tab-pane fade<?php echo $tab_class; ?> py-2 px-3" <?php
                                ?>id="<?php echo trim( $key ); ?>-area" <?php
                                ?>role="tabpanel" <?php
                                ?>aria-labelledby="<?php echo $key; ?>-tab" <?php
                                /*
                                ?>style="height: 480px; overflow-y: scroll"<?php
                                */
                                ?>><?php

                                if( file_exists( __DIR__ . '/' . $file_name ) ){

                                    require_once( __DIR__ . '/' . $file_name );

                                } else {
                                    ?><div class="row container">
                                        <div class="col-12 p-5 wpsg-grid-border text-center">
                                            UNDER CONSTRUCTION
                                        </div>
                                    </div><?php
                                }

                            ?></div><?php
                        }

                    ?></div>
                </div>

            </div>
        </div>
    </div>
</div>