<?
class QDServiceLocatorMAGPIE implements QDLocator{
	protected $base = '.';
	public function __construct($directory='.')  {
		$this->base = (string) $directory;
	}
	public function canLocate($class)  {
		$path = $this->getPath($class);
		if (file_exists($path)) return true;
		else return false;
	}
	public function getPath($class) {
		$fileName = 'RSS'.ucFirst(str_replace('rss_','',$class)).'.inc';
		$arrModules=glob($this->base.'/lib/3rd_php/magpierss*',GLOB_ONLYDIR);
		$rtn='';
		foreach ($arrModules as $k){
			if (file_exists($k.'/'.$fileName)){
				$rtn = $k.'/'.$fileName;
				break;
			}
		}
		return $rtn;
	}
}
QDServiceLocator::attachLocator(new QDServiceLocatorMAGPIE(), 'MAGPIE');
?>