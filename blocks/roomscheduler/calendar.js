/**
 * @package   block_roomscheduler
 * @copyright 2011 Raymond Wainman, Dustin Durand - University of Alberta
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Calls a PHP function from the controller with AJAX.
 *
 * @param id string - id of html component to refresh by AJAX.
 * @param func string - the function to be called
 * @param params array - the parameters of the function
 */
function calendar_phpFunction(id,func,params){
    
    var original_content = document.getElementById(id).innerHTML;
    var script = 'calendar_controller.php?id='+id+'&function='+func+'&params='+params;

    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==3)
        {
            document.getElementById(id).innerHTML= original_content;
        }
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById(id).innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("POST",script,true);
    xmlhttp.send();
    
}


var start;  //Start point of drag
var started_bool=false;   //Boolean - true if dragging has started
var highlighted;  //Array containing all highlighted elements
var end;    //End point of drag
function calendar_startAppt(start_element){
    start = start_element.id;
    started_bool = true;
    highlighted = new Array();
    
}

/*
 * 2011:
 * Originally calendar_hoverAppt, but for some reason,
 * Any function longer than 9 characters wouldn't be called.....
 *
 * Couldn't find solution, so renamed to return functionality.
 *
 */
function hoverAppt(over_element){

    if(started_bool){
        //First clear all elements that are in the highlighted array
        for(var i=0;i<highlighted.length;i++){
            try{
                var square = document.getElementById(highlighted[i]);
                square.className = '';
                square.innerHTML = '';
            }
            catch(e){
            }
        }

        var count=0;
        //Mouse is going down
        if(over_element.id>=start){
            //Re-color all elements from start position to hover position
            for(var i=parseInt(start);i<=over_element.id;i=i+600){
                highlighted[count] = i;
                try{
                    square = document.getElementById(i);
                    if(i==parseInt(start)){
                        square.className = 'calendar_selection_top';
                        var start_time = new Date(i*1000);
                        var end_time = new Date((over_element.id*1000)+(600*1000));
                        var start_minutes = start_time.getMinutes();
                        if(start_minutes==0){
                            start_minutes = '00';
                        }
                        var end_minutes = end_time.getMinutes();
                        if(end_minutes==0){
                            end_minutes = '00';
                        }
                        square.innerHTML = '&nbsp;'+start_time.getHours()+':'+start_minutes+' - '+end_time.getHours()+':'+end_minutes;
                    }
                    else if(i==over_element.id){
                        square.className = 'calendar_selection_bottom';
                    }
                    else{
                        square.className = 'calendar_selection_middle';
                    }
                }
                catch(e){
                }
                count++;
            }
        }
        //Mouse is going up
        else{
            for(var i=parseInt(start);i>=over_element.id;i=i-600){
                highlighted[count] = i;
                try{
                    square = document.getElementById(i);
                    if(i==parseInt(start)){
                        square.className = 'calendar_selection_bottom';
                    }
                    else if(i==over_element.id){
                        square.className = 'calendar_selection_top';
                        var start_time = new Date(over_element.id*1000);
                        var end_time = new Date((parseInt(start)*1000)+(600*1000));
                        var start_minutes = start_time.getMinutes();
                        if(start_minutes==0){
                            start_minutes = '00';
                        }
                        var end_minutes = end_time.getMinutes();
                        if(end_minutes==0){
                            end_minutes = '00';
                        }
                        square.innerHTML = '&nbsp;'+start_time.getHours()+':'+start_minutes+' - '+end_time.getHours()+':'+end_minutes;
                    }
                    else{
                        square.className = 'calendar_selection_middle';
                    }
                }
                catch(e){
                }
                count++;
            }
        }
    }
}
function calendar_endAppt(end_element){

    end = parseInt(end_element.id)+600;
    started_bool = false;
    //Plug values into form
    var start_time = document.getElementsByName('newAppt_startTime');
    var end_time = document.getElementsByName('newAppt_endTime');
    var start_date = document.getElementsByName('newAppt_startTime_date');
    var end_date = document.getElementsByName('newAppt_endTime_date');

    //Start time
    var start_time_date = new Date(start*1000);
    var start_time_minutes = start_time_date.getMinutes().toString();
    if(start_time_minutes=='0'){
        start_time_minutes = '00';
    }
    start_time[0].value = start_time_date.getHours().toString()+start_time_minutes;
    //End time
    var end_time_date = new Date(end*1000);
    var end_time_minutes = end_time_date.getMinutes().toString();
    if(end_time_minutes=='0'){
        end_time_minutes = '00';
    }
    end_time[0].value = end_time_date.getHours().toString()+end_time_minutes;

    //Start date
    start_date[0].value = (start_time_date.getMonth()+1).toString()+'/'+start_time_date.getDate().toString()+'/'+start_time_date.getFullYear().toString();
    //End date
    end_date[0].value = (end_time_date.getMonth()+1).toString()+'/'+end_time_date.getDate().toString()+'/'+end_time_date.getFullYear().toString();

    $('a#inline').trigger('click');
    calendar_apptForm_checkAvailability('newAppt');
}

