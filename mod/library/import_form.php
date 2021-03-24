<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

class mod_library_import_form extends moodleform {

    function definition() {
        global $CFG;
        $mform =& $this->_form;
        $cmid = $this->_customdata['id'] ?? null;

        $mform->addElement('filepicker', 'file', get_string('filetoimport', 'library'));
        $mform->addHelpButton('file', 'filetoimport', 'library');
        $options = array();
        $options['current'] = get_string('currentlibrary', 'library');
        $options['newlibrary'] = get_string('newlibrary', 'library');
        $mform->addElement('select', 'dest', get_string('destination', 'library'), $options);
        $mform->addHelpButton('dest', 'destination', 'library');
        $mform->addElement('checkbox', 'catsincl', get_string('importcategories', 'library'));
        $submit_string = get_string('submit');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(false, $submit_string);
    }
}
