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
require_once($CFG->dirroot. '/theme/edumy/ccn/block_handler/ccn_block_handler.php');
class block_cocoon_tabs extends block_base {

    /**
     * Start block instance.
     */
    function init() {
        $this->title = get_string('pluginname', 'block_cocoon_tabs');
    }

    /**
     * The block is usable in all pages
     */
     function applicable_formats() {
       $ccnBlockHandler = new ccnBlockHandler();
       return $ccnBlockHandler->ccnGetBlockApplicability(array('all'));
     }


    /**
     * Customize the block title dynamically.
     */
    function specialization() {
        global $CFG, $DB;
        include($CFG->dirroot . '/theme/edumy/ccn/block_handler/specialization.php');
        if (empty($this->config)) {
          $this->config->slidesnumber = '3';
          $this->config->title = 'Frequently Asked Questions';
          $this->config->title1 = 'Education';
          $this->config->title2 = 'Training';
          $this->config->title3 = 'Academia';
          $this->config->text1['text'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.';
          $this->config->text2['text'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.';
          $this->config->text3['text'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.';
        }

    }

    /**
     * The block can be used repeatedly in a page.
     */
    function instance_allow_multiple() {
        return true;
    }

    /**
     * Build the block content.
     */
    function get_content() {
        global $CFG, $PAGE;

        require_once($CFG->libdir . '/filelib.php');


        if ($this->content !== NULL) {
            return $this->content;
        }


        if (!empty($this->config) && is_object($this->config)) {
            $this->content = new \stdClass();
            if(!empty($this->config->title)){$this->content->title = $this->config->title;}
            $data = $this->config;
            $data->slidesnumber = is_numeric($data->slidesnumber) ? (int)$data->slidesnumber : 3;
        } else {
            $data = new stdClass();
            $data->slidesnumber = '3';
        }

        $text = '';

        if ($data->slidesnumber > 0) {
            $text .= '
            <div class="shortcode_widget_tab">';
              if(!empty($this->config->title)){
                $text .='  <h4 data-ccn="title">'.$this->content->title.'</h4>';
              }
            $text .='
                <div class="ui_kit_tab mt30">
                  <ul class="nav nav-tabs" id="myTab" role="tablist">';
                  for ($i = 1; $i <= $data->slidesnumber; $i++) {
                    $ccnTabTitle = 'title' . $i;
                    $ccnTabLink = 'ccnTab-' . $i;
                    $ccnAriaSelected = 'false';
                    $ccnClass = 'nav-link';
                    if($i == 1){
                      $ccnAriaSelected = 'true';
                      $ccnClass .= ' active';
                    }
                    $text .= '<li class="nav-item">
                                <a data-ccn="'.$ccnTabTitle.'" class="'.$ccnClass.'" id="'.$ccnTabLink.'-tab" data-toggle="tab" href="#'.$ccnTabLink.'" role="tab" aria-controls="'.$ccnTabLink.'" aria-selected="true">'.$data->$ccnTabTitle.'</a>
                              </li>';
                  }
                 $text .='
                    </ul>
                    <div class="tab-content" id="myTabContent">';
                    for ($i = 1; $i <= $data->slidesnumber; $i++) {
                      $ccnTabBody = 'text' . $i;
                      $ccnTabLink = 'ccnTab-' . $i;
                      $ccnBodyClass = 'tab-pane fade';
                      if($i == 1){
                        $ccnBodyClass .= ' show active';
                      }
                      $text .='<div data-ccn="'.$ccnTabBody.'" class="'.$ccnBodyClass.'" id="'.$ccnTabLink.'" role="tabpanel" aria-labelledby="'.$ccnTabLink.'-tab">'.$data->$ccnTabBody['text'].'</div>';
                    }
              $text .='
                  </div>
                </div>
              </div>';





            // <h4 class="fz20 mb30">'.format_text($data->title, FORMAT_HTML, array('filter' => true)).'</h4>
            // <div class="faq_according ccn-faq_according mb25">
            //   <div id="accordion" class="panel-group">';
            //   $fs = get_file_storage();
            //   for ($i = 1; $i <= $data->slidesnumber; $i++) {
            //     $faq_title = 'faq_title' . $i;
            //     $faq_subtitle = 'faq_subtitle' . $i;
            //     $faq_body = 'faq_body' . $i;
            //     $faq_body_html = 'faq_html' . $i;
            //     $faq_body_type = 'body_type' . $i;
            //     $text .= '
            //     <div class="panel">
            //       <div class="panel-heading">
            //         <h4 class="panel-title">
            //           <a href="#panel-'. $this->context->id . $i.'" class="accordion-toggle link fz20 mb15" data-toggle="collapse" data-parent="#accordion">'.format_text($data->$faq_title, FORMAT_HTML, array('filter' => true)).'</a>
            //         </h4>
            //       </div>
            //       <div id="panel-'.$this->context->id . $i.'" class="panel-collapse collapse">
            //         <div class="panel-body">
            //           <h4>'.format_text($data->$faq_subtitle, FORMAT_HTML, array('filter' => true)).'</h4>';
            //           if($data->$faq_body_type == '1'){
            //             $text .= format_text($data->$faq_body_html['text'], FORMAT_HTML, array('filter' => true));
            //           } else {
            //             $text .='<p>'.format_text($data->$faq_body, FORMAT_HTML, array('filter' => true)).'</p>';
            //           }
            //           $text .='
            //         </div>
            //       </div>
            //     </div>';
            //   }
            //   $text .= '
            //   </div>
            // </div>';
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = $text;

        return $this->content;

  }


    /**
     * Serialize and store config data
     */
    // function instance_config_save($data, $nolongerused = false) {
    //     global $CFG;
    //
    //     $filemanageroptions = array('maxbytes'      => $CFG->maxbytes,
    //                                 'subdirs'       => 0,
    //                                 'maxfiles'      => 1,
    //                                 'accepted_types' => array('.jpg', '.png', '.gif'));
    //
    //     for($i = 1; $i <= $data->slidesnumber; $i++) {
    //         $field = 'file_slide' . $i;
    //         if (!isset($data->$field)) {
    //             continue;
    //         }
    //
    //         file_save_draft_area_files($data->$field, $this->context->id, 'block_cocoon_tabs', 'slides', $i, $filemanageroptions);
    //     }
    //
    //     parent::instance_config_save($data, $nolongerused);
    // }

    /**
     * When a block instance is deleted.
     */
    // function instance_delete() {
    //     global $DB;
    //     $fs = get_file_storage();
    //     $fs->delete_area_files($this->context->id, 'block_cocoon_tabs');
    //     return true;
    // }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    // public function instance_copy($fromid) {
    //     global $CFG;
    //
    //     $fromcontext = context_block::instance($fromid);
    //     $fs = get_file_storage();
    //
    //     if (!empty($this->config) && is_object($this->config)) {
    //         $data = $this->config;
    //         $data->slidesnumber = is_numeric($data->slidesnumber) ? (int)$data->slidesnumber : 0;
    //     } else {
    //         $data = new stdClass();
    //         $data->slidesnumber = 0;
    //     }
    //
    //     $filemanageroptions = array('maxbytes'      => $CFG->maxbytes,
    //                                 'subdirs'       => 0,
    //                                 'maxfiles'      => 1,
    //                                 'accepted_types' => array('.jpg', '.png', '.gif'));
    //
    //     for($i = 1; $i <= $data->slidesnumber; $i++) {
    //         $field = 'file_slide' . $i;
    //         if (!isset($data->$field)) {
    //             continue;
    //         }
    //
    //         // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
    //         if (!$fs->is_area_empty($fromcontext->id, 'block_cocoon_tabs', 'slides', $i, false)) {
    //             $draftitemid = 0;
    //             file_prepare_draft_area($draftitemid, $fromcontext->id, 'block_cocoon_tabs', 'slides', $i, $filemanageroptions);
    //             file_save_draft_area_files($draftitemid, $this->context->id, 'block_cocoon_tabs', 'slides', $i, $filemanageroptions);
    //         }
    //     }
    //
    //     return true;
    // }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }
    public function html_attributes() {
      global $CFG;
      $attributes = parent::html_attributes();
      include($CFG->dirroot . '/theme/edumy/ccn/block_handler/attributes.php');
      return $attributes;
    }

}
