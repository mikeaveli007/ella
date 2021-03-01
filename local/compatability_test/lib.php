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


/**
 * Moodle Plugin
 *
 * lib
 *
 * @package    local
 * @subpackage compatability_test
 * @copyright  2014 Chris Clark, LearningWorks Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Creates an associative array (with nested arrays) with all compatibility test settings
 * and outputs a json encoded hash that can be passed into javascript.
 */
function local_compatability_test_check_enabled() {
    $enabled = array("browser" => false, "chrome" => array(false, local_compatability_test_min_version_chrome()),
        "gecko" => array(false, local_compatability_test_min_version_gecko()),
        "opera" => array(false, local_compatability_test_min_version_opera()),
        "safari" => array(false, local_compatability_test_min_version_safari()),
        "flash" => array(false, local_compatability_test_min_version_flash()),
        "java" => array(false, local_compatability_test_min_version_java()),
        "quicktime" => array(false, local_compatability_test_min_version_quicktime()),
        "silverlight" => array(false, local_compatability_test_min_version_silverlight()),
        "visit_website" => get_string('visit_website' , 'local_compatability_test')); // string.

    // Flash.
    if (local_compatability_test_enable_flash_check()) {
        $enabled["flash"][0] = true;
    }

    if (local_compatability_test_enable_java_check()) {
        $enabled["java"][0] = true;
    }

    if (local_compatability_test_enable_quicktime_check()) {
        $enabled["quicktime"][0] = true;
    }

    if (local_compatability_test_enable_silverlight_check()) {
        $enabled["silverlight"][0] = true;
    }

    if (local_compatability_test_enable_browser_check()) {
        $enabled["browser"] = true;

        if (local_compatability_test_enable_chrome_check()) {
            $enabled["chrome"][0] = true;
        }

        if (local_compatability_test_enable_gecko_check()) {
            $enabled["gecko"][0] = true;
        }

        if (local_compatability_test_enable_opera_check()) {
            $enabled["opera"][0] = true;
        }

        if (local_compatability_test_enable_safari_check()) {
            $enabled["safari"][0] = true;
        }
    }

    return json_encode($enabled);
}

/*
 * The following local_compatability_test_enable... functions check the settings from the database and outputs a boolean value.
 */
