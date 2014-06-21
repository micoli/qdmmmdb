<?php
	if(!defined('QD_PATH_CLASSES'	))	define ('QD_PATH_CLASSES'	,dirname(__FILE__).'/');
	if(!defined('QD_PATH_3RD_PHP'	))	define ('QD_PATH_3RD_PHP'	,realpath(dirname(__FILE__).'/../3rd_php').'/');
	if(!defined('QD_PATH_3RD_JS'	))	define ('QD_PATH_3RD_JS'	,realpath(dirname(__FILE__).'/../3rd_js').'/');
	if(!defined('QD_PATH_ROOT'		))	define ('QD_PATH_ROOT'		,realpath(dirname(__FILE__).'/../../').'/');
	if(!defined('QD_PATH_MODULES'	))	define ('QD_PATH_MODULES'	,realpath(dirname(__FILE__).'/../../modules').'/');

	//include "sessions.php";
	require QD_PATH_CLASSES.'QDServiceLocator.php';
	require QD_PATH_CLASSES.'QDSvc.php';
	require QD_PATH_3RD_PHP.'FirePHPCore/fb.php';

	function url_exists($url){
		if(!strstr($url, "http://")) {
			$url = "http://".$url;
		}
		$fp = @fsockopen($url, 80);
		if($fp === false) { return 'false'; } else { return true; };
	}

	function db($v){
		if(php_sapi_name()!='cli') print '<pre>'."\n";
		print_r($v);
		//print htmlentities(print_r($v,true));
		if(php_sapi_name()!='cli'){
			print '</pre>'."\n";
		}else{
			print "\n";
		}
	}

	function loadConf($path){
		$constants = get_defined_constants(true);
		$json_errors = array();
		foreach ($constants["json"] as $name => $value) {
			if (!strncmp($name, "JSON_ERROR_", 11)) {
				$json_errors[$value] = $name;
			}
		}
		$files = glob($path.'*.json');
		if (!array_key_exists('conf',$GLOBALS)){
			$GLOBALS['conf']=array();
		}
		foreach($files as $file){
			$ret = json_decode($cont = file_get_contents($file),true);
			if ($err = json_last_error()){
				print sprintf("<h1>ErrorConf : %s</h1>%s<br /><pre>%s</pre><hr />",$file,$json_errors[$err],print_r($cont,true));
			}else{
				$GLOBALS['conf'][str_replace('.json','',basename($file))]=$ret;
			}
		}
		//db($GLOBALS['conf']);
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
	function size_readable($size, $max = null, $si = true, $retstring = '%01.2f %s'){
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

	function myjson($s){
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

	function object2array($object){
		$return = NULL;
		if(is_array($object)){
			foreach($object as $key => $value)
				$return[$key] = object2array($value);
		}else{
			$var = @get_object_vars($object);
			if($var){
				foreach($var as $key => $value)
					$return[$key] = ($key && !$value) ? NULL : object2array($value);
			}
			else return $object;
		}
		return $return;
	}

	function array_key_exists_assign_default($k,$o,$d){
		if(!is_array($o)) return $d;
		if (array_key_exists($k,$o)) return $o[$k];
		return $d;
	}

	function akead($k,$o,$d){
		return array_key_exists_assign_default($k,$o,$d);
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
	function simpleXMLToArray(SimpleXMLElement $xml, $attributesKey=null, $childrenKey=null, $valueKey=null) {

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
			$value = simpleXMLToArray($child, $attributesKey, $childrenKey, $valueKey);
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

	loadConf(defined('CONF_ROOT')?CONF_ROOT:QD_PATH_ROOT.'conf/');
?>