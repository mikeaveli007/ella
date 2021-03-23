<?php

/// This page allows to edit entries categories for a particular instance of library

require_once("../../config.php");
require_once("lib.php");

$PAGE->requires->css('/mod/library/assets/css/fontawesome-iconpicker/fontawesome-iconpicker.min.css', true);
$PAGE->requires->js_call_amd('mod_library/util', 'init');

$id = required_param('id', PARAM_INT);                       // Course Module ID, or
$usedynalink = optional_param('usedynalink', 0, PARAM_INT);  // category ID
$confirm     = optional_param('confirm', 0, PARAM_INT);      // confirm the action
$name        = optional_param('name', '', PARAM_CLEAN);  // confirm the name
$icon        = optional_param('icon', '', PARAM_CLEAN);  // confirm the icon
$parentcatid   = optional_param('parentcatid', '', PARAM_CLEAN);  // confirm the parent category id

$action = optional_param('action', '', PARAM_ALPHA ); // what to do
$hook   = optional_param('hook', '', PARAM_ALPHANUM); // category ID
$mode   = optional_param('mode', '', PARAM_ALPHA);   // cat

$action = strtolower($action);

$url = new moodle_url('/mod/library/editcategories.php', array('id'=>$id));
if ($usedynalink !== 0) {
    $url->param('usedynalink', $usedynalink);
}
if ($confirm !== 0) {
    $url->param('confirm', $confirm);
}
if ($name !== 'name') {
    $url->param('name', $name);
}
if ($icon !== 'icon') {
    $url->param('icon', $icon);
}
if ($parentcatid !== 'parentcatid') {
    $url->param('parentcatid', $parentcatid);
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
    if ($category = $DB->get_record("library_categories", array("id"=>$hook))) {
        //Check it belongs to the same library
        if ($category->libraryid != $library->id) {
            print_error('invalidid', 'library');
        }
    } else {
        print_error('invalidcategoryid');
    }
}

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/library:managecategories', $context);

$strlibraries   = get_string("modulenameplural", "library");
$strlibrary     = get_string("modulename", "library");

$PAGE->navbar->add(get_string("categories","library"),
        new moodle_url('/mod/library/editcategories.php', array('id' => $cm->id,'mode' => 'cat')));
