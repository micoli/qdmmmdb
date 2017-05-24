<?php

define('ROOT_PATH',realpath(dirname(__FILE__).'/..'));
define('APP_PATH',realpath(dirname(__FILE__).'/../App'));
define('VAR_PATH',realpath('/var/tmp/'));
define('CFG_PATH',realpath(dirname(__FILE__)));
define('VENDOR_PATH',realpath(dirname(__FILE__).'/../../vendor'));

require dirname(__FILE__)."/../App/Components/QDmmmDB/Tools/QDGlobal.php";
App\Components\QDmmmDB\Misc\loadConf(dirname(__FILE__).'/json/');

$app['debug'					] = true;
$gblCfg['application.name'		] = 'App';
$gblCfg['application.version'	] = '0.1.0';
$gblCfg['log.level'				] = Monolog\Logger::DEBUG;
$gblCfg['log.file'				] = VAR_PATH	. '/qdmmmdb-app.log';
$gblCfg['cache.dir'				] = VAR_PATH	. '/cache/';
$gblCfg['smroutesloader.path'	] = CFG_PATH	. '/routes/';

$gblCfg['caches.options'		]=array(
	'filesystem' => array (
		'driver'	=> 'file',
		'cache_dir'	=> $gblCfg ['cache.dir']
	),
	'apc' => array (
		'driver'	=> 'apc'
	)
);
$gblCfg['caches.default']='filesystem';

return $gblCfg;
