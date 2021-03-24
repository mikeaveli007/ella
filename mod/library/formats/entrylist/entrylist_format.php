<?php

function library_show_entry_entrylist($course, $cm, $library, $entry, $mode='', $hook='', $printicons=1, $aliases=true) {
    global $USER, $OUTPUT;

    $return = false;
    //console_log($entry);

    echo '<table class="librarypost entrylist" cellspacing="0">';

    echo '<tr valign="top">';
    echo '<td class="entry">';
    if ($entry) {
        library_print_entry_approval($cm, $entry, $mode);

        $anchortagcontents = library_print_entry_concept($entry, true);

        $link = new moodle_url('/mod/library/showentry.php', array('courseid' => $course->id,
                'eid' => $entry->id, 'displayformat' => 'dictionary'));
        $anchor = html_writer::link($link, $anchortagcontents);

        if(!empty($entry->typeicon)) {echo '<i class="icon fa ' . $entry->typeicon . '"></i>';}
        echo "<div class=\"concept d-inline-block \">$anchor</div> ";
        echo '</td><td align="right" class="entrylowersection">';
        if ($printicons) {
            library_print_entry_icons($course, $cm, $library, $entry, $mode, $hook,'print');
        }
        if (!empty($entry->rating)) {
            echo '<br />';
            echo '<span class="ratings d-block p-t-1">';
            $return = library_print_entry_ratings($course, $entry);
            echo '</span>';
        }
        echo '<br />';
    } else {
        echo '<div style="text-align:center">';
        print_string('noentry', 'library');
        echo '</div>';
    }
    echo '</td></tr>';

    echo "</table>";
    echo "<hr>\n";
    return $return;
}

function library_print_entry_entrylist($course, $cm, $library, $entry, $mode='', $hook='', $printicons=1) {
    //Take out autolinking in definitions un print view
    // TODO use <nolink> tags MDL-15555.
    $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

    echo html_writer::start_tag('table', array('class' => 'librarypost entrylist mod-library-entrylist'));
    echo html_writer::start_tag('tr');
    echo html_writer::start_tag('td', array('class' => 'entry mod-library-entry'));
    echo html_writer::start_tag('div', array('class' => 'mod-library-concept'));
    library_print_entry_concept($entry, false, true, true);
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'mod-library-definition'));
    library_print_entry_definition($entry, $library, $cm);
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'mod-library-lower-section'));
    library_print_entry_lower_section($course, $cm, $library, $entry, $mode, $hook, false, false);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('td');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('table');
}


