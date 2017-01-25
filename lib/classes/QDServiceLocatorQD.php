<?php
class QDServiceLocatorQD implements QDLocator{
	protected $base = '.';
	public function __construct($directory='.')    {
		$this->base = (string) $directory;
		if(defined('QDBASE')){
			$this->base = QDBASE;
		}
	}
	public function canLocate($class)    {
	$path = $this->getPath($class);
	return file_exists($path);
	}
	function recursPath($path){
		//print $path.'<br>';
		$t = glob($path.'/*',GLOB_ONLYDIR);
		foreach ($t as $v){
			if(preg_match('!\/classes!',$v)){
				$this->arrModules[]=realpath($v);
			}else{
				$this->recursPath($v);
			}
		}
	}
	public function getPath($class)    {
		$this->arrModules = array();
		$this->recursPath($this->base.'/modules');
		if(defined('QD_PATH_MODULES')){
			$this->recursPath(QD_PATH_MODULES);
			$this->recursPath(QD_PATH_MODULES.'/modules');
		}
		//$this->arrModules=glob($this->base.'/modules/*',GLOB_ONLYDIR);
		//foreach($this->arrModules as $k){
		//  $this->arrModules[]=$k.'/classes';
		//}
		array_unshift($this->arrModules,realpath($this->base.'/classes'));
		array_unshift($this->arrModules,realpath($this->base.'/3rd_php'));
		$rtn='';
		foreach ($this->arrModules as $k){
			if (file_exists($k.'/'.$class.'.php')){
				$rtn = $k.'/'.$class.'.php';
				break;
			}
		}
		return $rtn;
	}
}
QDServiceLocator::attachLocator(new QDServiceLocatorQD(), 'QD');
