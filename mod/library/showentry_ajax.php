<?php
define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('lib.php');
require_once($CFG->libdir . '/filelib.php');

$concept  = optional_param('concept', '', PARAM_CLEAN);
$courseid = optional_param('courseid', 0, PARAM_INT);
$eid      = optional_param('eid', 0, PARAM_INT); // library entry id
$displayformat = optional_param('displayformat',-1, PARAM_SAFEDIR);

$url = new moodle_url('/mod/library/showentry.php');
$url->param('concept', $concept);
$url->param('courseid', $courseid);
$url->param('eid', $eid);
$url->param('displayformat', $displayformat);
$PAGE->set_url($url);

if ($CFG->forcelogin) {
    require_login();
}

if ($eid) {
    $entry = $DB->get_record('library_entries', array('id'=>$eid), '*', MUST_EXIST);
    $library = $DB->get_record('library', array('id'=>$entry->libraryid), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('library', $library->id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    require_course_login($course, true, $cm);
    $entry->libraryname = $library->name;
    $entry->cmid = $cm->id;
    $entry->courseid = $cm->course;
    $entries = array($entry);

} else if ($concept) {
    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    require_course_login($course);
    $entries = library_get_entries_search($concept, $courseid);

} else {
    print_error('invalidelementid');
}

if ($entries) {
    foreach ($entries as $key => $entry) {
        // Need to get the course where the entry is,
        // in order to check for visibility/approve permissions there
        $entrycourse = $DB->get_record('course', array('id' => $entry->courseid), '*', MUST_EXIST);
        $modinfo = get_fast_modinfo($entrycourse);
        // make sure the entry is visible
        if (empty($modinfo->cms[$entry->cmid]->uservisible)) {
            unset($entries[$key]);
            continue;
        }
        // make sure the entry is approved (or approvable by current user)
        if (!$entry->approved and ($USER->id != $entry->userid)) {
            $context = context_module::instance($entry->cmid);
            if (!has_capability('mod/library:approve', $context)) {
                unset($entries[$key]);
                continue;
            }
        }

        // Make sure entry is not autolinking itself.
        $LIBRARY_EXCLUDEENTRY = $entry->id;

        $context = context_module::instance($entry->cmid);
        $definition = file_rewrite_pluginfile_urls($entry->definition, 'pluginfile.php', $context->id, 'mod_library', 'entry', $entry->id);

        $options = new stdClass();
        $options->para = false;
        $options->trusted = $entry->definitiontrust;
        $options->context = $context;
        $entries[$key]->definition = format_text($definition, $entry->definitionformat, $options);

        if (core_tag_tag::is_enabled('mod_library', 'library_entries')) {
            $entries[$key]->definition .= $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('mod_library', 'library_entries', $entry->id), null, 'library-tags');
        }

        $entries[$key]->attachments = '';
        if (!empty($entries[$key]->attachment)) {
            $attachments = library_print_attachments($entry, $cm, 'html');
            $entries[$key]->attachments = html_writer::tag('p', $attachments);
        }

        $entries[$key]->footer = "<p style=\"text-align:right\">&raquo;&nbsp;<a href=\"$CFG->wwwroot/mod/library/view.php?g=$entry->libraryid\">".format_string($entry->libraryname,true)."</a></p>";
        library_entry_view($entry, $modinfo->cms[$entry->cmid]->context);
    }
}

echo $OUTPUT->header();

$result = new stdClass;
$result->success = true;
$result->entries = $entries;
echo json_encode($result);

echo $OUTPUT->footer();

