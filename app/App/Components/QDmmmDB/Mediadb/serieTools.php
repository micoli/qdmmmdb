<?php
namespace App\Components\QDmmmDB\Mediadb;

use App\Components\QDmmmDB\Mediadb\Series\SeriesBatch;

class serieTools
{

	static $cleanedFilenameCache = array();

	private static $aFilters = [
		'removeAccent',
		'removeLongTitle',
		'removeCountry',
		'removeApo',
		'removeProvider',
		'removeYear'
	];

	public static function crossedCleanupSaisonTitleForMatch($serieFilename)
	{
		$aResult = [];
		$serieFilename = strtolower($serieFilename);
		foreach (self::$aFilters as $sFilterName) {
			$s = serieTools::$sFilterName(trim($serieFilename));
			$aResult[] = $s;
			foreach (self::$aFilters as $sFilterName2) {
				if ($sFilterName != $sFilterName2) {
					$aResult[] = self::$sFilterName2(trim($s));
				}
			}
		}
		return array_unique($aResult);
	}

	public static function cleanupSaisonTitleForMatch($serieFilename)
	{
		$serieFilename = strtolower($serieFilename);

		if (array_key_exists($serieFilename, self::$cleanedFilenameCache)) {
			return self::$cleanedFilenameCache[$serieFilename];
		}
		$serieFilename = str_replace('.', ' ', $serieFilename);
		$serieFilename = str_replace('\'s ', 's ', $serieFilename);
		$serieFilename = str_replace('\'', ' ', $serieFilename);
		$serieFilename = str_replace('"', ' ', $serieFilename);
		$serieFilename = str_replace(':', ' ', $serieFilename);

		foreach (self::$aFilters as $sFilterName) {
			$serieFilename = self::$sFilterName(trim($serieFilename));
		}
		$serieFilename = trim($serieFilename);
		self::$cleanedFilenameCache[$serieFilename] = $serieFilename;
		return $serieFilename;
	}

	private function removeAccent($str, $charset='utf-8')
	{
		$str = htmlentities($str, ENT_NOQUOTES, $charset);

		$str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

		return $str;
	}

	private static function removeApo($s)
	{
		return str_replace('\'', '', $s);
	}

	private static function removeProvider($s)
	{
		$m = [];
		if (preg_match('!\[(.*?)\] (.*)!', $s, $m)) {
			return trim($m[2]);
		}
		return $s;
	}

	private static function removeCountry($s)
	{
		$m = [];
		if (preg_match('!(.*?) (us|uk)$!i', $s, $m)) {
			return $m[1];
		}
		if (preg_match('!(.*?) \(us|uk\)$!i', $s, $m)) {
			return $m[1];
		}
		return $s;
	}

	private static function removeLongTitle($s)
	{
		$m = [];
		if (preg_match('!(.*?) : (.*)!', $s, $m)) {
			return $m[1];
		}
		return $s;
	}

	private static function removeYear($s)
	{
		$m = [];
		if (preg_match('!(.*?) \(\d{4}\)!', $s, $m)) {
			return $m[1];
		}
		if (preg_match('!(.*?) \d{4}!', $s, $m)) {
			return $m[1];
		}
		return $s;
	}

	public static function extractLanguage($sTitle)
	{
		$tags = [];

		if (! array_key_exists('year', SeriesBatch::$aAllTags)) {
			for ($i = 1990; $i <= 2050; $i ++) {
				SeriesBatch::$aAllTags['year'][] = $i;
			}
		}

		$sTitle = ' ' . preg_replace('!\.!', ' ', $sTitle) . ' ';

		foreach (SeriesBatch::$aAllTags as $sTagType => $aTag) {
			foreach ($aTag as $sTag) {
				if (preg_match('! ' . preg_quote($sTag, '!') . ' !', $sTitle)) {
					$tags[$sTagType][] = $sTag;
					$sTitle = preg_replace('! ' . preg_quote($sTag, '!') . ' !', ' ', $sTitle);
				}
			}
		}
		$tags['language'] = array_map('strtolower', array_key_exists('language', $tags) ? $tags['language'] : []);

		// specials tag
		if (strpos($sTitle, '-jmt') !== false) {
			$tags['language'][] = 'french';
		}

		$tags['title'] = preg_replace('!\s{2,}!', ' ', trim($sTitle));
		$tags['short_language'] = (count(array_intersect($tags['language'], [
			'french',
			'jmt'
		])) > 0) ? 'FR' : 'VO';

		return $tags;
	}
}
