<?php
namespace App\Components\QDmmmDB\Misc;

/**
 * Description of QDSession
 *
 * @author omichaud
 */
class QDSession {
	private static $session_started = false;

	private static function session_start(){
		if (!self::$session_started && session_id()==''){
			self::$session_started = true;
			session_start();
		}
	}

	public static function _set($key,$val){
		self::session_start();
		$_SESSION[$key] = $val;
	}

	public static function _unset($key){
		self::session_start();
		if(array_key_exists($key,$_SESSION)){
			unset($_SESSION[$key]);
		}
	}

	public static function _get($key){
		self::session_start();
		if(array_key_exists($key,$_SESSION)){
			return $_SESSION[$key];
		}else{
			return null;
		}
	}
	public static function _isset($key){
		self::session_start();
		return(array_key_exists($key,$_SESSION));
	}
}
