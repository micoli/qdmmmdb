<?php
class QDServiceLocatorCOMPOSER implements QDLocator{
	protected $base = '.';
	public function __construct($directory='.')    {
		$this->base = (string) $directory;
		if(defined('QDBASE')){
			$this->base = QDBASE;
		}
		$this->arrModules = array();
		$this->recursPath($this->base.'/modules');

		if(defined('QD_PATH_MODULES')){
			$this->recursPath(QD_PATH_MODULES);
		}
		foreach ($this->arrModules as $v){
			$loader = require $v.'/autoload.php';
			//$loader->add('Acme\\Test\\', __DIR__);
			//db($loader);
		}
	}

	function recursPath($path){
		$t = glob($path.'/*',GLOB_ONLYDIR);

		foreach ($t as $v){
			$this->recursPath($v);
		}

		if(file_exists($path.'/composer.json') && file_exists($path.'/vendor') && file_exists($path.'/vendor/autoload.php')){
			$this->arrModules[]=realpath($v);
		}
	}
	public function getPath($class)    {
	}
	public function canLocate($class)    {
		return false;
	}
}
QDServiceLocator::attachLocator(new QDServiceLocatorCOMPOSER(), 'COMPOSER');

?>