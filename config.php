<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle414';
$CFG->dbuser    = 'user_moodle414';
$CFG->dbpass    = 'MatKhauCuaBan';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

$CFG->wwwroot   = 'http://192.168.0.201:202';
$CFG->dataroot  = '/var/www/moodledata4.1.14';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;
$CFG->sessioncookie = '414';
require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
