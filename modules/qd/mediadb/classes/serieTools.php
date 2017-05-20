<?php

class serieTools{
	static $cleanedFilenameCache=array();

	public static function crossedCleanupSaisonTitleForMatch($serieFilename){
		$aResult = [];
		$aFilters=['removeLongTitle','removeCountry','removeApo','removeProvider'];
		foreach($aFilters as $sFilterName){
			$s=serieTools::$sFilterName($sSerieName);
			$aResult[]=$s;
			foreach($aFilters as $sFilterName2){
				if($sFilterName!= $sFilterName2){
					$aResult[]=self::$sFilterName2($s);
				}
			}
		}
		return array_unique($aResult);
	}

	public static function cleanupSaisonTitleForMatch($serieFilename){
		if(array_key_exists($serieFilename,self::$cleanedFilenameCache)){
			return self::$cleanedFilenameCache[$serieFilename];
		}
		$aFilters=['removeLongTitle','removeCountry','removeApo','removeProvider'];
		$serieFilename = str_replace('.'	,' ',$serieFilename);
		$serieFilename = str_replace('\''	,' ',$serieFilename);
		$serieFilename = str_replace('"'	,' ',$serieFilename);
		$serieFilename = str_replace(':'	,' ',$serieFilename);
		$serieFilename = strtolower($serieFilename);
		foreach($aFilters as $sFilterName){
			$serieFilename= self::$sFilterName($serieFilename);
		}
		$serieFilename = trim(strtolower($serieFilename));
		self::$cleanedFilenameCache[$serieFilename] = $serieFilename;
		return $serieFilename;
	}

	private static function removeApo($s){
		return str_replace ('\'','',$s);
	}

	private static function removeProvider($s){
		$m = [];
		if(preg_match('!\[(.*?)\] (.*)!',$s,$m)){
			print trim($m[2])."\n";
			return trim($m[2]);
		}
		return $s;
	}

	private static function removeCountry($s){
		$m = [];
		if(preg_match('!(.*?) (us|uk)$!i',$s,$m)){
			return $m[1];
		}
		if(preg_match('!(.*?) \(us|uk\)$!i',$s,$m)){
			return $m[1];
		}
		return $s;
	}

	private static function removeLongTitle($s){
		$m = [];
		if(preg_match('!(.*?) : (.*)!',$s,$m)){
			return $m[1];
		}
		return $s;
	}

	public static function extractLanguage($sTitle){
		$tags=[];

		if(!array_key_exists('year', QDSeriesbatch::$aAllTags)){
			for($i=1990;$i<=2050;$i++){
				QDSeriesbatch::$aAllTags['year'][]=$i;
			}
		}

		$sTitle	= ' '.preg_replace('!\.!',' ',$sTitle).' ';

		foreach(QDSeriesbatch::$aAllTags as $sTagType=>$aTag){
			foreach($aTag as $sTag){
				if (preg_match('! '.preg_quote($sTag,'!').' !',$sTitle)){
					$tags[$sTagType][]=$sTag;
					$sTitle = preg_replace('! '.preg_quote($sTag,'!').' !',' ',$sTitle);
				}
			}
		}
		$tags['language'] = array_map('strtolower',array_key_exists('language',$tags)?$tags['language']:[]);

		//specials tag
		if(strpos($sTitle,'-jmt')!==false){
			$tags['language'][]='french';
		}

		$tags['title']	= preg_replace('!\s{2,}!',' ',trim($sTitle));
		$tags['short_language'] = (count(array_intersect($tags['language'], ['french','jmt'])) > 0)?'FR':'VO';

		return $tags;
	}
}
