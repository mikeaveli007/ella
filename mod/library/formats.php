<?php

/// This file allows to manage the default behaviour of the display formats

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
require_once("lib.php");

$id   = required_param('id', PARAM_INT);
$mode = optional_param('mode', '', PARAM_ALPHANUMEXT);

$url = new moodle_url('/mod/library/formats.php', array('id'=>$id));
if ($mode !== '') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);

admin_externalpage_setup('managemodules'); // this is hacky, tehre should be a special hidden page for it

if ( !$displayformat = $DB->get_record("library_formats", array("id"=>$id))) {
    print_error('invalidlibraryformat', 'library');
}

$form = data_submitted();
if ( $mode == 'visible' and confirm_sesskey()) {
    if ( $displayformat ) {
        if ( $displayformat->visible ) {
            $displayformat->visible = 0;
        } else {
            $displayformat->visible = 1;
        }
        $DB->update_record("library_formats",$displayformat);
    }
    redirect("$CFG->wwwroot/$CFG->admin/settings.php?section=modsettinglibrary#library_formats_header");
    die;
} elseif ( $mode == 'edit' and $form and confirm_sesskey()) {

    $displayformat->popupformatname = $form->popupformatname;
    $displayformat->showgroup   = $form->showgroup;
    $displayformat->defaultmode = $form->defaultmode;
    $displayformat->defaulthook = $form->defaulthook;
    $displayformat->sortkey     = $form->sortkey;
    $displayformat->sortorder   = $form->sortorder;

    // Extract visible tabs from array into comma separated list.
    $visibletabs = implode(',', $form->visibletabs);
    // Include 'standard' tab by default along with other tabs.
    // This way we don't run into the risk of users not selecting any tab for displayformat.
    $displayformat->showtabs = LIBRARY_STANDARD.','.$visibletabs;

    $DB->update_record("library_formats",$displayformat);
    redirect("$CFG->wwwroot/$CFG->admin/settings.php?section=modsettinglibrary#library_formats_header");
    die;
}

$strmodulename = get_string("modulename", "library");
$strdisplayformats = get_string("displayformats","library");

echo $OUTPUT->header();

echo $OUTPUT->heading($strmodulename . ': ' . get_string("displayformats","library"));

echo $OUTPUT->box(get_string("configwarning", 'admin'), "generalbox boxaligncenter boxwidthnormal");
echo "<br />";

$yes = get_string("yes");
$no  = get_string("no");

echo '<form method="post" action="formats.php" id="form">';
echo '<table width="90%" align="center" class="generalbox">';
?>
<tr>
    <td colspan="3" align="center"><strong>
    <?php echo get_string('displayformat'.$displayformat->name,'library'); ?>
    </strong></td>
</tr>
<tr valign="top">
    <td align="right" width="20%"><?php echo html_writer::label(get_string('popupformat','library'), 'menupopupformatname'); ?></td>
    <td>
 <?php
    //get and update available formats
    $recformats = library_get_available_formats();

    $formats = array();

    //Take names
    foreach ($recformats as $format) {
       $formats[$format->name] = get_string("displayformat$format->name", "library");
    }
    //Sort it
    asort($formats);

    echo html_writer::select($formats, 'popupformatname', $displayformat->popupformatname, false);
 ?>
    </td>
    <td width="60%">
    <?php print_string("cnfrelatedview", "library") ?><br /><br />
    </td>
</tr>
<tr valign="top">
    <td align="right" width="20%"><label for="defaultmode"><?php print_string('defaultmode','library'); ?></label></td>
    <td>
    <select size="1" id="defaultmode" name="defaultmode">
<?php
    $sletter = '';
    $scat = '';
    $sauthor = '';
    $sdate = '';
    switch ( strtolower($displayformat->defaultmode) ) {
    case 'letter':
        $sletter = ' selected="selected" ';
    break;

    case 'cat':
        $scat = ' selected="selected" ';
    break;

    case 'date':
        $sdate = ' selected="selected" ';
    break;

    case 'author':
        $sauthor = ' selected="selected" ';
    break;
    }
?>
    <option value="letter" <?php p($sletter)?>><?php print_string("letter", "library"); ?></option>
    <option value="cat" <?php p($scat)?>><?php print_string("cat", "library"); ?></option>
    <option value="date" <?php p($sdate)?>><?php print_string("date", "library"); ?></option>
    <option value="author" <?php p($sauthor)?>><?php print_string("author", "library"); ?></option>
    </select>
    </td>
    <td width="60%">
    <?php print_string("cnfdefaultmode", "library") ?><br /><br />
    </td>
</tr>
<tr valign="top">
    <td align="right" width="20%"><label for="defaulthook"><?php print_string('defaulthook','library'); ?></label></td>
    <td>
    <select size="1" id="defaulthook" name="defaulthook">
<?php
    $sall = '';
    $sspecial = '';
    $sallcategories = '';
    $snocategorised = '';
    switch ( strtolower($displayformat->defaulthook) ) {
    case 'all':
        $sall = ' selected="selected" ';
    break;

    case 'special':
        $sspecial = ' selected="selected" ';
    break;

    case '0':
        $sallcategories = ' selected="selected" ';
    break;

    case '-1':
        $snocategorised = ' selected="selected" ';
    break;
    }
