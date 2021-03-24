<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/library/lib.php');

    $settings->add(new admin_setting_heading('library_normal_header', get_string('libraryleveldefaultsettings', 'library'), ''));

    $settings->add(new admin_setting_configtext('library_entbypage', get_string('entbypage', 'library'),
                       get_string('entbypage', 'library'), 10, PARAM_INT));


    $settings->add(new admin_setting_configcheckbox('library_dupentries', get_string('allowduplicatedentries', 'library'),
                       get_string('cnfallowdupentries', 'library'), 0));

    $settings->add(new admin_setting_configcheckbox('library_allowcomments', get_string('allowcomments', 'library'),
                       get_string('cnfallowcomments', 'library'), 0));

    $settings->add(new admin_setting_configcheckbox('library_linkbydefault', get_string('usedynalink', 'library'),
                       get_string('cnflinklibraries', 'library'), 1));

    $settings->add(new admin_setting_configcheckbox('library_defaultapproval', get_string('defaultapproval', 'library'),
                       get_string('cnfapprovalstatus', 'library'), 1));


    if (empty($CFG->enablerssfeeds)) {
        $options = array(0 => get_string('rssglobaldisabled', 'admin'));
        $str = get_string('configenablerssfeeds', 'library').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');

    } else {
        $options = array(0=>get_string('no'), 1=>get_string('yes'));
        $str = get_string('configenablerssfeeds', 'library');
    }
    $settings->add(new admin_setting_configselect('library_enablerssfeeds', get_string('enablerssfeeds', 'admin'),
                       $str, 0, $options));


    $settings->add(new admin_setting_heading('library_levdev_header', get_string('entryleveldefaultsettings', 'library'), ''));

    $settings->add(new admin_setting_configcheckbox('library_linkentries', get_string('usedynalink', 'library'),
                       get_string('cnflinkentry', 'library'), 0));

    $settings->add(new admin_setting_configcheckbox('library_casesensitive', get_string('casesensitive', 'library'),
                       get_string('cnfcasesensitive', 'library'), 0));

    $settings->add(new admin_setting_configcheckbox('library_fullmatch', get_string('fullmatch', 'library'),
                       get_string('cnffullmatch', 'library'), 0));


    //Update and get available formats
    $recformats = library_get_available_formats();
    $formats = array();
    //Take names
    foreach ($recformats as $format) {
        $formats[$format->id] = get_string("displayformat$format->name", "library");
    }
    asort($formats);

    $str = '<table>';
    foreach ($formats as $formatid=>$formatname) {
        $recformat = $DB->get_record('library_formats', array('id'=>$formatid));
        $str .= '<tr>';
        $str .= '<td>' . $formatname . '</td>';
        $eicon = "<a title=\"".get_string("edit")."\" href=\"$CFG->wwwroot/mod/library/formats.php?id=$formatid&amp;mode=edit\">";
        $eicon .= $OUTPUT->pix_icon('t/edit', get_string('edit')). "</a>";
        if ( $recformat->visible ) {
            $vtitle = get_string("hide");
            $vicon  = "t/hide";
        } else {
            $vtitle = get_string("show");
            $vicon  = "t/show";
        }
        $url = "$CFG->wwwroot/mod/library/formats.php?id=$formatid&amp;mode=visible&amp;sesskey=".sesskey();
        $viconlink = "<a title=\"$vtitle\" href=\"$url\">";
        $viconlink .= $OUTPUT->pix_icon($vicon, $vtitle) . "</a>";

        $str .= '<td align="center">' . $eicon . '&nbsp;&nbsp;' . $viconlink . '</td>';
        $str .= '</tr>';
    }
    $str .= '</table>';

    $settings->add(new admin_setting_heading('library_formats_header', get_string('displayformatssetup', 'library'), $str));
}
