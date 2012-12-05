<?php


require_once('lib.php');





/**
 * Returns object in database or false if non existant.
 *
 * @param int $id
 * @return mixed
 */
function get_reservation_byId($id) {
    global $DB;

    return $DB->get_record('roomscheduler_reservations', array('id' => $id));
}

function get_reservation_byId_string($id) {
    global $DB;
    $reservation = $DB->get_record('roomscheduler_reservations', array('id' => $id));

    $string = $reservation->subject . ",";
    $string .= $reservation->startdate . ",";
    $string .= $reservation->enddate . ",";
    $string .= $reservation->alldayevent . ",";
    $string .= $reservation->categories . ",";
    $string .= $reservation->description . ",";
    $string .= $reservation->location . ",";
    $string .= $reservation->recurrence_id;


    print $string;
}

/**
 * Returns object in database or false if non existant.
 *
 * @param int $room id of room @link get_room_id()
 * @param int $atTime int timestamp
 * @return mixed
 */
function get_reservation_byTime($room, $atTime) {
    global $DB;
$records = $DB->get_records_select('roomscheduler_reservations', 'location=\'' . $room . '\' AND active=\'1\' AND startdate<=\'' . $atTime . '\' AND enddate>\'' . $atTime . '\'',$params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0);

if(is_array($records)){
    return end($records);
}
    
    return NULL;
}
/**
 * Get a room's id from name, if not found return false
 *
 * @param string $name name of room
 * @return mixed room id or false
 */
function get_room_id($name) {
    global $DB;

    return $DB->get_record('roomscheduler_rooms', array('name' => $name));
}

/**
 * Creates a new reservation. Does all collision checks.
 *
 * @param int $room id of room @link get_room_id()
 * @param int $fromTime start of reservation (UNIX timstamp)
 * @param int $toTime end of reservation (UNIX timestamp)
 * @param string $subject subject of reservation (max 255 characters)
 * @param string $category category of reservation (eg. meeting, class, etc..)
 * @param string $description description of reservation
 * @param boolean $allDay if the reservation is all day
 * @return boolean $success true if everything ok, false if error (like collision)
 */
function new_reservation($room, $fromTime, $toTime, $subject, $category, $description='', $allDay=0, $recurrence_id=0, $printresult=0) {
    global $USER, $DB;

    /*
    if ($colision = check_availability($room, $fromTime, $toTime)) {
        echo '<b>COLLISION!!! : </b>';
        print_r($colision);
    }
     * 
     */
 
//check if start time == end time
//this should result in no reservation
    
$starttime_notEqual_endtime = !($fromTime == $toTime);

    if ($starttime_notEqual_endtime && !check_availability($room, $fromTime, $toTime)) {
        $reservation = new stdClass();
        $reservation->subject = $subject;
        $reservation->startdate = $fromTime;
        $reservation->enddate = $toTime;
        $reservation->alldayevent = $allDay;
        $reservation->meetingorganizer = $USER->id;
        $reservation->categories = $category;
        $reservation->description = $description;
        $reservation->location = $room;
        $reservation->active = 1;
        $reservation->confirm = 0;
        $reservation->recurrence_id = $recurrence_id;

    

        $reservationID = $DB->insert_record('roomscheduler_reservations', $reservation);

        if($printresult){//used if want id of reservation returned with ajax
          print $reservationID;
        }

        return $reservationID;
    } else {
        return false;
    }
}

/**
 * Edits an Old reservation. Does all collision checks.
 *
 * @param int $room id of room @link get_room_id()
 * @param int $fromTime start of reservation (UNIX timstamp)
 * @param int $toTime end of reservation (UNIX timestamp)
 * @param string $subject subject of reservation (max 255 characters)
 * @param string $category category of reservation (eg. meeting, class, etc..)
 * @param string $description description of reservation
 * @param boolean $allDay if the reservation is all day
 * @return boolean $success true if everything ok, false if error (like collision)
 */
function edit_reservation($room, $reservation_id, $fromTime, $toTime, $subject, $category, $description='', $allDay=0, $recurrence_id=0) {
    global $USER, $DB;


    if (!check_availability_excluding_id($room, $fromTime, $toTime,$reservation_id)) {
        $reservation = new stdClass();

        $reservation->id = $reservation_id;
        $reservation->subject = $subject;
        $reservation->startdate = $fromTime;
        $reservation->enddate = $toTime;
        $reservation->alldayevent = $allDay;
        $reservation->meetingorganizer = $USER->id;
        $reservation->categories = $category;
        $reservation->description = $description;
        $reservation->active = 1;
        $reservation->confirm = 0;
        $reservation->recurrence_id = $recurrence_id;

        return $DB->update_record('roomscheduler_reservations', $reservation);
    } else {
        return false;
    }
}

/**
 * Checks the availability of a certain room between two times. Returns true
 * if the room is available, otherwise it returns an object containing the
 * conflicting reservation.
 *
 * @param int $room id of room @link get_room_id()
 * @param int $fromTime start of reservation (UNIX timstamp)
 * @param int $toTime end of reservation (UNIX timestamp)
 * @return mixed false if available, else returns an object containing the conflicting reservation
 */
