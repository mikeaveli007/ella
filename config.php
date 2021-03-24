<?php  // Moodle configuration file

//unset($CFG);
global $CFG;
//$CFG = new stdClass();

if (!isset($CFG)) {
    $CFG = new stdClass();
}

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = '127.0.0.1';
$CFG->dbname    = 'ella';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'pass4WAMP';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => 3307,
  'dbsocket' => '',
  'dbcollation' => 'utf8_unicode_ci',
);

if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = '127.0.0.1:82';
};

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $CFG->wwwroot   = 'https://' . $_SERVER['HTTP_HOST'] . '/ella';
} else {
    $CFG->wwwroot   = 'http://' . $_SERVER['HTTP_HOST'] . '/ella';
};
$CFG->dataroot  = 'C:/Users/Mike/DOCUME~1/Apps/wampstack-7.1.33-0/apps/ella/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 02775;

@error_reporting(E_ALL | E_STRICT);
@ini_set('display_errors', '1');
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1; 

$CFG->passwordsaltmain = 'f24cb6ceca514c6662c8e14f10087831ad139053ab99d2d203ef2c9311dee92b';
require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!