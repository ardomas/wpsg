<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$site_id = wpsg_get_network_id();

$user = wp_get_current_user();
$page_title = "Catatan Bulanan Perkembangan Anak";

$init_children   = new WPSG_ChildrenService();
$init_indicators = new WPSG_IndicatorsService();

$indicator_scores = fe_get_app_json_score_data();
// fe_get_app_json_data('scores')['indicators'];

$children_list = $init_children->get_children();
$indi_inlist   = $init_indicators->get_list();
$indi_outlist  = $init_indicators->get_list([], true);
$indicat_list  = $init_indicators->get_categories_output();
$children_with_keys = [];
$indi_in_with_keys  = [];
$indi_out_with_keys = [];

foreach( $children_list as $item ){ $children_with_keys[$item['id']] = $item; }
foreach( $indi_inlist   as $item ){ $indi_in_with_keys[$item['id']]  = $item; }
foreach( $indi_outlist  as $item ){ $indi_out_with_keys[$item['id']] = $item; }

/*
?><xmp><?php
    print_r( $indicat_list );
?></xmp><xmp><?php
    print_r( $children_list );
?></xmp><xmp><?php
    print_r( $indi_inlist );
?></xmp><?php
/* */

?><div class="row pb-4">
    <div class="col-12">
        <div class="row mb-2" id="header-title-area">
            <div class="col-12 col-sm-8 text-start">
                <h2><?php echo $page_title; ?></h2>
            </div>
            <div class="col-12 col-sm-4 text-end">
                <div class="row"><div class="col-12 d-inline"><?php
                    /*
                    echo fe_render_href_button([
                        'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null, 's2'=>$_GET['s2'] ?? null, 'act'=>wpsg_encrypt('add') ], 
                        'class'=>'btn-process mx-1',
                        'title'=>'Tambah',
                        'icon'=>'fas fa-plus fa-fw'
                    ]);
                    */
                    echo fe_render_href_button([
                        'url_params'=>[ 'sid' => $_GET['sid'] ], 
                        'exclude_keys'=>[], 
                        'class'=>'btn-process',
                        'title'=>'Kembali', 
                        'icon'=>'fas fa-reply fa-fw'
                    ]);
                ?></div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="d-none" id="form-data-code">
            <code class="d-none" id="children_with_keys"><?php
                echo json_encode( $children_with_keys );
            ?></code>
            <code class="d-none" id="indicators_in_list"><?php
                echo json_encode( $indi_in_with_keys );
            ?></code>
            <code class="d-none" id="indicators_out_list"><?php
                echo json_encode( $indi_out_with_keys );
            ?></code>
            <code class="d-none" id="indicators_scores"><?php
                echo json_encode( $indicator_scores );
            ?></code>
        </div>
        <div class="form">
            <div class="form-group">
                <div class="row">
                    <div class="d-none d-sm-none d-md-none d-lg-inline col-lg-2">
                        <div class="row">
                            <div class="col-12 m-2 py-1 px-3 g-2">
                                <label class="form-label">Daftar Nama</label>
                                <div class="row wpsg-grid-border">
                                    <div class="col-12 wpsg-boxed wpsg-grid-hover px-0 g-2" id="area_child_list" style="height: 521px; overflow-y: scroll"><?php
                                        foreach( $children_list as $item ){
                                            ?><div class="wpsg-grid-data child_data_row wpsg-data-row wpsg-m-0 py-2 px-3" data-id= "<?php echo $item['id']; ?>"><?php
                                                echo $item['name']; 
                                            ?></div><?php
                                        }
                                    ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-inline col-12 col-sm-12 col-md-12 col-lg-10">
                        <div class="row" id="page-header-area">
                            <div class="col-12 my-2 mx-1 py-1 g-2">
                                <div class="row px-3">
                                    <div class="col-12 col-sm-12 col-md-5 g-2">
                                        <label class="form-label" for="child_id">Nama Anak</label>
                                        <select class="form-control form-select" id="child_id" name="child_id"><?php
                                        foreach( $children_list as $child ){
                                            ?><option value="<?php echo $child['id']; ?>"><?php echo $child['name']; ?></option><?php    
                                        }
                                        ?></select>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-7 g-2">
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-4">
                                                <label class="form-label" for="toggle_month">Bulan</label>
                                                <input type="month"  id="toggle_date" name="toggle_date" class="form-control text-center"/>
                                            </div>
                                            <div class="col-6 col-sm-5">
                                                <label class="form-label">Usia</label>
                                                <div class="input-group">
                                                    <input type="number" id="age_month" name="age_month" readonly class="form-control text-center" value="0"/>
                                                    <span class="input-group-text">bulan</span>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <label class="form-label">Status</label>
                                                <div class="row m-0 p-0">
                                                    <span id="info_status" class="text-center btn">
                                                        Draft
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row d-none">
                                    <input type="number" class="d-none" id="person_rec_indicator_id" name="person_rec_indicator_id" value=""/>
                                    <input type="date"   class="d-none" id="date_record"             name="date_record"/>
                                </div>
                            </div>
                        </div>
                        <div class="row wpsg-grid-border px-3">
                            <ul class="nav nav-tabs nav-wpsg" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="indicators_list_area-tab" data-bs-toggle="tab" data-bs-target="#indicators_list_area" type="button" role="tab" aria-controls="indicator" aria-selected="true">
                                        Parameter<span class="d-none d-sm-inline"> Perkembangan</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="indicators_note_area-tab" data-bs-toggle="tab" data-bs-target="#indicators_note_area" type="button" role="tab" aria-controls="note" aria-selected="false">
                                        Deskripsi<span class="d-none d-sm-inline"> Kemampuan</span>
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content px-0" id="myTabContent">
                                <div class="tab-pane fade show active" id="indicators_list_area" role="tabpanel" aria-labelledby="indicators_list_area-tab"  style="height: 25%; overflow-y: scroll">
                                    <div class="container wpsg-grid-hover m-0 p-0">
                                        <div class="row m-0 py-3 px-0" id="indicators_list_area-content">Test 1</div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="indicators_note_area" role="tabpanel" aria-labelledby="note-tab" style="overflow-y: scroll">
                                    <div class="row container m-0 p-0">
                                        <div class="col-12 m-0 py-2 px-3 text-end">
                                            <span class="btn btn-process" id="btn_indicators_note_save">
                                                <i class="fas fa-floppy-disk fa-fw"></i> Simpan
                                            </span>
                                        </div>
                                        <div class="col-12 m-0 pt-0 pb-2 px-0 g-2" id="indicators_note_area_container">
                                            <?php
                                                // Ambil konten lama jika ada
                                                $saved_content = get_option('my_custom_content', '');
                                                // Panggil editor
                                                wp_editor($saved_content, 'indicators_note_area_content', [
                                                    'textarea_name' => 'indicators_note_area_content',
                                                    'textarea_rows' => 30,
                                                    'media_buttons' => false,
                                                    'tinymce'       => true ,
                                                    'quicktags'     => false
                                                ]);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
        </div>

        <div id="wpsg-data-published" class="wpsg-row-menu" style="display:none;">
            <ul>
                <li><a href="#" id="wpsg-data-published-action" data-handler="do-js-process-publish" data-action="publish">Publish</a></li>
            </ul>
        </div>

        <div id="wpsg-indicators-data-menu" class="wpsg-row-menu" style="display:none;">
            <ul><?php

                foreach( $indicator_scores as $item ){
                    ?><li><a href="javascript: void(0);" data-handler="do-js-process-score" data-value="<?php echo $item['score']; ?>">
                        <div class="row">
                            <div class="col-2 text-nowrap text-center"><?php echo $item['symbol']; ?></div>
                            <div class="col-10"><?php echo $item['title']; ?></div>
                        </div>
                    </a></li><?php
                }

            ?></ul>
        </div>

        <style>

            .wpsg-row-menu {
                position: absolute;
                background: #fff;
                border: 1px solid #ddd;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                border-radius: 6px;
                z-index: 9999;
            }

            .wpsg-row-menu ul {
                list-style: none;
                margin: 0;
                padding: 6px 0;
            }

            .wpsg-row-menu li a {
                display: block;
                padding: 6px 14px;
                text-decoration: none;
                color: #333;
            }

            .wpsg-row-menu li a:hover {
                background: #f0f0f0;
            }

        </style>
