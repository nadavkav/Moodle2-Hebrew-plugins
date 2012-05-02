/**
 * Room Scheduler Javascript Library
 */

/**
 * Validate new room form found on rooms.php
 */
function validate_newRoom(form) {
    var formname = form.name;
    var room = form.elements['room'];
    //var resources = document.getElementById('resources').value;

    //Room name validation
    if(room.value=='')
    {
        var room_error = document.getElementById(formname+'_room_error');
        room_error.style.display = '';
        room.focus();
        return false;
    }
    else
    {
        room_error.style.display = 'none';
    }

    return true;
}

function room_preview(room_id,course){
    var script = 'preview_controller.php?room='+room_id+'&course='+course;

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
            if(typeof(original_content) != 'undefined'){
            document.getElementById('cal_preview').innerHTML= original_content;
            }
        }
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById('cal_preview').innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("POST",script,true);
    xmlhttp.send();
}

function room_preview_out(){
    document.getElementById('cal_preview').innerHTML='';
}

/*
 * To elimate errors for preivew frame. 
 * 
 */
function calendar_hoverAppt(){
    return;
}