function calendar_apptForm_toggleTab(active_element){
    var details_tab = document.getElementById('tab_details');
    var details = document.getElementById('details');
    var recurrence_tab = document.getElementById('tab_recurrence');
    var recurrence = document.getElementById('recurrence');
    if(active_element.id=='tab_recurrence'){
        details.style.display = 'none';
        recurrence.style.display = '';
        details_tab.className = 'calendar_apptForm_tab';
        recurrence_tab.className = 'calendar_apptForm_selectedTab';
    }
    else{
        details.style.display = '';
        recurrence.style.display = 'none';
        details_tab.className = 'calendar_apptForm_selectedTab';
        recurrence_tab.className = 'calendar_apptForm_tab';
    }
}

function calendar_apptForm_allday(checkbox){
    var start_time = document.getElementsByName(checkbox.form.name+'_startTime');
    var end_time = document.getElementsByName(checkbox.form.name+'_endTime');
    if(start_time[0].disabled){
        start_time[0].disabled = false;
        end_time[0].disabled = false;
    }
    else{
        start_time[0].disabled = true;
        end_time[0].disabled = true;
    }
}

function calendar_apptForm_submit(form,id){
    //Form validation
    var subject = document.getElementsByName(form.name+'_subject');
    //alert(subject);
    var allday = document.getElementsByName(form.name+'_allday');
    //alert(allday);

    if(subject[0].value==''){
        subject[0].style.border = '1px solid red';
    }
    else{

        //DETAILS
        
        subject[0].style.border = '';
        //Room
        var room = document.getElementsByName('room')[0].value;
        //Start
        var startDate = document.getElementsByName(form.name+'_startTime_date')[0].value.split('/');

       // alert(document.getElementsByName(form.name+'_startTime_date')[0].value);

        var startTime;
        if(allday[0].checked){
            startTime = '0000';
        }
        else{
            startTime = document.getElementsByName(form.name+'_startTime')[0].value;
        }

       // alert(document.getElementsByName(form.name+'_startTime')[0].value);

        var startTimeMinutes = startTime.substring(startTime.length-2,startTime.length);
        var startTimeHour = startTime.substring(0,startTime.length-2);
        //End
        var endDate = document.getElementsByName(form.name+'_endTime_date')[0].value.split('/');

       // alert(document.getElementsByName(form.name+'_endTime_date')[0].value);

        var endTime;
        if(allday[0].checked){
            endTime = '0000';
            endDate[1] = parseInt(endDate[1])+1;
        }
        else{
            endTime = document.getElementsByName(form.name+'_endTime')[0].value;
        }

       // alert(document.getElementsByName(form.name+'_endTime')[0].value);

        var endTimeMinutes = endTime.substring(endTime.length-2,endTime.length);
        var endTimeHour = endTime.substring(0,endTime.length-2);
        //Date objects
        var start = new Date(startDate[2],startDate[0]-1,startDate[1],startTimeHour,startTimeMinutes,0,0);
        var end = new Date(endDate[2],endDate[0]-1,endDate[1],endTimeHour,endTimeMinutes,0,0);
        //Category
        var category = document.getElementsByName(form.name+'_category');

       // alert(document.getElementsByName(form.name+'_category')[0].value);

        var category = category[0].value;

        if(category===""){
            category='default';
        }

        //CHECK START TIME < END TIME
        //Check if start time is miliseconds is less than end time in miliseconds
        // -special case if all day checked, then start/end time is ignored, we ignore this check
        if(allday[0].type == 'checkbox' && !(allday[0].checked) && start.getTime() >= end.getTime() ){ //CHECK IF END > BEGINNING
             document.getElementsByName(form.name+'_startTime')[0].style.border = '1px solid red';
            document.getElementsByName(form.name+'_endTime')[0].style.border = '1px solid red';
            return false; //Prematurely returns and thus ajax never occurs, and popup doesn't close
        
        //START < END -- We reset the border to none, just in case it was set.
        } else {
            document.getElementsByName(form.name+'_startTime')[0].style.border = '0px';
            document.getElementsByName(form.name+'_endTime')[0].style.border = '0px';
        }


        //Description
        var description = document.getElementsByName(form.name+'_description');

        //If all day
        var alldayinput;
        if(allday[0].checked){
            alldayinput = 1;
        }
        else{
            alldayinput = 0;
        }


        var params = [room, start.valueOf()/1000, end.valueOf()/1000, subject[0].value, category, description[0].value, alldayinput];



        //RECURRENCE

        try{
            var recurrence;
            var recurrence_radio = document.getElementsByName(form.name+'_recurrence');
            for(i=0;i<recurrence_radio.length;i++){
                if(recurrence_radio[i].checked){
                    recurrence = recurrence_radio[i].value;
                }
            }
            var recurrence_params = Array();

            //End condition
            var endconditionvalue;
            var endcondition = document.getElementsByName(form.name+'_range');
            for(i=0;i<endcondition.length;i++){
                if(endcondition[i].checked){
                    endconditionvalue = endcondition[i].value;
                }
            }
            if(endconditionvalue=='endafter'){
                var endafter_param = document.getElementsByName(form.name+'_endafteroccurences');
                recurrence_params[0] = 'endafter';
                recurrence_params[1] = endafter_param[0].value;
            }
            else if(endconditionvalue=='endby'){
                var endby_param = document.getElementsByName(form.name+'_endbydate')[0].value.split('/');
                var endby_date = new Date(endby_param[2], endby_param[0]-1, endby_param[1], 0,0,0,0);
                recurrence_params[0] = 'endby';
                recurrence_params[1] = endby_date.valueOf()/1000;
            }

            //Recurrence type

            var recurrence_details = document.getElementById(form.name+'_recurrence_details');
            recurrence_details.style.border = '1px dashed #DDDDDD';

            /*
         * NONE
         */
            if(recurrence=='none'){
                params[7] = 'none';
            }

            /*
         * DAILY
         */
            else if(recurrence=='daily'){
                var recurrence_daily_radio = document.getElementsByName(form.name+'_daily_radio');
                var recurrence_daily;
                for(i=0;i<recurrence_daily_radio.length;i++){
                    if(recurrence_daily_radio[i].checked){
                        recurrence_daily = recurrence_daily_radio[i].value;
                    }
                }
            
                if(recurrence_daily=='everyxdays'){
                    var everyxdays_param = document.getElementsByName(form.name+'_daily_everyxdays');
                    params[7] = 'everyxdays';
                    recurrence_params[2] = everyxdays_param[0].value;
                }
                else if(recurrence_daily=='everyweekday'){
                    params[7] = 'everyweekday';
                }
            }

            /*
         * WEEKLY
         */
            else if(recurrence=='weekly'){
                var weekly_param1 = document.getElementsByName(form.name+'_weekly_recur');
                var weekly_param2 = document.getElementsByName(form.name+'_weekly_days');
                params[7] = 'everyxweeks';
                recurrence_params[2] = weekly_param1[0].value;
                var daysofweek = new Array();
                count=0;
                for(i=0;i<weekly_param2.length;i++){
                    if(weekly_param2[i].checked){
                        daysofweek[count] = weekly_param2[i].value;
                        count++;
                    }
                }
                var daysofweekstring = String(daysofweek.valueOf()).replace(/,/g,'?');
                recurrence_params[3] = daysofweekstring;
            }

            /*
         * MONTHLY
         */
            else if(recurrence=='monthly'){
                var recurrence_monthly_radio = document.getElementsByName(form.name+'_monthly_radio');
                var recurrence_monthly;
                for(i=0;i<recurrence_monthly_radio.length;i++){
                    if(recurrence_monthly_radio[i].checked){
                        recurrence_monthly = recurrence_monthly_radio[i].value;
                    }
                }

                if(recurrence_monthly=='dayofeverymonth'){
                    var monthly_day = document.getElementsByName(form.name+'_monthly_day');
                    var monthly_month = document.getElementsByName(form.name+'_monthly_months');
                    params[7] = 'everyxmonthxday';
                    recurrence_params[2] = monthly_day[0].value;
                    recurrence_params[3] = monthly_month[0].value;
                }
                else if(recurrence_monthly=='xxofeverymonth'){
                    var monthly_number = document.getElementsByName(form.name+'_monthly_number');
                    var monthly_day2 = document.getElementsByName(form.name+'_monthly_day2');
                    var monthly_xmonth = document.getElementsByName(form.name+'_monthly_ofeveryxmonth');
                    params[7] = 'thexyofeveryzmonths';
                    recurrence_params[2] = monthly_number[0].value;
                    recurrence_params[3] = monthly_day2[0].value;
                    recurrence_params[4] = monthly_xmonth[0].value;
                }

            }

            /*
         * YEARLY
         */
            else if(recurrence=='yearly'){
                var recurrence_yearly_radio = document.getElementsByName(form.name+'_yearly_radio');
                var recurrence_yearly;
                for(i=0;i<recurrence_yearly_radio.length;i++){
                    if(recurrence_yearly_radio[i].checked){
                        recurrence_yearly = recurrence_yearly_radio[i].value;
                    }
                }

                if(recurrence_yearly=='option1'){
                    var yearly_month = document.getElementsByName(form.name+'_yearly_month');
                    var yearly_day = document.getElementsByName(form.name+'_yearly_day');
                    params[7] = 'everyxmonthxdayyear';
                    recurrence_params[2] = yearly_month[0].value;
                    recurrence_params[3] = yearly_day[0].value;
                }
                else if(recurrence_yearly=='option2'){
                    var yearly_number = document.getElementsByName(form.name+'_yearly_number');
                    var yearly_month2 = document.getElementsByName(form.name+'_yearly_month2');
                    var yearly_day2 = document.getElementsByName(form.name+'_yearly_day2');
                    params[7] = 'thexyofz';
                    recurrence_params[2] = yearly_number[0].value;
                    recurrence_params[3] = yearly_month2[0].value;
                    recurrence_params[4] = yearly_day2[0].value;
                }
            }

            var recurrence_params_string = String(recurrence_params.valueOf());

            params[8] = recurrence_params_string.replace(/,/g,';');
        }
        catch(e){
            alert(e);
        }

        
        
        subject[0].value = '';
        description[0].value = '';
        category[0].value = '';

var reservation = document.getElementsByName(form.name+'_reservationid')[0];

var script;



if(reservation!=null){ //update

    params[8] = document.getElementsByName(form.name+'_editrecursive')[0].value;
    params[9] = reservation.value;
    params = encodeURIComponent(params);
    script = 'reservation_controller.php?function=update&params='+params;
} else { //new
    params = encodeURIComponent(params);
    script = 'reservation_controller.php?function=recurrence&params='+params;
    recurrence_radio[0].value = 'none';
    calendar_toggleRecurrence(recurrence_radio[0]);
}


        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==3)
            {
            }
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                //Refresh calendar + close the fancybox
                calendar_phpFunction('calendar_day_0','refresh',[]);
                $.fancybox.close();
            }
        }
        xmlhttp.open("POST",script,true);
        xmlhttp.send();
    }
    
    return false;
}