?>
    <option value="ALL" <?php p($sall)?>><?php p(get_string("allentries","library"))?></option>
    <option value="SPECIAL" <?php p($sspecial)?>><?php p(get_string("special","library"))?></option>
    <option value="0" <?php p($sallcategories)?>><?php p(get_string("allcategories","library"))?></option>
    <option value="-1" <?php p($snocategorised)?>><?php p(get_string("notcategorised","library"))?></option>
    </select>
    </td>
    <td width="60%">
    <?php print_string("cnfdefaulthook", "library") ?><br /><br />
    </td>
</tr>
<tr valign="top">
    <td align="right" width="20%"><label for="sortkey"><?php print_string('defaultsortkey','library'); ?></label></td>
    <td>
    <select size="1" id="sortkey" name="sortkey">
<?php
    $sfname = '';
    $slname = '';
    $supdate = '';
    $screation = '';
    switch ( strtolower($displayformat->sortkey) ) {
    case 'firstname':
        $sfname = ' selected="selected" ';
    break;

    case 'lastname':
        $slname = ' selected="selected" ';
    break;

    case 'creation':
        $screation = ' selected="selected" ';
    break;

    case 'update':
        $supdate = ' selected="selected" ';
    break;
    }
?>
    <option value="CREATION" <?php p($screation)?>><?php p(get_string("sortbycreation","library"))?></option>
    <option value="UPDATE" <?php p($supdate)?>><?php p(get_string("sortbylastupdate","library"))?></option>
    <option value="FIRSTNAME" <?php p($sfname)?>><?php p(get_string("firstname"))?></option>
    <option value="LASTNAME" <?php p($slname)?>><?php p(get_string("lastname"))?></option>
    </select>
    </td>
    <td width="60%">
    <?php print_string("cnfsortkey", "library") ?><br /><br />
    </td>
</tr>
<tr valign="top">
    <td align="right" width="20%"><label for="sortorder"><?php print_string('defaultsortorder','library'); ?></label></td>
    <td>
    <select size="1" id="sortorder" name="sortorder">
<?php
    $sasc = '';
    $sdesc = '';
    switch ( strtolower($displayformat->sortorder) ) {
    case 'asc':
        $sasc = ' selected="selected" ';
    break;

    case 'desc':
        $sdesc = ' selected="selected" ';
    break;
    }
?>
    <option value="asc" <?php p($sasc)?>><?php p(get_string("ascending","library"))?></option>
    <option value="desc" <?php p($sdesc)?>><?php p(get_string("descending","library"))?></option>
    </select>
    </td>
    <td width="60%">
    <?php print_string("cnfsortorder", "library") ?><br /><br />
    </td>
</tr>
<tr valign="top">
    <td align="right" width="20%"><label for="showgroup"><?php print_string("includegroupbreaks", "library"); ?>:</label></td>
    <td>
    <select size="1" id="showgroup" name="showgroup">
<?php
    $yselected = "";
    $nselected = "";
    if ($displayformat->showgroup) {
        $yselected = " selected=\"selected\" ";
    } else {
        $nselected = " selected=\"selected\" ";
    }
?>
    <option value="1" <?php echo $yselected ?>><?php p($yes)?></option>
    <option value="0" <?php echo $nselected ?>><?php p($no)?></option>
    </select>
    </td>
    <td width="60%">
    <?php print_string("cnfshowgroup", "library") ?><br /><br />
    </td>
</tr>
<tr>
    <td align="right" width="20%"><label for="visibletabs"><?php print_string("visibletabs", "library"); ?></label></td>
    <td>
        <?php
        // Get all library tabs.
        $librarytabs = library_get_all_tabs();
        // Extract showtabs value in an array.
        $visibletabs = library_get_visible_tabs($displayformat);
        $size = min(10, count($librarytabs));
        ?>
    <select id="visibletabs" name="visibletabs[]" size="<?php echo $size ?>" multiple="multiple">
<?php
    $selected = "";
foreach ($librarytabs as $tabkey => $tabvalue) {
    if (in_array($tabkey, $visibletabs)) {
?>
    <option value="<?php echo $tabkey ?>" selected="selected"><?php echo $tabvalue ?></option>
    <?php
    } else {
    ?>
    <option value="<?php echo $tabkey ?>"><?php echo $tabvalue ?></option>
    <?php
    }
}
    ?>
    </select>
    </td>
    <td width="60%">
        <?php print_string("cnftabs", "library") ?><br/><br/>
    </td>
</tr>
<tr>
    <td colspan="3" align="center">
    <input type="submit" value="<?php print_string("savechanges") ?>" /></td>
</tr>
<input type="hidden" name="id"    value="<?php p($id) ?>" />
<input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
<input type="hidden" name="mode"    value="edit" />
<?php

echo '</table></form>';

echo $OUTPUT->footer();
?>