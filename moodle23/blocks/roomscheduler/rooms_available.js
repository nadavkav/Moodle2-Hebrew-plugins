$(document).ready(function() {

    $("a#avaliable_rooms_link").fancybox({
        'transitionIn'		: 'none',
        'transitionOut'		: 'none',
        'modal'                 : true,
        'autoDimensions'        : false,
        'width'                 : 600,
        'height'                : 320


    });


});

function initalize_popup(form){



//Plug values into form
    var start_time = document.getElementsByName(form+'_startTime');
    var end_time = document.getElementsByName(form+'_endTime');
    //var start_date = document.getElementsByName(form+'_startTime_date');
    //var end_date = document.getElementsByName(form+'_endTime_date');

    //Start time
    //var start_time_date = new Date(start*1000);
    var start_time_date = new Date();
    var start_time_minutes = start_time_date.getMinutes().toString();

    var end_time_date = new Date();
    end_time_date.setTime(start_time_date.getTime() + (60*60*1000));

   start_time_minutes = round_down(start_time_minutes,start_time_date);


    start_time[0].value = start_time_date.getHours().toString()+start_time_minutes;

    var end_time_minutes = end_time_date.getMinutes().toString();    
    end_time_minutes = round_up(end_time_minutes, end_time_date);


    end_time[0].value = end_time_date.getHours().toString()+end_time_minutes;



}

function rooms_avaliable_popup(formname){
initalize_popup(formname);
$('a#avaliable_rooms_link').trigger('click');
get_avaliable_rooms(formname);
}


function get_avaliable_rooms(formname){

 var startDate = document.getElementsByName(formname+'_startTime_date')[0].value.split('/');
    var startTime;
        startTime = document.getElementsByName(formname+'_startTime')[0].value;

    var startTimeMinutes = startTime.substring(startTime.length-2,startTime.length);
    var startTimeHour = startTime.substring(0,startTime.length-2);
    //End
    var endDate = document.getElementsByName(formname+'_endTime_date')[0].value.split('/');
    var endTime;

        endTime = document.getElementsByName(formname+'_endTime')[0].value;

    var endTimeMinutes = endTime.substring(endTime.length-2,endTime.length);
    var endTimeHour = endTime.substring(0,endTime.length-2);
    //Date objects
    var start = new Date(startDate[2],startDate[0]-1,startDate[1],startTimeHour,startTimeMinutes,0,0);
    var end = new Date(endDate[2],endDate[0]-1,endDate[1],endTimeHour,endTimeMinutes,0,0);

    var params = [start.valueOf()/1000, end.valueOf()/1000];


    var script = 'reservation_controller.php?function=get_avaliable_rooms&params='+params;

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
            //Since we are returning a table -- .innerhtml craters on IE 8/9
            //Therefore we play the bait and switch with a new DOM element
            var oldDiv = document.getElementById("rooms_available");
            var replacement = document.createElement("div");
            replacement.id = oldDiv.id;
            replacement.innerHTML=xmlhttp.responseText;
            oldDiv.parentNode.replaceChild(replacement, oldDiv);

        }
    }
    xmlhttp.open("POST",script,true);
    xmlhttp.send();
    document.getElementById("rooms_available").innerHTML='<center><img src="img/ajax-loader.gif" alt="Loading" /></center>';
}


