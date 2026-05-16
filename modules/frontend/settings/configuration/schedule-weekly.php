<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$json_data = wpsg_get_json_data( __DIR__ . '/assets/json/data.json' );
$default_schedule = $json_data['schedules']['weekly'] ?? [];

?><div>
    <div class="wpsg-form">
        <div class="row">
            <div class="col-12 wpsg-grid-border-bottom py-2" id="schedule-weekly-area-data" style="height: 420px; overflow-y: scroll;"></div>
            <div class="col-12 text-center py-2" id="schedule-weekly-command">
                <div class="wpsg-grid-data text-center py-2"><span class="form-label" id="schedule-weekly-area-info">&nbsp;</span></div>
                <button type="button" id="btn-schedule-weekly-data-save" class="btn btn-process" style="width: 240px;">
                    <i class="fas fa-floppy-disk fa-fw"></i> Simpan
                </button>
            </div>
        </div>
    </div>
    <script type="text/javascript" lang="javascript">
        (()=>{

            document.addEventListener('DOMContentLoaded',()=>{

                // console.log('document ready!!!');

                const schedule_weekly = (class {

                    constructor(){
                        // this.load_data();
                        this.data_default = <?php echo json_encode( $default_schedule ); ?>;
                        // this.register_events();
                    }
                    //
                    submit_db_data(data){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        // console.log(data);
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-settings.submit_data',
                            nonce : '<?php echo wp_create_nonce('fe-settings.submit_data'); ?>',
                            data  : {
                                meta_key  : 'schedule.weekly',
                                meta_value: JSON.stringify( data )
                            }
                        });                        
                    }
                    //
                    save_data=(function(){
                        let hdiv = document.getElementById('schedule-weekly-area-data');
                        let data = {};
                        let obj_codes = hdiv.querySelectorAll('input[name="code"]');
                        let obj_signs = hdiv.querySelectorAll('input[name="sign"]');
                        Object.keys(obj_codes).forEach((key)=>{
                            data[key] = {
                                code: (obj_codes[key]).value,
                                sign: (obj_signs[key]).value
                            };
                        });
                        //
                        document.getElementById('schedule-weekly-area-info').innerHTML = 'Memproses...';
                        //
                        return this.submit_db_data( data ).then((response)=>{
                            if( response.success ){
                                document.getElementById('schedule-weekly-area-info').innerHTML = 'Data berhasil disimpan';

                            } else {
                                document.getElementById('schedule-weekly-area-info').innerHTML = 'Gagal menyimpan data';
                            }
                        });
                    });
                    //
                    do_save_data=(()=>{
                        this.save_data().then(()=>{
                            setTimeout(()=>{
                                console.log('click button save - 2');
                                document.getElementById('schedule-weekly-area-info').innerHTML = '&nbsp;';
                            },5000);
                        });                        
                    });
                    //
                    fetch_db_data(){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-settings.fetch_data',
                            nonce : '<?php echo wp_create_nonce('fe-settings.fetch_data'); ?>',
                            data  : {
                                meta_key: 'schedule.weekly'
                            }
                        });
                    }
                    //
                    render_row(item){
                        let obj_row = document.createElement('div');
                            obj_row.className = 'row wpsg-grid-data wpsg-data-row mb-2 g-2';
                        let obj_input_code = document.createElement('input');
                            obj_input_code.setAttribute('type','hidden');
                            obj_input_code.setAttribute('name','code');
                            obj_input_code.setAttribute('value',item['code']);
                        obj_row.appendChild( obj_input_code );
                        let obj_input_sign = document.createElement('input');
                            obj_input_sign.setAttribute('type','hidden');
                            obj_input_sign.setAttribute('name','sign');
                            obj_input_sign.setAttribute('value',item['sign']);
                        obj_row.appendChild( obj_input_sign );
                        let obj_col_day  = document.createElement('div');
                            obj_col_day.className  = 'col-6 px-3 text-end';
                        let obj_text_day = document.createElement('label');
                            obj_text_day.classList.add('form-label');
                            obj_text_day.classList.add('mt-1');
                            obj_text_day.setAttribute('js-process','data-js-process');
                            obj_text_day.style.width = '100px';
                            obj_text_day.setAttribute('for','sign_' + item['code']);
                            obj_text_day.textContent = numberToDayName( item['code'] );
                            obj_col_day.appendChild( obj_text_day );
                        obj_row.appendChild( obj_col_day );
                        let obj_col_sign = document.createElement('div');
                            obj_col_sign.className = 'col-6 px-3 text-start';
                        let obj_input_sign1 = document.createElement('select');
                            obj_input_sign1.classList.add("form-select");
                            obj_input_sign1.setAttribute('name','sign1');
                            obj_input_sign1.setAttribute('id','sign_'+item['code']);
                            obj_input_sign1.style.width = '100px';
                            obj_input_sign1.innerHTML = '<option value="0">Masuk</option>'
                                                    + '<option value="1">Libur</option>';
                            obj_input_sign1.value = item['sign'] ?? '0';
                            obj_col_sign.appendChild( obj_input_sign1 );
                        obj_row.appendChild( obj_col_sign );
                        return obj_row;
                    }
                    //
                    render_page=((data)=>{
                        let obj_weekly_area = document.getElementById('schedule-weekly-area-data');
                        //
                        clearSubElements( obj_weekly_area );
                        Object.keys(data).forEach((key)=>{
                            let item = data[key];
                            obj_weekly_area.appendChild( this.render_row( item ) );
                        });                        
                    });
                    //
                    load_data=(()=>{
                        return this.fetch_db_data().then((response)=>{
                            // console.log( response );
                            if( response.success ){
                                let data = response.data ? JSON.parse( response.data ) : default_schedule;
                                this.render_page( data );
                                this.register_events();
                            }
                        })
                    });
                    //
                    switch_data=((bit_bool)=>{ return (1 * bit_bool)==0 ? 1 : 0; });
                    object_trigger=((item)=>{
                            item.addEventListener('click',()=>{
                                let obj_sign = item.parentNode.parentNode.querySelectorAll('input[name="sign"]')[0];
                                let obj_sign1 = item.parentNode.parentNode.querySelectorAll('select[name="sign1"]')[0];
                                obj_sign.value = this.switch_data( obj_sign.value );
                                obj_sign1.value = obj_sign.value;
                            });
                    });
                    //
                    register_events=(()=>{
                        document.getElementById('btn-schedule-weekly-data-save').addEventListener('click',()=>{ this.do_save_data(); });
                        document.querySelectorAll('[js-process="data-js-process"]').forEach((item)=>{ this.object_trigger( item ); });
                    });

                });

                const weekly = new schedule_weekly();
                weekly.load_data();

                //
                // console.log( (new Date).getDay() );
                // console.log( numberToDayName( (new Date).getDay() ) );
                //
            })
        })();
    </script>
</div>