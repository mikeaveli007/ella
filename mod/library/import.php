<?php

require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/course/modlib.php");
require_once('import_form.php');

$id = required_param('id', PARAM_INT);    // Course Module ID

$mode     = optional_param('mode', 'letter', PARAM_ALPHA );
$hook     = optional_param('hook', 'ALL', PARAM_ALPHANUM);

$url = new moodle_url('/mod/library/import.php', array('id'=>$id));
if ($mode !== 'letter') {
    $url->param('mode', $mode);
}
if ($hook !== 'ALL') {
    $url->param('hook', $hook);
}
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('library', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $library = $DB->get_record("library", array("id"=>$cm->instance))) {
    print_error('invalidid', 'library');
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/library:import', $context);

$strlibraries = get_string("modulenameplural", "library");
$strlibrary = get_string("modulename", "library");
$strallcategories = get_string("allcategories", "library");
$straddentry = get_string("addentry", "library");
$strnoentries = get_string("noentries", "library");
$strsearchindefinition = get_string("searchindefinition", "library");
$strsearch = get_string("search");
$strimportentries = get_string('importentriesfromxml', 'library');

$PAGE->navbar->add($strimportentries);
$PAGE->set_title($library->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($strimportentries);

$form = new mod_library_import_form();

if ( !$data = $form->get_data() ) {
    echo $OUTPUT->box_start('librarydisplay generalbox');
    // display upload form
    $data = new stdClass();
    $data->id = $id;
    $form->set_data($data);
    $form->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

$result = $form->get_file_content('file');

if (empty($result)) {
    echo $OUTPUT->box_start('librarydisplay generalbox');
    echo $OUTPUT->continue_button('import.php?id='.$id);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    die();
}

// Large exports are likely to take their time and memory.
core_php_time_limit::raise();
raise_memory_limit(MEMORY_EXTRA);

if ($xml = library_read_imported_file($result)) {
    $importedentries = 0;
    $importedcats    = 0;
    $entriesrejected = 0;
    $rejections      = '';
    $librarycontext = $context;

    if ($data->dest == 'newlibrary') {
        // If the user chose to create a new library
        $xmllibrary = $xml['LIBRARY']['#']['INFO'][0]['#'];

        if ( $xmllibrary['NAME'][0]['#'] ) {
            $library = new stdClass();
            $library->modulename = 'library';
            $library->module = $cm->module;
            $library->name = ($xmllibrary['NAME'][0]['#']);
            $library->globallibrary = ($xmllibrary['GLOBALLIBRARY'][0]['#']);
            $library->intro = ($xmllibrary['INTRO'][0]['#']);
            $library->introformat = isset($xmllibrary['INTROFORMAT'][0]['#']) ? $xmllibrary['INTROFORMAT'][0]['#'] : FORMAT_MOODLE;
            $library->showspecial = ($xmllibrary['SHOWSPECIAL'][0]['#']);
            $library->showalphabet = ($xmllibrary['SHOWALPHABET'][0]['#']);
            $library->showall = ($xmllibrary['SHOWALL'][0]['#']);
            $library->cmidnumber = null;

            // Setting the default values if no values were passed
            if ( isset($xmllibrary['ENTBYPAGE'][0]['#']) ) {
                $library->entbypage = ($xmllibrary['ENTBYPAGE'][0]['#']);
            } else {
                $library->entbypage = $CFG->library_entbypage;
            }
            if ( isset($xmllibrary['ALLOWDUPLICATEDENTRIES'][0]['#']) ) {
                $library->allowduplicatedentries = ($xmllibrary['ALLOWDUPLICATEDENTRIES'][0]['#']);
            } else {
                $library->allowduplicatedentries = $CFG->library_dupentries;
            }
            if ( isset($xmllibrary['DISPLAYFORMAT'][0]['#']) ) {
                $library->displayformat = ($xmllibrary['DISPLAYFORMAT'][0]['#']);
            } else {
                $library->displayformat = 2;
            }
            if ( isset($xmllibrary['ALLOWCOMMENTS'][0]['#']) ) {
                $library->allowcomments = ($xmllibrary['ALLOWCOMMENTS'][0]['#']);
            } else {
                $library->allowcomments = $CFG->library_allowcomments;
            }
            if ( isset($xmllibrary['USEDYNALINK'][0]['#']) ) {
                $library->usedynalink = ($xmllibrary['USEDYNALINK'][0]['#']);
            } else {
                $library->usedynalink = $CFG->library_linkentries;
            }
            if ( isset($xmllibrary['DEFAULTAPPROVAL'][0]['#']) ) {
                $library->defaultapproval = ($xmllibrary['DEFAULTAPPROVAL'][0]['#']);
            } else {
                $library->defaultapproval = $CFG->library_defaultapproval;
            }

            // These fields were not included in export, assume zero.
            $library->assessed = 0;
            $library->availability = null;

            // Check if we're creating the new library on the front page or inside a course.
            if ($cm->course == SITEID) {
                // On the front page, activities are in section 1.
                $library->section = 1;
            } else {
                // Inside a course, add to the general section (0).
                $library->section = 0;
            }
            // New library is always visible.
            $library->visible = 1;
            $library->visibleoncoursepage = 1;

            // Include new library and return the new ID
            if ( !($library = add_moduleinfo($library, $course)) ) {
                echo $OUTPUT->notification("Error while trying to create the new library.");
                library_print_tabbed_table_end();
                echo $OUTPUT->footer();
                exit;
            } else {
                $librarycontext = context_module::instance($library->coursemodule);
                library_xml_import_files($xmllibrary, 'INTROFILES', $librarycontext->id, 'intro', 0);
                echo $OUTPUT->box(get_string("newlibrarycreated","library"),'generalbox boxaligncenter boxwidthnormal');
            }
        } else {
            echo $OUTPUT->notification("Error while trying to create the new library.");
            echo $OUTPUT->footer();
            exit;
        }
    }

    $xmlentries = $xml['LIBRARY']['#']['INFO'][0]['#']['ENTRIES'][0]['#']['ENTRY'];
    $sizeofxmlentries = is_array($xmlentries) ? count($xmlentries) : 0;
    for($i = 0; $i < $sizeofxmlentries; $i++) {
        // Inserting the entries
        $xmlentry = $xmlentries[$i];
        $newentry = new stdClass();
        $newentry->concept = trim($xmlentry['#']['CONCEPT'][0]['#']);
        $definition = $xmlentry['#']['DEFINITION'][0]['#'];
        if (!is_string($definition)) {
            print_error('errorparsingxml', 'library');
        }
        $newentry->definition = trusttext_strip($definition);
        if ( isset($xmlentry['#']['CASESENSITIVE'][0]['#']) ) {
            $newentry->casesensitive = $xmlentry['#']['CASESENSITIVE'][0]['#'];
        } else {
            $newentry->casesensitive = $CFG->library_casesensitive;
        }

        $permissiongranted = 1;
        if ( $newentry->concept and $newentry->definition ) {
            if ( !$library->allowduplicatedentries ) {
                // checking if the entry is valid (checking if it is duplicated when should not be)
                if ( $newentry->casesensitive ) {
                    $dupentry = $DB->record_exists_select('library_entries',
                                    'libraryid = :libraryid AND concept = :concept', array(
                                        'libraryid' => $library->id,
                                        'concept'    => $newentry->concept));
                } else {
                    $dupentry = $DB->record_exists_select('library_entries',
                                    'libraryid = :libraryid AND LOWER(concept) = :concept', array(
                                        'libraryid' => $library->id,
                                        'concept'    => core_text::strtolower($newentry->concept)));
                }
                if ($dupentry) {
                    $permissiongranted = 0;
                }
            }
        } else {
            $permissiongranted = 0;
        }
        if ($permissiongranted) {
            $newentry->libraryid       = $library->id;
            $newentry->sourcelibraryid = 0;
            $newentry->approved         = 1;
            $newentry->userid           = $USER->id;
            $newentry->teacherentry     = 1;
            $newentry->definitionformat = $xmlentry['#']['FORMAT'][0]['#'];
            $newentry->timecreated      = time();
            $newentry->timemodified     = time();

            // Setting the default values if no values were passed
            if ( isset($xmlentry['#']['USEDYNALINK'][0]['#']) ) {
                $newentry->usedynalink      = $xmlentry['#']['USEDYNALINK'][0]['#'];
            } else {
                $newentry->usedynalink      = $CFG->library_linkentries;
            }
            if ( isset($xmlentry['#']['FULLMATCH'][0]['#']) ) {
                $newentry->fullmatch        = $xmlentry['#']['FULLMATCH'][0]['#'];
            } else {
                $newentry->fullmatch      = $CFG->library_fullmatch;
            }

            $newentry->id = $DB->insert_record("library_entries",$newentry);
            $importedentries++;

            $xmlaliases = @$xmlentry['#']['ALIASES'][0]['#']['ALIAS']; // ignore missing ALIASES
            $sizeofxmlaliases = is_array($xmlaliases) ? count($xmlaliases) : 0;
            for($k = 0; $k < $sizeofxmlaliases; $k++) {
            /// Importing aliases
                $xmlalias = $xmlaliases[$k];
                $aliasname = $xmlalias['#']['NAME'][0]['#'];

                if (!empty($aliasname)) {
                    $newalias = new stdClass();
                    $newalias->entryid = $newentry->id;
                    $newalias->alias = trim($aliasname);
                    $newalias->id = $DB->insert_record("library_alias",$newalias);
                }
            }

            if (!empty($data->catsincl)) {
                // If the categories must be imported...
                $xmlcats = @$xmlentry['#']['CATEGORIES'][0]['#']['CATEGORY']; // ignore missing CATEGORIES
                $sizeofxmlcats = is_array($xmlcats) ? count($xmlcats) : 0;
                for($k = 0; $k < $sizeofxmlcats; $k++) {
                    $xmlcat = $xmlcats[$k];

                    $newcat = new stdClass();
                    $newcat->name = $xmlcat['#']['NAME'][0]['#'];
                    $newcat->usedynalink = $xmlcat['#']['USEDYNALINK'][0]['#'];
                    if ( !$category = $DB->get_record("library_categories", array("libraryid"=>$library->id,"name"=>$newcat->name))) {
                        // Create the category if it does not exist
                        $category = new stdClass();
                        $category->name = $newcat->name;
                        $category->libraryid = $library->id;
                        $category->id = $DB->insert_record("library_categories",$category);
                        $importedcats++;
                    }
                    if ( $category ) {
                        // inserting the new relation
                        $entrycat = new stdClass();
                        $entrycat->entryid    = $newentry->id;
                        $entrycat->categoryid = $category->id;
                        $DB->insert_record("library_entries_categories",$entrycat);
                    }
                }
            }

            // Import files embedded in the entry text.
            library_xml_import_files($xmlentry['#'], 'ENTRYFILES', $librarycontext->id, 'entry', $newentry->id);

            // Import files attached to the entry.
            if (library_xml_import_files($xmlentry['#'], 'ATTACHMENTFILES', $librarycontext->id, 'attachment', $newentry->id)) {
                $DB->update_record("library_entries", array('id' => $newentry->id, 'attachment' => '1'));
            }

            // Import tags associated with the entry.
            if (core_tag_tag::is_enabled('mod_library', 'library_entries')) {
                $xmltags = @$xmlentry['#']['TAGS'][0]['#']['TAG']; // Ignore missing TAGS.
                $sizeofxmltags = is_array($xmltags) ? count($xmltags) : 0;
                for ($k = 0; $k < $sizeofxmltags; $k++) {
                    // Importing tags.
                    $tag = $xmltags[$k]['#'];
                    if (!empty($tag)) {
                        core_tag_tag::add_item_tag('mod_library', 'library_entries', $newentry->id, $librarycontext, $tag);
                    }
                }
            }

        } else {
            $entriesrejected++;
            if ( $newentry->concept and $newentry->definition ) {
                // add to exception report (duplicated entry))
                $rejections .= "<tr><td>$newentry->concept</td>" .
                               "<td>" . get_string("duplicateentry","library"). "</td></tr>";
            } else {
                // add to exception report (no concept or definition found))
                $rejections .= "<tr><td>---</td>" .
                               "<td>" . get_string("noconceptfound","library"). "</td></tr>";
            }
        }
    }

    // Reset caches.
    \mod_library\local\concept_cache::reset_library($library);

    // processed entries
    echo $OUTPUT->box_start('librarydisplay generalbox');
    echo '<table class="libraryimportexport">';
    echo '<tr>';
    echo '<td width="50%" align="right">';
    echo get_string("totalentries","library");
    echo ':</td>';
    echo '<td width="50%" align="left">';
    echo $importedentries + $entriesrejected;
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td width="50%" align="right">';
    echo get_string("importedentries","library");
    echo ':</td>';
    echo '<td width="50%" align="left">';
    echo $importedentries;
    if ( $entriesrejected ) {
        echo ' <small>(' . get_string("rejectedentries","library") . ": $entriesrejected)</small>";
    }
    echo '</td>';
    echo '</tr>';
    if (!empty($data->catsincl)) {
        echo '<tr>';
        echo '<td width="50%" align="right">';
        echo get_string("importedcategories","library");
        echo ':</td>';
        echo '<td width="50%">';
        echo $importedcats;
        echo '</td>';
        echo '</tr>';
    }
    echo '</table><hr />';

    // rejected entries
    if ($rejections) {
        echo $OUTPUT->heading(get_string("rejectionrpt","library"), 4);
        echo '<table class="libraryimportexport">';
        echo $rejections;
        echo '</table><hr />';
    }
/// Print continue button, based on results
    if ($importedentries) {
        echo $OUTPUT->continue_button('view.php?id='.$id);
    } else {
        echo $OUTPUT->continue_button('import.php?id='.$id);
    }
    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->box_start('librarydisplay generalbox');
    echo get_string('errorparsingxml', 'library');
    echo $OUTPUT->continue_button('import.php?id='.$id);
    echo $OUTPUT->box_end();
}

/// Finish the page
echo $OUTPUT->footer();
