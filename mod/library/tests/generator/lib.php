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
 * mod_library data generator.
 *
 * @package    mod_library
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mod_library data generator class.
 *
 * @package    mod_library
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_library_generator extends testing_module_generator {

    /**
     * @var int keep track of how many entries have been created.
     */
    protected $entrycount = 0;

    /**
     * @var int keep track of how many entries have been created.
     */
    protected $categorycount = 0;

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->entrycount = 0;
        $this->categorycount = 0;
        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
        global $CFG;

        // Add default values for library.
        $record = (array)$record + array(
            'globallibrary' => 0,
            'mainlibrary' => 0,
            'defaultapproval' => $CFG->library_defaultapproval,
            'allowduplicatedentries' => $CFG->library_dupentries,
            'allowcomments' => $CFG->library_allowcomments,
            'usedynalink' => $CFG->library_linkbydefault,
            'displayformat' => 'dictionary',
            'approvaldisplayformat' => 'default',
            'entbypage' => !empty($CFG->library_entbypage) ? $CFG->library_entbypage : 10,
            'showalphabet' => 1,
            'showall' => 1,
            'showspecial' => 1,
            'allowprintview' => 1,
            'rsstype' => 0,
            'rssarticles' => 0,
            'grade' => 100,
            'assessed' => 0,
        );

        return parent::create_instance($record, (array)$options);
    }

    public function create_category($library, $record = array(), $entries = array()) {
        global $CFG, $DB;
        $this->categorycount++;
        $record = (array)$record + array(
            'name' => 'Library category '.$this->categorycount,
            'usedynalink' => $CFG->library_linkbydefault,
        );
        $record['libraryid'] = $library->id;

        $id = $DB->insert_record('library_categories', $record);

        if ($entries) {
            foreach ($entries as $entry) {
                $ce = new stdClass();
                $ce->categoryid = $id;
                $ce->entryid = $entry->id;
                $DB->insert_record('library_entries_categories', $ce);
            }
        }

        return $DB->get_record('library_categories', array('id' => $id), '*', MUST_EXIST);
    }

    public function create_content($library, $record = array(), $aliases = array()) {
        global $DB, $USER, $CFG;
        $this->entrycount++;
        $now = time();
        $record = (array)$record + array(
            'libraryid' => $library->id,
            'timecreated' => $now,
            'timemodified' => $now,
            'userid' => $USER->id,
            'concept' => 'Library entry '.$this->entrycount,
            'definition' => 'Definition of library entry '.$this->entrycount,
            'definitionformat' => FORMAT_MOODLE,
            'definitiontrust' => 0,
            'usedynalink' => $CFG->library_linkentries,
            'casesensitive' => $CFG->library_casesensitive,
            'fullmatch' => $CFG->library_fullmatch
        );
        if (!isset($record['teacherentry']) || !isset($record['approved'])) {
            $context = context_module::instance($library->cmid);
            if (!isset($record['teacherentry'])) {
                $record['teacherentry'] = has_capability('mod/library:manageentries', $context, $record['userid']);
            }
            if (!isset($record['approved'])) {
                $defaultapproval = $library->defaultapproval;
                $record['approved'] = ($defaultapproval || has_capability('mod/library:approve', $context));
            }
        }

        $id = $DB->insert_record('library_entries', $record);

        if ($aliases) {
            foreach ($aliases as $alias) {
                $ar = new stdClass();
                $ar->entryid = $id;
                $ar->alias = $alias;
                $DB->insert_record('library_alias', $ar);
            }
        }

        if (array_key_exists('tags', $record)) {
            $tags = is_array($record['tags']) ? $record['tags'] : preg_split('/,/', $record['tags']);

            core_tag_tag::set_item_tags('mod_library', 'library_entries', $id,
                context_module::instance($library->cmid), $tags);
        }

        return $DB->get_record('library_entries', array('id' => $id), '*', MUST_EXIST);
    }
}