function calendar_apptForm_edit_checkAvailability(formname){
    var allday = document.getElementsByName(formname+'_allday');
    //Room
    var room = document.getElementsByName('room')[0].value;
    //Start
    var startDate = document.getElementsByName(formname+'_startTime_date')[0].value.split('/');
    var startTime;
    if(allday[0].checked){
        startTime = '0000';
    }
    else{
        startTime = document.getElementsByName(formname+'_startTime')[0].value;
    }
    var startTimeMinutes = startTime.substring(startTime.length-2,startTime.length);
    var startTimeHour = startTime.substring(0,startTime.length-2);
    //End
    var endDate = document.getElementsByName(formname+'_endTime_date')[0].value.split('/');
    var endTime;
    if(allday[0].checked){
        endTime = '0000';
        endDate[1] = parseInt(endDate[1])+1;
    }
    else{
        endTime = document.getElementsByName(formname+'_endTime')[0].value;
    }
    var endTimeMinutes = endTime.substring(endTime.length-2,endTime.length);
    var endTimeHour = endTime.substring(0,endTime.length-2);
    //Date objects
    var start = new Date(startDate[2],startDate[0]-1,startDate[1],startTimeHour,startTimeMinutes,0,0);
    var end = new Date(endDate[2],endDate[0]-1,endDate[1],endTimeHour,endTimeMinutes,0,0);

    var params = [room, start.valueOf()/1000, end.valueOf()/1000];

    params[3] = 'none';

    var reservation = document.getElementsByName(formname+'_reservationid')[0];

    params[4] = 'id;'+reservation.value;
    params = escape(params);


    var script = 'reservation_check_controller.php?function=recurrence_check&params='+params;

    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    var div = formname+'_status';
    var div2 = formname+'_status_2';

    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==3)
        {
            document.getElementById(div).innerHTML='<img src="img/ajax-loader.gif">';
            document.getElementById(div2).innerHTML='<img src="img/ajax-loader.gif">';
        }
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById(div).innerHTML=xmlhttp.responseText;
            document.getElementById(div2).innerHTML=xmlhttp.responseText;
            var savebutton = document.getElementsByName(formname+'_save');
            var response = xmlhttp.responseText.substring(0,27);
            if(response=='<img src="img/failure.gif">'){
                savebutton[0].disabled=true;
            }
            else{
                savebutton[0].disabled=false;
            }
        }
    }
    xmlhttp.open("POST",script,true);
    xmlhttp.send();

    return false;
}


