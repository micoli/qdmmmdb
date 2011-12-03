<?
//session_start();

class QDSvc{
	protected static $object = array();

	function addClass($id,&$obj){
		self::$object[$id] = $obj;
	}

	function getObj($id){
		return self::$object[$id];
	}

	static function run(){
		global $argv;
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
			$objId		= $arrArg[0];
			$methodName	= $arrArg[1];
			//if (!array_key_exists($objId,self::$object)){
			//	die(json_encode(call_user_func(array($t,'svc_'.$methodName))));
				//print 'object '.$objId.' not in session';
				//return;
			//}else{
			self::$object[$objId] = new $objId();
			if (!in_array('svc_'.$methodName,get_class_methods (get_class  (self::$object[$objId])))){
				print 'method svc_'.$methodName.' not in session object '.$objId.' of class '.get_class  ($objId);
				return;
			}
			header("Content-Type: application/json; charset: UTF-8",true);
			$result = call_user_func(array(self::$object[$objId],'svc_'.$methodName));
			$result = json_encode($result);
			//$result = strtr ($result, '’', '\'');
			die($result);
			//}
		}
	}
}
?>