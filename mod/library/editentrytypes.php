<?php

/// This page allows to edit entry types for a particular instance of library

require_once("../../config.php");
require_once("lib.php");

$PAGE->requires->css('/mod/library/assets/css/fontawesome-iconpicker/fontawesome-iconpicker.min.css', true);
$PAGE->requires->js_call_amd('mod_library/util', 'init');

$id = required_param('id', PARAM_INT);                       // Course Module ID, or
$confirm     = optional_param('confirm', 0, PARAM_INT);      // confirm the action
$name        = optional_param('name', '', PARAM_CLEAN);  // confirm the name
$icon        = optional_param('icon', '', PARAM_CLEAN);  // confirm the icon

$action = optional_param('action', '', PARAM_ALPHA ); // what to do
$hook   = optional_param('hook', '', PARAM_ALPHANUM); // entrytype ID
$mode   = optional_param('mode', '', PARAM_ALPHA);   // entrytype

$action = strtolower($action);

$url = new moodle_url('/mod/library/editentrytypes.php', array('id'=>$id));

if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}
if ($name !== 'name') {
    $url->param('name', $name);
}
if ($icon !== 'icon') {
    $url->param('icon', $icon);
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
    if ($entrytype = $DB->get_record("library_entries_types", array("id"=>$hook))) {
        
    } else {
        print_error('invalidentrytypeid');
    }
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/library:managecategories', $context);

$strlibraries   = get_string("modulenameplural", "library");
$strlibrary     = get_string("modulename", "library");

$PAGE->navbar->add(get_string("entrytypes","library"),
        new moodle_url('/mod/library/editentrytypes.php', array('id' => $cm->id,'mode' => 'entrytype')));
if (!empty($action)) {
    $navaction = get_string(core_text::strtolower($action."entrytype"), 'library');
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

$entrytypes = $DB->get_records("library_entries_types", null, "name ASC");

if ( $hook > 0 ) {
    if ( $action == "edit" ) {
        if ( $confirm ) {
            require_sesskey();
            $action = "";
            $type = new stdClass();
            $type->id = $hook;
            $type->name = $name;
            $type->icon = $icon;

            $DB->update_record("library_entries_types", $type);
            $event = \mod_library\event\entrytype_updated::create(array(
                'context' => $context,
                'objectid' => $hook
            ));

            $event->add_record_snapshot('library_entries_types', $type);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);

        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($library->name), 2);
            echo $OUTPUT->heading(format_string(get_string("editentrytype", "library")), 3);

            $name = $entrytype->name;
            $icon = $entrytype->icon;

            require "editentrytypes.html";
            echo $OUTPUT->footer();
            die;
        }

    } elseif ( $action == "delete" ) {
        if ( $confirm ) {
            require_sesskey();
            $DB->delete_records("library_entries_types", array("id"=>$hook));

            $event = \mod_library\event\entrytype_deleted::create(array(
                'context' => $context,
                'objectid' => $hook
            ));
            $event->add_record_snapshot('library_entries_types', $entrytype);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);

            redirect("editentrytypes.php?id=$cm->id", get_string("entrytypedeleted", "library"), 2);
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($library->name), 2);
            echo $OUTPUT->heading(format_string(get_string("deleteentrytype", "library")), 3);

            echo $OUTPUT->box_start('generalbox boxaligncenter errorboxcontent boxwidthnarrow');
            echo "<div class=\"boxaligncenter deletetypeconfirm\">".format_string($entrytype->name, true, $fmtoptions)."<br/>";

            $num_entries = $DB->count_records("library_entries_types", array("id"=>$entrytype->id));
            if ( $num_entries ) {
                print_string("deletingnoneemptyentrytype","library");
            }
            echo "<p>";
            print_string("areyousuredelete","library");
            echo "</p>";
?>

                <table border="0" width="100" class="confirmbuttons">
                    <tr>
                        <td align="$rightalignment" style="width:50%">
                        <form id="form" method="post" action="editentrytypes.php">
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
            echo $OUTPUT->single_button(new moodle_url("editentrytypes.php", $options), get_string("no"), "post", array("primary" => true));
            echo "</td></tr></table>";
            echo "</div>";
            echo $OUTPUT->box_end();
        }
    }

} elseif ( $action == "add" ) {
    if ( $confirm ) {
        require_sesskey();
        $dupentrytype = $DB->get_records_sql("SELECT * FROM {library_entries_types} WHERE ".$DB->sql_like('name','?', false), array($name));
        if ( $dupentrytype ) {
            redirect("editentrytypes.php?id=$cm->id&amp;action=add&amp;name=$name", get_string("duplicateentrytype", "library"), 2);

        } else {
            $action = "";
            $type = new stdClass();
            $type->name = $name;
            $type->icon = $icon;

            $type->id = $DB->insert_record("library_entries_types", $type);
            $event = \mod_library\event\entrytype_created::create(array(
                'context' => $context,
                'objectid' => $type->id
            ));
            $event->add_record_snapshot('library_entries_types', $type);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($library->name), 2);
        echo "<h3 class=\"main\">" . get_string("addentrytype", "library"). "</h3>";
        $name="";
        require "editentrytypes.html";
    }
}

if ( $action ) {
    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($library->name), 2);

?>

<form method="post" action="editentrytypes.php">
<table width="75%" class="boxaligncenter generalbox" cellpadding="5">
        <tr>
          <th style="width:40%" align="center">
          <?php p(get_string("entrytypes","library")) ?></th>
          <th style="width:20%" align="center">
          <?php p(get_string("action")) ?></th>
        </tr>
        <tr>
            <td style="width:100%" colspan="3">

<?php
    if ( $entrytypes ) {
        echo '<table width="100%">';
        foreach ($entrytypes as $entrytype) {
            $num_entries = $DB->count_records("library_entries_types", array("id"=>$entrytype->id));
?>

             <tr>
               <td style="width:40%" align="left">
               <?php
                    echo "<span class=\"bold\">".format_string($entrytype->name, true, $fmtoptions)."</span> <span>($num_entries " . get_string("entries","library") . ")</span>";
               ?>
               </td>               
               <td style="width:19%" align="left" class="action">
               <?php
                echo "<a href=\"editentrytypes.php?id=$cm->id&amp;action=delete&amp;mode=entrytype&amp;hook=$entrytype->id\">" .
                     $OUTPUT->pix_icon('t/delete', get_string('delete')). "</a> ";
                echo "<a href=\"editentrytypes.php?id=$cm->id&amp;action=edit&amp;mode=entrytype&amp;hook=$entrytype->id\">" .
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
        <td style="width:100%" colspan="3"  align="center">
            <?php

             $options['id'] = $cm->id;
             $options['action'] = "add";

             echo "<table class=\"editbuttons\" border=\"0\"><tr><td align=\"$rightalignment\" class=\"pr-1\">";
             echo $OUTPUT->single_button(new moodle_url("editentrytypes.php", $options), get_string("addentrytype", "library"), "post", array("primary" => true));
             echo "</td><td align=\"$leftalignment\" class=\"pl-1\">";
             unset($options['action']);
             $options['mode'] = 'entrytype';
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