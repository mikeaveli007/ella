<?php

function library_show_entry_dictionary($course, $cm, $library, $entry, $mode='', $hook='', $printicons=1, $aliases=true) {

    global $CFG, $USER, $OUTPUT;

    echo '<table class="librarypost dictionary" cellspacing="0">';
    echo '<tr valign="top">';
    echo '<td class="entry">';
    library_print_entry_approval($cm, $entry, $mode);
    echo '<div class="concept">';
    library_print_entry_concept($entry, false, true, true);
    echo '</div> ';
    library_print_entry_definition($entry, $library, $cm);
    library_print_entry_attachment($entry, $cm, 'html');
    if (core_tag_tag::is_enabled('mod_library', 'library_entries')) {
        echo $OUTPUT->tag_list(core_tag_tag::get_item_tags('mod_library', 'library_entries', $entry->id), null, 'library-tags');
    }
    echo '</td></tr>';
    echo '<tr valign="top"><td class="entrylowersection">';
    library_print_entry_lower_section($course, $cm, $library, $entry, $mode, $hook, $printicons, $aliases);
    echo '</td>';
    echo '</tr>';
    echo "</table>\n";
}

function library_print_entry_dictionary($course, $cm, $library, $entry, $mode='', $hook='', $printicons=1) {

    //The print view for this format is exactly the normal view, so we use it

    //Take out autolinking in definitions in print view
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    //Call to view function (without icons, ratings and aliases) and return its result
    return library_show_entry_dictionary($course, $cm, $library, $entry, $mode, $hook, false, false, false);
}