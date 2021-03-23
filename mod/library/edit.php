<?php

require_once('../../config.php');
require_once('lib.php');
require_once('edit_form.php');

$cmid = required_param('cmid', PARAM_INT);            // Course Module ID
$id   = optional_param('id', 0, PARAM_INT);           // EntryID

if (!$cm = get_coursemodule_from_id('library', $cmid)) {
    print_error('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', array('id'=>$cm->course))) {
    print_error('coursemisconf');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);

if (!$library = $DB->get_record('library', array('id'=>$cm->instance))) {
    print_error('invalidid', 'library');
}

$url = new moodle_url('/mod/library/edit.php', array('cmid'=>$cm->id));
if (!empty($id)) {
    $url->param('id', $id);
}
$PAGE->set_url($url);

if ($id) { // if entry is specified
    if (isguestuser()) {
        print_error('guestnoedit', 'library', "$CFG->wwwroot/mod/library/view.php?id=$cmid");
    }

    if (!$entry = $DB->get_record('library_entries', array('id'=>$id, 'libraryid'=>$library->id))) {
        print_error('invalidentry');
    }

    $ineditperiod = ((time() - $entry->timecreated <  $CFG->maxeditingtime) || $library->editalways);
    if (!has_capability('mod/library:manageentries', $context) and !($entry->userid == $USER->id and ($ineditperiod and has_capability('mod/library:write', $context)))) {
        if ($USER->id != $entry->userid) {
            print_error('errcannoteditothers', 'library', "view.php?id=$cm->id&amp;mode=entry&amp;hook=$id");
        } elseif (!$ineditperiod) {
            print_error('erredittimeexpired', 'library', "view.php?id=$cm->id&amp;mode=entry&amp;hook=$id");
        }
    }

    //prepare extra data
    if ($aliases = $DB->get_records_menu("library_alias", array("entryid"=>$id), '', 'id, alias')) {
        $entry->aliases = implode("\n", $aliases) . "\n";
    }
    if ($categoriesarr = $DB->get_records_menu("library_entries_categories", array('entryid'=>$id), '', 'id, categoryid')) {
        // TODO: this fetches cats from both main and secondary library :-(
        $entry->categories = array_values($categoriesarr);
    }

} else { // new entry
    require_capability('mod/library:write', $context);
    // note: guest user does not have any write capability
    $entry = new stdClass();
    $entry->id = null;
}

list($definitionoptions, $attachmentoptions) = library_get_editor_and_attachment_options($course, $context, $entry);

$entry = file_prepare_standard_editor($entry, 'definition', $definitionoptions, $context, 'mod_library', 'entry', $entry->id);
$entry = file_prepare_standard_filemanager($entry, 'attachment', $attachmentoptions, $context, 'mod_library', 'attachment', $entry->id);

$entry->cmid = $cm->id;

// create form and set initial data
$mform = new mod_library_entry_form(null, array('current'=>$entry, 'cm'=>$cm, 'library'=>$library,
                                                 'definitionoptions'=>$definitionoptions, 'attachmentoptions'=>$attachmentoptions));

if ($mform->is_cancelled()){
    if ($id){
        redirect("view.php?id=$cm->id&mode=entry&hook=$id");
    } else {
        redirect("view.php?id=$cm->id");
    }

} else if ($data = $mform->get_data()) {
    $entry = library_edit_entry($data, $course, $cm, $library, $context);
    if (core_tag_tag::is_enabled('mod_library', 'library_entries') && isset($data->tags)) {
        core_tag_tag::set_item_tags('mod_library', 'library_entries', $data->id, $context, $data->tags);
    }
    redirect("view.php?id=$cm->id&mode=entry&hook=$entry->id");
}

if (!empty($id)) {
    $PAGE->navbar->add(get_string('edit'));
}

$PAGE->set_title($library->name);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($library->name), 2);
if ($library->intro) {
    //echo $OUTPUT->box(format_module_intro('library', $library, $cm->id), 'generalbox', 'intro');
}

$data = new StdClass();
$data->tags = core_tag_tag::get_item_tags_array('mod_library', 'library_entries', $id);
$mform->set_data($data);

$mform->display();

echo $OUTPUT->footer();

