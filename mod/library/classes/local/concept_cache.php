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
 * Entry caching for library filter.
 *
 * @package    mod_library
 * @copyright  2014 Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_library\local;
defined('MOODLE_INTERNAL') || die();

/**
 * Concept caching for library filter.
 *
 * @package    mod_library
 * @copyright  2014 Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class concept_cache {
    /**
     * Event observer, do not call directly.
     * @param \core\event\course_module_updated $event
     */
    public static function cm_updated(\core\event\course_module_updated $event) {
        if ($event->other['modulename'] !== 'library') {
            return;
        }
        // We do not know what changed exactly, so let's reset everything that might be affected.
        concept_cache::reset_course_muc($event->courseid);
        concept_cache::reset_global_muc();
    }

    /**
     * Reset concept related caches.
     * @param bool $phpunitreset
     */
    public static function reset_caches($phpunitreset = false) {
        if ($phpunitreset) {
            return;
        }
        $cache = \cache::make('mod_library', 'concepts');
        $cache->purge();
    }

    /**
     * Reset the cache for course concepts.
     * @param int $courseid
     */
    public static function reset_course_muc($courseid) {
        if (empty($courseid)) {
            return;
        }
        $cache = \cache::make('mod_library', 'concepts');
        $cache->delete((int)$courseid);
    }

    /**
     * Reset the cache for global concepts.
     */
    public static function reset_global_muc() {
        $cache = \cache::make('mod_library', 'concepts');
        $cache->delete(0);
    }

    /**
     * Utility method to purge caches related to given library.
     * @param \stdClass $library
     */
    public static function reset_library($library) {
        if (!$library->usedynalink) {
            return;
        }
        self::reset_course_muc($library->course);
        if ($library->globallibrary) {
            self::reset_global_muc();
        }
    }

    /**
     * Fetch concepts for given libraries.
     * @param int[] $libraries
     * @return array
     */
    protected static function fetch_concepts(array $libraries) {
        global $DB;

        $librarylist = implode(',', $libraries);

        $sql = "SELECT id, libraryid, concept, casesensitive, 0 AS category, fullmatch
                  FROM {library_entries}
                 WHERE libraryid IN ($librarylist) AND usedynalink = 1 AND approved = 1

                 UNION

                SELECT id, libraryid, name AS concept, 1 AS casesensitive, 1 AS category, 1 AS fullmatch
                  FROM {library_categories}
                 WHERE libraryid IN ($librarylist) AND usedynalink = 1

                UNION

                SELECT ge.id, ge.libraryid, ga.alias AS concept, ge.casesensitive, 0 AS category, ge.fullmatch
                  FROM {library_alias} ga
                  JOIN {library_entries} ge ON (ga.entryid = ge.id)
                 WHERE ge.libraryid IN ($librarylist) AND ge.usedynalink = 1 AND ge.approved = 1";

        $concepts = array();
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $concept) {
            $currentconcept = trim(strip_tags($concept->concept));

            // Concept must be HTML-escaped, so do the same as format_string to turn ampersands into &amp;.
            $currentconcept = replace_ampersands_not_followed_by_entity($currentconcept);

            if (empty($currentconcept)) {
                continue;
            }

            // Rule out any small integers, see MDL-1446.
            if (is_number($currentconcept) and $currentconcept < 1000) {
                continue;
            }

            $concept->concept = $currentconcept;

            $concepts[$concept->libraryid][] = $concept;
        }
        $rs->close();

        return $concepts;
    }

    /**
     * Get all linked concepts from course.
     * @param int $courseid
     * @return array
     */
    protected static function get_course_concepts($courseid) {
        global $DB;

        if (empty($courseid)) {
            return array(array(), array());
        }

        $courseid = (int)$courseid;

        // Get info on any libraries in this course.
        $modinfo = get_fast_modinfo($courseid);
        $cminfos = $modinfo->get_instances_of('library');
        if (!$cminfos) {
            // No libraries in this course, so don't do any work.
            return array(array(), array());
        }

        $cache = \cache::make('mod_library', 'concepts');
        $data = $cache->get($courseid);
        if (is_array($data)) {
            list($libraries, $allconcepts) = $data;

        } else {
            // Find all course libraries.
            $sql = "SELECT g.id, g.name
                      FROM {library} g
                      JOIN {course_modules} cm ON (cm.instance = g.id)
                      JOIN {modules} m ON (m.name = 'library' AND m.id = cm.module)
                     WHERE g.usedynalink = 1 AND g.course = :course AND cm.visible = 1 AND m.visible = 1
                  ORDER BY g.globallibrary, g.id";
            $libraries = $DB->get_records_sql_menu($sql, array('course' => $courseid));
            if (!$libraries) {
                $data = array(array(), array());
                $cache->set($courseid, $data);
                return $data;
            }
            foreach ($libraries as $id => $name) {
                $name = str_replace(':', '-', $name);
                $libraries[$id] = replace_ampersands_not_followed_by_entity(strip_tags($name));
            }

            $allconcepts = self::fetch_concepts(array_keys($libraries));
            foreach ($libraries as $gid => $unused) {
                if (!isset($allconcepts[$gid])) {
                    unset($libraries[$gid]);
                }
            }
            if (!$libraries) {
                // This means there are no interesting concepts in the existing libraries.
                $data = array(array(), array());
                $cache->set($courseid, $data);
                return $data;
            }
            $cache->set($courseid, array($libraries, $allconcepts));
        }

        $concepts = $allconcepts;

        // Verify access control to library instances.
        foreach ($concepts as $modid => $unused) {
            if (!isset($cminfos[$modid])) {
                // This should not happen.
                unset($concepts[$modid]);
                unset($libraries[$modid]);
                continue;
            }
            if (!$cminfos[$modid]->uservisible) {
                unset($concepts[$modid]);
                unset($libraries[$modid]);
                continue;
            }
        }

        return array($libraries, $concepts);
    }

    /**
     * Get all linked global concepts.
     * @return array
     */
    protected static function get_global_concepts() {
        global $DB;

        $cache = \cache::make('mod_library', 'concepts');
        $data = $cache->get(0);
        if (is_array($data)) {
            list($libraries, $allconcepts) = $data;

        } else {
            // Find all global libraries - no access control here.
            $sql = "SELECT g.id, g.name
                      FROM {library} g
                      JOIN {course_modules} cm ON (cm.instance = g.id)
                      JOIN {modules} m ON (m.name = 'library' AND m.id = cm.module)
                     WHERE g.usedynalink = 1 AND g.globallibrary = 1 AND cm.visible = 1 AND m.visible = 1
                  ORDER BY g.globallibrary, g.id";
            $libraries = $DB->get_records_sql_menu($sql);
            if (!$libraries) {
                $data = array(array(), array());
                $cache->set(0, $data);
                return $data;
            }
            foreach ($libraries as $id => $name) {
                $name = str_replace(':', '-', $name);
                $libraries[$id] = replace_ampersands_not_followed_by_entity(strip_tags($name));
            }
            $allconcepts = self::fetch_concepts(array_keys($libraries));
            foreach ($libraries as $gid => $unused) {
                if (!isset($allconcepts[$gid])) {
                    unset($libraries[$gid]);
                }
            }
            $cache->set(0, array($libraries, $allconcepts));
        }

        // NOTE: no access control is here because it would be way too expensive to check access
        //       to all courses that contain the global libraries.
        return array($libraries, $allconcepts);
    }

    /**
     * Get all concepts that should be linked in the given course.
     * @param int $courseid
     * @return array with two elements - array of libraries and concepts for each library
     */
    public static function get_concepts($courseid) {
        list($libraries, $concepts) = self::get_course_concepts($courseid);
        list($globallibraries, $globalconcepts) = self::get_global_concepts();

        foreach ($globalconcepts as $gid => $cs) {
            if (!isset($concepts[$gid])) {
                $concepts[$gid] = $cs;
            }
        }
        foreach ($globallibraries as $gid => $name) {
            if (!isset($libraries[$gid])) {
                $libraries[$gid] = $name;
            }
        }

        return array($libraries, $concepts);
    }
}
