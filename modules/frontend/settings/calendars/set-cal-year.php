<?php

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$site_id = wpsg_get_network_id();

$page_title = 'Perencanaan Kalender Tahunan';

?><div class="wpsg-page">

    <!-- Page Header -->
    <div class="wpsg-page-header">
        <div class="row mb-3">
            <div class="col-9 text-start">
                <h3 class="wpsg-page-title"><?php echo $page_title; ?></h3>
            </div>
            <div class="col-3 text-end"><?php

                echo fe_render_href_button([
                    'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null ], 
                    'exclude_keys'=>[], 
                    'class'=>'btn-process',
                    'title'=>'Kembali', 
                    'icon'=>'fas fa-reply fa-fw'
                ]);

            ?></div>
        </div>
    </div>
    <!-- Content -->
    <div class="wpsg-page-content">
        <!-- <div class="d-block d-sm-block d-md-none text-center p-5"><h4>LAYAR TERLALU KECIL</h4><br/>Layar ini di disain untuk operasional kantor.</div> -->
        <!-- <div class="d-none d-sm-none d-md-block"> -->
        <div class="d-block">
            <div class="row container g-2 mb-3">
                <div class="col-12 col-sm-8 col-md-7 col-lg-5">
                    <div class="row">
                        <div class="col-7">
                            <input type="month" class="form-control" id="year_month" value="<?php echo date('Y-m'); ?>" />
                        </div>
                        <div class="col-5 d-inline d-sm-inline d-md-none">
                            <select class="form-select text-center" id="date_of_month" name="date_of_month"></select>
                            <!-- <input type="number" class="form-control" id="date_of_month" list="list_of_date" value="<?php echo date('d'); ?>" />
                            <datalist id="list_of_date"></datalist> -->
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row wpsg-grid-data wpsg-data-row g-2">
                        <div class="d-none d-sm-none d-md-inline col-md-2 text-center wpsg-boxed wpsg-grid-hover text-center" id="date-list" style="height: 460px; overflow-y: scroll; overflow-x: hidden; scrollbar-width: thin;"></div>
                        <div class="col-12 col-sm-12 col-md-10 text-start m-0 p-2">
                            <div class="row g-2">
                                <div class="col-12 text-start wpsg-boxed mb-1 py-2 px-3" id="data-header">
                                    <div class="row" id="row-data-header">
                                        <div class="col-12 d-sm-none text-center"   id="s_head_date">DAY AND DATE</div>
                                        <div class="col-12 d-sm-none text-center"   id="s_head_title"></div>
                                        <div class="col-12 d-sm-none text-center"   id="s_head_sign">SIGN STATUS</div>
                                        <div class="d-none d-sm-inline col-sm-3 text-start"  id="l_head_date">DAY AND DATE</div>
                                        <div class="d-none d-sm-inline col-sm-6 text-center" id="l_head_title"></div>
                                        <div class="d-none d-sm-inline col-sm-3 text-end"    id="l_head_sign">SIGN STATUS</div>
                                    </div>
                                </div>
                                <div class="col-12 wpsg-form wpsg-boxed py-2 px-3" id="data-content-area" style="height: 406px; overflow-y: scroll; overflow-x: hidden; scrollbar-width: none;">
                                    <div class="row">
                                        <div class="col-12 wpsg-grid-border-bottom" id="data-content" style="min-height: 120px;"></div>
                                        <div class="col-12" id="data-content-locale"></div>
                                        <div class="col-12" id="data-content-global"></div>
                                    </div>
                                </div>
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

                const schedule_calendar_yearly = class {
                    //
                    master_data_calendar_weekly = null;
                    master_data_calendar_global = null;
                    // current_master_global = null;
                    master_data_calendar_locale = null;
                    // current_master_locale = null;
                    //
                    data_calendar = [];
                    //
                    year_month = null;
                    date_min = null;
                    date_max = null;
                    //
                    cur_data = [];
                    cur_sign = 0;
                    //
                    constructor(){
                        this.year_month = document.getElementById('year_month');
                        console.log('masuk 1 - constructor');
                        Promise.all( [this.fetch_data_all()] ).then(()=>{
                            try {
                                console.log('masuk 2');
                                console.log('fetch master data done');
                                this.initialize_date();
                                console.log('initialize date done');
                            } catch (error) {
                                console.log(error);
                            } finally {
                                console.log('masuk 3 - rendering page');
                                this.init();
                                console.log('success');
                            }
                        }).catch((error)=>{
                            console.log(error);
                        });
                    }
                    async fetch_data(){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let str_ym = document.getElementById('year_month').value;
                        let str_dd = String( document.getElementById('date_of_month').value ).padStart(2,'0');
                        let str_date = str_ym + '-' + str_dd;
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-calendars-year.fetch_data',
                            nonce : '<?php echo wp_create_nonce('fe-calendars-year.fetch_data'); ?>',
                            data  : {
                                date: str_date
                            }
                        })
                    }
                    async fetch_master(meta){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-settings.fetch_data',
                            nonce : '<?php echo wp_create_nonce('fe-settings.fetch_data'); ?>',
                            data  : {
                                meta_key: meta
                            }
                        });
                    }
                    async fetch_weekly(){
                        return this.fetch_master('schedule.weekly').then((response)=>{
                            if(  response.success ){
                                this.master_data_calendar_weekly = JSON.parse(response.data);
                            }
                        });
                    }
                    async fetch_global_date(){
                        return this.fetch_master('schedule.global').then((response)=>{
                            if(  response.success ){
                                let temp_data = JSON.parse(response.data);
                                let new_data  = [];
                                temp_data.forEach((item)=>{
                                    let code = item['code'];
                                    new_data[code] = item;
                                });
                                this.master_data_calendar_global = new_data;
                            }
                        });
                    }
                    async fetch_locale_date(){
                        return this.fetch_master('schedule.locale').then((response)=>{
                            if(  response.success ){
                                let tmp_data = JSON.parse(response.data);
                                let new_data = [];
                                tmp_data.forEach((item)=>{
                                    let code = item['code'];
                                    new_data[code] = item;
                                });
                                this.master_data_calendar_locale = new_data;
                            }
                        });
                    }
                    async fetch_data_all(){
                        await this.fetch_weekly();
                        await this.fetch_global_date();
                        await this.fetch_locale_date();
                    }

                    collect_date_title(str_date){
                        let title = '';
                        let chk_date = new myDate(str_date);
                        let chk_mmdd = chk_date.getAsMMDD();
                        if( typeof( this.master_data_calendar_locale[chk_mmdd] ) != 'undefined' ){
                            let meta = this.master_data_calendar_locale[chk_mmdd]['meta'][0];
                            title = meta['title'];
                        } else if(typeof( this.master_data_calendar_global[chk_mmdd] ) != 'undefined'){
                            let meta = this.master_data_calendar_global[chk_mmdd]['meta'][0];
                            title = meta['title'];
                        }
                        return title;
                    }
                    collect_date_sign(str_date){
                        let sign = 0;
                        let chk_date = new myDate(str_date);
                        let day_date = chk_date.getDay();
                        let chk_mmdd = chk_date.getAsMMDD();
                        sign |= ( 1 * this.master_data_calendar_weekly[day_date]['sign'] );
                        if( typeof( this.master_data_calendar_global[chk_mmdd] ) != 'undefined' ){
                            console.log('global ada');
                            sign |= ( 1 * this.master_data_calendar_global[chk_mmdd]['sign'] );
                        }
                        if( typeof( this.master_data_calendar_locale[chk_mmdd] ) != 'undefined' ){
                            console.log('locale ada');
                            console.log( this.master_data_calendar_locale[chk_mmdd] );
                            sign |= ( 1 * this.master_data_calendar_locale[chk_mmdd]['sign'] );
                        }
                        console.log( str_date );
                        console.log( sign );
                        return sign;
                    }
                    initialize_date(){
                        let obj_date_of_month = document.getElementById('date_of_month');
                        this.date_min = new myDate( this.year_month.value );
                        this.date_max = new myDate( this.date_min.getFullYear(), this.date_min.getMonth() + 1, 0 );
                        // obj_date_of_month.setAttribute('min',1);
                        // obj_date_of_month.setAttribute('max',this.date_max.getDate());
                        for( let i = 1; i <= this.date_max.getDate(); i++ ){
                            let opt = document.createElement('option');
                            opt.value = i;
                            opt.textContent = String(i).padStart(2,'0');
                            document.getElementById('date_of_month').appendChild(opt);
                        }
                    }
                    render_events(dev_type){
                        let init_data = dev_type === 'locale' ? this.current_master_locale : this.current_master_global;
                        let target    = dev_type === 'locale' ? 'data-content-locale' : 'data-content-global';
                        let title     = dev_type === 'locale' ? 'National Events' : 'World Events';
                        let div_target_area = document.getElementById( target );
                        clearSubElements( div_target_area );
                        console.log( init_data );
                        if( init_data!=null ){
                            if( init_data!=[] ){
                                if( init_data.length>0 ){
                                    let label_target = document.createElement('div');
                                        label_target.className = 'form-label wpsg-grid-border-bottom fw-bold py-1';
                                        label_target.textContent = title;
                                    div_target_area.appendChild( label_target );
                                    init_data.forEach((item)=>{
                                        let sub_div_row = document.createElement('div');
                                            sub_div_row.className = 'wpsg-grid-border-bottom';
                                        let str =   `<div class="col-12">`
                                                +       `<label class="form-label">${item['title']}</label>`
                                                +   `</div>`
                                                +   `<div class="col-12">`
                                                +       `<p>${item['description']}</p>`
                                                +   `</div>`;
                                        sub_div_row.innerHTML = str;
                                        div_target_area.appendChild( sub_div_row );
                                    });
                                }
                            }
                        }
                    }
                    change_date(){
                        let obj_date_list = document.getElementById('date-list');
                        let str_ym = document.getElementById('year_month').value;
                        let str_dd = String( document.getElementById('date_of_month').value ).padStart(2,'0');
                        let str_date = str_ym + '-' + str_dd;
                        // console.log(str_date);
                        let new_date = new myDate( str_date );
                        let day_code = new_date.getDay();
                        let str_md   = new_date.getPadMonth() + '-' + new_date.getPadDate();
                        let label_day = numberToDayName( day_code );
                        let title = this.collect_date_title( new_date );
                        this.cur_sign = this.collect_date_sign( new_date );
                        //
                        this.current_master_global = [];
                        this.current_master_locale = [];

                        document.getElementById( 's_head_date'  ).textContent = day_code + ' : ' + label_day + ', ' + str_date;
                        document.getElementById( 'l_head_date'  ).textContent = label_day + ', ' + str_date;
                        // document.getElementById( 's_head_title' ).textContent = title;
                        // document.getElementById( 'l_head_title' ).textContent = title;
                        document.getElementById( 'l_head_sign'  ).textContent = 'Hari ' + ( this.cur_sign==1 ? 'Libur' : 'Kerja' );
                        document.getElementById( 's_head_sign'  ).textContent = 'Hari ' + ( this.cur_sign==1 ? 'Libur' : 'Kerja' );
                        // console.log( day_code );
                        // console.log( str_md );
                        //
                        if( typeof( this.master_data_calendar_global[str_md] ) != 'undefined' ){
                            this.current_master_global = this.master_data_calendar_global[str_md]['meta'];
                        }
                        if( typeof( this.master_data_calendar_locale[str_md] ) != 'undefined' ){
                            this.current_master_locale = this.master_data_calendar_locale[str_md]['meta'];
                        }

                        this.render_events('locale');
                        this.render_events('global');
                        //
                        let test_rows = obj_date_list.querySelectorAll('.wpsg-data-row');
                        test_rows.forEach((row)=>{
                            if( row.classList.contains('active') ){
                                row.classList.remove('active');
                            }
                            let date_value = row.getAttribute('date-value');
                            if( date_value == str_date ){
                                row.classList.add('active');
                            }
                        });
                        this.fetch_data().then((response)=>{
                            if( response.success ){
                                let tmp_data = response.data;
                                if( tmp_data['id']==null ) {
                                    tmp_data['id'] = 0;
                                    tmp_data['sign'] = this.cur_sign;
                                    tmp_data['date_record'] = str_date;
                                }
                                this.cur_data = tmp_data;
                            } else {
                                this.cur_data = [];
                            }
                            console.log( this.cur_data );
                        });
                    }
                    render_list_row(item){
                        let obj_row  = document.createElement('div');
                        let cur_date = new myDate(item['date']);
                        let day_code = new myDate(item['date']).getDay();
                        let mon_date = cur_date.getPadMonth() + '-' + cur_date.getPadDate();
                        let sign  = this.collect_date_sign( item['date'] );
                        let info  = '';
                        //
                        // console.log( mon_date + ' : ' + sign );
                        //
                        obj_row.className = 'row wpsg-grid-data wpsg-data-row wpsg-grid-border-bottom py-2';
                        obj_row.setAttribute('date-value',cur_date.getAsYMD());
                        if( (1 * sign)==1 ){
                            obj_row.style.backgroundColor = 'rgba( 127,31,31, 0.125 )';
                        }
                        obj_row.innerHTML = `<div class="col-12 text-center" date-value="` + cur_date.getAsYMD() + `">${item['date']}</div>`;
                        obj_row.addEventListener('click',(e)=>{
                            let str_date = e.target.getAttribute('date-value');
                            let tmp_date = new myDate( str_date );
                            document.getElementById('date_of_month').value = tmp_date.getDate();
                            this.change_date();
                        });
                        return obj_row;
                    }
                    render_list(){
                        let obj_list = document.getElementById('date-list');
                        // let opt_list = document.getElementById('list_of_date');
                        let cur_date = new myDate( this.date_min.getFullYear(), this.date_min.getMonth(), 1 );
                        let test = 0;
                        // console.log(cur_date);
                        clearSubElements(obj_list);
                        // clearSubElements(opt_list);
                        while( cur_date <= this.date_max ){
                            let obj_row = this.render_list_row({
                                'date':cur_date.toLocaleDateString()
                            });
                            let opt_row = document.createElement('option');
                                opt_row.value = cur_date.getPadDate();
                                opt_row.innerHTML = cur_date.getPadDate();
                                // opt_list.appendChild(opt_row);
                            obj_list.appendChild(obj_row);
                            cur_date = new myDate( cur_date.getFullYear(), cur_date.getMonth(), cur_date.getDate() + 1 );
                            test++;
                        }
                        document.getElementById('date_of_month').value = (new myDate()).getDate();
                        this.change_date();
                    }
                    register_events(){
                        this.year_month.addEventListener('change',()=>{
                            this.initialize_date();
                            this.render_list();
                        });
                        document.getElementById('date_of_month').addEventListener('change',()=>{
                            this.change_date();
                        });
                    }
                    init(){
                        this.render_list();
                        this.register_events();
                    }
                }

                let cal_year = new schedule_calendar_yearly();

            });

        })();
    </script>
</div>