function calendar_apptForm_checkAvailability(formname){
    var allday = document.getElementsByName(formname+'_allday');
    //Room
    var room = document.getElementsByName('room')[0].value;
    //Start
    var startDate = document.getElementsByName(formname+'_startTime_date')[0].value.split('/');
    var startTime;
    if(allday[0].checked){
        startTime = '0000';
    }
    else{
        startTime = document.getElementsByName(formname+'_startTime')[0].value;
    }
    var startTimeMinutes = startTime.substring(startTime.length-2,startTime.length);
    var startTimeHour = startTime.substring(0,startTime.length-2);
    //End
    var endDate = document.getElementsByName(formname+'_endTime_date')[0].value.split('/');
    var endTime;
    if(allday[0].checked){
        endTime = '0000';
        endDate[1] = parseInt(endDate[1])+1;
    }
    else{
        endTime = document.getElementsByName(formname+'_endTime')[0].value;
    }
    var endTimeMinutes = endTime.substring(endTime.length-2,endTime.length);
    var endTimeHour = endTime.substring(0,endTime.length-2);
    //Date objects
    var start = new Date(startDate[2],startDate[0]-1,startDate[1],startTimeHour,startTimeMinutes,0,0);
    var end = new Date(endDate[2],endDate[0]-1,endDate[1],endTimeHour,endTimeMinutes,0,0);

    var params = [room, start.valueOf()/1000, end.valueOf()/1000];




    //RECURRENCE

    try{
        var recurrence;
        var recurrence_radio = document.getElementsByName(formname+'_recurrence');
        for(i=0;i<recurrence_radio.length;i++){
            if(recurrence_radio[i].checked){
                recurrence = recurrence_radio[i].value;
            }
        }
        var recurrence_params = Array();

        //End condition
        var endconditionvalue;
        var endcondition = document.getElementsByName(formname+'_range');
        for(i=0;i<endcondition.length;i++){
            if(endcondition[i].checked){
                endconditionvalue = endcondition[i].value;
            }
        }
        if(endconditionvalue=='endafter'){
            var endafter_param = document.getElementsByName(formname+'_endafteroccurences');
            recurrence_params[0] = 'endafter';
            recurrence_params[1] = endafter_param[0].value;
        }
        else if(endconditionvalue=='endby'){
            var endby_param = document.getElementsByName(formname+'_endbydate')[0].value.split('/');
            var endby_date = new Date(endby_param[2], endby_param[0]-1, endby_param[1], 0,0,0,0);
            recurrence_params[0] = 'endby';
            recurrence_params[1] = endby_date.valueOf()/1000;
        }

        //Recurrence type

        var recurrence_details = document.getElementById(formname+'_recurrence_details');
        recurrence_details.style.border = '1px dashed #DDDDDD';

        /*
         * NONE
         */
        

        if(recurrence=='none'){
            params[3] = 'none';
        }

        /*
         * DAILY
         */
        else if(recurrence=='daily'){
            var recurrence_daily_radio = document.getElementsByName(formname+'_daily_radio');
            var recurrence_daily;
            for(i=0;i<recurrence_daily_radio.length;i++){
                if(recurrence_daily_radio[i].checked){
                    recurrence_daily = recurrence_daily_radio[i].value;
                }
            }

            if(recurrence_daily=='everyxdays'){
                var everyxdays_param = document.getElementsByName(formname+'_daily_everyxdays');
                params[3] = 'everyxdays';
                recurrence_params[2] = everyxdays_param[0].value;
            }
            else if(recurrence_daily=='everyweekday'){
                params[3] = 'everyweekday';
            }
        }

        /*
         * WEEKLY
         */
        else if(recurrence=='weekly'){
            var weekly_param1 = document.getElementsByName(formname+'_weekly_recur');
            var weekly_param2 = document.getElementsByName(formname+'_weekly_days');
            params[3] = 'everyxweeks';
            recurrence_params[2] = weekly_param1[0].value;
            var daysofweek = new Array();
            count=0;
            for(i=0;i<weekly_param2.length;i++){
                if(weekly_param2[i].checked){
                    daysofweek[count] = weekly_param2[i].value;
                    count++;
                }
            }
            var daysofweekstring = String(daysofweek.valueOf()).replace(/,/g,'?');
            recurrence_params[3] = daysofweekstring;
        }

        /*
         * MONTHLY
         */
        else if(recurrence=='monthly'){
            var recurrence_monthly_radio = document.getElementsByName(formname+'_monthly_radio');
            var recurrence_monthly;
            for(i=0;i<recurrence_monthly_radio.length;i++){
                if(recurrence_monthly_radio[i].checked){
                    recurrence_monthly = recurrence_monthly_radio[i].value;
                }
            }

            if(recurrence_monthly=='dayofeverymonth'){
                var monthly_day = document.getElementsByName(formname+'_monthly_day');
                var monthly_month = document.getElementsByName(formname+'_monthly_months');
                params[3] = 'everyxmonthxday';
                recurrence_params[2] = monthly_day[0].value;
                recurrence_params[3] = monthly_month[0].value;
            }
            else if(recurrence_monthly=='xxofeverymonth'){
                var monthly_number = document.getElementsByName(formname+'_monthly_number');
                var monthly_day2 = document.getElementsByName(formname+'_monthly_day2');
                var monthly_xmonth = document.getElementsByName(formname+'_monthly_ofeveryxmonth');
                params[3] = 'thexyofeveryzmonths';
                recurrence_params[2] = monthly_number[0].value;
                recurrence_params[3] = monthly_day2[0].value;
                recurrence_params[4] = monthly_xmonth[0].value;
            }

        }

        /*
         * YEARLY
         */
        else if(recurrence=='yearly'){
            var recurrence_yearly_radio = document.getElementsByName(formname+'_yearly_radio');
            var recurrence_yearly;
            for(i=0;i<recurrence_yearly_radio.length;i++){
                if(recurrence_yearly_radio[i].checked){
                    recurrence_yearly = recurrence_yearly_radio[i].value;
                }
            }

            if(recurrence_yearly=='option1'){
                var yearly_month = document.getElementsByName(formname+'_yearly_month');
                var yearly_day = document.getElementsByName(formname+'_yearly_day');
                params[3] = 'everyxmonthxdayyear';
                recurrence_params[2] = yearly_month[0].value;
                recurrence_params[3] = yearly_day[0].value;
            }
            else if(recurrence_yearly=='option2'){
                var yearly_number = document.getElementsByName(formname+'_yearly_number');
                var yearly_month2 = document.getElementsByName(formname+'_yearly_month2');
                var yearly_day2 = document.getElementsByName(formname+'_yearly_day2');
                params[3] = 'thexyofz';
                recurrence_params[2] = yearly_number[0].value;
                recurrence_params[3] = yearly_month2[0].value;
                recurrence_params[4] = yearly_day2[0].value;
            }
        }

        var recurrence_params_string = String(recurrence_params.valueOf());

        params[4] = recurrence_params_string.replace(/,/g,';');
    }
    catch(e){
        alert(e);
    }

    params = escape(params);
    var script = 'reservation_check_controller.php?function=recurrence_check&params='+params;

    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }

    var div = formname+'_status';
    var div2 = formname+'_status_2';

    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==3)
        {
            document.getElementById(div).innerHTML='<img src="img/ajax-loader.gif">';
            document.getElementById(div2).innerHTML='<img src="img/ajax-loader.gif">';
        }
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById(div).innerHTML=xmlhttp.responseText;
            document.getElementById(div2).innerHTML=xmlhttp.responseText;
            var savebutton = document.getElementsByName(formname+'_save');
            var response = xmlhttp.responseText.substring(0,27);
            if(response=='<img src="img/failure.gif">'){
                savebutton[0].disabled=true;
            }
            else{
                savebutton[0].disabled=false;
            }
        }
    }
    xmlhttp.open("POST",script,true);
    xmlhttp.send();

    return false;
}

