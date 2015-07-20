<?php
date_default_timezone_set('Europe/Moscow');
define("TYM_PATH","./src/");
define("MOBI_SERIVCE_URL","http://10.0.31.111:8080/axis2/services/banking.bankingHttpSoap12Endpoint/");
define("MOBI_WSDL_LOCATION","http://10.0.31.111:8080/axis2/services/banking?wsdl");
require_once(TYM_PATH.'log4php/Logger.php');
function autoload($className) {
  if(isset(self::$classes[$className])) {
    include dirname(__FILE__) . self::$classes[$className];
  }
  else if(file_exists(TYM_PATH.$className.'.php')){
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
}
Logger::configure(dirname(__FILE__).'/config.xml');
$logger=Logger::getRootLogger();
?>
