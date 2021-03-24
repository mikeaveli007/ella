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
 * @package mod_library
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/library/backup/moodle2/restore_library_stepslib.php'); // Because it exists (must)

/**
 * library restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_library_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_library_activity_structure_step('library_structure', 'library.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('library', array('intro'), 'library');
        $contents[] = new restore_decode_content('library_entries', array('definition'), 'library_entry');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('LIBRARYVIEWBYID', '/mod/library/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LIBRARYINDEX', '/mod/library/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('LIBRARYSHOWENTRY', '/mod/library/showentry.php?courseid=$1&eid=$2',
                                           array('course', 'library_entry'));

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * library logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('library', 'add', 'view.php?id={course_module}', '{library}');
        $rules[] = new restore_log_rule('library', 'update', 'view.php?id={course_module}', '{library}');
        $rules[] = new restore_log_rule('library', 'view', 'view.php?id={course_module}', '{library}');
        $rules[] = new restore_log_rule('library', 'add category', 'editcategories.php?id={course_module}', '{library_category}');
        $rules[] = new restore_log_rule('library', 'edit category', 'editcategories.php?id={course_module}', '{library_category}');
        $rules[] = new restore_log_rule('library', 'delete category', 'editcategories.php?id={course_module}', '{library_category}');
        $rules[] = new restore_log_rule('library', 'add entry', 'view.php?id={course_module}&mode=entry&hook={library_entry}', '{library_entry}');
        $rules[] = new restore_log_rule('library', 'update entry', 'view.php?id={course_module}&mode=entry&hook={library_entry}', '{library_entry}');
        $rules[] = new restore_log_rule('library', 'delete entry', 'view.php?id={course_module}&mode=entry&hook={library_entry}', '{library_entry}');
        $rules[] = new restore_log_rule('library', 'approve entry', 'showentry.php?id={course_module}&eid={library_entry}', '{library_entry}');
        $rules[] = new restore_log_rule('library', 'disapprove entry', 'showentry.php?id={course_module}&eid={library_entry}', '{library_entry}');
        $rules[] = new restore_log_rule('library', 'view entry', 'showentry.php?eid={library_entry}', '{library_entry}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('library', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
