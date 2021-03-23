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
 * mod_library generator tests
 *
 * @package    mod_library
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Genarator tests class for mod_library.
 *
 * @package    mod_library
 * @category   test
 * @copyright  2013 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_library_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('library', array('course' => $course->id)));
        $library = $this->getDataGenerator()->create_module('library', array('course' => $course));
        $records = $DB->get_records('library', array('course' => $course->id), 'id');
        $this->assertCount(1, $records);
        $this->assertTrue(array_key_exists($library->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another library');
        $library = $this->getDataGenerator()->create_module('library', $params);
        $records = $DB->get_records('library', array('course' => $course->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals('Another library', $records[$library->id]->name);
    }

    public function test_create_content() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $library = $this->getDataGenerator()->create_module('library', array('course' => $course));
        /** @var mod_library_generator $librarygenerator */
        $librarygenerator = $this->getDataGenerator()->get_plugin_generator('mod_library');

        $entry1 = $librarygenerator->create_content($library);
        $entry2 = $librarygenerator->create_content($library,
            array('concept' => 'Custom concept', 'tags' => array('Cats', 'mice')), array('alias1', 'alias2'));
        $records = $DB->get_records('library_entries', array('libraryid' => $library->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($entry1->id, $records[$entry1->id]->id);
        $this->assertEquals($entry2->id, $records[$entry2->id]->id);
        $this->assertEquals('Custom concept', $records[$entry2->id]->concept);
        $this->assertEquals(array('Cats', 'mice'),
            array_values(core_tag_tag::get_item_tags_array('mod_library', 'library_entries', $entry2->id)));
        $aliases = $DB->get_records_menu('library_alias', array('entryid' => $entry2->id), 'id ASC', 'id, alias');
        $this->assertSame(array('alias1', 'alias2'), array_values($aliases));

        // Test adding of category to entry.
        $categories = $DB->get_records('library_categories', array('libraryid' => $library->id));
        $this->assertCount(0, $categories);
        $entry3 = $librarygenerator->create_content($library, array('concept' => 'In category'));
        $category1 = $librarygenerator->create_category($library, array());
        $categories = $DB->get_records('library_categories', array('libraryid' => $library->id));
        $this->assertCount(1, $categories);
        $category2 = $librarygenerator->create_category($library, array('name' => 'Some category'), array($entry2, $entry3));
        $categories = $DB->get_records('library_categories', array('libraryid' => $library->id));
        $this->assertCount(2, $categories);
        $members = $DB->get_records_menu('library_entries_categories', array('categoryid' => $category2->id), 'id ASC', 'id, entryid');
        $this->assertSame(array($entry2->id, $entry3->id), array_values($members));
    }
}
