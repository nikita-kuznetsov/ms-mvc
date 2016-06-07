<?php
function __autoload($class_name)
{
	$filename = strtolower($class_name) . '.php';
	$file = SITE_PATH . 'classes' . DIRSEP . $filename;	
	
	if (file_exists($file) == false)	
		return false;
	include ($file);
}
?>