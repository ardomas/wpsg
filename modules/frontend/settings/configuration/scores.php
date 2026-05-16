<?php

$site_id = wpsg_get_network_id();

$user = wp_get_current_user();
if( !isset( $page_title ) ){
    $page_title = 'Tidak ada judul, hubungi developer';
}

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
            <div class="row wpsg-boxed gap-1 mb-3 p-2 pb-3">
                <div class="col-12 mt-3" id="data-list-area"></div>
                <div class="col-12 text-end" id="block-command">
                    <button type="button" id="button_score_add" class="btn btn-warning" title="Add New Row">
                        <i class="fas fa-plus fa-fw"></i>
                    </button>
                </div>
            </div>
            <div class="row gap-1 text-end">
                <div class="col-12 text-center" id="data-list-alert">&nbsp;</div>
                <div class="col-12">
                    <button type="button" id="button_score_save" class="btn btn-process">
                        <i class="fas fa-save fa-fw"></i>
                        <span class="d-none d-md-inline">Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" lang="javascript">
        (()=>{
            document.addEventListener('DOMContentLoaded', function(){

                var score_message=((message)=>{ 
                    document.getElementById('data-list-alert').innerHTML = message; 
                    setTimeout(() => { document.getElementById('data-list-alert').innerHTML = '&nbsp;'; }, 5000);
                });

                var render_row=((item)=>{
                    let obj_caps = document.createElement('div');
                        obj_caps.className = 'row wpsg-grid-data child_data_row wpsg-data-row g-2 py-1 px-0';
                        obj_caps.setAttribute('data-id', item['id']);
                    let obj_cap_title  = document.createElement('div');
                        obj_cap_title.className = 'col-12 col-sm-6 col-md-7 text-start';
                        // let label_title = document.createElement('label');
                        //     label_title.classList.add('form-label');
                        //     label_title.innerHTML = 'Title';
                        let input_title = document.createElement('input');
                            input_title.className = 'form-control text-start';
                            input_title.setAttribute('type','text');
                            input_title.setAttribute('name','title');
                            input_title.setAttribute('value', item['title'] );
                        // obj_cap_title.appendChild( label_title );
                        obj_cap_title.appendChild( input_title );
                    let obj_cap_score  = document.createElement('div');
                        obj_cap_score.className = 'col-6 col-sm-2 col-md-2 text-start';
                        // let label_score = document.createElement('label');
                        //     label_score.classList.add('form-label');
                        //     label_score.innerHTML = 'Score';
                        let input_score = document.createElement('input');
                            input_score.className = 'form-control text-center';
                            input_score.setAttribute('type','number');
                            input_score.setAttribute('name','score');
                            input_score.setAttribute('value', item['score'] );
                        // obj_cap_score.appendChild( label_score );
                        obj_cap_score.appendChild( input_score );
                    let obj_cap_symbol = document.createElement('div');
                        obj_cap_symbol.className = 'col-6 col-sm-3 col-md-2 text-start';
                        // let label_symbol = document.createElement('label');
                        //     label_symbol.classList.add('form-label');
                        //     label_symbol.innerHTML = 'Symbol';
                        let input_symbol = document.createElement('input');
                            input_symbol.className = 'form-control text-center';
                            input_symbol.setAttribute('type','text');
                            input_symbol.setAttribute('name','symbol');
                            input_symbol.setAttribute('value', item['symbol'] );
                        // obj_cap_symbol.appendChild( label_symbol );
                        obj_cap_symbol.appendChild( input_symbol );
                    let obj_cap_button = document.createElement('div');
                        obj_cap_button.className = 'col-12 col-sm-1 col-md-1 text-end';
                        // let label_button = document.createElement('label');
                        //     label_button.className = 'form-label w-100';
                        //     label_button.innerHTML = '&nbsp;';
                        let input_button = document.createElement('button');
                            input_button.className = 'btn btn-danger text-center';
                            input_button.setAttribute('type','button');
                            input_button.setAttribute('name','btn-delete-row');
                            input_button.setAttribute('title','Delete Row');
                            input_button.innerHTML = '<i class="fas fa-trash-alt fa-fw"></i>';
                        // obj_cap_button.appendChild( label_button );
                        obj_cap_button.appendChild( input_button );
                    obj_caps.appendChild(obj_cap_title );
                    obj_caps.appendChild(obj_cap_score );
                    obj_caps.appendChild(obj_cap_symbol);
                    obj_caps.appendChild(obj_cap_button);
                    return obj_caps;
                });

                var bind_delete_buttons=(()=>{
                    document.getElementsByName('btn-delete-row').forEach((item)=>{
                        item.addEventListener('click',(e)=>{
                            item.parentNode.parentNode.remove();
                        });
                    });
                });

                document.getElementById('button_score_add').addEventListener('click',()=>{
                    let obj_area = document.getElementById('data-list-area');
                    obj_area.appendChild(
                        render_row({ 'id':0, 'title':'', 'score':0, 'symbol':'' })
                    );
                    bind_delete_buttons();
                });

                var render_list=(()=>{
                    let data_list_area = document.getElementById('data-list-area');
                    let data_list_json = JSON.parse( document.getElementById('data-list-json').textContent );
                    while( data_list_area.children.length > 0 ){
                        data_list_area.removeChild( data_list_area.children[0] );
                    }
                    data_list_json.forEach((item)=>{
                        data_list_area.appendChild( render_row(item) );
                    });
                    bind_delete_buttons();
                });

                var ajax_fetch_base_config_data=(()=>{
                    let data_url = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                    return jQuery.post(data_url,{
                        action: 'wpsg.fe-settings.fetch_data',
                        nonce: '<?php echo wp_create_nonce('fe-settings.fetch_data'); ?>',
                        data: {
                            'meta_key': 'score.grade'
                        }
                    });
                });
                var ajax_submit_base_config_data=(()=>{
                    let data_url  = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                    let data_text = document.getElementById('data-list-json').textContent;
                    return jQuery.post(data_url,{
                        action: 'wpsg.fe-settings.submit_data',
                        nonce: '<?php echo wp_create_nonce('fe-settings.submit_data'); ?>',
                        data: {
                            'meta_key'  : 'score.grade',
                            'meta_value': data_text
                        }
                    });
                });

                document.getElementById('button_score_save').addEventListener('click',()=>{
                    //
                    let data_json = [];
                    let item = [];
                    //
                    let obj_area    = document.getElementById('data-list-area');
                    let obj_titles  = obj_area.querySelectorAll('input[name="title"]');
                    let obj_scores  = obj_area.querySelectorAll('input[name="score"]');
                    let obj_symbols = obj_area.querySelectorAll('input[name="symbol"]');
                    //
                    obj_titles.forEach((item,index)=>{
                        if( String(obj_titles[index].value)!='' &&
                            String(obj_symbols[index].value)!=''
                        ) {
                            data_json[index] = {
                                'title': obj_titles[index].value,
                                'score': obj_scores[index].value,
                                'symbol': obj_symbols[index].value
                            };
                        }
                    });
                    //
                    data_json.sort( (a,b) => b['score'] - a['score'] );
                    data_text = JSON.stringify( data_json );
                    document.getElementById('data-list-json').textContent = data_text;
                    //
                    ajax_submit_base_config_data().then((response)=>{
                        if( response.success ){
                            score_message('Data berhasil disimpan!');
                        }
                        render_list();
                    });
                })

                var fetch_base_config_data=(()=>{
                    return ajax_fetch_base_config_data().then((response)=>{
                        if( response.success ){
                            let data_json = JSON.parse(response.data);
                            document.getElementById('data-list-json').textContent = JSON.stringify(data_json);
                        }
                    })
                });

                fetch_base_config_data().then(()=>{
                    render_list();                    
                });

            });
        })();
    </script>
</div>