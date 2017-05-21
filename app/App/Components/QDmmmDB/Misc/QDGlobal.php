<?php
namespace App\Components\QDmmmDB\Misc;

if(!defined('QD_PATH_CLASSES'	))	define ('QD_PATH_CLASSES'	,dirname(__FILE__).'/');
if(!defined('QD_PATH_3RD_PHP'	))	define ('QD_PATH_3RD_PHP'	,realpath(dirname(__FILE__).'/../3rd_php').'/');
if(!defined('QD_PATH_3RD_JS'	))	define ('QD_PATH_3RD_JS'	,realpath(dirname(__FILE__).'/../3rd_js').'/');
if(!defined('QD_PATH_ROOT'		))	define ('QD_PATH_ROOT'		,realpath(dirname(__FILE__).'/../../').'/');
if(!defined('QD_PATH_MODULES'	))	define ('QD_PATH_MODULES'	,realpath(dirname(__FILE__).'/../../modules').'/');

QDLogger::init(array(
	'cli'=>array(),
	/*'mom'=>array(
		'uri'=>"tcp://network.home.micoli.org:61613",
		'user'=>'guest',
		'password'=>'guest',
		'queue'=>'/topic/qdmmmdb'
	),*/
));

function db($v){
	if(php_sapi_name() != "cli") print '<pre>'."\n";
	print_r($v);
	if(php_sapi_name() == "cli") print "\n";
	//print htmlentities(print_r($v,true));
	if(php_sapi_name() != "cli") print '</pre>'."\n";
}

function loadConf($path){
	$constants = get_defined_constants(true);
	$json_errors = array();
	foreach ($constants["json"] as $name => $value) {
		if (!strncmp($name, "JSON_ERROR_", 11)) {
			$json_errors[$value] = $name;
		}
	}
	$files = glob($path.'*.json');
	if (!array_key_exists('conf',$GLOBALS)){
		$GLOBALS['conf']=array();
	}
	foreach($files as $file){
		$ret = json_decode($cont = file_get_contents($file),true);
		if ($err = json_last_error()){
			print sprintf("<h1>ErrorConf : %s</h1>%s<br /><pre>%s</pre><hr />",$file,$json_errors[$err],print_r($cont,true));
		}else{
			$GLOBALS['conf'][str_replace('.json','',basename($file))]=$ret;
		}
	}
	//db($GLOBALS['conf']);
}

