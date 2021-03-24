<?php

function library_show_entry_encyclopedia($course, $cm, $library, $entry, $mode='',$hook='',$printicons=1, $aliases=true) {
    global $CFG, $USER, $DB, $OUTPUT;


    $user = $DB->get_record('user', array('id'=>$entry->userid));
    $strby = get_string('writtenby', 'library');

    if ($entry) {
        echo '<table class="librarypost encyclopedia" cellspacing="0">';
        echo '<tr valign="top">';
        echo '<td class="left picture">';

        echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));

        echo '</td>';
        echo '<th class="entryheader">';
        echo '<div class="concept">';
        library_print_entry_concept($entry);
        echo '</div>';

        $fullname = fullname($user);
        $by = new stdClass();
        $by->name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id.'">'.$fullname.'</a>';
        $by->date = userdate($entry->timemodified);
        echo '<span class="author">'.get_string('bynameondate', 'forum', $by).'</span>';

        echo '</th>';

        echo '<td class="entryapproval">';
        library_print_entry_approval($cm, $entry, $mode);
        echo '</td>';

        echo '</tr>';

        echo '<tr valign="top">';
        echo '<td class="left side" rowspan="2">&nbsp;</td>';
        echo '<td colspan="2" class="entry">';

        library_print_entry_definition($entry, $library, $cm);
        library_print_entry_attachment($entry, $cm, null);
        if (core_tag_tag::is_enabled('mod_library', 'library_entries')) {
            echo $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('mod_library', 'library_entries', $entry->id), null, 'library-tags');
        }

        if ($printicons or $aliases) {
            echo '</td></tr>';
            echo '<tr>';
            echo '<td colspan="2" class="entrylowersection">';
            library_print_entry_lower_section($course, $cm, $library, $entry,$mode,$hook,$printicons,$aliases);
            echo ' ';
        }

        echo '</td></tr>';
        echo "</table>\n";

    } else {
        echo '<div style="text-align:center">';
        print_string('noentry', 'library');
        echo '</div>';
    }
}

function library_print_entry_encyclopedia($course, $cm, $library, $entry, $mode='', $hook='', $printicons=1) {

    //The print view for this format is exactly the normal view, so we use it

    //Take out autolinking in definitions un print view
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    //Call to view function (without icons, ratings and aliases) and return its result

    return library_show_entry_encyclopedia($course, $cm, $library, $entry, $mode, $hook, false, false);

}