function book_room(room){

var formname = document.getElementsByName('form_name')[0].value;
var baseurl = document.getElementsByName('base_url')[0].value;
var courseid = document.getElementsByName('courseid')[0].value;

   var startDate = document.getElementsByName(formname+'_startTime_date')[0].value.split('/');
    var startTime;
        startTime = document.getElementsByName(formname+'_startTime')[0].value;

    var startTimeMinutes = startTime.substring(startTime.length-2,startTime.length);
    var startTimeHour = startTime.substring(0,startTime.length-2);
    //End
    var endDate = document.getElementsByName(formname+'_endTime_date')[0].value.split('/');
    var endTime;

        endTime = document.getElementsByName(formname+'_endTime')[0].value;

    var endTimeMinutes = endTime.substring(endTime.length-2,endTime.length);
    var endTimeHour = endTime.substring(0,endTime.length-2);
    //Date objects
    var start = new Date(startDate[2],startDate[0]-1,startDate[1],startTimeHour,startTimeMinutes,0,0);
    var end = new Date(endDate[2],endDate[0]-1,endDate[1],endTimeHour,endTimeMinutes,0,0);

   
    var fromTime = start.valueOf()/1000;
    var toTime = end.valueOf()/1000;
    var subject = '';
    var category='default';

    var params = [room, fromTime, toTime, subject, category];

var script = 'reservation_controller.php?function=new_reservation&params='+params;

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
          document.getElementById("rooms_available").innerHTML='<center><img src="img/ajax-loader.gif" alt="Loading..." /></center>';
        }
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            window.location = baseurl+"/blocks/roomscheduler/room.php?course="+courseid+"&room="+room+"&time="+toTime;
        }
    }
    xmlhttp.open("POST",script,true);
    xmlhttp.send();

}

/*
 * Sets a char or string into a string at the specified postion
 *
 * @param string str The string to be modified.
 * @param string/char chr The char or string to be put in.
 * @param int pos The position in the string.
 *
 */
function setCharAt(str, chr, pos){
    var length = str.length;
     var before;
    var after;

    if(pos == 0){//case 1: first positon
   after = str.substring(1, length);
   return chr+after;
    } else if(pos == length-1){ //case 2: last position
   before = str.substring(0, length-1);

   //alert(before);
   return before+chr;
    } else if(pos > 0 && pos < length){ // interior positions
     before = str.substring(0, pos);
    after = str.substring((pos + 1), length);

    return before + chr + after;

    } else { //outside limits
        return str;
    }




}


/*
 * Rounds down the time to a division of 10.
 *
 * @param int start_time_minutes The minutes of the start time.
 * @param object start_time_date A date object.
 *
 */
function round_down(start_time_minutes,start_time_date){
    //Round down to a division of 10.
start_time_minutes = append_zero(start_time_minutes);
var length = start_time_minutes.length;


//ROUND DOWN
start_time_minutes = setCharAt(start_time_minutes, '0', length-1);
start_time_date.setMinutes(start_time_minutes);

start_time_minutes = append_zero(start_time_minutes);

return start_time_minutes; //return new minutes (date object changes persist)
}


/*
 * Round up minutes to next division of 10.
 *
 * Replaces the last digit with 0, and increments the tenth postion by 1.
 * If new minutes == 60, change to 00.
 *
 * @param int end_time_minutes The minutes of the end time.
 * @param object end_time_date A date object.
 *
 */
function round_up(end_time_minutes, end_time_date){

end_time_minutes = append_zero(end_time_minutes);
var length = end_time_minutes.length;


//ROUND UP
//get value of tenth
var tenth = end_time_minutes.charAt(length-2);


if(end_time_minutes[length-1]!='0'){ //check if last digit is already 0
var rounded_tenth = parseFloat(tenth)+1;



//Replaces the last digit with 0, and increments the tenth postion by 1.
end_time_minutes = setCharAt(end_time_minutes, rounded_tenth.toString(), length-2);

end_time_minutes = setCharAt(end_time_minutes, '0', length-1);
end_time_date.setMinutes(end_time_minutes);

}

end_time_minutes = append_zero(end_time_minutes);

if(end_time_minutes == '60'){
    end_time_minutes = '00';
}


return end_time_minutes;
}


/*
 * Appends '0' to the front of any value that is only 1 in length.
 *
 * @param string value  Any string value.
 *
 * @return string value New string value.
 */
function append_zero(value){
    if(value.length ==1){
        value = '0'+value;
    }
    return value;

}