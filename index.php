<?php
error_reporting(E_ALL);

if (version_compare(phpversion(), '5.1.0', '<') == true) { die ('PHP5.1 Only'); }

define('DIRSEP', DIRECTORY_SEPARATOR);
define('SITE_PATH', dirname(__FILE__).DIRSEP);

include(SITE_PATH.'startup.php');
include(SITE_PATH."../ab/site/_conf.php");

$registry = new Registry;

$router = new Router($registry);
$logger = new Logger($registry);
$db = new DB($registry);
$auth = new Auth($registry);
$local = new Local($registry);

$registry->set('router', $router);
$registry->set('logger', $logger);
$registry->set('db', $db);
$registry->set('auth', $auth);
$registry->set('local', $local);

$registry->set('abmagic', $abmagic);

$router->setPath(SITE_PATH.'controllers');
$router->delegate();
?>