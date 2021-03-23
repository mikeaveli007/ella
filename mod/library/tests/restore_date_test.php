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
 * Restore date tests.
 *
 * @package    mod_library
 * @copyright  2017 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . "/phpunit/classes/restore_date_testcase.php");
require_once($CFG->dirroot . '/rating/lib.php');

/**
 * Restore date tests.
 *
 * @package    mod_library
 * @copyright  2017 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_library_restore_date_testcase extends restore_date_testcase {

    /**
     * Test restore dates.
     */
    public function test_restore_dates() {
        global $DB, $USER;

        $gg = $this->getDataGenerator()->get_plugin_generator('mod_library');
        $record = ['assesstimefinish' => 100, 'assesstimestart' => 100, 'ratingtime' => 1, 'assessed' => 2, 'scale' => 1];
        list($course, $library) = $this->create_course_and_module('library', $record);

        // Library entries.
        $entry1 = $gg->create_content($library, array('approved' => 1));
        $gg->create_content($library, array('approved' => 0, 'userid' => $USER->id));
        $gg->create_content($library, array('approved' => 0, 'userid' => -1));
        $gg->create_content($library, array('approved' => 1));
        $timestamp = 10000;
        $DB->set_field('library_entries', 'timecreated', $timestamp);
        $DB->set_field('library_entries', 'timemodified', $timestamp);
        $ratingoptions = new stdClass;
        $ratingoptions->context = context_module::instance($library->cmid);
        $ratingoptions->ratingarea = 'entry';
        $ratingoptions->component = 'mod_library';
        $ratingoptions->itemid  = $entry1->id;
        $ratingoptions->scaleid = 2;
        $ratingoptions->userid  = $USER->id;
        $rating = new rating($ratingoptions);
        $rating->update_rating(2);
        $rating = $DB->get_record('rating', ['itemid' => $entry1->id]);

        // Do backup and restore.
        $newcourseid = $this->backup_and_restore($course);
        $newlibrary = $DB->get_record('library', ['course' => $newcourseid]);

        $this->assertFieldsNotRolledForward($library, $newlibrary, ['timecreated', 'timemodified']);
        $props = ['assesstimefinish', 'assesstimestart'];
        $this->assertFieldsRolledForward($library, $newlibrary, $props);

        $newentries = $DB->get_records('library_entries', ['libraryid' => $newlibrary->id]);
        $newcm = $DB->get_record('course_modules', ['course' => $newcourseid, 'instance' => $newlibrary->id]);

        // Entries test.
        foreach ($newentries as $entry) {
            $this->assertEquals($timestamp, $entry->timecreated);
            $this->assertEquals($timestamp, $entry->timemodified);
        }

        // Rating test.
        $newrating = $DB->get_record('rating', ['contextid' => context_module::instance($newcm->id)->id]);
        $this->assertEquals($rating->timecreated, $newrating->timecreated);
        $this->assertEquals($rating->timemodified, $newrating->timemodified);
    }
}
