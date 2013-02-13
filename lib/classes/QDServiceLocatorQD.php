<?
class QDServiceLocatorQD implements QDLocator{
	protected $base = '.';
	public function __construct($directory='.')    {
	$this->base = (string) $directory;
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
				$this->arrModules[]=$v;
			}else{
				$this->recursPath($v);
			}
		}
	}
	public function getPath($class)    {
		$this->arrModules = array();
		$this->recursPath($this->base.'/modules');
		//$this->arrModules=glob($this->base.'/modules/*',GLOB_ONLYDIR);
		//foreach($this->arrModules as $k){
		//  $this->arrModules[]=$k.'/classes';
		//}
		array_unshift($this->arrModules,$this->base.'/lib/classes');
		array_unshift($this->arrModules,$this->base.'/lib/3rd_php');
		//print_r($this->arrModules);die();
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

?>