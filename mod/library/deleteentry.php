<?php

require_once("../../config.php");
require_once("lib.php");

$id       = required_param('id', PARAM_INT);          // course module ID
$confirm  = optional_param('confirm', 0, PARAM_INT);  // commit the operation?
$entry    = optional_param('entry', 0, PARAM_INT);    // entry id
$prevmode = required_param('prevmode', PARAM_ALPHA);
$hook     = optional_param('hook', '', PARAM_CLEAN);

$url = new moodle_url('/mod/library/deleteentry.php', array('id'=>$id,'prevmode'=>$prevmode));
if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}
if ($entry !== 0) {
    $url->param('entry', $entry);
}
if ($hook !== '') {
    $url->param('hook', $hook);
}
$PAGE->set_url($url);

$strlibrary   = get_string("modulename", "library");
$strlibraries = get_string("modulenameplural", "library");
$stredit       = get_string("edit");
$entrydeleted  = get_string("entrydeleted","library");


if (! $cm = get_coursemodule_from_id('library', $id)) {
    print_error("invalidcoursemodule");
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $entry = $DB->get_record("library_entries", array("id"=>$entry))) {
    print_error('invalidentry');
}

// Permission checks are based on the course module instance so make sure it is correct.
if ($cm->instance != $entry->libraryid) {
    print_error('invalidentry');
}

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
$manageentries = has_capability('mod/library:manageentries', $context);

if (! $library = $DB->get_record("library", array("id"=>$cm->instance))) {
    print_error('invalidid', 'library');
}


$strareyousuredelete = get_string("areyousuredelete","library");

if (($entry->userid != $USER->id) and !$manageentries) { // guest id is never matched, no need for special check here
    print_error('nopermissiontodelentry');
}
$ineditperiod = ((time() - $entry->timecreated <  $CFG->maxeditingtime) || $library->editalways);
if (!$ineditperiod and !$manageentries) {
    print_error('errdeltimeexpired', 'library');
}

/// If data submitted, then process and store.

if ($confirm and confirm_sesskey()) { // the operation was confirmed.
    // if it is an imported entry, just delete the relation

    $origentry = fullclone($entry);
    if ($entry->sourcelibraryid) {
        if (!$newcm = get_coursemodule_from_instance('library', $entry->sourcelibraryid)) {
            print_error('invalidcoursemodule');
        }
        $newcontext = context_module::instance($newcm->id);

        $entry->libraryid       = $entry->sourcelibraryid;
        $entry->sourcelibraryid = 0;
        $DB->update_record('library_entries', $entry);

        // move attachments too
        $fs = get_file_storage();

        if ($oldfiles = $fs->get_area_files($context->id, 'mod_library', 'attachment', $entry->id)) {
            foreach ($oldfiles as $oldfile) {
                $file_record = new stdClass();
                $file_record->contextid = $newcontext->id;
                $fs->create_file_from_storedfile($file_record, $oldfile);
            }
            $fs->delete_area_files($context->id, 'mod_library', 'attachment', $entry->id);
            $entry->attachment = '1';
        } else {
            $entry->attachment = '0';
        }
        $DB->update_record('library_entries', $entry);

    } else {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_library', 'attachment', $entry->id);
        $DB->delete_records("comments", array('itemid'=>$entry->id, 'commentarea'=>'library_entry', 'contextid'=>$context->id));
        $DB->delete_records("library_alias", array("entryid"=>$entry->id));
        $DB->delete_records("library_entries", array("id"=>$entry->id));

        // Update completion state
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $library->completionentries) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $entry->userid);
        }

        //delete library entry ratings
        require_once($CFG->dirroot.'/rating/lib.php');
        $delopt = new stdClass;
        $delopt->contextid = $context->id;
        $delopt->component = 'mod_library';
        $delopt->ratingarea = 'entry';
        $delopt->itemid = $entry->id;
        $rm = new rating_manager();
        $rm->delete_ratings($delopt);
    }

    // Delete cached RSS feeds.
    if (!empty($CFG->enablerssfeeds)) {
        require_once($CFG->dirroot.'/mod/library/rsslib.php');
        library_rss_delete_file($library);
    }

    core_tag_tag::remove_all_item_tags('mod_library', 'library_entries', $origentry->id);

    $event = \mod_library\event\entry_deleted::create(array(
        'context' => $context,
        'objectid' => $origentry->id,
        'other' => array(
            'mode' => $prevmode,
            'hook' => $hook,
            'concept' => $origentry->concept
        )
    ));
    $event->add_record_snapshot('library_entries', $origentry);
    $event->trigger();

    // Reset caches.
    if ($entry->usedynalink and $entry->approved) {
        \mod_library\local\concept_cache::reset_library($library);
    }

    redirect("view.php?id=$cm->id&amp;mode=$prevmode&amp;hook=$hook");

} else {        // the operation has not been confirmed yet so ask the user to do so
    $PAGE->navbar->add(get_string('delete'));
    $PAGE->set_title($library->name);
    $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
    $areyousure = "<b>".format_string($entry->concept)."</b><p>$strareyousuredelete</p>";
    $linkyes    = 'deleteentry.php';
    $linkno     = 'view.php';
    $optionsyes = array('id'=>$cm->id, 'entry'=>$entry->id, 'confirm'=>1, 'sesskey'=>sesskey(), 'prevmode'=>$prevmode, 'hook'=>$hook);
    $optionsno  = array('id'=>$cm->id, 'mode'=>$prevmode, 'hook'=>$hook);

    echo $OUTPUT->confirm($areyousure, new moodle_url($linkyes, $optionsyes), new moodle_url($linkno, $optionsno));

    echo $OUTPUT->footer();
}
