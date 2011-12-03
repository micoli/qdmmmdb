<?php
	chdir(dirname(__FILE__));
	define ('QDBASE',dirname(__FILE__));
	header('Content-type: text/html; charset=UTF-8');
	require 'lib/classes/QDGlobal.php';
	QDSvc::run();	
?>