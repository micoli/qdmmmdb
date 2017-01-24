<?php
// bwping  -b 1024 -s 3600 -v 450000 www.micoli.org

class QDSabnzbdProxy{
	var $QDNet;

	function __construct() {
		$this->QDNet = new QDNet();
		$this->sabnzbd_root		= $GLOBALS['conf']['sabnzbd']['root'];
		$this->sabnzbd_user		= $GLOBALS['conf']['sabnzbd']['username'];
		$this->sabnzbd_password	= $GLOBALS['conf']['sabnzbd']['password'];
	}

	function pri_sendCommand($arrCmd){
		$arrCmd = array_merge(array(
			'output'		=> 'json',
			'ma_username'	=> $this->sabnzbd_user,
			'ma_password'	=> $this->sabnzbd_password
		),$arrCmd);
		$url = sprintf("%sapi?%s",$this->sabnzbd_root,http_build_query($arrCmd));
		$tmp = $this->QDNet->getUrl($url);
		if ($arrCmd['mode']=="queue" && array_key_exists('name',$arrCmd)){
			
		}
		return $tmp;
	}

	function svc_getSpeeds(){
		return array(
			'speed'=>array(
				array('s'=> 200),
				array('s'=> 300),
				array('s'=> 400),
				array('s'=> 500),
				array('s'=> 600),
				array('s'=> 700),
				array('s'=> 800),
				array('s'=> 99999)
			)
		);
	}

	function svc_setSpeed(){
		return $this->pri_sendCommand(array(
			'mode'	=> 'config',
			'name'	=> 'speedlimit',
			'value'	=> $_REQUEST['value'],
		));
	}

	function svc_action(){
		$arr = array();
		foreach($_REQUEST as $k=>$v){
			if (preg_match('!^sab_(.*)!',$k,$m)){
				$arr[$m[1]] = $v;
			}
		}
		$tmp = $this->pri_sendCommand($arr);
		if (array_key_exists('obj_return',$_REQUEST)){
			$tmp2 = json_decode($tmp);
			$objreturn = $_REQUEST['obj_return'];
			if (isset($tmp2->$objreturn->slots)){
				foreach($tmp2->$objreturn->slots as $k=>&$v){
					$v->icondwn=($v->status=='Paused')?'pause':'play';
				}
			}
			return object2array($tmp2->$objreturn);
		}else{
			return $tmp;
		}
	}
}
?>