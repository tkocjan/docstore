<?php
// Define path to application directory
defined('APPLICATION_PATH')
    //don't need /../application but keeping for compatibility
    || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 
        (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

error_log(__METHOD__.': APPLICATION_ENV='.APPLICATION_ENV);

require_once APPLICATION_PATH.'/constants.php';

$includePaths = array(
//    realpath(APPLICATION_PATH.'/../../libphp'),
    realpath(APPLICATION_PATH.'/../library'),
    get_include_path());

$iniFiles = array(
    APPLICATION_PATH.'/configs/application.ini',
    APPLICATION_PATH.'/configs/doctrine.ini');

set_include_path(implode(PATH_SEPARATOR, $includePaths));

/*
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH.'/configs/application.ini');
 */
require_once APPLICATION_PATH.'/MyApplication.php';
$application = new MyApplication(APPLICATION_ENV, array('config' => $iniFiles));
$application->bootstrap();
$application->run();
