<?php
// This file is part of the Local Analytics plugin for Moodle
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
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package    local_analytics
 * @copyright  Bas Brands, Sonsbeekmedia 2017
 * @author     Bas Brands <bas@sonsbeekmedia.nl>, David Bezemer <info@davidbezemer.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use local_analytics\injector;

/**
 * Output callback, available since Moodle 3.3
 *
 */
function local_analytics_before_footer() {
    injector::inject();
}

/**
 * Output callback, available since Moodle 3.3
 *
 */
function local_analytics_before_http_headers() {
    injector::inject();
}

function get_entry($id) {
    global $DB;
    $entry = $DB->get_record('library_entries', ['id' => $id]);
    return $entry;
}

function get_entry_category($id) {
    global $DB;
    $cat = $DB->get_record('library_categories', ['id' => $id]);
    return $cat;
}

function get_user_cohort_name($id) {
    global $DB;

    return $DB->get_field_sql('SELECT c.name
                    FROM {cohort} c
                    JOIN {cohort_members} cm ON c.id = cm.cohortid
                    WHERE cm.userid=? 
                    AND c.name LIKE ?', array($id, "ochin-crowd-sa%"));    
}

function get_user_service_area($id) {
    $cohort = get_user_cohort_name($id);
    
    $sa = $cohort != null ? explode("-", $cohort)[2]: null;
    return $sa;
}

function consolelog($message) {
    echo '<script>';
    echo 'console.log('. json_encode($message, JSON_HEX_TAG) .')';
    echo '</script>';
}