function check_availability($room, $fromTime, $toTime) {
    global $DB;

    //Case 1: startTime(new)->starttime(existing)->endTime(new)->endtime(existing)
    if ($reservation = get_reservation_byTime($room, $fromTime)) {

        return $reservation;

    }
    //Case 2: starttime(existing)->startTime(new)->endtime(existing)->endTime(new)
    else if ($reservation = get_reservation_byTime($room, $toTime - 600)) {
  
        return $reservation;
    }
    //Case 3: starttime(existing)->startTime(new)->endTime(new)->endtime(existing)
    //is a combination of case 1 and 2 and is therefore already taken care of
    //Case 4: startTime(new)->starttime(existing)->endtime(existing)->endTime(new)
    
    else if ($reservation = $DB->get_records_select('roomscheduler_reservations', 'location=\'' . $room . '\' AND active=\'1\' AND startdate>=\'' . $fromTime . '\' AND enddate<=\'' . $toTime . '\'',$params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=1)) {

        if(is_array($reservation)){
    return end($reservation);
}
    }
    //Case 5: no conflict
    else {
        return false;
    }
}

function check_availability_excluding_id($room, $fromTime, $toTime, $id){
global $DB;


    //Case 1: startTime(new)->starttime(existing)->endTime(new)->endtime(existing)

$records = $DB->get_records_select('roomscheduler_reservations', 'location=\'' . $room . '\' AND active=\'1\' AND startdate<=\'' . $fromTime . '\' AND enddate>\'' . $fromTime . '\'',$params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0);

    if (is_array($records)) {
        foreach($records as $record){
        if($record->id != $id){
          return $record;
        }
    }
    }

    //Case 2: starttime(existing)->startTime(new)->endtime(existing)->endTime(new)
    
    
    $records = $DB->get_records_select('roomscheduler_reservations', 'location=\'' . $room . '\' AND active=\'1\' AND startdate<=\'' . ($toTime - 600) . '\' AND enddate>\'' . ($toTime - 600) . '\'',$params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0);

    if (is_array($records)) {
        foreach($records as $record){
        if($record->id != $id){
          return $record;
        }
    }
    }

    //Case 3: starttime(existing)->startTime(new)->endTime(new)->endtime(existing)
    //is a combination of case 1 and 2 and is therefore already taken care of
    //
    //
    //Case 4: startTime(new)->starttime(existing)->endtime(existing)->endTime(new)
    if ($records = $DB->get_records_select('roomscheduler_reservations', 'location=\'' . $room . '\' AND active=\'1\' AND startdate>=\'' . $fromTime . '\' AND enddate<=\'' . $toTime . '\'',$params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0)) {

        if(is_array($records)){
            
foreach($records as $record){

   if($record->id != $id){
          return $record;
        }
}
        }
    }


    //Case 5: no conflict

        return false;


}

/**
 * Deletes a reservation from the database
 *
 * @param int $id reservation id
 * @return boolean $success
 */
function delete_reservation($id) {
    global $DB;

    $reservation = $DB->get_record('roomscheduler_reservations', array('id' => $id));

    //print_object($reservation);

    $reservation->active = 0;

    return $DB->update_record('roomscheduler_reservations', $reservation);
}

/**
 * Deletes all reservations in a recurrence from the database
 *
 * @param int $id reservation id
 * @return boolean $success
 */
function delete_reservations($id) {
    global $DB;
    $success=true;
    $reservation = $DB->get_record('roomscheduler_reservations', array('id' => $id));

    $reservations = $DB->get_records('roomscheduler_reservations', array('location'=>$reservation->location, 'recurrence_id'=>$reservation->recurrence_id));
    foreach($reservations as $res){
        $res->active = 0;
        $success &= $DB->update_record('roomscheduler_reservations', $res);
    }

    return $success;
}

/**
 * Confirms a reservation
 *
 * @param int $id reservation id
 * @return boolean $success
 */
function confirm_reservation($id) {
    global $DB;

    $reservation = $DB->get_record('roomscheduler_reservations', array('id' => $id));
    $reservation->confirm = 1;

    return $DB->update_record('roomscheduler_reservations', $reservation);
}

/**
 * Confirms a reservation for recurrence items
 *
 * @param int $id reservation id
 * @return boolean $success
 */
function confirm_reservations($id) {
    global $DB;

    $reservation = $DB->get_record('roomscheduler_reservations', array('id' => $id));

    $reservations = $DB->get_records('roomscheduler_reservations', array('recurrence_id'=>$reservation->recurrence_id,'location'=>$reservation->location));
    
    foreach($reservations as $res){
        $res->confirm = 1;
        $DB->update_record('roomscheduler_reservations', $res);
    }
}

/**
 * Denies a reservation
 *
 * @param int $id reservation id
 * @return boolean $success
 */
function deny_reservation($id) {
    global $DB;

    $reservation = $DB->get_record('roomscheduler_reservations', array('id' => $id));
    $reservation->confirm = 1;

    return $DB->update_record('roomscheduler_reservations', $reservation);
}