function calendar_resetAppt(button){
    //First clear all elements that are in the highlighted array
    for(var i=0;i<highlighted.length;i++){
        try{
            var square = document.getElementById(highlighted[i]);
            square.className = '';
            square.innerHTML = '';
        }
        catch(e){}
    }

    //Clear form elements
    var subject = document.getElementsByName(button.form.name+'_subject');
    subject[0].value = '';
    
    var description = document.getElementsByName(button.form.name+'_description');
    description[0].value = '';

    var category = document.getElementsByName(button.form.name+'_category');
    category[0].value = '';

document.getElementsByName(button.form.name+'_startTime')[0].style.border = '';
document.getElementsByName(button.form.name+'_endTime')[0].style.border = '';

    //Close fancy box
    $.fancybox.close();
}

function calendar_removeAppt(appt_id,deletestring){
    if(confirm(deletestring)){
        var script = 'reservation_controller.php?function=delete_reservation&params='+appt_id;

        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttpobj=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttpobj=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttpobj.onreadystatechange=function()
        {
            if (xmlhttpobj.readyState==3)
            {
                
            }
            if (xmlhttpobj.readyState==4 && xmlhttpobj.status==200)
            {
                //Refresh calendar
                calendar_phpFunction('calendar_day_0','refresh',[]);
            }
        }
        xmlhttpobj.open("POST",script,true);
        xmlhttpobj.send();
    }
    else{
//Do nothing
}
}

