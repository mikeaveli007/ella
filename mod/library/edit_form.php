<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/lib/formslib.php');

class mod_library_entry_form extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $currententry      = $this->_customdata['current'];
        $library          = $this->_customdata['library'];
        $cm                = $this->_customdata['cm'];
        $definitionoptions = $this->_customdata['definitionoptions'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];

        $context  = context_module::instance($cm->id);
        // Prepare format_string/text options
        $fmtoptions = array(
            'context' => $context);

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'concept', get_string('concept', 'library'));
        $mform->setType('concept', PARAM_TEXT);
        $mform->addRule('concept', null, 'required', null, 'client');

        $mform->addElement('editor', 'definition_editor', get_string('definition', 'library'), null, $definitionoptions);
        $mform->setType('definition_editor', PARAM_RAW);
        $mform->addRule('definition_editor', get_string('required'), 'required', null, 'client');

        if ($categories = $DB->get_records_menu('library_categories', array('libraryid'=>$library->id), 'name ASC', 'id, name')){
            foreach ($categories as $id => $name) {
                $categories[$id] = format_string($name, true, $fmtoptions);
            }
            $categories = array(0 => get_string('notcategorised', 'library')) + $categories;
            $categoriesEl = $mform->addElement('select', 'categories', get_string('categories', 'library'), $categories);
            $categoriesEl->setMultiple(true);
            $categoriesEl->setSize(5);
        }

        if ($entrytypes = $DB->get_records_menu('library_entries_types', null, 'name ASC', 'id, name')){
            foreach ($entrytypes as $id => $name) {
                $entrytypes[$id] = format_string($name, true, $fmtoptions);
            }
            $entrytype = array(null => get_string('unassigned', 'library')) + $entrytypes;
            $entrytypeEl = $mform->addElement('select', 'typeid', get_string('entrytype', 'library'), $entrytype);
        }

        if ($epicvers = $DB->get_records_menu('library_epic_ver', null, 'name ASC', 'id, name')){
            foreach ($epicvers as $id => $name) {
                $epicvers[$id] = format_string($name, true, $fmtoptions);
            }
            $epicver = array(null => get_string('unassigned', 'library')) + $epicvers;
            $epicverEl = $mform->addElement('select', 'epicverid', get_string('epicver', 'library'), $epicver);
        }

        $mform->addElement('textarea', 'aliases', get_string('aliases', 'library'), 'rows="2" cols="40"');
        $mform->setType('aliases', PARAM_TEXT);
        $mform->addHelpButton('aliases', 'aliases', 'library');

        $mform->addElement('filemanager', 'attachment_filemanager', get_string('attachment', 'library'), null, $attachmentoptions);
        $mform->addHelpButton('attachment_filemanager', 'attachment', 'library');

        if (!$library->usedynalink) {
            $mform->addElement('hidden', 'usedynalink',   $CFG->library_linkentries);
            $mform->setType('usedynalink', PARAM_INT);
            $mform->addElement('hidden', 'casesensitive', $CFG->library_casesensitive);
            $mform->setType('casesensitive', PARAM_INT);
            $mform->addElement('hidden', 'fullmatch',     $CFG->library_fullmatch);
            $mform->setType('fullmatch', PARAM_INT);

        } else {
//-------------------------------------------------------------------------------
            $mform->addElement('header', 'linkinghdr', get_string('linking', 'library'));

            $mform->addElement('checkbox', 'usedynalink', get_string('entryusedynalink', 'library'));
            $mform->addHelpButton('usedynalink', 'entryusedynalink', 'library');
            $mform->setDefault('usedynalink', $CFG->library_linkentries);

            $mform->addElement('checkbox', 'casesensitive', get_string('casesensitive', 'library'));
            $mform->addHelpButton('casesensitive', 'casesensitive', 'library');
            $mform->hideIf('casesensitive', 'usedynalink');
            $mform->setDefault('casesensitive', $CFG->library_casesensitive);

            $mform->addElement('checkbox', 'fullmatch', get_string('fullmatch', 'library'));
            $mform->addHelpButton('fullmatch', 'fullmatch', 'library');
            $mform->hideIf('fullmatch', 'usedynalink');
            $mform->setDefault('fullmatch', $CFG->library_fullmatch);
        }

        if (core_tag_tag::is_enabled('mod_library', 'library_entries')) {
            $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));

            $mform->addElement('tags', 'tags', get_string('tags'),
                array('itemtype' => 'library_entries', 'component' => 'mod_library'));
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

//-------------------------------------------------------------------------------
        $this->add_action_buttons();

//-------------------------------------------------------------------------------
        $this->set_data($currententry);
    }

    function validation($data, $files) {
        global $CFG, $USER, $DB;
        $errors = parent::validation($data, $files);

        $library = $this->_customdata['library'];
        $cm       = $this->_customdata['cm'];
        $context  = context_module::instance($cm->id);

        $id = (int)$data['id'];
        $data['concept'] = trim($data['concept']);

        if ($id) {
            //We are updating an entry, so we compare current session user with
            //existing entry user to avoid some potential problems if secureforms=off
            //Perhaps too much security? Anyway thanks to skodak (Bug 1823)
            $old = $DB->get_record('library_entries', array('id'=>$id));
            $ineditperiod = ((time() - $old->timecreated <  $CFG->maxeditingtime) || $library->editalways);
            if ((!$ineditperiod || $USER->id != $old->userid) and !has_capability('mod/library:manageentries', $context)) {
                if ($USER->id != $old->userid) {
                    $errors['concept'] = get_string('errcannoteditothers', 'library');
                } elseif (!$ineditperiod) {
                    $errors['concept'] = get_string('erredittimeexpired', 'library');
                }
            }
            if (!$library->allowduplicatedentries) {
                if ($DB->record_exists_select('library_entries',
                        'libraryid = :libraryid AND LOWER(concept) = :concept AND id != :id', array(
                            'libraryid' => $library->id,
                            'concept'    => core_text::strtolower($data['concept']),
                            'id'         => $id))) {
                    $errors['concept'] = get_string('errconceptalreadyexists', 'library');
                }
            }

        } else {
            if (!$library->allowduplicatedentries) {
                if (library_concept_exists($library, $data['concept'])) {
                    $errors['concept'] = get_string('errconceptalreadyexists', 'library');
                }
            }
        }

        return $errors;
    }
}