/**
 * Function that is called when form is submitted, central hub function.
 *
 * @param int $room id of room @link get_room_id()
 * @param int $fromTime start of reservation (UNIX timstamp)
 * @param int $toTime end of reservation (UNIX timestamp)
 * @param string $subject subject of meeting
 * @param string $category category of meeting
 * @param string $description description of meeting
 * @param int $allday 1=allday 0=no
 * @param string $recurrence_type different keywords TBD
 * @param string $params javascript array (comma delimited)
 *
 * @return boolean $success
 */
function recurrence($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_type, $parameters) {
    global $DB,$CFG;

    //Take parameters and convert to array
    $parameters = urldecode($parameters);
    $params = explode(';', $parameters);
    //print_r($params);
    //echo 'TYPE: ' . $recurrence_type;

    if($recurrence_type == 'none'){
        $recurrence_id = 0;
    }
    else{
        $recurrences = $DB->get_records_select('roomscheduler_reservations', 'location=\''.$room.'\' GROUP BY {roomscheduler_reservations}.recurrence_id',array(),"",'{roomscheduler_reservations}.recurrence_id');
        $recurrence_id = sizeof($recurrences);
    }

    //No recursion
    if ($recurrence_type == 'none') {
        return new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
    }

    //Daily: Every x days
    else if ($recurrence_type == 'everyxdays') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xdays = $params[2];
        if ($xdays <= 0 || $endtype_param<0) {}
        

        //Condition 1: End by 
        else if ($endtype == 'endby') {

            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                $fromTime += $xdays * (24 * 60 * 60);
                $toTime += $xdays * (24 * 60 * 60);
            }
        }
        //Condition 2: End after
        else if ($endtype == 'endafter') {
            for ($occurences = 1; $occurences <= $endtype_param; $occurences++) {
                new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                $fromTime += $xdays * (24 * 60 * 60);
                $toTime += $xdays * (24 * 60 * 60);
            }
        }
    }
    //Daily: Every week day
    else if ($recurrence_type == 'everyweekday') {
        $endtype = $params[0];
        $endtype_param = $params[1];

        //Condition 1: End by
        if ($endtype == 'endby') {

            while ($fromTime < $endtype_param && $toTime < $endtype_param) {

                $dayofweek = strftime_dayofweek_compatible($fromTime);
                if ($dayofweek == 6 || $dayofweek == 7) {
                    
                } else {
                    new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                }
                $fromTime += 1 * (24 * 60 * 60);
                $toTime += 1 * (24 * 60 * 60);
            }
        }
        //Condition 2: End after
        else if ($endtype == 'endafter') {

            $occurences = 0;
            while ($occurences < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                if ($dayofweek == 6 || $dayofweek == 7) {
                    
                } else {
                    new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                    $occurences++;
                }

                $fromTime += 1 * (24 * 60 * 60);
                $toTime += 1 * (24 * 60 * 60);
            }
        }
    }


    //Weekly: Every x weeks
    else if ($recurrence_type == 'everyxweeks') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xweeks = $params[2];
        $dayofweek = explode('?', $params[3]);

        if ($xweeks <= 0 || $xweeks > 48 || $endtype_param <0) {
            
        }

        //Condition 1: End by
        else if ($endtype == 'endby') {
            $counter = 0;
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                if ($counter == 0) {
                    for ($day = 1; $day <= 7; $day++) {

                        $dayofweek_temp = strftime_dayofweek_compatible($fromTime);
                        if (in_array($dayofweek_temp, $dayofweek)) {
                            new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        }
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                } else if ($counter == $xweeks) {
                    $counter = 0;
                } else if ($counter != 0) {

                    for ($day = 1; $day <= 7; $day++) {
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                }
            }
        }


        //Condition 2: End after
        else if ($endtype == 'endafter') {
            $occurences = 0;
            $counter = 0;
            while ($occurences < $endtype_param) {
                if ($counter == 0) {
                    for ($day = 1; $day <= 7; $day++) {

                        $dayofweek_temp = strftime_dayofweek_compatible($fromTime);
                        if (in_array($dayofweek_temp, $dayofweek)) {
                            new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        }
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                    $occurences++;
                } else if ($counter == $xweeks) {
                    $counter = 0;
                } else if ($counter != 0) {

                    for ($day = 1; $day <= 7; $day++) {
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                }
            }
        }
    }

    //Monthly: Day x of every x month(s)
    else if ($recurrence_type == 'everyxmonthxday') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xmonths_param = $params[3];
        $dayofmonth_param = $params[2];
        if ($dayofmonth_param <= 0 || $dayofmonth_param > 31 || $xmonths_param <= 0 || $endtype_param < 0) {
            
        }

        //Condition 1: End by
        if ($endtype == 'endby') {
            $counter = 0;
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {

                $dayofmonth = strftime_dayofmonth_compatible($fromTime);

                while ($firstmatch == 0) {
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    if ($dayofmonth == $dayofmonth_param) {
                        new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        $firstmatch = 1;
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }

                if ($dayofmonth == $dayofmonth_param && $counter == 0) {
                    new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                }
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                if ($dayofmonth == 01) {
                    $counter++;
                }
                if ($dayofmonth_param == 29) {
                    if ($dayofmonth == 28) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 30) {
                    if ($dayofmonth == 29) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 31) {
                    if ($dayofmonth == 30) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }


                if ($counter == $xmonths_param) {
                    $counter = 0;
                    $occurences++;
                }
            }
        }
        //Condition 2: End after
        else if ($endtype == 'endafter') {
            $counter = 0;
            $occurences = 0;
            $firstmatch = 0;

            while ($occurences < $endtype_param) {
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);

                while ($firstmatch == 0) {
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    if ($dayofmonth == $dayofmonth_param) {
                        new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        $firstmatch = 1;
                        $occurences++;
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }

                if ($dayofmonth == $dayofmonth_param && $counter == 0) {
                    new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                }
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                if ($dayofmonth == 01) {
                    $counter++;
                }
                if ($dayofmonth_param == 29) {
                    if ($dayofmonth == 28) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 30) {
                    if ($dayofmonth == 29) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 31) {
                    if ($dayofmonth == 30) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }


                if ($counter == $xmonths_param) {
                    $counter = 0;
                    $occurences++;
                }
            }
        }
    }

    //Monthly: The x(first/second/third/fourth) y(m,t,w,t,f,s,s) of every z(1-12) month(s)
    else if ($recurrence_type == 'thexyofeveryzmonths') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $numberdate_param = $params[2];
        $dayofweek_param = $params[3];
        $xmonths_param = $params[4];
        if ($xmonths_param <= 0 || $endtype_param <0) {
            
        }
        //Condition 1: End by
        if ($endtype == 'endby') {
            $numberdatecounter = 0;
            $xmonthscounter = 0;
            $firstmatch = 0;

            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                while ($firstmatch == 0) {
                    if ($dayofweek == $dayofweek_param) {
                        $numberdatecounter++;
                        if ($numberdatecounter == $numberdate_param && $xmonthscounter == 0) {
                            new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                            //$xmonthscounter++;
                            $firstmatch = 1;
                        }
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    $dayofweek = strftime_dayofweek_compatible($fromTime);

                    if ($dayofmonth == 01) {
                        $numberdatecounter = 0;
                    }
                }
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param) {
                        $xmonthscounter++;
                        if ($xmonthscounter == $xmonths_param) {
                            $xmonthscounter = 0;
                            new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        }
                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                $dayofweek = strftime_dayofweek_compatible($fromTime);

                if ($dayofmonth == 01) {
                    $numberdatecounter = 0;
                }
            }
        }
        //Condition 2: End after
        if ($endtype == 'endafter') {
            $numberdatecounter = 0;
            $xmonthscounter = 0;
            $occurences = 0;
            $firstmatch = 0;

            while ($occurences < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                while ($firstmatch == 0) {
                    if ($dayofweek == $dayofweek_param) {
                        $numberdatecounter++;
                        if ($numberdatecounter == $numberdate_param && $xmonthscounter == 0) {
                            new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                            //$xmonthscounter++;
                            $occurences++;
                            $firstmatch = 1;
                        }
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    $dayofweek = strftime_dayofweek_compatible($fromTime);

                    if ($dayofmonth == 01) {
                        $numberdatecounter = 0;
                    }
                }
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param) {
                        $xmonthscounter++;
                        if ($xmonthscounter == $xmonths_param) {
                            $xmonthscounter = 0;
                            new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                            $occurences++;
                        }
                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                $dayofweek = strftime_dayofweek_compatible($fromTime);

                if ($dayofmonth == 01) {
                    $numberdatecounter = 0;
                }
            }
        }
    }
    //Yearly: Every xmonth xday
    else if ($recurrence_type == 'everyxmonthxdayyear') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xmonth_param = $params[2];
        $xday_param = $params[3];
        if ($xday_param <= 0 || $xday_param > 31 || $endtype_param <0) {

        }
        //Condition1: End by
        if ($endtype == 'endby') {
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $xmonth = strftime('%m', $fromTime);
                $xday = strftime_dayofmonth_compatible($fromTime);
                if ($xmonth == $xmonth_param && $xday == $xday_param) {
                    new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                    $fromTime += ( 150 * 24 * 60 * 60);
                    $toTime += ( 150 * 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }
            }
        }
        //Condition2: End After
        else if ($endtype == 'endafter') {
            $occurences = 0;
            while ($occurences < $endtype_param) {
                $xmonth = strftime('%m', $fromTime);
                $xday = strftime_dayofmonth_compatible($fromTime);
                if ($xmonth == $xmonth_param && $xday == $xday_param) {
                    new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                    $occurences++;
                    $fromTime += ( 150 * 24 * 60 * 60);
                    $toTime += ( 150 * 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }
            }
        }
    }
    //Yearly: The x(first/second/third/fourth) y(1-7) of (1-12)
    else if ($recurrence_type == 'thexyofz') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $numberdate_param = $params[2];
        $dayofweek_param = $params[4];
        $xmonth_param = $params[3];

        //Condition 1: Endby
        if ($endtype == 'endby') {
            $numberdatecounter = 0;
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $xmonth = strftime('%m', $fromTime);
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param && $xmonth_param == $xmonth) {
                        new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        $fromTime += (150*24*60*60);
                        $toTime += (150*24*60*60);

                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);

                $dayofthemonth = strftime_dayofmonth_compatible($fromTime);

                if ($dayofthemonth == 1) {
                    $numberdatecounter = 0;
                }
            }
        }
        //Condition 2: End after
        if ($endtype == 'endafter') {
            $occurences = 0;
            $numberdatecounter = 0;
            while ($occurences < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $xmonth = strftime('%m', $fromTime);
                $xmonthday = strftime('%d', $fromTime);
                $year = strftime('%Y', $fromTime);
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param && $xmonth_param == $xmonth) {
                        new_reservation($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_id);
                        $occurences++;
                        $fromTime += (150*24*60*60);
                        $toTime += (150*24*60*60);

                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);

                $dayofthemonth = strftime_dayofmonth_compatible($fromTime);

                if ($dayofthemonth == 1) {
                    $numberdatecounter = 0;
                }
            }
        }
    }
}



