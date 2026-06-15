<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$site_id = wpsg_get_network_id();

$user = wp_get_current_user();
$page_title = "Catatan Harian Aktivitas Anak";

$repo_children   = new WPSG_ChildrenService();
$repo_activities = new WPSG_DailyActivitiesService();

$data_scores = fe_get_app_json_score_data();

// print_r( $data_scores );
// die('gagal?');

// fe_get_app_json_data('scores')['indicators'];

$children        = $repo_children->get_children();
$list_activities = $repo_activities->get_list();

$children_with_keys        = [];
$list_activities_with_keys = [];
foreach( $children as $item ){ $children_with_keys[$item['id']] = $item; } 
foreach( $list_activities as $item ){ 
    $index_key = ( ( isset( $item['time_start'] ) && $item['time_start']!=null ) ? $item['time_start'] : '00:00:00' ) . '_'
               . ( ( isset( $item['time_end'  ] ) && $item['time_end'  ]!=null ) ? $item['time_end'  ] : '23:59:59' ) . '_'
               . ( str_pad( $item['sort_order'], 6, '0', STR_PAD_LEFT ) );
    $list_activities_with_keys[$index_key] = $item; 
}

?><div class="row pb-4" id="action-daily-activity-page-form">

    <div class="d-none" id="form-data-code">
        <section group="system-variables">
            <code class="d-none" id="children_data"><?php   echo json_encode( $children_with_keys );        ?></code>
            <code class="d-none" id="activities_data"><?php echo json_encode( $list_activities_with_keys ); ?></code>
            <code class="d-none" id="data_scores"><?php     echo json_encode( $data_scores );               ?></code>
        </section>
    </div>

    <div class="col-12">
        <div class="row mb-4">
            <div class="col-9">
                <h3><?php echo $page_title; ?></h3>
            </div>
            <div class="col-3 text-end"><?php
                echo fe_render_href_button([
                    'url_params'=>[ 'sid' => $_GET['sid'] ], 
                    'exclude_keys'=>[], 
                    'class'=>'btn-process',
                    'title'=>'Kembali', 
                    'icon'=>'fas fa-reply fa-fw'
                ]);
            ?></div>
        </div>

        <div class="row">
            <div class="d-none d-sm-none d-md-none d-lg-inline col-lg-2">
                <div class="row">
                    <div class="col-12 m-2 px-3 g-2">
                        <div class="row wpsg-grid-border">
                            <label class="form-label">Daftar Nama</label>
                            <div class="col-12 wpsg-grid-hover wpsg-boxed m-0 px-0 g-2" id="children_list_area" style="height: 521px; overflow-y: scroll"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-inline col-12 col-sm-12 col-md-12 col-lg-10">
                <!-- filter text - begin -->
                <div class="row" id="page-header-area">
                    <div class="col-12 m-2 px-3 g-2">
                        <div class="row">
                            <div class="mb-3 col-12 col-sm-8 col-md-8 col-lg-8 col-xl-6">
                                <label class="form-label">Nama Anak</label>
                                <select class="form-select" id="child_id" name="child_id"><?php
                                foreach( $children as $child ){
                                    echo '<option value="'.$child['id'].'">'.$child['name'].'</option>';
                                }
                                ?></select>
                            </div>
                            <div class="mb-3 col-12 col-sm-4 col-md-4 col-lg-4 col-xl-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control text-center" id="date_record" name="date_record" value=""/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-6 col-sm-4 col-md-4 col-lg-4 col-xl-3">
                                <label class="form-label">Datang</label>
                                <div class="input-group">
                                    <input type="time" class="form-control text-center" id="time_check" name="time_check" placeholder="08:00" value=""/>
                                    <span class="input-group-text btn btn-process" id="btn_check_clear"><i class="fa fa-trash-alt fa-fw"></i></span>
                                </div>
                            </div>
                            <div class="mb-3 col-6 col-sm-4 col-md-4 col-lg-4 col-xl-3">
                                <label class="form-label">Pulang</label>
                                <div class="input-group">
                                    <input type="time" class="form-control text-center" id="time_leave" name="time_leave" placeholder="17:00" value=""/>
                                    <span class="input-group-text btn btn-process" id="btn_leave_clear"><i class="fa fa-trash-alt fa-fw"></i></span>
                                </div>
                            </div>
                            <div class="mb-3 col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3">
                                <div class="row m-0 p-0" id="info_status_area">
                                    <label class="form-label">Status</label>
                                    <span id="info_status" class="text-center btn">Draft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- filter text - end -->
                <!-- content - begin -->
                <div class="row">
                    <div class="col-12 m-2 px-3">
                        <ul class="nav nav-tabs nav-wpsg" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="activities_list_area-tab" data-bs-toggle="tab" data-bs-target="#activities_list_area" type="button" role="tab" aria-controls="activity" aria-selected="true">
                                    Aktivitas<span class="d-none d-sm-inline"> Harian</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="activities_note_area-tab" data-bs-toggle="tab" data-bs-target="#activities_note_area" type="button" role="tab" aria-controls="note" aria-selected="false">
                                    Keterangan
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content wpsg-grid-border px-0" id="myTabContent">
                            <div class="tab-pane fade show active" id="activities_list_area" role="tabpanel" aria-labelledby="activities_list_area-tab"  style="height: 25%; overflow-y: scroll">
                                <div class="container wpsg-grid-hover m-0 p-0">
                                    <div class="row m-0 py-2 px-0" id="activities_list_area-content">Test 1</div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="activities_note_area" role="tabpanel" aria-labelledby="note-tab" style="overflow-y: scroll">
                                <div class="row container m-0 p-0">
                                    <div class="col-12 m-0 py-2 px-3 text-end">
                                        <span class="btn btn-process" id="btn_activities_note_save">
                                            <i class="fas fa-floppy-disk fa-fw"></i> Simpan
                                        </span>
                                    </div>
                                    <div class="col-12 m-0 pt-0 pb-2 px-0 g-2" id="activities_note_area_container">
                                        <?php
                                            // Ambil konten lama jika ada
                                            $saved_content = get_option('my_custom_content', '');
                                            // Panggil editor
                                            wp_editor($saved_content, 'activitiy_note_area_content', [
                                                'textarea_name' => 'activitiy_note_area_content',
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
                <!-- content - end -->
            </div>
        </div>

        <div id="wpsg-data-published" class="wpsg-row-menu" style="display:none;">
            <ul>
                <li><a href="javascript:void(0);" id="wpsg-data-published-action" data-handler="do-js-process-publish" data-action="publish">Publish</a></li>
            </ul>
        </div>

        <div id="wpsg-activites-data-menu" class="wpsg-row-menu" style="display:none;">
            <ul><?php

                foreach( $data_scores as $item ){
                    ?><li><a href="javascript:void(0);" data-handler="do-js-process-score" data-value="<?php echo $item['score']; ?>">
                        <div class="row">
                            <div class="col-2 text-nowrap text-center"><?php echo $item['symbol']; ?></div>
                            <div class="col-10"><?php echo $item['title']; ?></div>
                        </div>
                    </a></li><?php
                }

            ?></ul>
        </div>

        <style>

            input[type="time"]::-webkit-clear-button {
                -webkit-appearance: clickablebox !important;
                display: inline-block !important;
            }
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

        <script type="text/javascript" lang="javascript">
            //
            var clear_child_nodes=((element)=>{
                if( element!=null ){

                }
            });
            //
            (()=>{
                //
                document.addEventListener('DOMContentLoaded',()=>{
                    //
                    var string_date = new Date( (new Date).toLocaleString() );
                    //
                    var person_activity_id = '';
                    var strDateTime = string_date.getFullYear() 
                                    + '-' + String( string_date.getMonth()+1 ).padStart(2, '0') 
                                    + '-' + String( string_date.getDate()    ).padStart(2, '0')
                                    + 'T' + String( string_date.getHours()   ).padStart(2, '0') 
                                    + ':' + String( string_date.getMinutes() ).padStart(2, '0')
                                    + ':' + String( string_date.getSeconds() ).padStart(2, '0');
                    var currentDate = ( strDateTime ).split('T')[0];
                    var temp_scores = JSON.parse( document.getElementById('data_scores').textContent );
                    var css_grades  = [];
                    var data_scores = [];
                        temp_scores.forEach((item)=>{
                            data_scores[ item['score'] ] = item;
                            css_grades.push( 'grade-' + item['score'] );
                        });
                    var currentData = [];
                    var currentDetailData = [];
                    var currentRow  = null;
                    //
                    var activities_data = JSON.parse( document.getElementById('activities_data').textContent );
                    console.log( activities_data );
                    //
                    var set_screen_size = function(){
                        let area_header      = document.getElementById('page-header-area' );
                        let area_title       = document.getElementById('header-title-area');
                        let area_child_list  = document.getElementById('children_list_area');
                        let area_activities  = document.getElementById('activities_list_area');
                        let area_description = document.getElementById('activities_note_area');
                        // let rect_page_area   = area_page.getBoundingClientRect();
                        // let rect_title_area = area_title.getBoundingClientRect();
                        let header_rect     = area_header.getBoundingClientRect();
                        let rect_child_list = area_child_list.getBoundingClientRect();
                        let height_center_area = window.innerHeight - 120;
                        if( height_center_area <  639 ){ height_center_area =  639; }
                        //
                        area_activities.style.height = ( height_center_area - header_rect.height - 18 ) + 'px';
                        //
                        let rect_activities = area_activities.getBoundingClientRect();
                        area_child_list.style.height = ( height_center_area - 8 ) + 'px';
                        area_description.style.height = area_activities.style.height;
                        //
                        document.getElementById('activitiy_note_area_content').style.height = ( height_center_area - header_rect.height - 120 ) + 'px';
                        //
                    }
                    //
                    const menu = document.getElementById('wpsg-activites-data-menu');
/*
                    menu.addEventListener('click', function (e) {
                        e.preventDefault();

                        let code_id = currentRow.getAttribute('code-id');

                        if (!action || !currentRow) return;

                        const url = new URL( currentRow.dataset.editUrl, window.location.origin );
                        url.searchParams.set( 'cid', code_id );
                        url.searchParams.set( 'act', action  );
                        url.searchParams.set( 'id' , currentRow.dataset.id );

                        console.log( url.toString() );

                        //
                        // window.location.href = url.toString();
                        //
                    });
*/
                    //
                    const menu_published = document.getElementById('wpsg-data-published');
                    menu_published.addEventListener('click', function (e) {
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        e.preventDefault();
                        let init_data = { 'id': person_activity_id, 'status': e.target.dataset.action };
                        // console.log( init_data );
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_publish_person_activity',
                            nonce: '<?php echo wp_create_nonce('publish_person_activity'); ?>',
                            data: init_data
                        }, (response)=>{
                            if( response.success ){
                                // let data = response.data;
                                ajax_fetch_data_master().then((response)=>{
                                    if(  response.success ){
                                        currentData = response.data;
                                        render_master_data_status();
                                    }
                                });
                                // load_activity_data_master();
                                // console.log(currentData);
                            } else {
                                console.error( response.data );
                            }
                        });
                    });
                    //
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
                    //
                    var show_menu_status=((e)=>{
                        let act_area = document.getElementById('wpsg-data-published-action');
                        if( currentData['date_publish']==null || currentData['date_publish']=='0000-00-00 00:00:00' ){
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
                    });
                    //
                    var set_child_absence=(()=>{
                        document.getElementById('info_status_area').classList.add('d-none');
                        document.getElementById('activities_list_area-content').innerHTML = '<div class="p-5 text-center">' 
                            + '<h5 class="alert alert-danger">Tidak Hadir</h5><p?>Untuk menandai kedatangan, harap mengisi jam kedatangan.</p>' 
                            + '</div>';                        
                    });
                    //
                    var ajax_fetch_data_master=(()=>{
                        let ajaxUrl   = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        currentDetailData = [];
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_person_activities_master',
                            nonce: '<?php echo wp_create_nonce('fetch_person_activities_master'); ?>',
                            data: { 'id': person_activity_id  }
                        });
                    });
                    //
                    var ajax_fetch_data_detail=(()=>{
                        let ajaxUrl   = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        currentDetailData = [];
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_person_activities_detail',
                            nonce: '<?php echo wp_create_nonce('fetch_person_activities_detail'); ?>',
                            data: { 'person_activity_id': person_activity_id  }
                        });
                    });
                    //
                    var render_activities_scores=(()=>{
                        Object.keys( currentDetailData ).forEach((key) => {
                            let item = currentDetailData[key];
                            let score_area = document.getElementById('score_area_' + item['daily_activity_id'] );
                            let daily_activity_id = item['daily_activity_id'];
                            let score  = item['score'] ?? 0;
                            let symbol = data_scores[ score ]['symbol'];
                            document.getElementById( 'score_label_' + daily_activity_id ).textContent = symbol;
                            css_grades.forEach((css_item)=>{
                                score_area.classList.remove(css_item);
                            })
                            score_area.classList.add( 'grade-' + score );
                        })
                    });
                    //
                    var register_action_for_activity_score=(()=>{
                        let obj_score_actions = document.querySelectorAll('[data-handler="do-js-process-score"]');
                        obj_score_actions.forEach((item)=>{
                            item.addEventListener('click',()=>{
                                //
                                let data_id = currentRow.getAttribute('data-id');
                                let data_indicator_id = currentRow.getAttribute('indicator-id');
                                //
                                let data = {
                                    'id': data_id,
                                    'person_activity_id': person_activity_id,
                                    'daily_activity_id': currentRow.getAttribute('daily-activity-id'),
                                    'score': item.getAttribute('data-value')
                                };
                                //
                                console.log( currentRow );
                                console.log( data );
                                //
                                let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                                return jQuery.post( ajaxUrl, {
                                    action: 'wpsg_submit_person_activity_data_detail',
                                    nonce: '<?php echo wp_create_nonce('submit_person_activity_data_detail'); ?>',
                                    data: data
                                }, (response)=>{
                                    if( response.success ){
                                        // let data = response.data;
                                        currentRow = response.data;
                                        ajax_fetch_data_detail().then((response)=>{
                                            currentDetailData = response.data;
                                            render_activities_scores();
                                        });
                                        // load_activity_data_master();
                                        // console.log(currentData);
                                    } else {
                                        console.error( response.data );
                                    }
                                    menu.style.display = 'none';
                                });
                                //
                                // update_data_person_indicator_detail(data);
                                //
                                //
                            })
                        });
                    });
                    //
                    var render_data_detail_item=((item)=>{
                        // let item_score = currentDetailData[item['id']];
                        // console.log( item_score );
                        let str_html =  '<div class="row">'
                                     +      '<div class="col-9 col-sm-10 col-md-10 col-lg-11 text-start">'
                                     +          '<div class="row">'
                                     +              '<div class="col-12 col-sm-6 col-md-4 col-xl-3 text-nowrap text-start">'
                                     +                  '<div class="row">' 
                                     +                      '<div class="col-6 text-nowrap">' + item['time_start'] + '</div>'
                                     +                      '<div class="col-6 text-nowrap">' + item['time_end']   + '</div>'
                                     +                  '</div>'
                                     +              '</div>'
                                     +              '<div class="col-12 col-sm-6 col-md-8 col-xl-9">' + item['title'] + '</div>'
                                     +          '</div>'
                                     +      '</div>'
                                     +      '<div class="col-3 col-sm-2 col-md-2 col-lg-1 text-center" id="score_area_' + item['id'] + '" style="border-width: 1px; border-radius: 9px;">' 
                                     +              '<label class="form-label p-1 text-center data-score-label text-nowrap fw-bold" id="score_label_' + item['id'] + '" style="cursor: pointer;">' 
                                     +                  '* * *'
                                     +              '</label>'
                                     +      '</div>'
                                     +  '</div>';
                        return str_html;
                    });
                    //
                    var render_data_detail=(()=>{
                        //
                        let activities_list_area_content = document.getElementById('activities_list_area-content');
                        activities_list_area_content.innerHTML = '';
                        clear_child_nodes(activities_list_area_content);
                        let str_html = '';
                        Object.keys(activities_data).forEach( (idxkey) => {
                            let item     = activities_data[idxkey];
                            let trx_item = currentDetailData[item['id']];
                            // console.log( trx_item );
                            let elm_row = document.createElement('div');
                            let attributes = {
                                'data-id': trx_item['id'],
                                'person-activity-id': person_activity_id,
                                'daily-activity-id': item['id'],
                                'name': 'daily-activity-id'
                            };
                            elm_row.className = "col-12 wpsg-grid-data wpsg-grid-border-bottom wpsg-data-row m-0 py-2 px-3";
                            Object.keys(attributes).forEach( (attrkey) => {
                                elm_row.setAttribute(attrkey, attributes[attrkey]);
                            });
                            elm_row.innerHTML = render_data_detail_item(item);
                            elm_row.addEventListener('click', (e)=>{

                                let body_rect = document.getElementById('activities_list_area-content').getBoundingClientRect();
                                e.preventDefault();

                                currentRow = elm_row;

                                elm_rect = elm_row.getBoundingClientRect();

                                pos_y = e.clientY - 10;
                                pos_x = e.clientX + 10;
                                if( pos_x > body_rect.right - 300 ){
                                    pos_x = body_rect.right - 340;
                                }
                                if( pos_y > body_rect.bottom - 180 ){
                                    pos_y = body_rect.bottom - 190;
                                }

                                if( (( body_rect.bottom - elm_rect.top - elm_rect.height ) < 48) ) 
                                {
                                    pos_y = e.clientY - 182;
                                }

                                menu.style.display = 'block';
                                menu.style.position = 'fixed';
                                menu.style.top  = pos_y + 'px';
                                menu.style.left = pos_x + 'px';

                            })
                            //
                            activities_list_area_content.appendChild(elm_row);
                            //
                        });
                        //
                    });
                    //
                    var fetch_data_detail=(()=>{
                        let ajaxUrl   = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        currentDetailData = [];
                        return ajax_fetch_data_detail().then((response)=>{
                            currentDetailData = response.data;
                            render_data_detail();
                            render_activities_scores();
                            //
                            register_action_for_activity_score();
                            //
                        });
                    });
                    //
                    var set_wp_content_editor = (()=>{
                        let str_text = currentData['note_by_teacher'] ?? '';
                        if (window.tinymce && tinymce.get('activitiy_note_area_content')) {
                            tinymce.get('activitiy_note_area_content').setContent(str_text);
                        } else {
                            // Fallback jika editor belum siap, masukkan ke textarea native
                            document.getElementById('activitiy_note_area_content').value = str_text;
                        }
                    });

                    var submit_daily_activity_note=(()=>{
                        let str_text = tinymce.get('activitiy_note_area_content').getContent();
                        let ajaxUrl  = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_submit_person_activity_data_master',
                            nonce : '<?php echo wp_create_nonce('submit_person_activity_data_master'); ?>',
                            data: {
                                'id'              : person_activity_id,
                                'note_by_teacher' : str_text
                            }
                        }, (response)=>{
                            if( response.success ){
                                currentData['note_by_teacher'] = str_text;
                                console.log( response.data );
                            } else {
                                console.error( response.data );
                            }
                        });
                    });

                    var submit_daily_activity_data_master=(()=>{
                        let ajaxUrl   = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = {
                            'id'         : person_activity_id,
                            'date_record': document.getElementById('date_record').value
                        };
                        if( document.getElementById( 'time_check' ).value != '00:00' ){
                            init_data['time_check'] = document.getElementById( 'time_check' ).value;
                        }
                        if( document.getElementById( 'time_leave' ).value != '00:00' ){
                            init_data['time_leave'] = document.getElementById( 'time_leave' ).value;
                        }
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_submit_person_activity_data_master',
                            nonce : '<?php echo wp_create_nonce('submit_person_activity_data_master'); ?>',
                            data: init_data,
                        }, (response)=>{
                            if( response.success ){
                                fetch_data_master();
                            } else {
                                console.error( response.data );
                            }
                        });
                    })

                    var render_master_data_status=(()=>{
                        console.log( currentData );
                        let info_status_area = document.getElementById('info_status_area');
                        info_status_area.classList.remove('d-none');
                        if( currentData['date_publish']!=null ){
                            info_status.classList.remove('btn-warning');
                            info_status.classList.add('btn-success');
                            info_status.textContent = 'Published';
                        } else {
                            info_status.classList.remove('btn-success');
                            info_status.classList.add('btn-warning');
                            info_status.textContent = 'Draft';
                        }
                    });

                    var render_master_data=(()=>{
                        let fetch_detail = false;
                        document.getElementById('time_check').value = '';
                        document.getElementById('time_leave').value = '';
                        tinymce.get('activitiy_note_area_content').setContent('');
                        if( currentData!=null && currentData!=[] ){
                            document.getElementById( 'time_check' ).value = currentData['time_check'];
                            document.getElementById( 'time_leave' ).value = currentData['time_leave'];
                            if( document.getElementById('time_check').value != '' ){
                                set_wp_content_editor();
                                render_master_data_status();
                                fetch_data_detail();
                            } else {
                                set_child_absence();
                            }
                        } else {
                            set_child_absence();
                        }
                    });
                    //
                    var fetch_data_master=(()=>{
                        return ajax_fetch_data_master().then((response)=>{
                            currentData = response.data;
                            if( response.success ){
                                currentData = response.data;
                                render_master_data();
                            } else {
                                console.error( response.data );
                            }
                        });
                    });
                    //
                    var ensure_data_master=(()=>{
                        let ajaxUrl   = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = {
                            'person_id': document.getElementById('child_id').value,
                            'date_record': document.getElementById('date_record').value
                        };
                        // console.log( init_data );
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_ensure_person_activity_data_master',
                            nonce: '<?php echo wp_create_nonce('ensure_person_activity_data_master'); ?>',
                            data: init_data,
                        }).then((response)=>{
                            if( response.success ){
                                person_activity_id = response.data;
                                if( person_activity_id!=null && person_activity_id!='' && person_activity_id!=0 ){
                                    fetch_data_master();
                                }
                            } else {
                                console.error( response.data );
                            }
                        })
                    })
                    //
                    var show_active_child_row=((id)=>{
                        let children_area = document.getElementById('children_list_area');
                        elms_children = children_area.querySelectorAll('.child_data_row');
                        // getElementsByClassName('child_data_row');
                        for( let i = 0; i < elms_children.length; i++ ){
                            if( elms_children[i].classList.contains('active') ){
                                elms_children[i].classList.remove('active');
                            }
                            if( elms_children[i].getAttribute('data-id') == id ){
                                elms_children[i].classList.add('active');
                            }
                        }
                    });
                    //
                    var select_children=((child_id)=>{
                        show_active_child_row( child_id );
                        document.getElementById('child_id').value = child_id;
                        ensure_data_master();
                    });
                    //
                    var render_children_list=(()=>{
                        let active_child_id = '';
                        let children_area = document.getElementById('children_list_area');
                        let children_data = JSON.parse( document.getElementById('children_data').textContent );
                        clear_child_nodes(children_area);
                        Object.keys(children_data).forEach( (key)=>{
                            let item = children_data[key];
                            let child = document.createElement('div');
                            if( active_child_id=='' ) active_child_id = item['id'];
                            child.className = 'wpsg-grid-data wpsg-grid-border-bottom child_data_row wpsg-data-row m-0 py-2 px-3';
                            child.setAttribute('data-id', item['id']);
                            child.setAttribute('data-name', item['name']);
                            child.innerHTML = item['name'];
                            children_area.appendChild(child);
                            //
                            child.addEventListener('click', function (e) { select_children(item['id']); });
                            //
                        });
                        select_children(active_child_id);
                    });
                    //
                    document.getElementById('date_record').value = currentDate;
                    //
                    set_screen_size(); 
                    render_children_list();
                    //
                    window.addEventListener('resize', set_screen_size);
                    document.getElementById('info_status').addEventListener('click',(e)=>{ show_menu_status(e); });
                    document.getElementById('child_id'   ).addEventListener('change',()=>{ show_active_child_row( document.getElementById('child_id').value ); });
                    document.getElementById('date_record').addEventListener('change',()=>{ ensure_data_master(); });
                    document.getElementById('time_check' ).addEventListener('change',()=>{ submit_daily_activity_data_master(); });
                    document.getElementById('time_leave' ).addEventListener('change',()=>{ submit_daily_activity_data_master(); });
                    document.getElementById('btn_check_clear').addEventListener('click',()=>{
                        if( window.confirm('Menghapus jam kedatangan artinya membatalkan kehadiran anak. Apakah anda yakin?') ){
                            document.getElementById('time_check').value = '';
                            submit_daily_activity_data_master();
                        }
                    })
                    document.getElementById('btn_leave_clear').addEventListener('click',()=>{
                        document.getElementById('time_leave').value = '';
                        submit_daily_activity_data_master();
                    });
                    document.getElementById('btn_activities_note_save').addEventListener('click',()=>{ submit_daily_activity_note(); });
                    //
                });
            })();
        </script>

    </div>
</div>