<?php
/* */
?>
        <script type="text/javascript" lang="javascript">
            (function(){
                document.addEventListener('DOMContentLoaded', function(){

                    let current_date = new Date();
                    let child_age    = 0;
                    let person_rec_indicator_id = 0;
                    let db_person_indicator_master = [];
                    let db_person_indicator_detail = [];
                    let score2symbols = [];
                    let temp_scores = JSON.parse( document.getElementById('indicators_scores').textContent );
                    temp_scores.forEach((item)=>{ score2symbols[ item['score'] ] = item['symbol']; });

                    let current_indicators_list = [];
                    let current_indicators_list_with_key = [];

                    var set_screen_size=(()=>{
                        let area_header      = document.getElementById('page-header-area' );
                        let area_title       = document.getElementById('header-title-area');
                        let area_child_list  = document.getElementById('area_child_list');
                        let area_indicators  = document.getElementById('indicators_list_area');
                        let area_description = document.getElementById('indicators_note_area');
                        // let rect_page_area   = area_page.getBoundingClientRect();
                        // let rect_title_area = area_title.getBoundingClientRect();
                        let header_rect     = area_header.getBoundingClientRect();
                        let rect_child_list = area_child_list.getBoundingClientRect();
                        let height_center_area = window.innerHeight - 120;
                        if( height_center_area <  639 ){ height_center_area =  639; }
                        //
                        area_indicators.style.height = ( height_center_area - header_rect.height ) + 'px';
                        //
                        let rect_indicators = area_indicators.getBoundingClientRect();
                        area_child_list.style.height = ( height_center_area - 8  ) + 'px';
                        area_description.style.height = area_indicators.style.height;
                        //
                        document.getElementById('indicators_note_area_content').style.height = ( height_center_area - header_rect.height - 120 ) + 'px';
                        //
                    });

                    let currentRow = null;

                    const menu = document.getElementById('wpsg-indicators-data-menu');

                    const menu_published = document.getElementById('wpsg-data-published');
                    menu_published.addEventListener('click', function (e) {
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        e.preventDefault();
                        let init_data = { 'id': person_rec_indicator_id, 'status': e.target.dataset.action };
                        console.log( init_data );
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_publish_person_indicator',
                            nonce: '<?php echo wp_create_nonce('publish_person_indicator'); ?>',
                            data: init_data
                        }, (response)=>{
                            if( response.success ){
                                // let data = response.data;
                                db_person_indicator_master = response.data;
                                render_indicator_master_status();
                                // console.log(db_person_indicator_master);
                            } else {
                                console.error( response.data );
                            }
                        });
                    });

                    document.addEventListener('click', function (e) {
                        if(!(menu.contains(e.target))) {
                            if(!(e.target.closest('.wpsg-grid-data'))){
                                menu.style.display = 'none';
                            }
                            if(!(e.target.closest('#info_status'))){
                                menu_published.style.display = 'none';
                            }
                        }
                    });

                    var fetch_data_children = function(){
                        let ajaxUrl   = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = Array();
                        //
                        // console.log( ajaxUrl );
                        //
                        jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_children',
                            nonce: '<?php echo wp_create_nonce('fetch_children'); ?>'
                        }, (response)=>{
                            if( response.success ){
                                init_data = response.data;
                            } else {
                                console.error( response.data );
                            }
                        });
                        return init_data;
                    }

                    var fetch_data_indicators = function(){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = Array();
                        //
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_get_indicators',
                            nonce: '<?php echo wp_create_nonce('get_indicators'); ?>'
                        }, (response)=>{
                            if( response.success ){
                                init_data = response.data;
                                document.getElementById('indicators_in_list').textContent = JSON.stringify( init_data );
                            } else {
                                console.error( response.data );
                            }
                        });
                        // return init_data;
                    }

                    var fetch_data_indicators_output = function(){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = Array();
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_get_indicators_output',
                            nonce: '<?php echo wp_create_nonce('get_indicators_output'); ?>'
                        }, (response)=>{
                            if( response.success ){
                                init_data = response.data;
                                document.getElementById('indicators_out_list').textContent = JSON.stringify( init_data );
                            } else {
                                console.error( response.data );
                            }
                        });
                    }

                    var display_active_child=((id)=>{
                        elms_children = document.getElementsByClassName('child_data_row');
                        for( let i = 0; i < elms_children.length; i++ ){
                            elms_children[i].classList.remove('active');
                            if( elms_children[i].getAttribute('data-id') == id ){
                                elms_children[i].classList.add('active');
                            }
                        }
                    })

                    var show_menu_status=((e)=>{
                        let act_area = document.getElementById('wpsg-data-published-action');
                        if( db_person_indicator_master['date_publish']==null || db_person_indicator_master['date_publish']=='0000-00-00 00:00:00' ){
                            act_area.setAttribute('data-action', 'publish');
                            act_area.textContent = 'Publish';
                        } else {
                            act_area.setAttribute('data-action', 'unpublish');
                            act_area.textContent = 'Unpublish';
                        }
                        e.preventDefault();
                        menu_published.style.display = 'block';
                        menu_published.style.position = 'fixed';
                        menu_published.style.top  = e.clientY + 10 + 'px';
                        menu_published.style.left = e.clientX + 10 + 'px';
                    })

                    var refresh_indicators_area=(()=>{
                        let obj_indi_area = document.getElementById('indicators_list_area-content');
                        let obj_indi_data = obj_indi_area.querySelectorAll('.wpsg-grid-data');
                        //
                        // console.log( obj_indi_data );
                        //
                        obj_indi_data.forEach((item)=>{
                            let data_id = item.getAttribute('data-id');
                            item.addEventListener('click',(e)=>{

                                // console.log( data_id );
                                let body_rect = document.getElementById('indicators_list_area-content').getBoundingClientRect();
                                e.preventDefault();

                                pos_y = e.clientY - 20;
                                pos_x = e.clientX + 10;
                                if( pos_x > body_rect.right - menu.offsetWidth ){
                                    pos_x = body_rect.right - menu.offsetWidth - 10;
                                }
                                if( pos_x > body_rect.right - 120 ){
                                    pos_x = e.clientX - menu.offsetWidth - 10;
                                    pos_y = e.clientY + 10;
                                }

                                currentRow = item;

                                menu.style.display = 'block';
                                menu.style.position = 'fixed';
                                menu.style.top  = pos_y + 'px';
                                menu.style.left = pos_x + 'px';

                            });
                        });
                    });

                    var fetch_data_person_indicator_detail=((person_rec_indicator_id)=>{
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        // current_indicators_list
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_person_indicator_detail',
                            nonce: '<?php echo wp_create_nonce('fetch_person_indicator_detail'); ?>',
                            data: {
                                'id': person_rec_indicator_id
                            }
                        }, (response)=>{
                            if( response.success ){
                                let data = response.data;
                                // console.log( data );
                                db_person_indicator_detail[1 * data['indicator_id']] = data;
                            }
                        });
                    });

                    var fetch_data_person_indicator_master=((person_rec_indicator_id)=>{
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        // current_indicators_list
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_person_indicator_master',
                            nonce: '<?php echo wp_create_nonce('fetch_person_indicator_master'); ?>',
                            data: {
                                'id': person_rec_indicator_id
                            }
                        }, (response)=>{
                            if( response.success ){
                                let data = response.data;
                                // console.log( data );
                                db_person_indicator_master = data;
                            }
                        });
                    });

                    var submit_data_person_indicator_detail=((data)=>{
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_submit_person_indicator_detail',
                            nonce: '<?php echo wp_create_nonce('submit_person_indicator_detail'); ?>',
                            data: data
                        }, (response)=>{
                            if( response.success ){
                                let data = response.data;
                                // console.log(data);
                                let itemRow = current_indicators_list_with_key[data['indicator_id']];
                                db_person_indicator_detail[1 * data['indicator_id']] = data;
                                // console.log( data );
                                // console.log( current_indicators_list );
                                let effected_row = document.getElementById('indicator_row_' + data['indicator_id']);
                                let str_html = render_indicators_one_row_content( itemRow );
                                effected_row.innerHTML = str_html;
                                refine_indicator_list_content( itemRow );
                                // console.log( effected_row );
                            }
                        });
                    })

                    var ensure_data_detail=((data_id)=>{
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        document.getElementById('person_rec_indicator_id').value = '';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_ensure_person_indicator_data_detail',
                            nonce : '<?php echo wp_create_nonce('ensure_person_indicator_data_detail'); ?>',
                            data: {
                                'person_rec_indicator_id'   : person_rec_indicator_id,
                                'indicator_id'              : data_id
                            }
                        }, (response)=>{
                            if( response.success) {
                                // console.log( response );
                                if( typeof(db_person_indicator_detail[1 * data_id]) == 'undefined' ){
                                    db_person_indicator_detail[data_id] = {
                                        'id' : response.data,
                                        'person_rec_indicator_id' : person_rec_indicator_id,
                                        'indicator_id' : data_id,
                                        'score' : 0
                                    }
                                }
                                return fetch_data_person_indicator_detail(response.data);
                                //
                            } else {
                                // console.log( response.data );
                                window.alert('halt process');
                                //
                                return 0;
                            }
                        });
                    });

                    var ensure_data_master=(()=>{
                        if( child_age > 0 ){
                            let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                            document.getElementById('person_rec_indicator_id').value = '';
                            return jQuery.post( ajaxUrl, {
                                action: 'wpsg_ensure_person_indicator_data_master',
                                nonce : '<?php echo wp_create_nonce('ensure_person_indicator_data_master'); ?>',
                                data: {
                                    'person_id'   : document.getElementById('child_id').value,
                                    'date_record' : document.getElementById('date_record').value
                                }
                            }, (response)=>{
                                if( response.success ){
                                    // console.log( response );
                                    person_rec_indicator_id = response.data;
                                    document.getElementById('person_rec_indicator_id').value = person_rec_indicator_id;                                        
                                    //
                                    // render_report_area_data();
                                    return fetch_data_person_indicator_master(person_rec_indicator_id).then(()=>{ 
                                        render_report_area_data(); 
                                    });
                                    //
                                } else {
                                    console.error( response.data );
                                }
                            });
                        }
                    });

                    var refine_indicator_list_content=((item)=>{
                        let css_grades = ['grade-0','grade-1','grade-2','grade-3'];
                        let score = db_person_indicator_detail[ item['id'] ]['score'] ?? 0;
                        let elm_indi_row = document.getElementById( 'indicator_row_' + item['id'] );
                        let elm_indi_score_area = document.getElementById( 'score_area_' + item['id'] );
                        let elm_indi_score_label = document.getElementById( 'score_label_' + item['id'] );
                        elm_indi_row.setAttribute('data-id', db_person_indicator_detail[ item['id'] ]['id']);
                        elm_indi_score_label.textContent = score2symbols[score];
                        css_grades.forEach((css_item)=>{
                            elm_indi_score_area.classList.remove(css_item);
                        }); elm_indi_score_area.classList.add('grade-' + score);
                    })
                    var render_indicators_one_row_content = ((item)=>{
                        let score = 0;
                        let str_html = '<div class="row m-0 px-3">'
                                     +      '<div class="col-9 col-sm-10 col-md-11">'
                                     +          '<span class="form-label">' + item['title'] + '</span>'
                                     +      '</div>'
                                     +      '<div class="col-3 col-sm-2 col-md-1 text-center" id="score_area_' + item['id'] + '" style="border-width: 1px; border-radius: 9px;">'
                                     +          '<label class="form-label p-1 text-center data-score-label text-nowrap fw-bold" id="score_label_' + item['id'] + '" style="cursor: pointer;">' 
                                     +              score2symbols[score]  
                                     +          '</label>'
                                     +       '</div>'
                                     + '</div>';
                        return str_html;
                    });

                    var render_indicators_one_row = ((item)=>{
                        let data_id = 0;
                        let str_html =  '<div class="col-12 wpsg-grid-data wpsg-grid-border-bottom m-0 p-2" id="indicator_row_' + item['id'] + '" data-id="' + data_id + '" indicator-id="' + item['id'] + '">'
                                     +      render_indicators_one_row_content(item)
                                     +  '</div>';
                        return str_html;
                    });

                    var render_indicator_list=(()=>{
                        let elm_content_indicators_list_area = document.getElementById('indicators_list_area-content');
                        db_person_indicator_detail = [];
                        elm_content_indicators_list_area.childNodes.forEach((item)=>{
                            item.remove();
                        })
                        str_html = '';
                        num_test = 0;
                        current_indicators_list.forEach((item)=>{
                            str_html = render_indicators_one_row(item);
                            elm_content_indicators_list_area.innerHTML = elm_content_indicators_list_area.innerHTML + str_html;
                            ensure_data_detail(item['id']).then(()=>{
                                fetch_data_person_indicator_detail( db_person_indicator_detail[ item['id'] ]['id'] ).then(()=>{
                                    num_test++;
                                    // refining data
                                    refine_indicator_list_content(item);
                                    // console.log(item);
                                    if( num_test == current_indicators_list.length ){
                                        refresh_indicators_area();
                                        // console.log( db_person_indicator_detail );
                                    }
                                })
                            });
                        });
                        //
                    });

                    var render_indicator_master_status=(()=>{
                        let info_status_area = document.getElementById('info_status');
                        // console.log( db_person_indicator_master );
                        info_status_area.classList.remove('btn-success');
                        info_status_area.classList.remove('btn-warning');
                        if( db_person_indicator_master['date_publish']==null || typeof( db_person_indicator_master['date_publish'] )=='null' ){
                            info_status_area.textContent = 'Draft';
                            info_status_area.classList.add('btn-warning');
                        } else {
                            info_status_area.textContent = 'Published';
                            info_status_area.classList.add('btn-success');
                        }                        
                    })

                    var set_wp_content_editor = (()=>{
                        let str_text = db_person_indicator_master['note_by_teacher'] ?? '';
                        if (window.tinymce && tinymce.get('indicators_note_area_content')) {
                            tinymce.get('indicators_note_area_content').setContent(str_text);
                        } else {
                            // Fallback jika editor belum siap, masukkan ke textarea native
                            document.getElementById('indicators_note_area_content').value = str_text;
                        }
                    });

                    var submit_indicators_note=(()=>{
                        let str_text = tinymce.get('indicators_note_area_content').getContent();
                        let ajaxUrl  = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_submit_person_indicator_master',
                            nonce : '<?php echo wp_create_nonce('submit_person_indicator_master'); ?>',
                            data: {
                                'id'              : person_rec_indicator_id,
                                'note_by_teacher' : str_text
                            }
                        }, (response)=>{
                            if( response.success ){
                                db_person_indicator_master['note_by_teacher'] = str_text;
                                console.log( response.data );
                            } else {
                                console.error( response.data );
                            }
                        });
                    });

                    var render_report_area_data=(()=>{
                        render_indicator_master_status();
                        render_indicator_list();
                        set_wp_content_editor();
                    });

                    var get_last_date_from_input_month = function(){
                        let toggle_date = document.getElementById('toggle_date').value;
                        let cur_date = new Date( toggle_date );
                        let new_date = new Date( cur_date.getFullYear(), cur_date.getMonth() + 1, 0);
                        current_date = new_date;
                        return new_date;
                    }

                    var set_to_last_month = function(){
                        let cur_date = new Date();
                        let last_day = new Date( cur_date.getFullYear(), cur_date.getMonth() + 1, 0);
                        let cur_YY = last_day.getFullYear();
                        let cur_mm = String( last_day.getMonth()+1 ).padStart( 2, '0' );
                        let cur_dd = String( last_day.getDate()    ).padStart( 2, '0' );
                        document.getElementById('toggle_date').value = cur_YY + '-' + cur_mm;
                        //
                        current_date = get_last_date_from_input_month();
                        document.getElementById('date_record').value = current_date.getFullYear() + '-' + String( current_date.getMonth()+1 ).padStart( 2, '0' ) + '-' + String( current_date.getDate() ).padStart( 2, '0' );
                        //
                        return current_date;
                    }
                    var render_indicator_list_by_age=(()=>{
                        let indi_inlist = JSON.parse( document.getElementById('indicators_in_list').textContent );
                        let indi_select = new Array();
                        current_indicators_list = new Array();
                        current_indicators_list_with_key = new Array();
                        document.getElementById('indicators_list_area-content').innerHTML = '';
                        Object.keys(indi_inlist).forEach( (key)=>{
                            item = indi_inlist[key];
                            if( 1 * item['age_min_month'] <= child_age ){
                                if( 1 * item['age_max_month'] > child_age ){
                                    current_indicators_list.push(item);
                                    current_indicators_list_with_key[item['id']] = item;
                                }
                            }
                        });
                        return current_indicators_list;
                    });

                    var count_age_in_month = function( birth_date ){
                        //
                        // console.log( birth_date   );
                        // console.log( current_date );
                        //
                        let cur_YY = current_date.getFullYear();
                        let cur_mm = String( current_date.getMonth()+1 ).padStart( 2, '0' );
                        let cur_dd = String( current_date.getDate()    ).padStart( 2, '0' );
                        let birth = new Date( birth_date );
                        let birth_YY = birth.getFullYear();
                        let birth_mm = String( birth.getMonth()+1 ).padStart( 2, '0' );
                        let birth_dd = String( birth.getDate()    ).padStart( 2, '0' );
                        //
                        child_age = ( 1 * ( cur_YY * 12 ) + ( 1 * cur_mm ) ) - ( 1 * ( birth_YY * 12) + ( 1 * birth_mm ) );
                        //
                        return child_age;
                    }

                    var selected_child = (()=>{
                        let children_list = JSON.parse( document.getElementById('children_with_keys').textContent );
                        let child_id = document.getElementById('child_id').value;
                        //
                        // console.log( child_id );
                        //
                        let child_select = children_list[child_id];
                        display_active_child( child_id );
                        //
                        // console.log( child_select );
                        //
                        let age = count_age_in_month( child_select.birth_date );
                        document.getElementById( 'age_month' ).value = age;
                        render_indicator_list_by_age();
                        //
                        ensure_data_master();
                        //
                    });

                    var change_toggle_date=(()=>{
                        let children_list = JSON.parse( document.getElementById('children_with_keys').textContent );
                        let child_id = document.getElementById('child_id').value;
                        let selected_child = children_list[child_id];
                        //
                        // console.log( children_list );
                        //
                        let last_day = get_last_date_from_input_month();
                        current_date = last_day;
                        document.getElementById('date_record').value = last_day.getFullYear() + '-' + String( last_day.getMonth()+1 ).padStart( 2, '0' ) + '-' + String( last_day.getDate() ).padStart( 2, '0' );
                        // set_to_last_month();
                        // console.log( selected_child );
                        let age = count_age_in_month( selected_child.birth_date );
                        document.getElementById( 'age_month' ).value = age;
                        render_indicator_list_by_age();
                        //
                        ensure_data_master();
                        //
                    });

                    var register_action_for_children_area=(()=>{
                        let obj_child_list = document.querySelectorAll('.child_data_row');
                        obj_child_list.forEach((item)=>{
                            id = item.getAttribute('data-id');
                            item.addEventListener('click',()=>{ 
                                id = item.getAttribute('data-id');
                                document.getElementById('child_id').value = id;
                                //
                                selected_child(); 
                                //
                                menu.style.display = 'none';
                            });
                        });
                    });

                    var register_action_for_indicator_score=(()=>{
                        let obj_score_actions = document.querySelectorAll('[data-handler="do-js-process-score"]');
                        obj_score_actions.forEach((item)=>{
                            item.addEventListener('click',()=>{
                                //
                                let data_id = currentRow.getAttribute('data-id');
                                let data_indicator_id = currentRow.getAttribute('indicator-id');
                                //
                                let data = {
                                    'id': data_id,
                                    'person_rec_indicator_id': person_rec_indicator_id,
                                    'indicator_id': currentRow.getAttribute('indicator-id'),
                                    'score': item.getAttribute('data-value')
                                };
                                //
                                // console.log( data );
                                //
                                submit_data_person_indicator_detail(data);
                                //
                                menu.style.display = 'none';
                                //
                            })
                        });
                    });

                    set_screen_size();
                    set_to_last_month();

                    init_children = fetch_data_children();
                    fetch_data_indicators().then(()=>{
                        fetch_data_indicators_output().then(()=>{
                            // console.log( JSON.parse( document.getElementById( 'indicators_in_list' ).textContent ) );
                            // console.log( JSON.parse( document.getElementById( 'indicators_out_list' ).textContent ) );
                            selected_child();
                            register_action_for_children_area();
                            register_action_for_indicator_score();
                            document.getElementById('info_status').addEventListener('click',(e)=>{ show_menu_status(e); });
                            document.getElementById('child_id'   ).addEventListener('change',()=>{ selected_child(); });
                            document.getElementById('toggle_date').addEventListener('change',(obj)=>{ change_toggle_date(); });
                            document.getElementById('btn_indicators_note_save').addEventListener('click',()=>{ 
                                submit_indicators_note(); 
                            });
                        });
                    })
                    // refresh_indicators_area();

                });
            })();
        </script>

    </form>

</div>