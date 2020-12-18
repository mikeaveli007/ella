<?php

class block_cocoon_features_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
      global $CFG;
      $ccnFontList = include($CFG->dirroot . '/theme/edumy/ccn/font_handler/ccn_font_select.php');

        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // Title
        $mform->addElement('text', 'config_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_title', 'Dove Kindergarten');
        $mform->setType('config_title', PARAM_RAW);

        // Subtitle
        $mform->addElement('text', 'config_subtitle', get_string('config_subtitle', 'theme_edumy'));
        $mform->setDefault('config_subtitle', 'Cum doctus civibus efficiantur in imperdiet deterruisset.');
        $mform->setType('config_subtitle', PARAM_RAW);

          // $radioarray=array();
          // $radioarray[] = $mform->createElement('radio', 'config_style', '', 'Align left', 0, $attributes);
          // $radioarray[] = $mform->createElement('radio', 'config_style', '', 'Align center', 1, $attributes);
          // $mform->addGroup($radioarray, 'config_style', 'Style', array(' '), false);

        $options = array(
            0 => 'Align left',
            1 => 'Align center',
        );
        $select = $mform->addElement('select', 'config_style', get_string('config_style', 'theme_edumy'), $options, array('class'=>'ccnCommLcRef_change'));
        $select->setSelected('0');

        // $options = array(
        //     '0' => 'Icons',
        //     '1' => 'Images',
        // );
        // $select = $mform->addElement('select', 'config_ccn_iori', get_string('config_ccn_iori', 'theme_edumy'), $options, array('class'=>'ccnCommLcRef_change'));
        // $select->setSelected('0');


        // Counter 1
        $mform->addElement('header', 'config_feature_1_header', 'Feature 1');

        $mform->addElement('text', 'config_feature_1_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_feature_1_title', 'Learn From The Experts');
        $mform->setType('config_feature_1_title', PARAM_RAW);

        // $mform->addElement('text', 'config_feature_1_icon', get_string('config_icon_class', 'theme_edumy'));
        // $mform->setDefault('config_feature_1_icon', 'flaticon-student');
        // $mform->setType('config_feature_1_icon', PARAM_RAW);

        $select = $mform->addElement('select', 'config_feature_1_icon', get_string('config_icon_class', 'theme_edumy'), $ccnFontList, array('class'=>'ccn_icon_class'));
        $select->setSelected('flaticon-student');

        // Counter 2
        $mform->addElement('header', 'config_feature_2_header', 'Feature 2');

        $mform->addElement('text', 'config_feature_2_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_feature_2_title', 'Book Library & Store');
        $mform->setType('config_feature_2_title', PARAM_RAW);

        // $mform->addElement('text', 'config_feature_2_icon', get_string('config_icon_class', 'theme_edumy'));
        // $mform->setDefault('config_feature_2_icon', 'flaticon-book');
        // $mform->setType('config_feature_2_icon', PARAM_RAW);

        $select = $mform->addElement('select', 'config_feature_2_icon', get_string('config_icon_class', 'theme_edumy'), $ccnFontList, array('class'=>'ccn_icon_class'));
        $select->setSelected('flaticon-book');

        // Counter 1
        $mform->addElement('header', 'config_feature_3_header', 'Feature 3');

        $mform->addElement('text', 'config_feature_3_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_feature_3_title', 'Worldwide Recognize');
        $mform->setType('config_feature_3_title', PARAM_RAW);

        // $mform->addElement('text', 'config_feature_3_icon', get_string('config_icon_class', 'theme_edumy'));
        // $mform->setDefault('config_feature_3_icon', 'flaticon-global');
        // $mform->setType('config_feature_3_icon', PARAM_RAW);

        $select = $mform->addElement('select', 'config_feature_3_icon', get_string('config_icon_class', 'theme_edumy'), $ccnFontList, array('class'=>'ccn_icon_class'));
        $select->setSelected('flaticon-global');

        // Counter 1
        $mform->addElement('header', 'config_feature_4_header', 'Feature 4');

        $mform->addElement('text', 'config_feature_4_title', get_string('config_title', 'theme_edumy'));
        $mform->setDefault('config_feature_4_title', 'Best Industry Leaders');
        $mform->setType('config_feature_4_title', PARAM_RAW);

        // $mform->addElement('text', 'config_feature_4_icon', get_string('config_icon_class', 'theme_edumy'));
        // $mform->setDefault('config_feature_4_icon', 'flaticon-first');
        // $mform->setType('config_feature_4_icon', PARAM_RAW);

        $select = $mform->addElement('select', 'config_feature_4_icon', get_string('config_icon_class', 'theme_edumy'), $ccnFontList, array('class'=>'ccn_icon_class'));
        $select->setSelected('flaticon-first');

        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/edit.php');

    }

    function set_data($defaults)
    {

        // Begin CCN Image Processing
        if (empty($entry->id)) {
            $entry = new stdClass;
            $entry->id = null;
        }
        $draftitemid = file_get_submitted_draft_itemid('config_image');
        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_cocoon_features', 'content', 0,
            array('subdirs' => true));
        $entry->attachments = $draftitemid;
        parent::set_data($defaults);
        if ($data = parent::get_data()) {
            file_save_draft_area_files($data->config_image, $this->block->context->id, 'block_cocoon_features', 'content', 0,
                array('subdirs' => true));
        }
        // END CCN Image Processing



        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->bio;
            $draftid_editor = file_get_submitted_draft_itemid('config_bio');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_bio['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_cocoon_features', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_bio['itemid'] = $draftid_editor;
            $defaults->config_bio['format'] = $this->block->config->format;
        } else {
            $text = '';
        }


    }
}
