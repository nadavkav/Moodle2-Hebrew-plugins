<?php
defined('MOODLE_INTERNAL') || die;

$capabilities = array(
    'mod/tab:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'guest' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),
);
?>
