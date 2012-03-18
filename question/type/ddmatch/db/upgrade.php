<?php

// This file keeps track of upgrades to
// the match qtype plugin
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

function xmldb_qtype_ddmatch_upgrade($oldversion) {
    global $CFG, $DB, $QTYPES;

    $dbman = $DB->get_manager();

    if ($oldversion < 2010121800) {

        // Define field questiontextformat to be added to question_ddmatch_sub
        $table = new xmldb_table('question_ddmatch_sub');
        $field = new xmldb_field('questiontextformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'questiontext');

        // Conditionally launch add field questiontextformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // In the past, question_ddmatch_sub.questiontext assumed to contain
        // content of the same form as question.questiontextformat. If we are
        // using the HTML editor, then convert FORMAT_MOODLE content to FORMAT_HTML.

        // Because this question type was updated later than the core types,
        // the available/relevant version dates make it hard to differentiate
        // early 2.0 installs from 1.9 updates, hence the extra check for
        // the presence of oldquestiontextformat

        $table = new xmldb_table('question');
        $field = new xmldb_field('oldquestiontextformat');
        if ($dbman->field_exists($table, $field)) {
            $rs = $DB->get_recordset_sql('
                    SELECT qms.*, q.oldquestiontextformat
                    FROM {question_ddmatch_sub} qms
                    JOIN {question} q ON qms.question = q.id');
            foreach ($rs as $record) {
                if ($CFG->texteditors !== 'textarea' && $record->oldquestiontextformat == FORMAT_MOODLE) {
                    $record->questiontext = text_to_html($record->questiontext, false, false, true);
                    $record->questiontextformat = FORMAT_HTML;
                } else {
                    $record->questiontextformat = $record->oldquestiontextformat;
                }
                $DB->update_record('question_ddmatch_sub', $record);
            }
            $rs->close();
        }

        // match savepoint reached
        upgrade_plugin_savepoint(true, 2010121800, 'qtype', 'ddmatch');
    }
    
    if ($oldversion < 2011080300) {
        $table = new xmldb_table('question_ddmatch_sub');

        $field = new xmldb_field('answertext', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'questiontextformat');
        $dbman->change_field_type($table, $field);
        
        $field = new xmldb_field('answertextformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'answertext');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        if ($CFG->texteditors !== 'textarea') {
            $rs = $DB->get_recordset('question_ddmatch_sub', array('answertextformat' => FORMAT_MOODLE), '', 'id,answertext,answertextformat');
            foreach ($rs as $s) {
                $s->answertext       = text_to_html($s->answertext, false, false, true);
                $s->answertextformat = FORMAT_HTML;
                $DB->update_record('question_ddmatch_sub', $s);
                upgrade_set_timeout();
            }
            $rs->close();
        }
        
        upgrade_plugin_savepoint(true, 2011080300, 'qtype', 'ddmatch');
    }

    if ($oldversion < 2011080500) {

        $table = new xmldb_table('question_ddmatch');
        
        // Define field correctfeedback to be added to question_ddmatch
        $field = new xmldb_field('correctfeedback', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null, 'shuffleanswers');

        // Conditionally launch add field correctfeedback
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // Now fill it with '';
            $DB->set_field('question_ddmatch', 'correctfeedback', '');

            // Now add the not null constraint.
            $field = new xmldb_field('correctfeedback', XMLDB_TYPE_TEXT, 'small', null,
                    XMLDB_NOTNULL, null, null, 'shuffleanswers');
            $dbman->change_field_notnull($table, $field);
        }

        // Define field correctfeedbackformat to be added to question_ddmatch
        $field = new xmldb_field('correctfeedbackformat', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'correctfeedback');

        // Conditionally launch add field correctfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field partiallycorrectfeedback to be added to question_ddmatch
        $field = new xmldb_field('partiallycorrectfeedback', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null, 'correctfeedbackformat');

        // Conditionally launch add field partiallycorrectfeedback
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // Now fill it with '';
            $DB->set_field('question_ddmatch', 'partiallycorrectfeedback', '');

            // Now add the not null constraint.
            $field = new xmldb_field('partiallycorrectfeedback', XMLDB_TYPE_TEXT, 'small', null,
                    XMLDB_NOTNULL, null, null, 'correctfeedbackformat');
            $dbman->change_field_notnull($table, $field);
        }

        // Define field partiallycorrectfeedbackformat to be added to question_ddmatch
        $field = new xmldb_field('partiallycorrectfeedbackformat', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'partiallycorrectfeedback');

        // Conditionally launch add field partiallycorrectfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field incorrectfeedback to be added to question_ddmatch
        $field = new xmldb_field('incorrectfeedback', XMLDB_TYPE_TEXT, 'small', null,
                null, null, null, 'partiallycorrectfeedbackformat');

        // Conditionally launch add field incorrectfeedback
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);

            // Now fill it with '';
            $DB->set_field('question_ddmatch', 'incorrectfeedback', '');

            // Now add the not null constraint.
            $field = new xmldb_field('incorrectfeedback', XMLDB_TYPE_TEXT, 'small', null,
                    XMLDB_NOTNULL, null, null, 'partiallycorrectfeedbackformat');
            $dbman->change_field_notnull($table, $field);
        }

        // Define field incorrectfeedbackformat to be added to question_ddmatch
        $field = new xmldb_field('incorrectfeedbackformat', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'incorrectfeedback');

        // Conditionally launch add field incorrectfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field shownumcorrect to be added to question_ddmatch
        $field = new xmldb_field('shownumcorrect', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'incorrectfeedbackformat');

        // Conditionally launch add field shownumcorrect
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // match savepoint reached
        upgrade_plugin_savepoint(true, 2011080500, 'qtype', 'ddmatch');
    }    
    return true;
}