/**
 * Function that is called when form is submitted, central hub function.
 *
 * @param int $room id of room @link get_room_id()
 * @param int $fromTime start of reservation (UNIX timstamp)
 * @param int $toTime end of reservation (UNIX timestamp)
 * @param string $subject subject of meeting
 * @param string $category category of meeting
 * @param string $description description of meeting
 * @param int $allday 1=allday 0=no
 * @param string $recurrence_type different keywords TBD
 * @param string $params javascript array (comma delimited)
 *
 * @return boolean $success
 */
function recurrence_check($room, $fromTime, $toTime, $recurrence_type, $parameters) {

    //Take parameters and convert to array
    $parameters = urldecode($parameters);
    $params = explode(';', $parameters);
    
    $CHECK_BOOL = true;
    
    //No recursion
    if ($recurrence_type == 'none') {

        if($params[0]=='id'){
          $CHECK_BOOL = check_availability_excluding_id($room, $fromTime, $toTime, $params[1]);
        if($CHECK_BOOL){
            return $CHECK_BOOL;
        }

        } else {
        $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
        if($CHECK_BOOL){
            return $CHECK_BOOL;
        }

        }
    }

    //Daily: Every x days
    else if ($recurrence_type == 'everyxdays') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xdays = $params[2];
        if ($xdays <= 0 || $endtype_param<0) {}


        //Condition 1: End by
        else if ($endtype == 'endby') {

            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                if($CHECK_BOOL){
                    return $CHECK_BOOL;
                }
                $fromTime += $xdays * (24 * 60 * 60);
                $toTime += $xdays * (24 * 60 * 60);
            }
        }
        //Condition 2: End after
        else if ($endtype == 'endafter') {
            for ($occurences = 1; $occurences <= $endtype_param; $occurences++) {
                $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                if($CHECK_BOOL){
                    return $CHECK_BOOL;
                }
                $fromTime += $xdays * (24 * 60 * 60);
                $toTime += $xdays * (24 * 60 * 60);
            }
        }
    }
    //Daily: Every week day
    else if ($recurrence_type == 'everyweekday') {
        $endtype = $params[0];
        $endtype_param = $params[1];

        //Condition 1: End by
        if ($endtype == 'endby') {

            while ($fromTime < $endtype_param && $toTime < $endtype_param) {

                $dayofweek = strftime_dayofweek_compatible($fromTime);
                if ($dayofweek == 6 || $dayofweek == 7) {

                } else {
                    $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                    if($CHECK_BOOL){
                        return $CHECK_BOOL;
                    }
                }
                $fromTime += 1 * (24 * 60 * 60);
                $toTime += 1 * (24 * 60 * 60);
            }
        }
        //Condition 2: End after
        else if ($endtype == 'endafter') {

            $occurences = 0;
            while ($occurences < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                if ($dayofweek == 6 || $dayofweek == 7) {

                } else {
                    $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                    if($CHECK_BOOL){
                        return $CHECK_BOOL;
                    }
                    $occurences++;
                }

                $fromTime += 1 * (24 * 60 * 60);
                $toTime += 1 * (24 * 60 * 60);
            }
        }
    }


    //Weekly: Every x weeks
    else if ($recurrence_type == 'everyxweeks') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xweeks = $params[2];
        $dayofweek = explode('?', $params[3]);

        if ($xweeks <= 0 || $xweeks > 48 || $endtype_param <0) {

        }

        //Condition 1: End by
        else if ($endtype == 'endby') {
            $counter = 0;
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                if ($counter == 0) {
                    for ($day = 1; $day <= 7; $day++) {

                        $dayofweek_temp = strftime_dayofweek_compatible($fromTime);
                        if (in_array($dayofweek_temp, $dayofweek)) {
                            $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                            if($CHECK_BOOL){
                                return $CHECK_BOOL;
                            }
                        }
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                } else if ($counter == $xweeks) {
                    $counter = 0;
                } else if ($counter != 0) {

                    for ($day = 1; $day <= 7; $day++) {
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                }
            }
        }


        //Condition 2: End after
        else if ($endtype == 'endafter') {
            $occurences = 0;
            $counter = 0;
            while ($occurences < $endtype_param) {
                if ($counter == 0) {
                    for ($day = 1; $day <= 7; $day++) {

                        $dayofweek_temp = strftime_dayofweek_compatible($fromTime);
                        if (in_array($dayofweek_temp, $dayofweek)) {
                            $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                            if($CHECK_BOOL){
                                return $CHECK_BOOL;
                            }
                        }
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                    $occurences++;
                } else if ($counter == $xweeks) {
                    $counter = 0;
                } else if ($counter != 0) {

                    for ($day = 1; $day <= 7; $day++) {
                        $fromTime += 1 * (24 * 60 * 60);
                        $toTime += 1 * (24 * 60 * 60);
                    }
                    $counter++;
                }
            }
        }
    }

    //Monthly: Day x of every x month(s)
    else if ($recurrence_type == 'everyxmonthxday') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xmonths_param = $params[3];
        $dayofmonth_param = $params[2];
        if ($dayofmonth_param <= 0 || $dayofmonth_param > 31 || $xmonths_param <= 0 || $endtype_param < 0) {

        }

        //Condition 1: End by
        if ($endtype == 'endby') {
            $counter = 0;
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {

                $dayofmonth = strftime_dayofmonth_compatible($fromTime);

                while ($firstmatch == 0) {
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    if ($dayofmonth == $dayofmonth_param) {
                        $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                        if($CHECK_BOOL){
                            return $CHECK_BOOL;
                        }
                        $firstmatch = 1;
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }

                if ($dayofmonth == $dayofmonth_param && $counter == 0) {
                    $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                    if($CHECK_BOOL){
                        return $CHECK_BOOL;
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                }
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                if ($dayofmonth == 01) {
                    $counter++;
                }
                if ($dayofmonth_param == 29) {
                    if ($dayofmonth == 28) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 30) {
                    if ($dayofmonth == 29) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 31) {
                    if ($dayofmonth == 30) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }


                if ($counter == $xmonths_param) {
                    $counter = 0;
                    $occurences++;
                }
            }
        }
        //Condition 2: End after
        else if ($endtype == 'endafter') {
            $counter = 0;
            $occurences = 0;
            $firstmatch = 0;

            while ($occurences < $endtype_param) {
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);

                while ($firstmatch == 0) {
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    if ($dayofmonth == $dayofmonth_param) {
                        $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                        if($CHECK_BOOL){
                            return $CHECK_BOOL;
                        }
                        $firstmatch = 1;
                        $occurences++;
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }

                if ($dayofmonth == $dayofmonth_param && $counter == 0) {
                    $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                    if($CHECK_BOOL){
                        return $CHECK_BOOL;
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                }
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                if ($dayofmonth == 01) {
                    $counter++;
                }
                if ($dayofmonth_param == 29) {
                    if ($dayofmonth == 28) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 30) {
                    if ($dayofmonth == 29) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }
                if ($dayofmonth_param == 31) {
                    if ($dayofmonth == 30) {
                        $fromTime += 24 * 60 * 60;
                        $toTime += 24 * 60 * 60;
                        $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                        if ($dayofmonth == 01) {
                            $counter--;
                        }
                        $fromTime -= 24 * 60 * 60;
                        $toTime -= 24 * 60 * 60;
                    }
                }


                if ($counter == $xmonths_param) {
                    $counter = 0;
                    $occurences++;
                }
            }
        }
    }

    //Monthly: The x(first/second/third/fourth) y(m,t,w,t,f,s,s) of every z(1-12) month(s)
    else if ($recurrence_type == 'thexyofeveryzmonths') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $numberdate_param = $params[2];
        $dayofweek_param = $params[3];
        $xmonths_param = $params[4];
        if ($xmonths_param <= 0 || $endtype_param <0) {

        }
        //Condition 1: End by
        if ($endtype == 'endby') {
            $numberdatecounter = 0;
            $xmonthscounter = 0;
            $firstmatch = 0;

            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                while ($firstmatch == 0) {
                    if ($dayofweek == $dayofweek_param) {
                        $numberdatecounter++;
                        if ($numberdatecounter == $numberdate_param && $xmonthscounter == 0) {
                            $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                            if($CHECK_BOOL){
                                return $CHECK_BOOL;
                            }
                            //$xmonthscounter++;
                            $firstmatch = 1;
                        }
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    $dayofweek = strftime_dayofweek_compatible($fromTime);

                    if ($dayofmonth == 01) {
                        $numberdatecounter = 0;
                    }
                }
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param) {
                        $xmonthscounter++;
                        if ($xmonthscounter == $xmonths_param) {
                            $xmonthscounter = 0;
                            $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                            if($CHECK_BOOL){
                                return $CHECK_BOOL;
                            }
                        }
                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                $dayofweek = strftime_dayofweek_compatible($fromTime);

                if ($dayofmonth == 01) {
                    $numberdatecounter = 0;
                }
            }
        }
        //Condition 2: End after
        if ($endtype == 'endafter') {
            $numberdatecounter = 0;
            $xmonthscounter = 0;
            $occurences = 0;
            $firstmatch = 0;

            while ($occurences < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                while ($firstmatch == 0) {
                    if ($dayofweek == $dayofweek_param) {
                        $numberdatecounter++;
                        if ($numberdatecounter == $numberdate_param && $xmonthscounter == 0) {
                            $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                            if($CHECK_BOOL){
                                return $CHECK_BOOL;
                            }
                            //$xmonthscounter++;
                            $occurences++;
                            $firstmatch = 1;
                        }
                    }
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                    $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                    $dayofweek = strftime_dayofweek_compatible($fromTime);

                    if ($dayofmonth == 01) {
                        $numberdatecounter = 0;
                    }
                }
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param) {
                        $xmonthscounter++;
                        if ($xmonthscounter == $xmonths_param) {
                            $xmonthscounter = 0;
                            $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                            if($CHECK_BOOL){
                                return $CHECK_BOOL;
                            }
                            $occurences++;
                        }
                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);
                $dayofmonth = strftime_dayofmonth_compatible($fromTime);
                $dayofweek = strftime_dayofweek_compatible($fromTime);

                if ($dayofmonth == 01) {
                    $numberdatecounter = 0;
                }
            }
        }
    }
    //Yearly: Every xmonth xday
    else if ($recurrence_type == 'everyxmonthxdayyear') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $xmonth_param = $params[2];
        $xday_param = $params[3];
        if ($xday_param <= 0 || $xday_param > 31 || $endtype_param <0) {

        }
        //Condition1: End by
        if ($endtype == 'endby') {
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $xmonth = strftime('%m', $fromTime);
                $xday = strftime_dayofmonth_compatible($fromTime);
                if ($xmonth == $xmonth_param && $xday == $xday_param) {
                    $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                    if($CHECK_BOOL){
                        return $CHECK_BOOL;
                    }
                    $fromTime += ( 150 * 24 * 60 * 60);
                    $toTime += ( 150 * 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }
            }
        }
        //Condition2: End After
        else if ($endtype == 'endafter') {
            $occurences = 0;
            while ($occurences < $endtype_param) {
                $xmonth = strftime('%m', $fromTime);
                $xday = strftime_dayofmonth_compatible($fromTime);
                if ($xmonth == $xmonth_param && $xday == $xday_param) {
                    $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                    if($CHECK_BOOL){
                        return $CHECK_BOOL;
                    }
                    $occurences++;
                    $fromTime += ( 150 * 24 * 60 * 60);
                    $toTime += ( 150 * 24 * 60 * 60);
                } else {
                    $fromTime += ( 24 * 60 * 60);
                    $toTime += ( 24 * 60 * 60);
                }
            }
        }
    }
    //Yearly: The x(first/second/third/fourth) y(1-7) of (1-12)
    else if ($recurrence_type == 'thexyofz') {
        $endtype = $params[0];
        $endtype_param = $params[1];
        $numberdate_param = $params[2];
        $dayofweek_param = $params[4];
        $xmonth_param = $params[3];

        //Condition 1: Endby
        if ($endtype == 'endby') {
            $numberdatecounter = 0;
            while ($fromTime < $endtype_param && $toTime < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $xmonth = strftime('%m', $fromTime);
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param && $xmonth_param == $xmonth) {
                        $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                        if($CHECK_BOOL){
                            return $CHECK_BOOL;
                        }
                        $fromTime += (150*24*60*60);
                        $toTime += (150*24*60*60);

                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);

                $dayofthemonth = strftime_dayofmonth_compatible($fromTime);

                if ($dayofthemonth == 1) {
                    $numberdatecounter = 0;
                }
            }
        }
        //Condition 2: End after
        if ($endtype == 'endafter') {
            $occurences = 0;
            $numberdatecounter = 0;
            while ($occurences < $endtype_param) {
                $dayofweek = strftime_dayofweek_compatible($fromTime);
                $xmonth = strftime('%m', $fromTime);
                $xmonthday = strftime('%d', $fromTime);
                $year = strftime('%Y', $fromTime);
                if ($dayofweek == $dayofweek_param) {
                    $numberdatecounter++;
                    if ($numberdatecounter == $numberdate_param && $xmonth_param == $xmonth) {
                        $CHECK_BOOL = check_availability($room, $fromTime, $toTime);
                        if($CHECK_BOOL){
                            return $CHECK_BOOL;
                        }
                        $occurences++;
                        $fromTime += (150*24*60*60);
                        $toTime += (150*24*60*60);

                    }
                }

                $fromTime += ( 24 * 60 * 60);
                $toTime += ( 24 * 60 * 60);

                $dayofthemonth = strftime_dayofmonth_compatible($fromTime);

                if ($dayofthemonth == 1) {
                    $numberdatecounter = 0;
                }
            }
        }
    }
}

