<?php
namespace App\Components\QDmmmDB\Misc;

class QDLoggerCli{
	static $ok=true;
	function __construct(){
		self::$ok=(PHP_SAPI == 'cli');
	}
	public function log($m){
		if(self::$ok){
			db($m);
		}
	}
}