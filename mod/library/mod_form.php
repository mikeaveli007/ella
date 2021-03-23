<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_library_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $COURSE, $DB;

        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'headertitle', get_string('headertitle', 'library'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('headertitle', PARAM_TEXT);
        } else {
            $mform->setType('headertitle', PARAM_CLEANHTML);
        }

        $mform->addElement('text', 'headerdescription', get_string('headerdescription', 'library'), array('size'=>'80'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('headerdescription', PARAM_TEXT);
        } else {
            $mform->setType('headerdescription', PARAM_CLEANHTML);
        }

        $this->standard_intro_elements();

        if (has_capability('mod/library:manageentries', context_system::instance())) {
            $mform->addElement('checkbox', 'globallibrary', get_string('isglobal', 'library'));
            $mform->addHelpButton('globallibrary', 'isglobal', 'library');

        }else{
            $mform->addElement('hidden', 'globallibrary');
            $mform->setType('globallibrary', PARAM_INT);
        }

        $options = array(1=>get_string('mainlibrary', 'library'), 0=>get_string('secondarylibrary', 'library'));
        $mform->addElement('select', 'mainlibrary', get_string('librarytype', 'library'), $options);
        $mform->addHelpButton('mainlibrary', 'librarytype', 'library');
        $mform->setDefault('mainlibrary', 0);

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'entrieshdr', get_string('entries', 'library'));

        $mform->addElement('selectyesno', 'defaultapproval', get_string('defaultapproval', 'library'));
        $mform->setDefault('defaultapproval', $CFG->library_defaultapproval);
        $mform->addHelpButton('defaultapproval', 'defaultapproval', 'library');

        $mform->addElement('selectyesno', 'editalways', get_string('editalways', 'library'));
        $mform->setDefault('editalways', 0);
        $mform->addHelpButton('editalways', 'editalways', 'library');

        $mform->addElement('selectyesno', 'allowduplicatedentries', get_string('allowduplicatedentries', 'library'));
        $mform->setDefault('allowduplicatedentries', $CFG->library_dupentries);
        $mform->addHelpButton('allowduplicatedentries', 'allowduplicatedentries', 'library');

        $mform->addElement('selectyesno', 'allowcomments', get_string('allowcomments', 'library'));
        $mform->setDefault('allowcomments', $CFG->library_allowcomments);
        $mform->addHelpButton('allowcomments', 'allowcomments', 'library');

        $mform->addElement('selectyesno', 'usedynalink', get_string('usedynalink', 'library'));
        $mform->setDefault('usedynalink', $CFG->library_linkbydefault);
        $mform->addHelpButton('usedynalink', 'usedynalink', 'library');

        // ----------------------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        // Get and update available formats.
        $recformats = library_get_available_formats();
        $formats = array();
        foreach ($recformats as $format) {
           $formats[$format->name] = get_string('displayformat'.$format->name, 'library');
        }
        asort($formats);
        $mform->addElement('select', 'displayformat', get_string('displayformat', 'library'), $formats);
        $mform->setDefault('displayformat', 'dictionary');
        $mform->addHelpButton('displayformat', 'displayformat', 'library');

        $displayformats['default'] = get_string('displayformatdefault', 'library');
        $displayformats = array_merge($displayformats, $formats);
        $mform->addElement('select', 'approvaldisplayformat', get_string('approvaldisplayformat', 'library'), $displayformats);
        $mform->setDefault('approvaldisplayformat', 'default');
        $mform->addHelpButton('approvaldisplayformat', 'approvaldisplayformat', 'library');

        $mform->addElement('text', 'entbypage', get_string('entbypage', 'library'));
        $mform->setDefault('entbypage', $this->get_default_entbypage());
        $mform->addRule('entbypage', null, 'numeric', null, 'client');
        $mform->setType('entbypage', PARAM_INT);

        $mform->addElement('selectyesno', 'showalphabet', get_string('showalphabet', 'library'));
        $mform->setDefault('showalphabet', 1);
        $mform->addHelpButton('showalphabet', 'showalphabet', 'library');

        $mform->addElement('selectyesno', 'showall', get_string('showall', 'library'));
        $mform->setDefault('showall', 1);
        $mform->addHelpButton('showall', 'showall', 'library');

        $mform->addElement('selectyesno', 'showspecial', get_string('showspecial', 'library'));
        $mform->setDefault('showspecial', 1);
        $mform->addHelpButton('showspecial', 'showspecial', 'library');

        $mform->addElement('selectyesno', 'allowprintview', get_string('allowprintview', 'library'));
        $mform->setDefault('allowprintview', 1);
        $mform->addHelpButton('allowprintview', 'allowprintview', 'library');

        if ($CFG->enablerssfeeds && isset($CFG->library_enablerssfeeds) && $CFG->library_enablerssfeeds) {
//-------------------------------------------------------------------------------
            $mform->addElement('header', 'rssheader', get_string('rss'));
            $choices = array();
            $choices[0] = get_string('none');
            $choices[1] = get_string('withauthor', 'library');
            $choices[2] = get_string('withoutauthor', 'library');
            $mform->addElement('select', 'rsstype', get_string('rsstype'), $choices);
            $mform->addHelpButton('rsstype', 'rsstype', 'library');

            $choices = array();
            $choices[0] = '0';
            $choices[1] = '1';
            $choices[2] = '2';
            $choices[3] = '3';
            $choices[4] = '4';
            $choices[5] = '5';
            $choices[10] = '10';
            $choices[15] = '15';
            $choices[20] = '20';
            $choices[25] = '25';
            $choices[30] = '30';
            $choices[40] = '40';
            $choices[50] = '50';
            $mform->addElement('select', 'rssarticles', get_string('rssarticles'), $choices);
            $mform->addHelpButton('rssarticles', 'rssarticles', 'library');
            $mform->hideIf('rssarticles', 'rsstype', 'eq', 0);
        }

