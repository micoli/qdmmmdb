<?php
class QDLoggerMom{
	static $cnx=null;
	static $queue=null;
	function __construct($aParam){
		self::$queue=$aParam['queue'];
		self::$cnx = new Stomp($aParam['uri']);
		self::$cnx->connect($aParam['user'],$aParam['password']);
	}
	public function log($m){
		self::$cnx->send(self::$queue, $m);
	}
}