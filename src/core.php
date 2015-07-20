<?php 
date_default_timezone_set('Europe/Moscow');
function __autoload($className){ 
	if(file_exists(TYM_PATH.$className.'.php')){ 
		require_once TYM_PATH.$className.'.php'; 
		return true;
	} 
	if(file_exists(TYM_PATH.'core/'.$className.'.php')){ 
		require_once TYM_PATH.'core/'.$className.'.php'; 
		return true;
	} 
	// for test use
	if(file_exists($className.'.php')){ 
		require_once $className.'.php'; 
		return true;
	} 
	if(file_exists('core/'.$className.'.php')){ 
		require_once 'core/'.$className.'.php'; 
		return true;
	} 
	return false; 
}
?>