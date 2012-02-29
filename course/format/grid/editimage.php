<?php

/* Imports */
require_once("../../../config.php");
require_once("./lib.php");
require_once('./editimage_form.php');
require_once("./imagelib.php");

/* Script settings */
$image_width = 210;
$image_height = 140;

/* Page parameters */
$contextid = required_param('contextid', PARAM_INT);
$sectionid = required_param('sectionid', PARAM_INT);
$id = optional_param('id', null, PARAM_INT);

/* No idea, copied this from an example. Sets form data options but I don't know what they all do exactly */
$formdata = new stdClass();
$formdata->userid = required_param('userid', PARAM_INT);
$formdata->offset = optional_param('offset', null, PARAM_INT);
$formdata->forcerefresh = optional_param('forcerefresh', null, PARAM_INT);
$formdata->mode = optional_param('mode', null, PARAM_ALPHA);

$url = new moodle_url('/course/format/grid/editimage.php',  array('contextid'=>$contextid,
                            'id'=>$id,'offset'=>$formdata->offset,'forcerefresh'=>$formdata->forcerefresh,'userid'=>$formdata->userid,'mode'=>$formdata->mode));

/* No exactly sure what this stuff does, but it seems fairly straightforward */
list($context, $course, $cm) = get_context_info_array($contextid);

require_login($course, true, $cm);
if (isguestuser()) {
    die();
}

$PAGE->set_url($url);
$PAGE->set_context($context);

/* Functional part. Create the form and display it, handle results, etc */
$options = array('subdirs'=>0, 'maxfiles'=>1, 'accepted_types'=>array('web_image'), 'return_types'=>FILE_INTERNAL);
$mform = new image_form(null, array('contextid'=>$contextid, 'userid'=>$formdata->userid, 'sectionid'=>$sectionid, 'options'=>$options));

if ($mform->is_cancelled()) { //Someone has hit the 'cancel' button
    redirect(new moodle_url($CFG->wwwroot . '/course/view.php?id='.$course->id));
} else if ($formdata = $mform->get_data()) { //Form has been submitted

        /* Delete old images associated with this course section */
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'course', 'section', $sectionid);

        if ($newfilename = $mform->get_new_filename('assignment_file')) {

            /* Resize the new image and save it */
            $temp_path = $mform->save_temp_file('assignment_file');
            resize_image($temp_path, $image_width, $image_height);

            $file_record = array('contextid'=>$context->id, 'component'=>'course', 'filearea'=>'section',
             'itemid'=>$sectionid, 'filepath'=>'/', 'filename'=>$newfilename,
             'timecreated'=>time(), 'timemodified'=>time());

            $fs->create_file_from_pathname($file_record, $temp_path);
            $DB->set_field("format_grid_icon", "imagepath", $newfilename,  array("sectionid" => $sectionid));

            unlink($temp_path);
            redirect($CFG->wwwroot . "/course/view.php?id=".$course->id);
        }
}

/* Draw the form */

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

function resize_image($path, $width, $height) {
    $image = new ImageFunctions(); //imagelib.php
    $image->load($path);
    $image->resizeAndCrop($width, $height);
    $image->save($path);
}

?>