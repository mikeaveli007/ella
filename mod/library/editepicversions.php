<?php

/// This page allows to edit Epic Versions for a particular instance of library

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);                       // Course Module ID, or
$confirm     = optional_param('confirm', 0, PARAM_INT);      // confirm the action
$name        = optional_param('name', '', PARAM_CLEAN);  // confirm the name

$action = optional_param('action', '', PARAM_ALPHA ); // what to do
$hook   = optional_param('hook', '', PARAM_ALPHANUM); // Epic Ver ID
$mode   = optional_param('mode', '', PARAM_ALPHA);   // epicver

$action = strtolower($action);

$url = new moodle_url('/mod/library/editepicversions.php', array('id'=>$id));

if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}
if ($name !== 'name') {
    $url->param('name', $name);
}
if ($action !== 'action') {
    $url->param('action', $action);
}
if ($hook !== 'hook') {
    $url->param('hook', $hook);
}
if ($mode !== 'mode') {
    $url->param('mode', $mode);
}

$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('library', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $library = $DB->get_record("library", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

if ($hook > 0) {
    if (!$epicver = $DB->get_record("library_epic_ver", array("id"=>$hook))) {
        print_error('invalidepicverid');
    } 
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/library:managecategories', $context);

$strlibraries   = get_string("modulenameplural", "library");
$strlibrary     = get_string("modulename", "library");

$PAGE->navbar->add(get_string("epicversions","library"),
        new moodle_url('/mod/library/editepicversions.php', array('id' => $cm->id,'mode' => 'epicver')));
if (!empty($action)) {
    $navaction = get_string(core_text::strtolower($action."epicver"), 'library');
    $PAGE->navbar->add($navaction);
}
$PAGE->set_title($library->name);
$PAGE->set_heading($course->fullname);

// Prepare format_string/text options
$fmtoptions = array(
    'context' => $context);

if (right_to_left()) { // RTL table alignment support
    $rightalignment = 'left';
    $leftalignment = 'right';
} else {
    $rightalignment = 'right';
    $leftalignment = 'left';

}

if ( $hook > 0 ) {
    if ( $action == "edit" ) {
        if ( $confirm ) {
            require_sesskey();
            $action = "";
            $ev = new stdClass();
            $ev->id = $hook;
            $ev->name = $name;

            $DB->update_record("library_epic_ver", $ev);
            $event = \mod_library\event\epicver_updated::create(array(
                'context' => $context,
                'objectid' => $hook
            ));

            $event->add_record_snapshot('library_epic_ver', $ev);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);

        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($library->name), 2);
            echo $OUTPUT->heading(format_string(get_string("epicver", "library")), 3);

            $name = $epicver->name;
            require "editepicversions.html";
            echo $OUTPUT->footer();
            die;
        }

    } elseif ( $action == "delete" ) {
        if ( $confirm ) {
            require_sesskey();
            $DB->delete_records("library_epic_ver", array("id"=>$hook));

            $event = \mod_library\event\epicver_deleted::create(array(
                'context' => $context,
                'objectid' => $hook
            ));
            $event->add_record_snapshot('library_epic_ver', $epicver);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);

            redirect("editepicversions.php?id=$cm->id", get_string("epicverdeleted", "library"), 2);
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($library->name), 2);
            echo $OUTPUT->heading(format_string(get_string("deleteepicver", "library")), 3);

            echo $OUTPUT->box_start('generalbox boxaligncenter errorboxcontent boxwidthnarrow');
            echo "<div class=\"boxaligncenter deletecatconfirm\">".format_string($epicver->name, true, $fmtoptions)."<br/>";

            echo "<p>";
            print_string("areyousuredelete","library");
            echo "</p>";
?>
            <table border="0" width="100" class="confirmbuttons">
                <tr>
                    <td align="$rightalignment" style="width:50%">
                        <form id="form" method="post" action="editepicversions.php">
                            <div>
                                <input type="hidden" name="sesskey"     value="<?php echo sesskey(); ?>" />
                                <input type="hidden" name="id"          value="<?php p($cm->id) ?>" />
                                <input type="hidden" name="action"      value="delete" />
                                <input type="hidden" name="confirm"     value="1" />
                                <input type="hidden" name="mode"         value="<?php echo $mode ?>" />
                                <input type="hidden" name="hook"         value="<?php echo $hook ?>" />
                                <input type="submit" class="btn btn-primary" value=" <?php print_string("yes")?> " />
                            </div>
                        </form>
                    </td>
                    <td align="$leftalignment" style="width:50%">
<?php
            unset($options);
            $options = array ("id" => $id);
            echo $OUTPUT->single_button(new moodle_url("editepicversions.php", $options), get_string("no"), "post", array("primary" => true));
            echo "</td></tr></table>";
            echo "</div>";
            echo $OUTPUT->box_end();
        }
    }

} elseif ( $action == "add" ) {
    if ( $confirm ) {
        require_sesskey();
        $dupepicver = $DB->get_records_sql("SELECT * FROM {library_epic_ver} WHERE ".$DB->sql_like('name','?', false), array($name));
        if ( $dupepicver ) {
            redirect("editepicversions.php?id=$cm->id&amp;action=add&amp;name=$name", get_string("duplicateepicver", "library"), 2);

        } else {
            $action = "";
            $ev = new stdClass();
            $ev->name = $name;

            $ev->id = $DB->insert_record("library_epic_ver", $ev);
            $event = \mod_library\event\epicver_created::create(array(
                'context' => $context,
                'objectid' => $ev->id
            ));
            $event->add_record_snapshot('library_epic_ver', $ev);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($library->name), 2);
        echo "<h3 class=\"main\">" . get_string("addepicver", "library"). "</h3>";
        $name="";
        require "editepicversions.html";
    }
}

