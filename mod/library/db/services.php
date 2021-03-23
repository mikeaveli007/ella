<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library module external functions.
 *
 * @package    mod_library
 * @category   external
 * @copyright  2015 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'mod_library_get_libraries_by_courses' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_libraries_by_courses',
        'description'   => 'Retrieve a list of libraries from several courses.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_view_library' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'view_library',
        'description'   => 'Notify the library as being viewed.',
        'type'          => 'write',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_view_entry' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'view_entry',
        'description'   => 'Notify a library entry as being viewed.',
        'type'          => 'write',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_letter' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_letter',
        'description'   => 'Browse entries by letter.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_date' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_date',
        'description'   => 'Browse entries by date.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_categories' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_categories',
        'description'   => 'Get the categories.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_category' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_category',
        'description'   => 'Browse entries by category.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_authors' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_authors',
        'description'   => 'Get the authors.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_author' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_author',
        'description'   => 'Browse entries by author.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_author_id' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_author_id',
        'description'   => 'Browse entries by author ID.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_search' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_search',
        'description'   => 'Browse entries by search query.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_by_term' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_by_term',
        'description'   => 'Browse entries by term (concept or alias).',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entries_to_approve' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entries_to_approve',
        'description'   => 'Browse entries to be approved.',
        'type'          => 'read',
        'capabilities'  => 'mod/library:approve',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_get_entry_by_id' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'get_entry_by_id',
        'description'   => 'Get an entry by ID',
        'type'          => 'read',
        'capabilities'  => 'mod/library:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_library_add_entry' => array(
        'classname'     => 'mod_library_external',
        'methodname'    => 'add_entry',
        'description'   => 'Add a new entry to a given library',
        'type'          => 'write',
        'capabilities'  => 'mod/library:write',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

);