function calendar_removeAppts(appt_id,deletestring){
    if(confirm(deletestring)){
        var script = 'reservation_controller.php?function=delete_reservations&params='+appt_id;

        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttpobj=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttpobj=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttpobj.onreadystatechange=function()
        {
            if (xmlhttpobj.readyState==3)
            {

            }
            if (xmlhttpobj.readyState==4 && xmlhttpobj.status==200)
            {
                //Refresh calendar
                calendar_phpFunction('calendar_day_0','refresh',[]);
            }
        }
        xmlhttpobj.open("POST",script,true);
        xmlhttpobj.send();
    }
    else{
//Do nothing
}
}

function calendar_toggleRecurrence(element){
    var daily = document.getElementById(element.form.name+'_daily');
    var weekly = document.getElementById(element.form.name+'_weekly');
    var monthly = document.getElementById(element.form.name+'_monthly');
    var yearly = document.getElementById(element.form.name+'_yearly');

    var radio = document.getElementsByName(element.form.name+'_recurrence');
    var checked;
    for(i=0;i<radio.length;i++){
        if(radio[i].checked){
            checked = radio[i].value;
        }
    }

    daily.style.display = 'none';
    weekly.style.display = 'none';
    monthly.style.display = 'none';
    yearly.style.display = 'none';

    if(checked == 'daily'){
        daily.style.display = '';
    }
    else if(checked == 'weekly'){
        weekly.style.display = '';
    }
    else if(checked == 'monthly'){
        monthly.style.display = '';
    }
    else if(checked == 'yearly'){
        yearly.style.display = '';
    }

}

