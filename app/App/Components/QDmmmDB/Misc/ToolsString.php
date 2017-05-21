<?php
namespace App\Components\QDmmmDB\Misc;

class ToolsString {
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

	/**
	 * Indents a flat JSON string to make it more human-readable.
	 * http://recursive-design.com/blog/2008/03/11/format-json-with-php/
	 *
	 * @param string $json The original JSON string to process.
	 *
	 * @return string Indented version of the original JSON string.
	 */
	static function indentJSON($json) {
		$json			= str_replace(Array("\n","\r"),"",$json);
		$result			= '';
		$pos			= 0;
		$strLen			= strlen($json);
		$indentStr		= '  ';
		$newLine		= "\n";
		$prevChar		= '';
		$outOfQuotes	= true;

		for ($i=0; $i<=$strLen; $i++) {
			// Grab the next character in the string.
			$char = substr($json, $i, 1);
			$nextchar = substr($json, $i+1, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;
				// If this character is the end of an element,
				// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			$prevChar = $char;
		}
		return $result;
	}
	//http://stackoverflow.com/questions/6054033/pretty-printing-json-with-php
	function prettyPrint( $json ){
		$result				= '';
		$level				= 0;
		$prev_char			= '';
		$in_quotes			= false;
		$ends_line_level	= NULL;
		$json_length		= strlen( $json );
		$indentifierLen		= 0;

		for( $i = 0; $i < $json_length; $i++ ) {
			$char	= $json[$i  ];
			$nchar	= $json[$i+1];
			$pchar	= $json[$i-1];
			$new_line_level = NULL;
			$post = "";
			if( $ends_line_level !== NULL ) {
				$new_line_level = $ends_line_level;
				$ends_line_level = NULL;
			}
			if( $char === '"' && $prev_char != '\\' ) {
				$in_quotes = !$in_quotes;
				if($in_quotes){
					$indentifierLen=0;
				}
			}else if(!$in_quotes ) {
				switch( $char ) {
					case '}':
					case ']':
						if($char==']'){
							if($pchar!='}'){
								$level--;
								$ends_line_level = NULL;
								$new_line_level = $level;
							}
						}else{
							$level--;
							$ends_line_level = NULL;
							$new_line_level = $level;
						}
					break;

					case '{':
					case '[':
						if($char=='['){
							if($nchar!='{'){
								$level++;
								$ends_line_level = $level;
							}
						}else{
							$level++;
							$ends_line_level = $level;
						}
					break;
					case ',':
						if($char==','){
							if($nchar!='{'){
								$ends_line_level = $level;
							}
						}else{
							$ends_line_level = $level;
						}
					break;

					case ':':
						$s="";
						if($indentifierLen<=16){
							$s=str_repeat("\t",floor((16-$indentifierLen)/4));
						}
						$char=$s.":";
						$post = " ";
						$indentifierLen=0;
					break;

					case " ":
					case "\t":
					case "\n":
					case "\r":
						$char = "";
						$ends_line_level = $new_line_level;
						$new_line_level = NULL;
					break;
				}
			}else{
				$indentifierLen++;
			}
			if( $new_line_level !== NULL ) {
				$result .= "\n".str_repeat( "\t", $new_line_level );
			}
			$result .= $char.$post;
			$prev_char = $char;
		}

		return $result;
	}
}