if ( $action ) {
    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($library->name), 2);

?>

<form method="post" action="editepicversions.php">
<table width="40%" class="boxaligncenter generalbox" cellpadding="5">
        <tr>
          <th style="width:90%" align="center">
          <?php p(get_string("epicversions","library")) ?></th>
          <th style="width:10%" align="center">
          <?php p(get_string("action")) ?></th>
        </tr>
        <tr><td style="width:100%" colspan="2">

<?php
    $epicversions = $DB->get_records("library_epic_ver", null, "name ASC");

    if ( $epicversions ) {
        echo '<table width="100%">';
        foreach ($epicversions as $epicver) {
            $num_entries = $DB->count_records("library_entries", array("epicverid"=>$epicver->id));
?>

             <tr>
               <td style="width:80%" align="$leftalignment">
               <?php
                    echo "<span class=\"bold\">".format_string($epicver->name, true, $fmtoptions)."</span> <span>($num_entries " . get_string("entries","library") . ")</span>";
               ?>
               </td>
               <td style="width:19%" align="center" class="action">
               <?php
                echo "<a href=\"editepicversions.php?id=$cm->id&amp;action=delete&amp;mode=epicver&amp;hook=$epicver->id\">" .
                     $OUTPUT->pix_icon('t/delete', get_string('delete')). "</a> ";
                echo "<a href=\"editepicversions.php?id=$cm->id&amp;action=edit&amp;mode=epicver&amp;hook=$epicver->id\">" .
                     $OUTPUT->pix_icon('t/edit', get_string('edit')). "</a> ";
               ?>
               </td>
             </tr>

             <?php

          }
        echo '</table>';
     }
?>

        </td></tr>
        <tr>
        <td style="width:100%" colspan="2"  align="center">
            <?php

             $options['id'] = $cm->id;
             $options['action'] = "add";

             echo "<table class=\"editbuttons\" border=\"0\"><tr><td align=\"$rightalignment\" class=\"pr-1\">";
             echo $OUTPUT->single_button(new moodle_url("editepicversions.php", $options), get_string("addepicver", "library"), "post", array("primary" => true));
             echo "</td><td align=\"$leftalignment\" class=\"pl-1\">";
             unset($options['action']);
             $options['mode'] = 'epicver';
             $options['hook'] = $hook;
             echo $OUTPUT->single_button(new moodle_url("view.php", $options), get_string("back","library"));
             echo "</td></tr>";
             echo "</table>";

            ?>
        </td>
        </tr>
        </table>


</form>

<?php
echo $OUTPUT->footer();