<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$site_id = wpsg_get_network_id();

$user = wp_get_current_user();
$page_title = "Laporan Bulanan Perkembangan Anak";

$init_children   = new WPSG_ChildrenService();
$init_indicators = new WPSG_IndicatorsService();

$indicator_scores = fe_get_app_json_score_data();
// fe_get_app_json_data('scores')['indicators'];

?><div class="wpsg-page">
    <div class="wpsg-page-header">
        <div class="row pb-4">
            <h4><?php echo $page_title; ?></h4>
        </div>
    </div><?php

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

    ?><div class="form wpsg-page-content">
        <div class="form-group">
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
            <div class="row">
                <div class="col-9 col-sm-9 col-md-10 col-lg-11 text-start">
                    <!-- filter area - begin -->
                    <div class="row g-2 pb-4">
                        <div class="col-12 col-sm-8 col-md-6 col-lg-6">
                            <select class="form-control form-select" id="child_id" name="child_id">
                                <?php foreach( $children_list as $child ){ ?><option value="<?php echo $child['id']; ?>"><?php echo $child['name']; ?></option><?php } ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4 col-md-4 col-lg-3">
                            <select class="form-select text-center" id="rpt_id" name="rpt_id"></select>
                        </div>
                    </div>
                    <!-- filter area - end -->
                </div>
                <div class="col-3 col-sm-3 col-md-2 col-lg-1 text-end">
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
            <!-- area data - begin -->
            <div class="row g-2">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
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
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="tab-content m-0 px-0 wpsg-boxed" id="myTabContent">
                                <div class="tab-pane fade show active" id="indicators_list_area" role="tabpanel" aria-labelledby="indicators_list_area-tab">
                                    <div class="row container wpsg-grid-hover m-0 p-0">
                                        <div class="col-12 m-0 p-3" id="indicators_list_area-content">
                                            LIST INDIKATOR
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="indicators_note_area" role="tabpanel" aria-labelledby="note-tab">
                                    <div class="row container m-0" id="indicators_note_area-content"
                                         style="font-family: Times; font-size: 16pt; line-height: 2; max-width: 960px;">
                                        DESKRIPSI PERKEMBANGAN
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript" lang="javascript">
                document.addEventListener('DOMContentLoaded',()=>{
                    //
                    let current_date = new Date();
                    let child_age    = 0;
                    let person_rec_indicator_id = 0;
                    let db_person_indicator_master = [];
                    let db_person_indicator_master_with_key = [];
                    let db_person_indicator_detail = [];
                    let score2symbols = [];
                    let temp_scores = JSON.parse( document.getElementById('indicators_scores').textContent );
                    //
                    temp_scores.forEach((item)=>{ score2symbols[ item['score'] ] = item['symbol']; });
                    //
                    var clear_children_elements=((elm_obj)=>{
                        let children = elm_obj.children;
                        for( let i=0; i<children.length; i++ ){
                            children[i].remove();
                        }
                    });
                    //
                    var clear_report_area=(()=>{
                        document.getElementById('indicators_list_area-content').innerHTML = 'no indicators';
                        document.getElementById('indicators_note_area-content').innerHTML = 'no description';
                    });
                    //
                    var generate_list_of_indicators=(()=>{
                        console.log(  db_person_indicator_detail );
                    });
                    //
                    var generate_report_page=(()=>{
                        let child_id = document.getElementById('child_id').value;
                        let master_data = db_person_indicator_master_with_key[document.getElementById('rpt_id').value];
                        let detail_data = db_person_indicator_detail;
                        let indicators_list = JSON.parse( document.getElementById('indicators_in_list').textContent );
                        //
                        document.getElementById('indicators_note_area-content').innerHTML = master_data['note_by_teacher'];
                        //
                        let list = '';
                        // let total_score = 0;
                        for( let i=0; i<detail_data.length; i++ ){
                            let score  = detail_data[i]['score'];
                            let symbol = score2symbols[score];
                            // total_score += score;
                            list += '<div class="row wpsg-grid-border-bottom m-0 py-2 px-3">'
                                 +      '<div class="col-9 col-sm-10 col-md-11">'
                                 +          '<span class="control-label">' + indicators_list[detail_data[i]['indicator_id']]['title'] + '</span>'
                                 +      '</div>'
                                 +      '<div class="col-3 col-sm-2 col-md-1 border-grade-' + score + ' text-center" id="score_area_' + detail_data[i]['id'] + '" style="border-width: 1px; border-radius: 0px;">'
                                 +          '<label class="control-label p-1 text-center data-score-label text-nowrap fw-bold" id="score_label_' + detail_data[i]['id'] + '" style="cursor: pointer;">' 
                                 +              symbol  
                                 +          '</label>'
                                 +       '</div>'
                                 + '</div>';
                        }
                        document.getElementById('indicators_list_area-content').innerHTML = list;
                        // document.getElementById('total_score').textContent = total_score;
                    });
                    //
                    var generate_rpt_header_list=((data)=>{
                        let cur_date = new Date();
                        let elm_master_list = document.getElementById('rpt_id');
                        let list = '';
                        clear_children_elements( document.getElementById('rpt_id') );
                        data.sort( (a,b) => b.date_record.localeCompare(a.date_record) );
                        for( let i=0; i<data.length; i++ ){
                            rpt_mon = (new Date( data[i]['date_record'] )).getMonth();
                            console.log( cur_date.getMonth() + ' vs. ' + rpt_mon );
                            if( rpt_mon < cur_date.getMonth() ){
                                let sys_date = new Date( data[i]['date_record'] );
                                let str_date = shortMonth = sys_date.toLocaleString('default', { month: 'long' }) + ' ' + sys_date.getFullYear();
                                list += '<option value="' + data[i]['id'] + '">' + str_date + '</option>';
                            }
                        }
                        elm_master_list.innerHTML = list;
                    })
                    //
                    var load_detail_report_by_person=((rpt_id)=>{
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        db_person_indicator_detail = [];
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_person_indicator_detail_list',
                            nonce : '<?php echo wp_create_nonce('fetch_person_indicator_detail_list'); ?>',
                            data: {
                                'id' : rpt_id
                            }
                        }, (response)=>{
                            if( response.success ){
                                db_person_indicator_detail = response.data;
                                generate_report_page();
                            } else {
                                console.error( response.data );
                            }
                        });
                    });
                    //
                    var on_date_change=(()=>{
                        let child_id = document.getElementById('child_id').value;
                        let person_rec_indicator_id = document.getElementById('rpt_id').value;
                        if( (child_id!=null) && (person_rec_indicator_id!=null) ){
                            load_detail_report_by_person( person_rec_indicator_id );
                        }
                    });
                    //
                    var load_master_report_by_person=((child_id)=>{
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        db_person_indicator_master_with_key = [];
                        db_person_indicator_master = [];
                        db_person_indicator_detail = [];
                        //
                        clear_report_area();
                        //
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg_fetch_master_list_report_by_person',
                            nonce : '<?php echo wp_create_nonce('fetch_master_list_report_by_person'); ?>',
                            data: {
                                'child_id' : child_id
                            }
                        }, (response)=>{
                            if( response.success ){
                                db_person_indicator_master = response.data;
                                db_person_indicator_master.forEach((item)=>{
                                    key = item['id'];
                                    db_person_indicator_master_with_key[key] = item;
                                });
                                // console.log( db_person_indicator_master_with_key );
                                db_person_indicator_master.sort( (a,b) => a['date_record'] - b['date_record'] );
                                // console.log( db_person_indicator_master );
                                generate_rpt_header_list( db_person_indicator_master );
                                //
                                person_rec_indicator_id = document.getElementById('rpt_id').value;
                                if( person_rec_indicator_id ){
                                    on_date_change();
                                }
                                //
                            } else {
                                console.error( response.data );
                            }
                        });
                    })
                    //
                    var on_child_change=(()=>{
                        let child_id = document.getElementById('child_id').value;
                        if( child_id ){
                            load_master_report_by_person( child_id );
                        }
                    });
                    //
                    document.getElementById('child_id').addEventListener('change',()=>{ on_child_change(); });
                    document.getElementById('rpt_id'  ).addEventListener('change',()=>{ on_date_change();  });
                    //
                    if( document.getElementById('child_id').value ){ 
                        on_child_change(); 
                    }
                    //
                });
            </script>
        </form>
    </div>
</div>