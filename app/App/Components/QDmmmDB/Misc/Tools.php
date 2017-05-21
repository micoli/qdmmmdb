<?php
namespace App\Components\QDmmmDB\Misc;

class Tools{
	public static function url_exists($url){
		if(!strstr($url, "http://")) {
			$url = "http://".$url;
		}
		$fp = @fsockopen($url, 80);
		if($fp === false) { return 'false'; } else { return true; };
	}

	/**
	 * @param byte $bytes
	 * @return string
	 * http://php.net/manual/en/function.disk-total-space.php#tularis at php dot net
	 */
	public static function getSymbolByQuantity($bytes) {
		$symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$exp = $bytes ? (floor(log($bytes)/log(1024))):0;

		$str=sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
		return $str;
	}

	/**
	 * Return human readable sizes
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.2.0
	 * @link        http://aidanlister.com/repos/v/function.size_readable.php
	 * @param       int     $size        size in bytes
	 * @param       string  $max         maximum unit
	 * @param       bool    $si          use SI (1000) prefixes
	 * @param       string  $retstring   return string format
	 */
	public static function size_readable($size, $max = null, $si = true, $retstring = '%01.2f %s'){
		// Pick units
		if ($si === true) {
			$sizes = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
			$mod   = 1000;
		} else {
			$sizes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
			$mod   = 1024;
		}

		// Max unit to display
		if ($max && false !== $d = array_search($max, $sizes)) {
			$depth = $d;
		} else {
			$depth = count($sizes) - 1;
		}

		// Loop
		$i = 0;
		while ($size >= 1024 && $i < $depth) {
			$size /= $mod;
			$i++;
		}

		return sprintf($retstring, $size, $sizes[$i]);
	}

	public static function myjson($s){
		if(is_numeric($s)) return $s;
		if(is_string($s)) return '"'.addslashes (utf8_encode($s)).'"';
		/*if(is_string($s))
		return preg_replace(
		"@([\1-\037])@e",
		"sprintf('\\\\u%04X',ord('$1'))",
		str_replace("\0", '\u0000',
		utf8_decode(json_encode(utf8_encode($s))))
		);
		 */
		if($s===false) return 'false';
		if($s===true) return 'true';
		if(is_array($s))
		{
			$c=0;
			foreach($s as $k=>&$v)
				if($k !== $c++)
				{
					foreach($s as $k=>&$v) $v = myjson((string)$k).':'.myjson($v);
					return '{'.join(',', $s).'}';
				}
			return '[' . join(',', array_map('myjson', $s)) . ']';
		}
		return 'null';
	}

	public static function object2array($object){
		$return = NULL;
		if(is_array($object)){
			foreach($object as $key => $value)
				$return[$key] = self::object2array($value);
		}else{
			$var = @get_object_vars($object);
			if($var){
				foreach($var as $key => $value)
					$return[$key] = ($key && !$value) ? NULL : self::object2array($value);
			}
			else return $object;
		}
		return $return;
	}

	public static function array_key_exists_assign_default($k,$o,$d){
		if(!is_array($o)) return $d;
		if (array_key_exists($k,$o)) return $o[$k];
		return $d;
	}

	/**
	 * Converts a simpleXML element into an array. Preserves attributes.<br/>
	 * You can choose to get your elements either flattened, or stored in a custom
	 * index that you define.<br/>
	 * For example, for a given element
	 * <code>
	 * <field name="someName" type="someType"/>
	 * </code>
	 * <br>
	 * if you choose to flatten attributes, you would get:
	 * <code>
	 * $array['field']['name'] = 'someName';
	 * $array['field']['type'] = 'someType';
	 * </code>
	 * If you choose not to flatten, you get:
	 * <code>
	 * $array['field']['@attributes']['name'] = 'someName';
	 * </code>
	 * <br>__________________________________________________________<br>
	 * Repeating fields are stored in indexed arrays. so for a markup such as:
	 * <code>
	 * <parent>
	 *     <child>a</child>
	 *     <child>b</child>
	 *     <child>c</child>
	 * ...
	 * </code>
	 * you array would be:
	 * <code>
	 * $array['parent']['child'][0] = 'a';
	 * $array['parent']['child'][1] = 'b';
	 * ...And so on.
	 * </code>
	 * @param simpleXMLElement    $xml            the XML to convert
	 * @param boolean|string    $attributesKey    if you pass TRUE, all values will be
	 *                                            stored under an '@attributes' index.
	 *                                            Note that you can also pass a string
	 *                                            to change the default index.<br/>
	 *                                            defaults to null.
	 * @param boolean|string    $childrenKey    if you pass TRUE, all values will be
	 *                                            stored under an '@children' index.
	 *                                            Note that you can also pass a string
	 *                                            to change the default index.<br/>
	 *                                            defaults to null.
	 * @param boolean|string    $valueKey        if you pass TRUE, all values will be
	 *                                            stored under an '@values' index. Note
	 *                                            that you can also pass a string to
	 *                                            change the default index.<br/>
	 *                                            defaults to null.
	 * @return array the resulting array.
	 */
	public static function simpleXMLToArray(\SimpleXMLElement $xml, $attributesKey=null, $childrenKey=null, $valueKey=null) {

		if ($childrenKey && !is_string($childrenKey)) {
			$childrenKey = '@children';
		}
		if ($attributesKey && !is_string($attributesKey)) {
			$attributesKey = '@attributes';
		}
		if ($valueKey && !is_string($valueKey)) {
			$valueKey = '@values';
		}

		$return = array();
		$name = $xml->getName();
		$_value = trim((string) $xml);
		if (!strlen($_value)) {
			$_value = null;
		};

		if ($_value !== null) {
			if ($valueKey) {
				$return[$valueKey] = $_value;
			} else {
				$return = $_value;
			}
		}

		$children = array();
		$first = true;
		foreach ($xml->children() as $elementName => $child) {
			$value = self::simpleXMLToArray($child, $attributesKey, $childrenKey, $valueKey);
			if (isset($children[$elementName])) {
				if (is_array($children[$elementName])) {
					if ($first) {
						$temp = $children[$elementName];
						unset($children[$elementName]);
						$children[$elementName][] = $temp;
						$first = false;
					}
					$children[$elementName][] = $value;
				} else {
					$children[$elementName] = array($children[$elementName], $value);
				}
			} else {
				$children[$elementName] = $value;
			}
		}
		if ($children) {
			if ($childrenKey) {
				$return[$childrenKey] = $children;
			} else {
				$return = array_merge($return, $children);
			}
		}

		$attributes = array();
		foreach ($xml->attributes() as $name => $value) {
			$attributes[$name] = trim($value);
		}
		if ($attributes) {
			if ($attributesKey) {
				$return[$attributesKey] = $attributes;
			} else {
				$return = array_merge($return, $attributes);
			}
		}

		return $return;
	}
}
