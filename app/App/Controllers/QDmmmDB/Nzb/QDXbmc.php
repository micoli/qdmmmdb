<?php

/**
 * Description of QDXbmc
 *
 * @author omichaud
 */
class QDXbmc {
    /**
    * http://forum.xbmc.org/showthread.php?t=85445
    *
    * @author  narfight
    */
	function thumbnailHash($input){
		$chars = strtolower($input);
		$crc = 0xffffffff;
		for ($ptr = 0; $ptr < strlen($chars); $ptr++)
		{
			$chr = ord($chars[$ptr]);
			$crc ^= $chr << 24;
			for ($i=0; $i<8; $i++)
			{
				if ($crc & 0x80000000)
				{
					$crc = ($crc << 1) ^ 0x04C11DB7;
				}
				else
				{
					$crc <<= 1;
				}
			}
		}
		//Formatting the output in a 8 character hex
		if ($crc>=0)
		{
			return sprintf("%08s",sprintf("%x",sprintf("%u",$crc)));
		}
		else
		{
			$Source = sprintf('%b', $crc);
			$StrConvert = "";
			while ($Source <> "")
			{
				$Digit = substr($Source, -4, 4);
				$Source = substr($Source, 0, -4);
				$StrConvert = base_convert($Digit, 2, 16) . $StrConvert;
			}

			return $StrConvert;
		}
	}

}

?>