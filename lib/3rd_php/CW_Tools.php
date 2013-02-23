<?php

class CW_Tools{
	/**
	 * @param byte $bytes
	 * @return string
	 * http://php.net/manual/en/function.disk-total-space.php#tularis at php dot net
	 */
	function getSymbolByQuantity($bytes) {
		$bytes = (int)$bytes;
		$symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$exp = $bytes ? (floor(log($bytes)/log(1024))):0;

		return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
	}

}
?>