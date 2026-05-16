<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$site_id = wpsg_get_network_id();

$user = wp_get_current_user();
$page_title = "Catatan Aktivitas Harian Anak";

$service_activities = new WPSG_DailyActivitiesService();
$service_children   = new WPSG_ChildrenService();

$activities_scores  = fe_get_app_json_score_data();

?><div class="wpsg-page">
    <div class="row container">
        <div class="col-12 wpsg-page-header">
            <div class="row pb-4">
                <h4><?php echo $page_title; ?></h4>
            </div>
        </div><?php

        $master_activities = $service_activities->get_list();
        $children_list = $service_children->get_children();

        ?><div class="d-none" id="form-data-code">

        </div>
        <div class="col-12 wpsg-page-content">

            <div class="row">
                <div class="col-9 col-sm-10 col-md-10 text-start">
                    <!-- filter area - begin -->
                    <div class="row g-2 pb-4">
                        <div class="col-12 col-sm-8 col-md-9 col-lg-7">
                            <select class="form-control form-select" id="child_id" name="child_id">
                                <?php foreach( $children_list as $child ){ ?><option value="<?php echo $child['id']; ?>"><?php echo $child['name']; ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4 col-md-3 col-lg-3">
                            <select class="form-select text-center" id="date_record" name="date_record"></select>
                        </div>
                    </div>
                    <!-- filter area - end -->
                </div>
                <div class="col-3 col-sm-2 col-md-2 text-end">
                    <?php
                            echo fe_render_href_button([
                                'url_params'=>[ 'sid' => $_GET['sid'] ], 
                                'exclude_keys'=>[], 
                                'class'=>'btn-process',
                                'title'=>'Kembali', 
                                'icon'=>'fas fa-reply fa-fw'
                            ]);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="d-none d-sm-none d-md-none d-lg-inline col-lg-3 col-xl-2">
                    <div class="row wpsg-grid-border p-0 g-1">
                        <div class="col-12 wpsg-grid-hover text-center" id="date_list_area" style="height: 479px; overflow-y: scroll">
                            TANGGAL
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-12 col-lg-9 col-xl-10">

                    <ul class="nav nav-tabs nav-wpsg" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="activity_list_area-tab" data-bs-toggle="tab" data-bs-target="#activity_list_area" type="button" role="tab" aria-controls="activity_list" aria-selected="true">
                                Aktivitas<span class="d-none d-sm-inline"> Harian</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity_note-tab" data-bs-toggle="tab" data-bs-target="#activity_note_area" type="button" role="tab" aria-controls="activity_note" aria-selected="false">
                                Catatan<span class="d-none d-sm-inline"> Guru</span>
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content wpsg-grid-border px-0" id="myTabContent">
                        <div class="tab-pane fade show active" id="activity_list_area" role="tabpanel" aria-labelledby="activity_list_area-tab">
                            <div class="row" style="height: 435px; overflow-y: scroll">
                                <div class="col-12 wpsg-grid-border wpsg-grid-hover" id="report_activity_body_content_area">DETAIL AKTIVITAS</div>
                            </div>
                        </div>
                        <div class="tab-pane fade show" id="activity_note_area" role="tabpanel" aria-labelledby="activity_note_area-tab">
                            <div class="row" style="height: 435px; overflow-y: scroll">
                                <div class="col-12 wpsg-grid-border py-3 px-4" id="report_note_body_area">CATATAN GURU</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" lang="javascript">
        (()=>{
            document.addEventListener('DOMContentLoaded',()=>{
                //
                var data_list = [];
                var local_date  = new Date( new Date().toLocaleString() );
                var str_local_date = local_date.getFullYear() 
                                + '-' + String( local_date.getMonth()+1 ).padStart(2, '0') 
                                + '-' + String( local_date.getDate()    ).padStart(2, '0')
                var activities_main_list = JSON.parse( '<?php echo json_encode( $master_activities ); ?>' );
                var temp_scores = JSON.parse( '<?php echo json_encode( $activities_scores); ?>' );
                var currentData = [];
                var currentDataDetail = [];
                var data_scores = [];
                    temp_scores.forEach((item)=>{
                        data_scores[ item['score'] ] = item;
                    });
                // console.log( data_scores );
                console.log('document ready!!!');
                //
                var render_date_list=(()=>{
                    let date_list_area = document.getElementById('date_list_area');
                    let date_select    = document.getElementById('date_record');
                    while( date_list_area.firstChild ){
                        date_list_area.removeChild( date_list_area.firstChild );
                    }
                    while( date_select.firstChild ){
                        date_select.removeChild( date_select.firstChild );
                    }
                    data_list.forEach((item)=>{
                        //
                        let test_date = new Date(item['date_record']);
                        let day_code  = test_date.getDay();
                        let day_name  = test_date.toLocaleDateString('id-ID', { weekday: 'long' });
                        //
                        // console.log( item );
                        // console.log( day_code + ' - ' + day_name );
                        //
                        let obj_item = document.createElement('div');
                        obj_item.className = 'wpsg-grid-data wpsg-grid-border-bottom child_data_row wpsg-data-row m-0 py-2 px-3';
                        if( day_code==0 || day_code==6     ) { 
                            obj_item.style.backgroundColor = 'rgba(255,0,0,0.125)';
                        } else if( ( item['date_publish']!=null ) && ( item['date_publish']!='0000-00-00' ) ){
                            if( (item['time_check']!=null) && (item['time_check']!='00:00:00') ){
                                obj_item.style.backgroundColor = 'rgba(0,255,0,0.125)';
                            } else {
                                obj_item.style.backgroundColor = 'rgba(255,0,0,0.125)';
                            }
                        } else {
                            if( (item['time_check']!=null) && (item['time_check']!='00:00:00') ){
                                obj_item.style.backgroundColor = 'rgba(192,232,127,0.125)';
                            } else {
                                obj_item.style.backgroundColor = 'rgba(232,127,127,0.125)';
                            }
                        }
                        obj_item.innerHTML = item['date_record'];
                        date_list_area.appendChild(obj_item);
                        //
                        let obj_opt  = document.createElement('option');
                        obj_opt.value = item['date_record'];
                        obj_opt.innerHTML = item['date_record'];
                        date_select.appendChild(obj_opt);
                        //
                        obj_item.addEventListener('click', function (e) { 
                            document.getElementById('date_record').value = e.target.textContent;
                            change_date_record();
                         });
                        //
                    });
                });
                //
                var render_activities=(()=>{
                    let activity_list_body_area = document.getElementById('report_activity_body_content_area');
                    while( activity_list_body_area.firstChild ){
                        activity_list_body_area.removeChild( activity_list_body_area.firstChild );
                    }

                    activities_main_list.forEach((item,key)=>{
                        let item_detail = currentDataDetail[item['id']];
                        // console.log(item);
                        let obj_item = document.createElement('div');
                        obj_item.className = 'row wpsg-grid-data wpsg-grid-border-bottom child_data_row wpsg-data-row m-0 py-2 px-3';
                        str_item_text = '<div class="col-4 col-sm-3">' 
                                      +     '<div class="row">'
                                      +         '<div class="col-12 col-sm-12 col-md-6 text-center">' + item['time_start'] + '</div>'
                                      +         '<div class="col-12 col-sm-12 col-md-6 text-center">' + item['time_end'] + '</div>'
                                      +     '</div>'
                                      + '</div>'
                                      + '<div class="col-6 col-sm-7 col-md-8">' + item['title'] + '</div>'
                                      + '<div class="col-2 col-sm-2 col-md-1 pt-2 pb-0 border-1 grade-' + item_detail['score'] + ' text-center" style="border-radius: 9px;">' 
                                      +     '<label class="form-label wpsg-border-1 text-center">' 
                                      +         data_scores[item_detail['score']]['symbol']
                                      +     '</label>' 
                                      + '</div>';
                        obj_item.innerHTML = str_item_text;
                        activity_list_body_area.appendChild(obj_item);
                        // console.log(key );
                        // console.log(item);
                    })
                });
                //
                var ajax_fetch_data_detail=(()=>{
                    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
                    return jQuery.post( ajaxUrl, {
                        action: 'wpsg_fetch_person_activities_detail',
                        nonce: '<?php echo wp_create_nonce('fetch_person_activities_detail'); ?>',
                        data: { 'person_activity_id': currentData[ document.getElementById('date_record').value ]['id'] }
                    });
                })
                var fetch_data_detail=(()=>{
                    currentDataDetail = [];
                    return ajax_fetch_data_detail().then((response)=>{
                        if( response.success ){
                            currentDataDetail = response.data;
                            // console.log( currentDataDetail );
                        }
                    })
                })
                //
                var ajax_fetch_data_master=(()=>{
                    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
                    return jQuery.post( ajaxUrl, {
                        action: 'wpsg_fetch_person_activities_list_master',
                        nonce: '<?php echo wp_create_nonce('fetch_person_activities_list_master'); ?>',
                        data: { 'person_id': document.getElementById('child_id').value }
                    });
                });
                var fetch_data_master=(()=>{
                    currentData = [];
                    ajax_fetch_data_master().then((response)=>{
                        if( response.success ){
                            // console.log( str_local_date );
                            temp_list = response.data;
                            data_list = [];
                            for( n=temp_list.length-1; n>=0; n-- ){
                                let item = temp_list[n];
                                if( item['date_record'] > str_local_date ) { continue; }
                                data_list.push( item );
                                currentData[ item['date_record'] ] = item;
                            }
                            render_date_list();
                            change_date_record();
                        }
                    });
                })
                //
                var change_date_record=(()=>{
                    let date_record = document.getElementById('date_record').value;
                    let masterDataItem = currentData[date_record];
                    let currentDate = new Date( masterDataItem['date_record'] );
                    //
                    console.log( currentDate.getDay() );
                    console.log( currentDate.toLocaleString('id-ID', { weekday: 'long' }) );
                    if( masterDataItem['time_check']==null || String( masterDataItem['time_check'] ).trim()=='' ){
                        if( currentDate.getDay() == 0 || currentDate.getDay() == 6 ){
                            document.getElementById('report_activity_body_content_area').innerHTML = '<div class="p-5 text-center"><h5>Hari Libur</h5></div>';
                        } else if( masterDataItem['time_check']==null || masterDataItem['time_check']=='00:00' ){
                            document.getElementById('report_activity_body_content_area').innerHTML = '<div class="p-5 text-center"><h5>Tidak ada catatan kehadiran.</h5></div>';
                        }
                        document.getElementById('report_note_body_area').innerHTML = '';
                    } else {
                        document.getElementById('report_note_body_area').innerHTML = masterDataItem['note_by_teacher'] ? masterDataItem['note_by_teacher'] : '';
                        fetch_data_detail().then(()=>{
                            render_activities();
                        });
                    }

                })
                //
                document.getElementById('child_id'   ).addEventListener('change',()=>{ fetch_data_master();  });
                document.getElementById('date_record').addEventListener('change',()=>{ change_date_record(); });
                //
                fetch_data_master();
                //
            })
        })();
    </script>
</div>