function calendar_confirmAppt(appt_id,deletestring){
    if(confirm(deletestring)){
        var script = 'reservation_controller.php?function=confirm_reservation&params='+appt_id;

        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttpobj=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttpobj=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttpobj.onreadystatechange=function()
        {
            if (xmlhttpobj.readyState==3)
            {

            }
            if (xmlhttpobj.readyState==4 && xmlhttpobj.status==200)
            {
                //Refresh calendar
                calendar_phpFunction('calendar_day_0','refresh',[]);
            }
        }
        xmlhttpobj.open("POST",script,true);
        xmlhttpobj.send();
    }
    else{
//Do nothing
}
}

function calendar_confirmAppts(appt_id,deletestring){
    if(confirm(deletestring)){
        var script = 'reservation_controller.php?function=confirm_reservations&params='+appt_id;

        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttpobj=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttpobj=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttpobj.onreadystatechange=function()
        {
            if (xmlhttpobj.readyState==3)
            {

            }
            if (xmlhttpobj.readyState==4 && xmlhttpobj.status==200)
            {
                //Refresh calendar
                calendar_phpFunction('calendar_day_0','refresh',[]);
            }
        }
        xmlhttpobj.open("POST",script,true);
        xmlhttpobj.send();
    }
    else{
//Do nothing
}
}

