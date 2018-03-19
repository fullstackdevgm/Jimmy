<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
date_default_timezone_set("Australia/Sydney");
ini_set("display_errors",1);
error_reporting(E_ALL & ~ E_WARNING & ~ E_STRICT & ~ E_NOTICE);
//error_reporting(E_ALL);
chdir(dirname(__DIR__));
//define('ZF_CLASS_CACHE', 'data/modulecache/classes.php.cache'); if (file_exists(ZF_CLASS_CACHE)) require_once ZF_CLASS_CACHE;

define('BASE_DIR', dirname(__FILE__));

/** Hack for Injecting Module Resources from Modules*/
if (isset($_GET['action']) && $_GET['action'] == "resource") {
    $module = $_GET['module'];
    $path   = $_GET['path'];
    $dir    = $_GET['dir'];

    $filetype = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimetypes = array(
        'html'   => "text/html",
        'xhtml'  => "text/html",
        'js' 	 => "text/javascript",
        'css'	 => "text/css",
        'jpg' 	 => "image/jpeg",
        'jpeg'	 => "image/jpeg",
        'png'    => "image/png",
        'pdf'    => "application/pdf",
        'svg'    => "application/svg",
    );

    if (!isset($mimetypes[$filetype]))
        die(sprintf("Unrecognized file extension '%s'. Supported extensions: %s.", htmlspecialchars($filetype, ENT_QUOTES), implode(", ", array_keys($mimetypes))));

    $currentDir  = realpath(".");
    $destination = realpath("module/$module/public/$path");

	if(!$module){
		    $destination = realpath($dir."/$path");
		if (!$destination)
        	die(sprintf("File not found: '%s'!", htmlspecialchars("resources/logos/public/$path", ENT_QUOTES)));

	}

	if (!$destination)
        die(sprintf("File not found: '%s'!", htmlspecialchars("module/$module/public/$path", ENT_QUOTES)));

    if (substr($destination, 0, strlen($currentDir)) != $currentDir)
            die(sprintf("Access to '%s' is not allowed!", htmlspecialchars($destination, ENT_QUOTES)));


    header(sprintf("Content-type: %s", $mimetypes[$filetype]));


    readfile($destination, FALSE);

	die();
}


// Setup autoloading
require 'init_autoloader.php';
if($_REQUEST && $_REQUEST['PHPSESSID']){
    $sess_id = $_REQUEST['PHPSESSID'];
	session_id($sess_id);
/*	$redirect = str_replace('?PHPSESSID='.$sess_id,'',$_SERVER['REQUEST_URI']);
	header('Location:'.$redirect);
	exit;
*/
}

//session_write_close();

// Run the application!
ignore_user_abort(false);
if(connection_aborted()){
    flush();
}


Zend\Mvc\Application::init(require 'config/application.config.php')->run();
