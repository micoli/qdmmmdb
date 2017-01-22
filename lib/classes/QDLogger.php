<?php
class QDLogger{
	static $aLogger=array();
	static function init($aParams){
		foreach($aParams as $k=>$v){
			$type = 'QDLogger'.ucfirst($k);
			if(class_exists($type)){
				self::$aLogger[$k]=new $type($v);
			}else{
				print "\n $type dos not exists";
			}
		}
	}
	public function log($m){
		foreach(self::$aLogger as $logger){
			$logger->log($m);
		}
	}
}