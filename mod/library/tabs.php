<?php
    if (!isset($sortorder)) {
        $sortorder = '';
    }
    if (!isset($sortkey)) {
        $sortkey = '';
    }

    //make sure variables are properly cleaned
    $sortkey   = clean_param($sortkey, PARAM_ALPHA);// Sorted view: CREATION | UPDATE | FIRSTNAME | LASTNAME...
    $sortorder = clean_param($sortorder, PARAM_ALPHA);   // it defines the order of the sorting (ASC or DESC)

    $toolsrow = array();
    $browserow = array();
    $inactive = array();
    $activated = array();

    if (!has_capability('mod/library:approve', $context) && $tab == LIBRARY_APPROVAL_VIEW) {
    /// Non-teachers going to approval view go to defaulttab
        $tab = $defaulttab;
    }

    // Get visible tabs for the format and check tab needs to be displayed.
    $dt = library_get_visible_tabs($dp);

    if (in_array(LIBRARY_STANDARD, $dt)) {
        $browserow[] = new tabobject(LIBRARY_STANDARD_VIEW,
            $CFG->wwwroot.'/mod/library/view.php?id='.$id.'&amp;mode=letter',
            get_string('standardview', 'library'));
    }

    if (in_array(LIBRARY_CATEGORY, $dt)) {
        $browserow[] = new tabobject(LIBRARY_CATEGORY_VIEW,
            $CFG->wwwroot.'/mod/library/view.php?id='.$id.'&amp;mode=cat',
            get_string('categoryview', 'library'));
    }

    if (in_array(LIBRARY_DATE, $dt)) {
        $browserow[] = new tabobject(LIBRARY_DATE_VIEW,
            $CFG->wwwroot.'/mod/library/view.php?id='.$id.'&amp;mode=date',
            get_string('dateview', 'library'));
    }

    if (in_array(LIBRARY_AUTHOR, $dt)) {
        $browserow[] = new tabobject(LIBRARY_AUTHOR_VIEW,
            $CFG->wwwroot.'/mod/library/view.php?id='.$id.'&amp;mode=author',
            get_string('authorview', 'library'));
    }

    if ($tab < LIBRARY_STANDARD_VIEW || $tab > LIBRARY_AUTHOR_VIEW) {   // We are on second row
        $inactive = array('edit');
        $activated = array('edit');

        $browserow[] = new tabobject('edit', '#', get_string('edit'));
    }

/// Put all this info together

    $tabrows = array();
    $tabrows[] = $browserow;     // Always put these at the top
    if ($toolsrow) {
        $tabrows[] = $toolsrow;
    }

?>
  <div class="librarydisplay">


<?php
if ($showcommonelements && (count($tabrows[0]) > 1)) {
    print_tabs($tabrows, $tab, $inactive, $activated);
}
?>

  <div class="entrybox">

<?php

    if (!isset($category)) {
        $category = "";
    }


    switch ($tab) {
        case LIBRARY_CATEGORY_VIEW:
            library_print_categories_menu($cm, $library, $hook, $category);
        break;
        case LIBRARY_APPROVAL_VIEW:
            library_print_approval_menu($cm, $library, $mode, $hook, $sortkey, $sortorder);
        break;
        case LIBRARY_AUTHOR_VIEW:
            $search = "";
            library_print_author_menu($cm, $library, "author", $hook, $sortkey, $sortorder, 'print');
        break;
        case LIBRARY_IMPORT_VIEW:
            $search = "";
            $l = "";
            library_print_import_menu($cm, $library, 'import', $hook, $sortkey, $sortorder);
        break;
        case LIBRARY_EXPORT_VIEW:
            $search = "";
            $l = "";
            library_print_export_menu($cm, $library, 'export', $hook, $sortkey, $sortorder);
        break;
        case LIBRARY_DATE_VIEW:
            if (!$sortkey) {
                $sortkey = 'UPDATE';
            }
            if (!$sortorder) {
                $sortorder = 'desc';
            }
            library_print_alphabet_menu($cm, $library, "date", $hook, $sortkey, $sortorder);
        break;
        case LIBRARY_STANDARD_VIEW:
        default:
            library_print_alphabet_menu($cm, $library, "letter", $hook, $sortkey, $sortorder);
            if ($mode == 'search' and $hook) {
                echo html_writer::tag('div', "$strsearch: $hook");
            }
        break;
    }
    echo html_writer::empty_tag('hr');
?>