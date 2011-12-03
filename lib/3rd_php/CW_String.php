<?php
	class CW_String {
		function pregExtract($preg,$txt,$idx){
			if (preg_match($preg,$txt,$m)){
				return $m[$idx];
			}
			return '';
		}

		function replaceMultiSpace($str){
			$str =preg_replace("/[[:blank:]]+/"," ", $str);
			$str =preg_replace("/�+/"," ", $str);
			return trim($str);
		}

		function delmulspace($str) {
			do {
					$str = str_replace("  ", " ", $str);
			} while (strpos($str, "  ") > 0);
			return $str;
		}
	}
?>