function calendar_editAppt(id,form, mode){
var response;
params = id;
        var script = 'reservation_controller.php?function=get_reservation_byId_string&params='+params;

        
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==3)
            {
            }
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                 response = xmlhttp.responseText;

               if(mode=='single'){

              edit_popup(response, form,id)
               } else {
              editrecurs_popup(response, form, id)
                    }
              
}
}
        xmlhttp.open("POST",script,true);
        xmlhttp.send();
    }



function edit_popup(response, form, id){

    var default_values = response.split(',')
highlighted = new Array();
    var string;
    for(i=0; i<11; i++){


    string += i + ". " + default_values[i] +" ";
    }



   // alert(string);

    //calendar_editAppt(\'' . $reservation->id . '\',\'' . apptForm::apptForm_formName() . '\');

var subject = document.getElementsByName(form+'_subject')[0];
var allday = document.getElementsByName(form+'_allday')[0];
var category = document.getElementsByName(form+'_category')[0];
var description = document.getElementsByName(form+'_description')[0];
var start_time = document.getElementsByName(form+'_startTime');
var end_time = document.getElementsByName(form+'_endTime');
var start_date = document.getElementsByName(form+'_startTime_date');
var end_date = document.getElementsByName(form+'_endTime_date');
var reservation = document.getElementsByName(form+'_reservationid')[0];


subject.value = default_values[0];
reservation.value = id;



    //Start time
    var start_time_date = new Date(default_values[1]*1000);
    var start_time_minutes = start_time_date.getMinutes().toString();
    if(start_time_minutes=='0'){
        start_time_minutes = '00';
    }
    start_time[0].value = start_time_date.getHours().toString()+start_time_minutes;
    //End time
    var end_time_date = new Date(default_values[2]*1000);
    var end_time_minutes = end_time_date.getMinutes().toString();
    if(end_time_minutes=='0'){
        end_time_minutes = '00';
    }
    end_time[0].value = end_time_date.getHours().toString()+end_time_minutes;

    //Start date
    start_date[0].value = (start_time_date.getMonth()+1).toString()+'/'+start_time_date.getDate().toString()+'/'+start_time_date.getFullYear().toString();
    //End date
    end_date[0].value = (end_time_date.getMonth()+1).toString()+'/'+end_time_date.getDate().toString()+'/'+end_time_date.getFullYear().toString();


if(default_values[3]==1){
allday.checked= true;
start_time[0].disabled = true;
end_time[0].disabled = true;
} else {
start_time[0].disabled = false;
end_time[0].disabled = false;
}

category.value=default_values[4];
description.value = default_values[5];

$('a#inline2').trigger('click');
calendar_apptForm_edit_checkAvailability(form);
}

function editrecurs_popup(response, form, id){

    var default_values = response.split(',')
highlighted = new Array();
    var string;
    for(i=0; i<11; i++){


    string += i + ". " + default_values[i] +" ";
    }

   // alert(string);

    //calendar_editAppt(\'' . $reservation->id . '\',\'' . apptForm::apptForm_formName() . '\');


var subject = document.getElementsByName(form+'_subject')[0];
var category = document.getElementsByName(form+'_category')[0];
var description = document.getElementsByName(form+'_description')[0];
var reservation = document.getElementsByName(form+'_reservationid')[0];
var recurence_id = document.getElementsByName(form+'_editrecursive')[0];

recurence_id.value = default_values[7];



subject.value = default_values[0];
reservation.value = id;
category.value=default_values[4];
description.value = default_values[5];

$('a#inline3').trigger('click');
//calendar_apptForm_edit_checkAvailability(form);
}
