<?php
class amiRedirectorEnqueuer{
	/**
	 * kill -9 $(ps aux | grep "local.batchIpbx.redirectorDequeuer" | egrep -v "(sh|grep)" | awk '{print $2}')
	 */
	static $classname		= null;
	static $AMISocket			= null;
	static $amiTimeout			= 5;
	static $debug				= false;
	static $amhost				= null;
	static $amuser				= null;
	static $ampassword			= null;
	static $momCnx				= null;
	static $dequeuerProcess		= null;
	static $dequeueurCmd		= null;
	static $dequeueurCmdParams	= null;
	static $dequeueurPipes		= array();

	public static function init($amhost,$amuser,$ampassword,$dequeueurCmd,$dequeueurCmdParams=array()){
		self::$classname			= __CLASS__;
		self::$amhost				= $amhost;
		self::$amuser				= $amuser;
		self::$ampassword			= $ampassword;
		self::$dequeueurCmd			= $dequeueurCmd;
		self::$dequeueurCmdParams	= $dequeueurCmdParams;
		db(array(
			'amhost'				=>self::$amhost		,
			'amuser'				=>self::$amuser		,
			'ampassword'			=>self::$ampassword	,
			'dequeueurCmd'			=>self::$dequeueurCmd,
			'dequeueurCmdParams'	=>self::$dequeueurCmdParams
		));
		self::connectDequeuer();
		self::connectAMI();
		print "init enqueuer done\n";
	}

	private static function connectDequeuer(){
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("file", "/tmp/error-output.txt", "a")
		);
		$cmd=self::$dequeueurCmd;
		foreach(self::$dequeueurCmdParams as $param=>$value){
			$cmd.=' '.$param.'='.$value;
		}

		self::$dequeuerProcess = proc_open($cmd, $descriptorspec, self::$dequeueurPipes);

		if (is_resource(self::$dequeuerProcess)) {
			echo "dequeuer ok: $cmd\n";
		}else{
			echo "dequeuer not ok: $cmd\n";
		}
	}

	private function connectAMI(){
		ini_set('default_socket_timeout', 100);
		$timeout=10;
		self::$AMISocket = @fsockopen(self::$amhost,"5038", $errno, $errstr, $timeout);
		if(is_resource(self::$AMISocket) && !feof(self::$AMISocket)){
			stream_set_blocking	(self::$AMISocket	, TRUE);
			stream_set_timeout	(self::$AMISocket	, self::$amiTimeout);
			self::amiWrite(sprintf('Action: %s'		, 'Login'));
			self::amiWrite(sprintf('UserName: %s'	, self::$amuser));
			self::amiWrite(sprintf('Secret: %s'		, self::$ampassword));
			self::amiWrite("");
			self::pipeWrite("Event: redirectorStarted\n\n");
		}
	}

	private static function amiWrite($str){
		fputs(self::$AMISocket, $str."\r\n");
	}

	private static function pipeWrite($str){
		$pstatus = proc_get_status(self::$dequeuerProcess);
		if (!$pstatus["running"]){
			self::connectDequeuer();
		}
		fputs(self::$dequeueurPipes[0],trim($str)."\n");
	}

	public static function runEvents(){
		self::$debug=true;
		while(true){
			//while(($buffer = fgets(self::$AMISocket, 4096))!== FALSE){
				/*$info = stream_get_meta_data(self::$AMISocket);
				if($info['timed_out']){
					fputs(self::$dequeueurPipes[0],"Event:smtick\n\n");
				}elseif($info['eof']){
					break;
				}else{
					fputs(self::$dequeueurPipes[0],trim($buffer)."\n");
				}
				print ".";
				*/
				while (!feof(self::$AMISocket)) {
					$buffer	= fgets(self::$AMISocket, 4096);
					$info	= stream_get_meta_data(self::$AMISocket);
					if($info['timed_out']){
						self::amiWrite("Action: Ping\n");
					}elseif($info['eof']){
						break;
					}else{
						self::pipeWrite($buffer);
					}
					print ".";
				}
			//}
			while(!is_resource(self::$AMISocket) || feof(self::$AMISocket)){
				if(self::$debug){
					print date('Y-m-d H:i:s')." reconnecting AMI.....\n";
				}
				sleep(1);
				self::connectAMI();
			}
		}
	}
}