function local_compatability_test_enable_browser_check() {
    $enabled = get_config('local_compatability_test', 'enable_browser_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_chrome_check() {
    $enabled = get_config('local_compatability_test', 'enable_chrome_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_gecko_check() {
    $enabled = get_config('local_compatability_test', 'enable_gecko_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_opera_check() {
    $enabled = get_config('local_compatability_test', 'enable_opera_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_safari_check() {
    $enabled = get_config('local_compatability_test', 'enable_safari_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_flash_check() {
    $enabled = get_config('local_compatability_test', 'enable_flash_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_java_check() {
    $enabled = get_config('local_compatability_test', 'enable_java_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_quicktime_check() {
    $enabled = get_config('local_compatability_test', 'enable_quicktime_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}
function local_compatability_test_enable_silverlight_check() {
    $enabled = get_config('local_compatability_test', 'enable_silverlight_check');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}

/*
 * The following local_compatibility_test_min_version... functions out put the settings for the set minimum version, or false
 * if it has not been said; the setting is empty.
 */
function local_compatability_test_min_version_flash() {
    $enabled = get_config('local_compatability_test', 'min_version_flash');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_java() {
    $enabled = get_config('local_compatability_test', 'min_version_java');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_quicktime() {
    $enabled = get_config('local_compatability_test', 'min_version_quicktime');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_silverlight() {
    $enabled = get_config('local_compatability_test', 'min_version_silverlight');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_chrome() {
    $enabled = get_config('local_compatability_test', 'min_version_chrome');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_gecko() {
    $enabled = get_config('local_compatability_test', 'min_version_gecko');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_opera() {
    $enabled = get_config('local_compatability_test', 'min_version_opera');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}
function local_compatability_test_min_version_safari() {
    $enabled = get_config('local_compatability_test', 'min_version_safari');
    if (empty($enabled)) {
        return false;
    } else {
        return $enabled;
    }
}

/*
 * Checks if the force_view_page setting is set and returns the value.
 */
function local_compatability_test_force_view_page() {
    $enabled = get_config('local_compatability_test', 'force_view_page');
    if (empty($enabled)) {
        return false;
    } else {
        return true;
    }
}

/*
 * Gets the admin_feedback setting.
 */
function local_compatability_test_admin_feedback() {
    $enabled = get_config('local_compatability_test', 'admin_feedback');
    if (empty($enabled)) {
        return "";
    } else {
        return $enabled;
    }
}

global $COURSE, $USER, $DB, $CFG, $PAGE;

$enabled = local_compatability_test_check_enabled();

// Adds the required scripts to the head of the page and calls the inital isUpToDate() function.
$CFG->additionalhtmlhead .= '
<script src="'.$CFG->wwwroot .'/local/compatability_test/js/PluginDetect_Java_Flash.js"></script>
<script src="'.$CFG->wwwroot .'/local/compatability_test/js/scripts.js"></script>
<script>isUpToDate('.$enabled.');</script>';

// If the force_view_page setting is set and the the user is not an admin, they will be redirected to
// he view.php page.
if (local_compatability_test_force_view_page()) {
    if (!is_siteadmin()) {
        $CFG->additionalhtmlhead .= '
        <script>
            forceStatusPage(\''. $CFG->wwwroot .'/local/compatability_test/view.php' .'\');
        </script>
        ';
    }
}

// Gets the strings to output into the banner.
$bannerfailure = get_string('banner_failure', 'local_compatability_test');
$bannerlink = get_string('banner_link', 'local_compatability_test');
$link = $CFG->wwwroot . '/local/compatability_test/view.php';

//GET THE LANG FILE STRINGS 
$stringman = get_string_manager();
$strings = $stringman->load_component_strings('local_compatability_test','en');
// Calls the functions required to build the display banner and the view.php table content.
$CFG->additionalhtmlfooter .= '
<script>
	var lang_strings = [];
    lang_strings["banner_success"] =  "'.$strings['banner_success']
	.'";
	lang_strings["failure_java_not_installed"] =  "'.$strings['failure_java_not_installed']
	.'";
	lang_strings["failure_java_mac"] =  "'.$strings['failure_java_mac']
	.'";
	lang_strings["visit_website_java"] =  "'.$strings['visit_website_java']
	.'";
	lang_strings["failure_flash_not_installed"] =  "'.$strings['failure_flash_not_installed']
	.'";
	lang_strings["visit_website_flash"] =  "'.$strings['visit_website_flash']
	.'";
	lang_strings["failure_quicktime_not_installed"] =  "'.$strings['failure_quicktime_not_installed']
	.'";
	lang_strings["visit_website_quicktime"] =  "'.$strings['visit_website_quicktime']
	.'";
	lang_strings["failure_silverlight_not_installed"] =  "'.$strings['failure_silverlight_not_installed']
	.'";
	lang_strings["visit_website_silverlight"] =  "'.$strings['visit_website_silverlight']
	.'";
	lang_strings["visit_website_chrome"] =  "'.$strings['visit_website_chrome']
	.'";
	lang_strings["visit_website_gecko"] =  "'.$strings['visit_website_gecko']
	.'";
	lang_strings["visit_website_opera"] =  "'.$strings['visit_website_opera']
	.'";
	lang_strings["visit_website_safari"] =  "'.$strings['visit_website_safari']
	.'";
	
	
    updateUserView(' . $enabled . ');
    checkDisplayBanner( \''. $bannerfailure .'\', \''. $link .'\', \''. $bannerlink .'\');
</script>
';