<?php
	chdir(dirname(__FILE__));
	if(!defined('QD_BASE'))			define ('QDBASE'			,realpath(dirname(__FILE__).'/lib').'/');
	if(!defined('QD_PATH_MODULES'))	define ('QD_PATH_MODULES'	,realpath(dirname(__FILE__)).'/');
	if(!defined('CONF_ROOT'))		define ('CONF_ROOT'			,dirname(__FILE__).'/conf/');

	header('Content-type: text/html; charset=UTF-8');
	require 'lib/classes/QDGlobal.php';

	QDSvc::run();
?>