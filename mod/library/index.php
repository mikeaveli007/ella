<?php

/// This page lists all the instances of library in a particular course
/// Replace library with the name of your module

require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->libdir/rsslib.php");
require_once("$CFG->dirroot/course/lib.php");

$id = required_param('id', PARAM_INT);   // course

$PAGE->set_url('/mod/library/index.php', array('id'=>$id));

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');
$context = context_course::instance($course->id);

$event = \mod_library\event\course_module_instance_list_viewed::create(array(
    'context' => $context
));
$event->add_record_snapshot('course', $course);
$event->trigger();

/// Get all required strings

$strlibrarys = get_string("modulenameplural", "library");
$strlibrary  = get_string("modulename", "library");
$strrss = get_string("rss");


/// Print the header
$PAGE->navbar->add($strlibrarys, "index.php?id=$course->id");
$PAGE->set_title($strlibrarys);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($strlibrarys), 2);

/// Get all the appropriate data

if (! $librarys = get_all_instances_in_course("library", $course)) {
    notice(get_string('thereareno', 'moodle', $strlibrarys), "../../course/view.php?id=$course->id");
    die;
}

$usesections = course_format_uses_sections($course->format);

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strname  = get_string("name");
$strentries  = get_string("entries", "library");

$table = new html_table();

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strentries);
    $table->align = array ('center', 'left', 'center');
} else {
    $table->head  = array ($strname, $strentries);
    $table->align = array ('left', 'center');
}

if ($show_rss = (isset($CFG->enablerssfeeds) && isset($CFG->library_enablerssfeeds) &&
                 $CFG->enablerssfeeds && $CFG->library_enablerssfeeds)) {
    $table->head[] = $strrss;
    $table->align[] = 'center';
}

$currentsection = "";

foreach ($librarys as $library) {
    if (!$library->visible && has_capability('moodle/course:viewhiddenactivities',
            context_module::instance($library->coursemodule))) {
        // Show dimmed if the mod is hidden.
        $link = "<a class=\"dimmed\" href=\"view.php?id=$library->coursemodule\">".format_string($library->name,true)."</a>";
    } else if ($library->visible) {
        // Show normal if the mod is visible.
        $link = "<a href=\"view.php?id=$library->coursemodule\">".format_string($library->name,true)."</a>";
    } else {
        // Don't show the library.
        continue;
    }
    $printsection = "";
    if ($usesections) {
        if ($library->section !== $currentsection) {
            if ($library->section) {
                $printsection = get_section_name($course, $library->section);
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $library->section;
        }
    }

    // TODO: count only approved if not allowed to see them

    $count = $DB->count_records_sql("SELECT COUNT(*) FROM {library_entries} WHERE (libraryid = ? OR sourcelibraryid = ?)", array($library->id, $library->id));

    //If this library has RSS activated, calculate it
    if ($show_rss) {
        $rsslink = '';
        if ($library->rsstype and $library->rssarticles) {
            //Calculate the tolltip text
            $tooltiptext = get_string("rsssubscriberss","library",format_string($library->name));
            if (!isloggedin()) {
                $userid = 0;
            } else {
                $userid = $USER->id;
            }
            //Get html code for RSS link
            $rsslink = rss_get_link($context->id, $userid, 'mod_library', $library->id, $tooltiptext);
        }
    }

    if ($usesections) {
        $linedata = array ($printsection, $link, $count);
    } else {
        $linedata = array ($link, $count);
    }

    if ($show_rss) {
        $linedata[] = $rsslink;
    }

    $table->data[] = $linedata;
}

echo "<br />";

echo html_writer::table($table);

/// Finish the page

echo $OUTPUT->footer();

