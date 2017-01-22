<?php
class amiRedirectorDequeuer{
	static $classname		= null;

	static $outputFd		= null;
	static $momCnx			= null;
	static $debug			= false;

	public static function init($momCnx=null){
		self::$classname	= __CLASS__;
		self::$outputFd		= fopen('/tmp/dequeue.txt','a');

		if($momCnx){
			self::$momCnx=$momCnx;
			//parent::initMOM($momCnx);
		}
	}

	private function connectFifo($force=false){
		if($force || feof(self::$fifoFd)){
			print "conenct1 ".self::$fifoPath;
			self::$fifoFd=fopen(self::$fifoPath,'w');
			print "conenct2";
			db(self::$fifoFd);
		}
	}

	private static function onEvent($str){
		$event = array();
		foreach(explode("\n",trim($str)) as $line){
			list($k,$v)=explode(':',$line,2);
			$event[$k]=trim($v);
		}
		fputs(self::$outputFd,json_encode($event)."\n");
	}

	public static function runEvents(){
		self::$debug=true;
		while(true){
			$eventBuffer='';
			while(($buffer = fgets(STDIN, 4096))!== FALSE){
				if($buffer=="\n"){
					self::onEvent($eventBuffer);
					$eventBuffer='';
				}else{
					$eventBuffer.=$buffer;
				}
			}
		}
	}
}