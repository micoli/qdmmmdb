<?php
include __DIR__ . '/../vendor/autoload.php';

use App\RoutesLoader;
use App\ServicesLoader;
use Silex\Provider\MonologServiceProvider;
use SM\SilexRestApi\Security\User\UserProvider;
use SM\SilexRestApi\Silex\Application;
use Symfony\Component\Config\Definition\Exception\Exception;
use SM\SilexRestApi\Services\ApiServiceProvider;
use Moust\Silex\Provider\CacheServiceProvider;
use Silex\Provider\TwigServiceProvider;

//use Knp\Provider\ConsoleServiceProvider;

function db($o,$withTrace=false){
	header('Content-type: text/html');
	print "<pre>";
	if($withTrace){
		$e = new Exception();
		$aExpl=explode("\n",$e->getTraceAsString());
		print sprintf('[%s,%s]',$aExpl[1],$aExpl[2]);
	}
	print_r($o);
	print "</pre>";
}

$app = new Application();

$gblCfg= include 'config/config.php';

$app->register(new WyriHaximus\SliFly\FlysystemServiceProvider(), [
	'flysystem.filesystems' => [
		'local__DIR__' => [
			'adapter' => 'League\Flysystem\Adapter\Local',
			'args' => [VAR_PATH]
		]
	]
]);

$app->register(new MonologServiceProvider(), array(
	'monolog.logfile'	=> $gblCfg['log.file'],
	'monolog.level'		=> $gblCfg['log.level'],
	'monolog.name'		=> $gblCfg['application.name']
));
if(array_key_exists('caches.default',$gblCfg)){
	$app['caches.default'] = $gblCfg['caches.default'];
}
$app->register(new CacheServiceProvider(), array(
	'caches.options' => $gblCfg['caches.options']
));

$app->register(new ApiServiceProvider());

/*
$app->register(new ConsoleServiceProvider(),array(
	'console.name'				=> $gblCfg['application.name'],
	'console.version'			=> $gblCfg['application.version'],
	'console.project_directory'	=> __DIR__
));
*/
/*
$app['security.users'] = $app->share(function () use ($app) {
	return new UserProvider('App\ORM\TAuthQuery','AutLogin','AutPassword', 'AutFonction' );
} );

$app['jwtoken.config']=$gblCfg['jwtoken.config'];
$app ['security.firewalls'] = array (
	'login' => array(
		'pattern'	=> 'auth',
		'anonymous'	=> true
	),
	'apidoc' => array(
		'pattern'	=> '^/api/$',
		'anonymous'	=> true
	),
	'secured' => array (
		'pattern'	=> '^/api.*$',
		'users'		=> $app['security.users'],
		'anonymous'	=> false,
		'stateless'	=> true,
		'jwtoken'	=> true
	)
);
*/
$app ['security.firewalls']=array(
	'open' => array (
		'anonymous'	=> true
	)
);
$app->register(new ServicesLoader());

$app['smroutesloader.path'] = $gblCfg['smroutesloader.path'];
$app->register(new RoutesLoader());

return $app;
