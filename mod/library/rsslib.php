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
 * This file adds support to rss feeds generation
 *
 * @package mod_library
 * @category rss
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the path to the cached rss feed contents. Creates/updates the cache if necessary.
 *
 * @param stdClass $context the context
 * @param array    $args    the arguments received in the url
 * @return string the full path to the cached RSS feed directory. Null if there is a problem.
 */
    function library_rss_get_feed($context, $args) {
        global $CFG, $DB, $COURSE, $USER;

        $status = true;

        if (empty($CFG->library_enablerssfeeds)) {
            debugging("DISABLED (module configuration)");
            return null;
        }

        $libraryid  = clean_param($args[3], PARAM_INT);
        $cm = get_coursemodule_from_instance('library', $libraryid, 0, false, MUST_EXIST);
        $modcontext = context_module::instance($cm->id);

        if ($COURSE->id == $cm->course) {
            $course = $COURSE;
        } else {
            $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        }
        //context id from db should match the submitted one
        if ($context->id != $modcontext->id || !has_capability('mod/library:view', $modcontext)) {
            return null;
        }

        $library = $DB->get_record('library', array('id' => $libraryid), '*', MUST_EXIST);
        if (!rss_enabled_for_mod('library', $library)) {
            return null;
        }

        $sql = library_rss_get_sql($library);

        //get the cache file info
        $filename = rss_get_file_name($library, $sql);
        $cachedfilepath = rss_get_file_full_name('mod_library', $filename);

        //Is the cache out of date?
        $cachedfilelastmodified = 0;
        if (file_exists($cachedfilepath)) {
            $cachedfilelastmodified = filemtime($cachedfilepath);
        }
        //if the cache is more than 60 seconds old and there's new stuff
        $dontrecheckcutoff = time()-60;
        if ( $dontrecheckcutoff > $cachedfilelastmodified && library_rss_newstuff($library, $cachedfilelastmodified)) {
            if (!$recs = $DB->get_records_sql($sql, array(), 0, $library->rssarticles)) {
                return null;
            }

            $items = array();

            $formatoptions = new stdClass();
            $formatoptions->trusttext = true;

            foreach ($recs as $rec) {
                $item = new stdClass();
                $item->title = $rec->entryconcept;

                if ($library->rsstype == 1) {//With author
                    $item->author = fullname($rec);
                }

                $item->pubdate = $rec->entrytimecreated;
                $item->link = $CFG->wwwroot."/mod/library/showentry.php?courseid=".$library->course."&eid=".$rec->entryid;

                $definition = file_rewrite_pluginfile_urls($rec->entrydefinition, 'pluginfile.php',
                    $modcontext->id, 'mod_library', 'entry', $rec->entryid);
                $item->description = format_text($definition, $rec->entryformat, $formatoptions, $library->course);
                $items[] = $item;
            }

            //First all rss feeds common headers
            $header = rss_standard_header(format_string($library->name,true),
                                          $CFG->wwwroot."/mod/library/view.php?g=".$library->id,
                                          format_string($library->intro,true));
            //Now all the rss items
            if (!empty($header)) {
                $articles = rss_add_items($items);
            }
            //Now all rss feeds common footers
            if (!empty($header) && !empty($articles)) {
                $footer = rss_standard_footer();
            }
            //Now, if everything is ok, concatenate it
            if (!empty($header) && !empty($articles) && !empty($footer)) {
                $rss = $header.$articles.$footer;

                //Save the XML contents to file.
                $status = rss_save_file('mod_library', $filename, $rss);
            }
        }

        if (!$status) {
            $cachedfilepath = null;
        }

        return $cachedfilepath;
    }

    /**
     * The appropriate SQL query for the library items to go into the RSS feed
     *
     * @param stdClass $library the library object
     * @param int      $time     check for items since this epoch timestamp
     * @return string the SQL query to be used to get the entried from the library table of the database
     */
    function library_rss_get_sql($library, $time=0) {
        //do we only want new items?
        if ($time) {
            $time = "AND e.timecreated > $time";
        } else {
            $time = "";
        }

        if ($library->rsstype == 1) {//With author
            $allnamefields = get_all_user_name_fields(true,'u');
            $sql = "SELECT e.id AS entryid,
                      e.concept AS entryconcept,
                      e.definition AS entrydefinition,
                      e.definitionformat AS entryformat,
                      e.definitiontrust AS entrytrust,
                      e.timecreated AS entrytimecreated,
                      u.id AS userid,
                      $allnamefields
                 FROM {library_entries} e,
                      {user} u
                WHERE e.libraryid = {$library->id} AND
                      u.id = e.userid AND
                      e.approved = 1 $time
             ORDER BY e.timecreated desc";
        } else {//Without author
            $sql = "SELECT e.id AS entryid,
                      e.concept AS entryconcept,
                      e.definition AS entrydefinition,
                      e.definitionformat AS entryformat,
                      e.definitiontrust AS entrytrust,
                      e.timecreated AS entrytimecreated,
                      u.id AS userid
                 FROM {library_entries} e,
                      {user} u
                WHERE e.libraryid = {$library->id} AND
                      u.id = e.userid AND
                      e.approved = 1 $time
             ORDER BY e.timecreated desc";
        }

        return $sql;
    }

    /**
     * If there is new stuff in since $time this returns true
     * Otherwise it returns false.
     *
     * @param stdClass $library the library activity object
     * @param int      $time     epoch timestamp to compare new items against, 0 for everyting
     * @return bool true if there are new items
     */
    function library_rss_newstuff($library, $time) {
        global $DB;

        $sql = library_rss_get_sql($library, $time);

        $recs = $DB->get_records_sql($sql, null, 0, 1);//limit of 1. If we get even 1 back we have new stuff
        return ($recs && !empty($recs));
    }

    /**
      * Given a library object, deletes all cached RSS files associated with it.
      *
      * @param stdClass $library
      */
    function library_rss_delete_file($library) {
        global $CFG;
        require_once("$CFG->libdir/rsslib.php");

        rss_delete_file('mod_library', $library);
    }