if (!empty($action)) {
    $navaction = get_string(core_text::strtolower($action."category"), 'library');
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

$categories = $DB->get_records("library_categories", array("libraryid"=>$library->id), "name ASC");

if ( $hook > 0 ) {
    if ( $action == "edit" ) {
        if ( $confirm ) {
            require_sesskey();
            $action = "";
            $cat = new stdClass();
            $cat->id = $hook;
            $cat->name = $name;
            $cat->usedynalink = $usedynalink;
            $cat->icon = $icon;
            $cat->parentcatid = $parentcatid;

            $DB->update_record("library_categories", $cat);
            $event = \mod_library\event\category_updated::create(array(
                'context' => $context,
                'objectid' => $hook
            ));
            $cat->libraryid = $library->id;
            $event->add_record_snapshot('library_categories', $cat);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);

        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($library->name), 2);
            echo $OUTPUT->heading(format_string(get_string("editcategory", "library")), 3);

            $name = $category->name;
            $usedynalink = $category->usedynalink;
            $icon = $category->icon;
            $parentcatid = $category->parentcatid;

            require "editcategories.html";
            echo $OUTPUT->footer();
            die;
        }

    } elseif ( $action == "delete" ) {
        if ( $confirm ) {
            require_sesskey();
            $DB->delete_records("library_entries_categories", array("categoryid"=>$hook));
            $DB->delete_records("library_categories", array("id"=>$hook));

            $event = \mod_library\event\category_deleted::create(array(
                'context' => $context,
                'objectid' => $hook
            ));
            $event->add_record_snapshot('library_categories', $category);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);

            redirect("editcategories.php?id=$cm->id", get_string("categorydeleted", "library"), 2);
        } else {
            echo $OUTPUT->header();
            echo $OUTPUT->heading(format_string($library->name), 2);
            echo $OUTPUT->heading(format_string(get_string("deletecategory", "library")), 3);

            echo $OUTPUT->box_start('generalbox boxaligncenter errorboxcontent boxwidthnarrow');
            echo "<div class=\"boxaligncenter deletecatconfirm\">".format_string($category->name, true, $fmtoptions)."<br/>";

            $num_entries = $DB->count_records("library_entries_categories", array("categoryid"=>$category->id));
            if ( $num_entries ) {
                print_string("deletingnoneemptycategory","library");
            }
            echo "<p>";
            print_string("areyousuredelete","library");
            echo "</p>";
?>

                <table border="0" width="100" class="confirmbuttons">
                    <tr>
                        <td align="$rightalignment" style="width:50%">
                        <form id="form" method="post" action="editcategories.php">
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
            echo $OUTPUT->single_button(new moodle_url("editcategories.php", $options), get_string("no"), "post", array("primary" => true));
            echo "</td></tr></table>";
            echo "</div>";
            echo $OUTPUT->box_end();
        }
    }

} elseif ( $action == "add" ) {
    if ( $confirm ) {
        require_sesskey();
        $dupcategory = $DB->get_records_sql("SELECT * FROM {library_categories} WHERE ".$DB->sql_like('name','?', false)." AND libraryid=?", array($name, $library->id));
        if ( $dupcategory ) {
            redirect("editcategories.php?id=$cm->id&amp;action=add&amp;name=$name", get_string("duplicatecategory", "library"), 2);

        } else {
            $action = "";
            $cat = new stdClass();
            $cat->name = $name;
            $cat->usedynalink = $usedynalink;
            $cat->icon = $icon;
            $cat->parentcatid = $parentcatid;
            $cat->libraryid = $library->id;

            $cat->id = $DB->insert_record("library_categories", $cat);
            $event = \mod_library\event\category_created::create(array(
                'context' => $context,
                'objectid' => $cat->id
            ));
            $event->add_record_snapshot('library_categories', $cat);
            $event->add_record_snapshot('library', $library);
            $event->trigger();

            // Reset caches.
            \mod_library\local\concept_cache::reset_library($library);
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(format_string($library->name), 2);
        echo "<h3 class=\"main\">" . get_string("addcategory", "library"). "</h3>";
        $name="";
        require "editcategories.html";
    }
}

if ( $action ) {
    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($library->name), 2);

?>

<form method="post" action="editcategories.php">
<table width="75%" class="boxaligncenter generalbox" cellpadding="5">
        <tr>
          <th style="width:40%" align="center">
          <?php p(get_string("categories","library")) ?></th>
          <th style="width:40%" align="center">
          <?php p(get_string("parentcatid","library")) ?></th>
          <th style="width:20%" align="center">
          <?php p(get_string("action")) ?></th>
        </tr>
        <tr>
            <td style="width:100%" colspan="3">

<?php
    if ( $categories ) {
        echo '<table width="100%">';
        foreach ($categories as $category) {
            $num_entries = $DB->count_records("library_entries_categories", array("categoryid"=>$category->id));
            $parentcat = get_category($category->parentcatid);
?>

             <tr>
               <td style="width:40%" align="$leftalignment">
               <?php
                    echo "<span class=\"bold\">".format_string($category->name, true, $fmtoptions)."</span> <span>($num_entries " . get_string("entries","library") . ")</span>";
               ?>
               </td>
               <td style="width:40%" align="$leftalignment">
               <?php
                    if($parentcat) echo "<span>".format_string($parentcat->name, true, $fmtoptions)."</span>";
               ?>
               </td>
               <td style="width:19%" align="center" class="action">
               <?php
                echo "<a href=\"editcategories.php?id=$cm->id&amp;action=delete&amp;mode=cat&amp;hook=$category->id\">" .
                     $OUTPUT->pix_icon('t/delete', get_string('delete')). "</a> ";
                echo "<a href=\"editcategories.php?id=$cm->id&amp;action=edit&amp;mode=cat&amp;hook=$category->id\">" .
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
             echo $OUTPUT->single_button(new moodle_url("editcategories.php", $options), get_string("addcategory", "library"), "post", array("primary" => true));
             echo "</td><td align=\"$leftalignment\" class=\"pl-1\">";
             unset($options['action']);
             $options['mode'] = 'cat';
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