//-------------------------------------------------------------------------------

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }

    function definition_after_data() {
        global $COURSE, $DB;

        parent::definition_after_data();
        $mform    =& $this->_form;
        $mainlibraryel =& $mform->getElement('mainlibrary');
        $mainlibrary = $DB->get_record('library', array('mainlibrary'=>1, 'course'=>$COURSE->id));
        if ($mainlibrary && ($mainlibrary->id != $mform->getElementValue('instance'))){
            //secondary library, a main one already exists in this course.
            $mainlibraryel->setValue(0);
            $mainlibraryel->freeze();
            $mainlibraryel->setPersistantFreeze(true);
        } else {
            $mainlibraryel->unfreeze();
            $mainlibraryel->setPersistantFreeze(false);

        }
    }

    function data_preprocessing(&$default_values){
        parent::data_preprocessing($default_values);

        // Fallsback on the default setting if 'Entries shown per page' has been left blank.
        // This prevents the field from being required and expand its section which should not
        // be the case if there is a default value defined.
        if (empty($default_values['entbypage']) || $default_values['entbypage'] < 0) {
            $default_values['entbypage'] = $this->get_default_entbypage();
        }

        // Set up the completion checkboxes which aren't part of standard data.
        // Tick by default if Add mode or if completion entries settings is set to 1 or more.
        if (empty($this->_instance) || !empty($default_values['completionentries'])) {
            $default_values['completionentriesenabled'] = 1;
        } else {
            $default_values['completionentriesenabled'] = 0;
        }
        if (empty($default_values['completionentries'])) {
            $default_values['completionentries']=1;
        }
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completionentriesenabled', '', get_string('completionentries','library'));
        $group[] =& $mform->createElement('text', 'completionentries', '', array('size'=>3));
        $mform->setType('completionentries', PARAM_INT);
        $mform->addGroup($group, 'completionentriesgroup', get_string('completionentriesgroup','library'), array(' '), false);
        $mform->disabledIf('completionentries','completionentriesenabled','notchecked');

        return array('completionentriesgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completionentriesenabled']) && $data['completionentries']!=0);
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked
            $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionentriesenabled) || !$autocompletion) {
                $data->completionentries = 0;
            }
        }
    }

    /**
     * Returns the default value for 'Entries shown per page'.
     *
     * @return int default for number of entries per page.
     */
    protected function get_default_entbypage() {
        global $CFG;
        return !empty($CFG->library_entbypage) ? $CFG->library_entbypage : 10;
    }

}