/*
 * Function relay for all functions to update a reservation
 * Determines which function to call depending if a single, or recurrsive update
 *
 * @param int $room The ID of the room for the reservation.
 * @param int $fromTime The start time of the reservation in seconds.
 * @param int $toTime The end time of the reservation in seconds.
 * @param string $subject The subject of the reservation.
 * @param string $category The category  of the reservation: 'default', 'class', or 'meeting'
 * @param string $description The description of the reservation
 * @param int $allday 1 means all day, 0 means disabled or not all day
 * @param int $recurrence_type UNUSED
 * @param int $recurence_id The recurssion ID.
 * @param int $id The ID of the reservation.
 *
 */
function update($room, $fromTime, $toTime, $subject, $category, $description, $allday, $recurrence_type, $recurence_id, $id){

if($recurence_id == 0){
   edit_reservation($room, $id, $fromTime, $toTime, $subject, $category, $description, $allday, 0);
    
} elseif($recurence_id > 0) {
    edit_reservations($recurence_id, $subject, $category, $description);
}




}

/*
 * Updates a series of reservations of the same recursive id
 * 
 * @param string $subject The subject of the reservation.
 * @param string $category The category  of the reservation: 'default', 'class', or 'meeting'
 * @param string $description The description of the reservation
 * @param int $recurence_id The recurssion ID.
 *
 */
