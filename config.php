<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = $_ENV['ELLA_DB_TYPE'] ?? 'mysqli';
$CFG->dblibrary = $_ENV['ELLA_DB_LIBRARY'] ?? 'native';
$CFG->dbhost    = $_ENV['ELLA_DB_HOST'] ?? '172.25.0.2';
$CFG->dbname    = $_ENV['ELLA_DB_NAME'] ?? 'moodle';
$CFG->dbuser    = $_ENV['ELLA_DB_USER'] ?? 'user';
$CFG->dbpass    = $_ENV['ELLA_DB_PASS'] ?? 'password';
$CFG->prefix    = $_ENV['ELLA_DB_PREFIX'] ?? 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

$CFG->wwwroot   = $_ENV['ELLA_WWWROOT'] ?? 'http://localhost:8000';
$CFG->dataroot  = $_ENV['ELLA_DATAROOT'] ?? '/workspace/moodledata';
$CFG->tempdir = $_ENV['ELLA_TEMPDIR'] ?? $CFG->tempdir;
$CFG->cachedir = $_ENV['ELLA_CACHEDIR'] ?? $CFG->cachedir;
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

// Use the following flag to completely disable the installation of plugins 
// (new plugins, available updates and missing dependencies) and related 
// features (such as cancelling the plugin installation or upgrade) via the 
// server administration web interface. 
$CFG->disableupdateautodeploy = true; 

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!