
var numberToDayName = ((code=0, type='long')=>{
    const init_date = new myDate( 1970, 0, 4 + code );
    return init_date.toLocaleDateString( undefined, { weekday: type } );
});

var numberToMonthName = ((code=0, type='long')=>{
    const init_date = new myDate( 1970, code, 0 );
    return init_date.toLocaleDateString( undefined, { month: type } );
});

var clearSubElements = ((selector)=>{
    if( !selector ) return;
    if( selector.replaceChildren ){
        selector.replaceChildren();
    } else {
        while(selector.firstChild){
            selector.removeChild(selector.firstChild);
        }
    }
});
var clearChildren = ((selector)=>{ clearSubElements(selector); });

class myDate extends Date {

    constructor() {
        return super( ...arguments );
    }
    getPadMonth(){
        return (this.getMonth() + 1).toString().padStart(2, '0');
    }
    getPadDate(){
        return this.getDate().toString().trim().padStart(2, '0');
    }
    getPadHour(){
        return this.getHours().toString().trim().padStart(2, '0');
    }
    getPadMinute(){
        return this.getMinutes().toString().trim().padStart(2, '0');
    }
    getPadSecond(){
        return this.getSeconds().toString().trim().padStart(2, '0');
    }
    getAsYMD(){
        return `${this.getFullYear()}-${this.getPadMonth()}-${this.getPadDate()}`;
    }
    getAsMMDD(){
        return `${this.getPadMonth()}-${this.getPadDate()}`;
    }

}