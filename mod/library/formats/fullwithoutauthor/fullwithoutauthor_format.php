<?php

function library_show_entry_fullwithoutauthor($course, $cm, $library, $entry, $mode="", $hook="", $printicons=1, $aliases=true) {
    global $CFG, $USER, $OUTPUT;


    if ($entry) {
        echo '<table class="librarypost fullwithoutauthor" cellspacing="0">';
        echo '<tr valign="top">';

        echo '<th class="entryheader">';

        echo '<div class="concept">';
        library_print_entry_concept($entry);
        echo '</div>';

        echo '<span class="time">('.get_string('lastedited').': '.
             userdate($entry->timemodified).')</span>';
        echo '</th>';
        echo '<td class="entryattachment">';

        library_print_entry_approval($cm, $entry, $mode);
        echo '</td>';

        echo '</tr>';

        echo '<tr valign="top">';
        echo '<td width="100%" colspan="2" class="entry">';

        library_print_entry_definition($entry, $library, $cm);
        library_print_entry_attachment($entry, $cm, 'html');

        if (core_tag_tag::is_enabled('mod_library', 'library_entries')) {
            echo $OUTPUT->tag_list(
                core_tag_tag::get_item_tags('mod_library', 'library_entries', $entry->id), null, 'library-tags');
        }

        echo '</td></tr>';
        echo '<tr valign="top"><td colspan="2" class="entrylowersection">';
        library_print_entry_lower_section($course, $cm, $library, $entry, $mode, $hook, $printicons, $aliases);

        echo ' ';
        echo '</td></tr>';
        echo "</table>\n";
    } else {
        echo '<center>';
        print_string('noentry', 'library');
        echo '</center>';
    }
}

function library_print_entry_fullwithoutauthor($course, $cm, $library, $entry, $mode="", $hook="", $printicons=1) {

    //The print view for this format is exactly the normal view, so we use it

    //Take out autolinking in definitions un print view
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    //Call to view function (without icons, ratings and aliases) and return its result
    return library_show_entry_fullwithoutauthor($course, $cm, $library, $entry, $mode, $hook, false, false, false);

}


