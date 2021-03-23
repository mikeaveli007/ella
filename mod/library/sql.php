<?php

/**
 * SQL.PHP
 *    This file is include from view.php and print.php
 * @copyright 2003
 **/

/**
 * This file defines, or redefines, the following variables:
 *
 * bool $userispivot Whether the user is the pivot.
 * bool $fullpivot Whether the pivot should be displayed in full.
 * bool $printpivot Whether the pivot should be displayed.
 * string $pivotkey The property of the record at which the pivot is.
 * int $count The number of records matching the request.
 * array $allentries The entries matching the request.
 * mixed $field Unset in this file.
 * mixed $entry Unset in this file.
 * mixed $canapprove Unset in this file.
 *
 * It relies on the following variables:
 *
 * object $library The library object.
 * context $context The library context.
 * mixed $hook The hook for the selected tab.
 * string $sortkey The key to sort the records.
 * string $sortorder The order of the sorting.
 * int $offset The number of records to skip.
 * int $pagelimit The number of entries on this page, or 0 if unlimited.
 * string $mode The mode of browsing.
 * string $tab The tab selected.
 */

$userispivot = false;
$fullpivot = true;
$pivotkey = 'concept';

switch ($tab) {

    case LIBRARY_AUTHOR_VIEW:
        $userispivot = true;
        $pivotkey = 'userid';
        $field = ($sortkey == 'LASTNAME' ? 'LASTNAME' : 'FIRSTNAME');
        list($allentries, $count) = library_get_entries_by_author($library, $context, $hook,
            $field, $sortorder, $offset, $pagelimit);
        unset($field);
        break;

    case LIBRARY_CATEGORY_VIEW:
        $hook = (int) $hook; // Make sure it's properly casted to int.
        list($allentries, $count) = library_get_entries_by_category($library, $context, $hook, $offset, $pagelimit);
        $pivotkey = 'categoryname';
        if ($hook != LIBRARY_SHOW_ALL_CATEGORIES) {
            $printpivot = false;
        }
        break;

    case LIBRARY_DATE_VIEW:
        $printpivot = false;
        $field = ($sortkey == 'CREATION' ? 'CREATION' : 'UPDATE');
        list($allentries, $count) = library_get_entries_by_date($library, $context, $field, $sortorder,
            $offset, $pagelimit);
        unset($field);
        break;

    case LIBRARY_APPROVAL_VIEW:
        $fullpivot = false;
        $printpivot = false;
        list($allentries, $count) = library_get_entries_to_approve($library, $context, $hook, $sortkey, $sortorder,
            $offset, $pagelimit);
        break;

    case LIBRARY_STANDARD_VIEW:
    default:
        $fullpivot = false;
        switch ($mode) {
            case 'search':
                list($allentries, $count) = library_get_entries_by_search($library, $context, $hook, $fullsearch,
                    $sortkey, $sortorder, $offset, $pagelimit);
                break;

            case 'term':
                $printpivot = false;
                list($allentries, $count) = library_get_entries_by_term($library, $context, $hook, $offset, $pagelimit);
                break;

            case 'entry':
                $printpivot = false;
                $entry = library_get_entry_by_id($hook);
                $canapprove = has_capability('mod/library:approve', $context);
                if ($entry && ($entry->libraryid == $library->id || $entry->sourcelibraryid != $library->id)
                        && (!empty($entry->approved) || $entry->userid == $USER->id || $canapprove)) {
                    $count = 1;
                    $allentries = array($entry);
                } else {
                    $count = 0;
                    $allentries = array();
                }
                unset($entry, $canapprove);
                break;
            case 'cat':
                $hook = (int) $hook; // Make sure it's properly casted to int.
                list($allentries, $count) = library_get_entries_by_category($library, $context, $hook, $offset, $pagelimit);
                $pivotkey = 'categoryname';
                if ($hook != LIBRARY_SHOW_ALL_CATEGORIES) {
                    $printpivot = false;
                }
                break;
            case 'letter':
            default:
                list($allentries, $count) = library_get_entries_by_letter($library, $context, $hook, $offset, $pagelimit);
                break;
        }
        break;
}
