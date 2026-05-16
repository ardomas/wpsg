<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$json_data = wpsg_get_json_data( __DIR__ . '/assets/json/data.json' );
$default_schedule = $json_data['schedules']['locale'] ?? [];

/*
?><xmp><?php

print_r( $default_schedule );

?></xmp>
*/

?><div class="form">

    <div class="row">
        <div class="col-12 wpsg-grid-border-bottom py-2" id="schedule-locale-area-data" style="height: 420px; overflow-y: scroll;">
            <div class="row">
                <div class="d-none d-sm-none d-md-inline col-md-3 wpsg-grid-border">MON</div>
                <div class="d-none d-sm-none d-md-inline col-md-3 wpsg-grid-border">DAY</div>
                <div class="col-12 col-sm-12 col-md-6 wpsg-grid-border">GROUP DATA</div>
            </div>
        </div>
        <div class="col-12 text-center py-2 d-block" id="schedule-locale-command">
            <div class="wpsg-grid-data text-center py-2"><span class="form-label" id="schedule-locale-area-info">&nbsp;</span></div>
            <button type="button" id="btn-schedule-locale-data-save" class="btn btn-process" style="width: 240px;">
                <i class="fas fa-floppy-disk fa-fw"></i> Simpan
            </button>
        </div>
    </div>

    <script type="text/javascript" lang="javascript">
        (()=>{
            document.addEventListener('DOMContentLoaded', function(){
                const schedule_locale = (class {

                    data_area_id = '';
                    info_area_id = '';
                    data_default = [];
                    data_content = [];

                    constructor(){
                        this.data_area_id = 'schedule-locale-area-data';
                        this.info_area_id = 'schedule-locale-area-info';
                        this.data_default = <?php echo json_encode( $default_schedule ); ?>;
                    }
                    //
                    submit_data(data){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        // console.log(data);
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-settings.submit_data',
                            nonce : '<?php echo wp_create_nonce('fe-settings.submit_data'); ?>',
                            data  : {
                                meta_key  : 'schedule.locale',
                                meta_value: JSON.stringify( data )
                            }
                        });                        
                    };
                    save_data(){
                        document.getElementById( this.info_area_id ).innerHTML = 'Memproses...';
                        return this.submit_data(this.data_content).then((response)=>{
                            if(  response.success ){
                                this.render_data();
                                document.getElementById( this.info_area_id ).innerHTML = 'Data berhasil disimpan';
                            } else {
                                document.getElementById( this.info_area_id ).innerHTML = 'Gagal menyimpan data';
                            }
                            setTimeout(()=>{
                                console.log('click button save - 2');
                                document.getElementById( this.info_area_id ).innerHTML = '&nbsp;';
                            },5000);
                        })
                    }
                    //
                    fetch_data(){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-settings.fetch_data',
                            nonce : '<?php echo wp_create_nonce('fe-settings.fetch_data'); ?>',
                            data  : {
                                meta_key: 'schedule.locale'
                            }
                        });
                    }
                    render_row(item){
                        //
                        let div_row = document.createElement('div');
                        //
                        let code = item['code'];
                        let sign  = item['sign'];
                        let init_data = item['meta'];
                        //
                        let mmdd = code.split('-');
                        let month_name = numberToMonthName( mmdd[0] );
                        let day = mmdd[1];
                        //
                        let sign_text = (1*sign) == 1 ? 'Libur' : 'Kerja';
                        let str_html = '';
                        //
                        div_row.className = 'row wpsg-grid-border p-0 m-1';
                        if( (1*sign)=="1" ){
                            div_row.style.background = 'rgba( 127,31,31, 0.125 )';
                            div_row.style.color = 'rgb(192,0,0)';
                        }
                        div_row.setAttribute('data-id', code);
                        str_html  = `<div class="py-1 col-7 col-sm-3 col-md-2">${month_name}</div>`
                                +     `<div class="py-1 col-3 col-sm-2 col-md-1">${day}</div>`
                                +     `<div class="py-1 col-2 col-sm-1 col-md-1 text-center">${sign_text}</div>`
                                +     `<div class="py-1 px-0 col-12 col-sm-6 col-md-8">`;
                        init_data.forEach( (sub_item )=>{
                            let sub_title = sub_item['title'];
                            let description = sub_item['description'];
                            str_html += `<div class="row m-0 px-0 pb-2 wpsg-grid-border-bottom">`;
                            str_html +=     `<div class="d-block"><b>${sub_title}</b></div>`;
                            str_html +=     `<div class="d-block"><i>${description}</i></div>`;
                            str_html += `</div>`;
                        });
                        str_html +=     `</div>`;
                        str_html += `</div>`;
                        //
                        // str_html += `<div class="row wpsg-grid-border">`;
                        //
                        div_row.innerHTML = str_html;
                        return div_row;
                    }
                    render_data(){
                        let data_area = document.getElementById( this.data_area_id );
                        clearChildren(data_area);
                        this.data_content.forEach((item)=>{
                            data_area.appendChild( this.render_row(item) );
                        });
                    }

                    register_events(){
                        document.getElementById('btn-schedule-locale-data-save').addEventListener('click', (e)=>{
                            e.preventDefault();
                            this.save_data();
                        });
                    }
                    init(){
                        this.register_events();
                        this.fetch_data().then((response)=>{
                            if(  response.success ){
                                let raw_data = response.data ? JSON.parse( response.data ) : this.data_default;
                                this.data_content = raw_data;
                                if( response.data == null ) {
                                    console.log('data masih kosong');
                                    this.save_data();
                                }
                                this.render_data();
                            }
                        });
                    }
                });
                //
                const sched_locale = new schedule_locale();
                sched_locale.init();
                //
            });

        })();
    </script>

</div>