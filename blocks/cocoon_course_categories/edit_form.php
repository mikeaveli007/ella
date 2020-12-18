<?php

class block_cocoon_course_categories_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
      global $CFG;

      if (!empty($this->block->config) && is_object($this->block->config)) {
          $data = $this->block->config;
      } else {
          $data = new stdClass();
          $data->items = 8;
      }

        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // Title
        $mform->addElement('text', 'config_title', get_string('config_title', 'block_cocoon_course_categories'));
        $mform->setDefault('config_title', 'Via School Categories Courses');
        $mform->setType('config_title', PARAM_RAW);

        // Subtitle
        $mform->addElement('text', 'config_subtitle', get_string('config_subtitle', 'block_cocoon_course_categories'));
        $mform->setDefault('config_subtitle', 'Cum doctus civibus efficiantur in imperdiet deterruisset.');
        $mform->setType('config_subtitle', PARAM_RAW);

        // // Button Link
        // $mform->addElement('text', 'config_button_link', get_string('config_arrow_link', 'theme_edumy'));
        // $mform->setDefault('config_button_link', '#our-courses');
        // $mform->setType('config_button_link', PARAM_RAW);

        // Button Text
        $mform->addElement('text', 'config_button_text', get_string('config_button_text', 'theme_edumy'));
        $mform->setDefault('config_button_text', 'View All Courses');
        $mform->setType('config_button_text', PARAM_RAW);

        $options = array(
            '0' => 'Category description',
            '1' => 'Category course count',
            '2' => 'None',
        );
        $select = $mform->addElement('select', 'config_body', get_string('config_body', 'theme_edumy'), $options);
        $select->setSelected('0');

        $items_range = array(
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
          7 => '7',
          8 => '8',
          9 => '9',
          10 => '10',
          11 => '11',
          12 => '12',
          13 => '13',
          14 => '14',
          15 => '15',
          16 => '16',
        );
        $mform->addElement('select', 'config_items', get_string('config_items', 'theme_edumy'), $items_range);
        $mform->setDefault('config_items', 8);

        // Style
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'config_style', '', 'Hide arrow', 0, $attributes);
        $radioarray[] = $mform->createElement('radio', 'config_style', '', 'Show arrow', 1, $attributes);
        $mform->addGroup($radioarray, 'config_style', 'Arrow', array(' '), false);

        $mform->addElement('header', 'config_ccn_colors', get_string('block_styles', 'theme_edumy'));

        $mform->addElement('text', 'config_color_bg', get_string('config_color_bg', 'theme_edumy'), array('class'=>'ccn_spectrum_class'));
        $mform->setDefault('config_color_bg', 'rgb(255,255,255)');
        $mform->setType('config_color_bg', PARAM_TEXT);

        $mform->addElement('text', 'config_color_title', get_string('config_color_title', 'theme_edumy'), array('class'=>'ccn_spectrum_class'));
        $mform->setDefault('config_color_title', '#0a0a0a');
        $mform->setType('config_color_title', PARAM_TEXT);

        $mform->addElement('text', 'config_color_subtitle', get_string('config_color_subtitle', 'theme_edumy'), array('class'=>'ccn_spectrum_class'));
        $mform->setDefault('config_color_subtitle', '#6f7074');
        $mform->setType('config_color_subtitle', PARAM_TEXT);

        $mform->addElement('text', 'config_color_overlay', get_string('config_color_overlay', 'theme_edumy'), array('class'=>'ccn_spectrum_class'));
        $mform->setDefault('config_color_overlay', 'rgba(10,10,10,.5)');
        $mform->setType('config_color_overlay', PARAM_TEXT);

        $mform->addElement('text', 'config_color_hover', get_string('config_color_hover', 'theme_edumy'), array('class'=>'ccn_spectrum_class'));
        $mform->setDefault('config_color_hover', '#2441e7');
        $mform->setType('config_color_hover', PARAM_TEXT);

        $mform->addElement('text', 'config_color_btn', get_string('config_color_btn', 'theme_edumy'), array('class'=>'ccn_spectrum_class'));
        $mform->setDefault('config_color_btn', '#2441e7');
        $mform->setType('config_color_btn', PARAM_TEXT);

        $mform->addElement('text', 'config_button_bdrrd', get_string('config_button', 'theme_edumy') . get_string('border_radius', 'theme_edumy') , array('class'=>'ccn_range_class'));
        $mform->setDefault('config_button_bdrrd', '50');
        $mform->setType('config_button_bdrrd', PARAM_TEXT);


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
        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_cocoon_course_categories', 'content', 0,
            array('subdirs' => true));
        $entry->attachments = $draftitemid;
        parent::set_data($defaults);
        if ($data = parent::get_data()) {
            file_save_draft_area_files($data->config_image, $this->block->context->id, 'block_cocoon_course_categories', 'content', 0,
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
            $defaults->config_bio['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_cocoon_course_categories', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_bio['itemid'] = $draftid_editor;
            $defaults->config_bio['format'] = $this->block->config->format;
        } else {
            $text = '';
        }


    }
}