function  edit_reservations($recurence_id, $subject, $category, $description){
    global $DB;

$reservations = $DB->get_records('roomscheduler_reservations', array('recurrence_id' => $recurence_id), $sort='', $fields='*', $limitfrom=0, $limitnum=0);

foreach($reservations as $reservation){

  $reservation_object = new stdClass();
  $reservation_object->subject = $subject;
  $reservation_object->categories = $category;
  $reservation_object->description = $description;
  $reservation_object->id = $reservation->id;

$DB->update_record('roomscheduler_reservations', $reservation_object);
print $reservation->id . " ".$subject." ".$description." ".$category;
}
}


/*
 * Prints out all avaliable rooms as a table between the start time to the end time.
 *
 * @param int $fromTime The start time of the reservation in seconds.
 * @param int $toTime The end time of the reservation in seconds.
 *
 */
function get_avaliable_rooms($fromTime, $toTime){
    global $DB, $USER, $CFG;

    //reservation starts: Start = S, End = E
    //Location: Above Given Start Time : A, Between given start/end: M, After End Time: E

    //Cases:
    //SA EA, --(start above, end above) non useful
    //SA EM, --(start above, end mid)
    //SA EE, -- (Start above, end after)
    //SM EA -- (start mid, end above) -- no useful
    //SM EM -- start mid, end mid
    //SM EE -- start mid, end after
    //SE EA -- non useful
    //SE EM -- non useful
    //SE EE -- non useful


    $sql = "SELECT DISTINCT * FROM {roomscheduler_rooms} room ".
"WHERE room.active = 1 AND room.id not in ( ". //check which rooms are not avaliable
"SELECT DISTINCT rom.id FROM {roomscheduler_reservations} res, {roomscheduler_rooms} rom ".
"WHERE rom.id = res.location AND res.active=1 AND ( ".
"(res.startdate <= $fromTime AND res.enddate <= $toTime and res.enddate > $fromTime ) OR ".  //--(start above, end mid)
"(res.startdate <= $fromTime AND res.enddate > $toTime ) OR ". //-- (Start above, end after)
"(res.startdate >= $fromTime AND res.startdate < $toTime and res.enddate > $fromTime AND res.enddate <= $toTime ) OR ". //-- start mid, end mid
"(res.startdate >= $fromTime AND res.startdate < $toTime AND res.enddate > $toTime )) ".//-- start mid, end after
");";

$rooms = $DB->get_records_sql($sql, array(), $limitfrom=0, $limitnum=0);


print '<center><table id="open_rooms_table">';
print '<tr><th>'.get_string('room','block_roomscheduler');
print '</th><th>'.get_string('resources','block_roomscheduler');
print '</th><th>'.get_string('bookroom','block_roomscheduler').'</th></tr>';
if($rooms){
$index = 1;
foreach($rooms as $room){

print '<tr onclick=""><td>';

print "$index. ".$room->name;

print '</td><td>';
print $room->resources;
print '</td><td>';

print '<form><center>';
if($room->reservable){
print '<input type="image" src="img/success.gif" value="Book Room" onclick="book_room('.$room->id.');return false;" />';
} else {
$email = $CFG->roomscheduler_manage_email;
$subject = get_string('book_room_email_subject','block_roomscheduler');

$information = get_string('email_room','block_roomscheduler')." ".$room->name . "%0A";
$information .= get_string('email_start','block_roomscheduler')." ".date( 'F n, Y @ G:i' , $fromTime ). "%0A";
$information .= get_string('email_end','block_roomscheduler')." ".date( 'F n, Y @ G:i' , $toTime ). "%0A";
$information .= get_string('email_reason','block_roomscheduler')." _______________________________%0A%0A";

$body = get_string('book_room_email_body','block_roomscheduler') . $information;
$body .= get_string('book_room_email_close','block_roomscheduler') . $USER->firstname ." ".$USER->lastname ;


print '<a href="mailto:'.$email.'?subject='.$subject.'&body='.$body.'" border="0">
 <img src="img/email.png"></a>';

}
print '</form></center>';
print '</td></tr>';




    $index++;
}
}
print '</table></center>';

}
?>
