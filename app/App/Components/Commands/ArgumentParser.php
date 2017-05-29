<?php
namespace App\Components\Commands;

final class ArgumentParser{

	public static function toBool($var) {
		if (is_bool($var)){
			return $var;
		}
		if (is_string($var)){
			return (bool) $var;
		}else{
			switch (strtolower($var)) {
				case '1':
				case 'true':
				case 'on':
				case 'yes':
				case 'y':
					return true;
				default:
					return false;
			}
		}
	}
}