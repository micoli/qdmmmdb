<?php
//session_start();

class QDSvc{
	protected static $object = array();

	function addClass($id,&$obj){
		self::$object[$id] = $obj;
	}

	function getObj($id){
		return self::$object[$id];
	}
	static function utf8ize($mixed) {
		//return $mixed;
		if (is_array($mixed)) {
			    foreach ($mixed as $key => $value) {
					        $mixed[$key] = self::utf8ize($value);
							    }
		} else if (is_string ($mixed)) {
			    return utf8_encode($mixed);
		}
		return $mixed;
	}

	static function run(){
		global $argv;
		if(defined('SMF_COMPATIBLE')&&SMF_COMPATIBLE){
			$smfCompatible=true;
		}else{
			$smfCompatible=false;
		}
		date_default_timezone_set('Europe/Paris');
		if(isset($argv)){
			$_SERVER['SERVER_NAME']='local';
			foreach ($argv as $k=>$arg){
				if ($k>0){
					$t = split("=",$arg,2);
					$_REQUEST[$t[0]]=$t[1];
				}
			}
		}
		error_reporting(E_ERROR | E_WARNING | E_PARSE );
		if ($_REQUEST['exw_action']){
			$arrArg		= split('\.',$_REQUEST['exw_action']);
			if($smfCompatible){
				$objId		= 'svc'.ucfirst($arrArg[1]);
				$methodName	= $arrArg[2];
			}else{
				$objId		= $arrArg[0];
				$methodName	= $arrArg[1];
			}

			self::$object[$objId] = new $objId();

			if (!in_array(($smfCompatible?'pub_':'svc_').$methodName,get_class_methods (get_class  (self::$object[$objId])))){
				print 'method <b>'.($smfCompatible?'pub_':'svc_').$methodName.'</b> not in session object <b>'.$objId.'</b> of class <b>'.get_class  ($objId).'</b>';
				return;
			}
			$output_mode = strtolower(array_key_exists_assign_default('output_mode', $_REQUEST, 'json'));

			if(!(defined('SMF_CLI') && SMF_CLI)){
				switch ($output_mode){
					case 'json' :
						header("Content-Type: application/json; charset: UTF-8",true);
					break;
					case 'html' :
						header('content-type:text/html');
					break;
				}
			}

			if($smfCompatible){
				$result = call_user_func(array(self::$object[$objId],($smfCompatible?'pub_':'svc_').$methodName),$_REQUEST);
			}else{
				$result = call_user_func(array(self::$object[$objId],($smfCompatible?'pub_':'svc_').$methodName));
			}

			switch ($output_mode){
				case 'json' :
					$result = json_encode(self::utf8ize($result));
					//$result = json_encode($result);
					//print json_last_error() ;
				break;
				case 'html' :
				break;
			}
			die($result);
		}
	}
}
