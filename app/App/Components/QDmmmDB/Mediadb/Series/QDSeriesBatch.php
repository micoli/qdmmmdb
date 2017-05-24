<?php
namespace App\Components\QDmmmDB\Mediadb\Series;
include "BEncode.php";
use Bhutanio\BEncode\BEncode;
use App\Components\QDmmmDB\Misc\ToolsFiles;

class QDSeriesBatch extends QDSeriesProxy
{

	static $aAllTags = array(
		'quality' => [
			'BluRay 720p',
			'BluRay 1080p',
			'DVDSCR',
			'DVDRIP',
			'HDRIP',
			'BRRIP',
			'WEBRIP',
			'x264',
			'HDTV',
			'PROPER',
			'720p',
			'WEB-DL',
			'DD5 1',
			'H264',
			'1080p',
			'H265',
			'WEBRip',
			'X264',
			'XviD',
			'DVDR',
			'Multi',
			'MPEG-2',
			'AC3',
			'AVC',
			'mkv',
			'AAC2',
			'TVripHD',
			'HEVC',
			'x265',
			'Avc',
			'AAC',
			'mp4',
			'TVRip',
			'H 264-BS',
			'DD5 1-PSA',
			'BluRay',
			'x264-PopHD',
			'MULTI',
			'VFQ',
			'DTS',
			'J0D',
			'DVD',
			'MKV',
			'LD'
		],
		'language' => [
			'VOSTFR',
			'TRUEFRENCH',
			'FRENCH',
			'french',
			'jmt',
			'FASTSUB',
			'SUBFRENCH',
			'Vostfr',
			'TVRip'
		],
		'year' => []
	);

	public function svc_testTorrent()
	{
		$aFiles = [];
		$bencoder = new BEncode();
		$path = '/mnt/###dwn/torrents';
		$dh = glob($path . '/*.added');
		foreach ($dh as $k => $v) {
			$aTorrent = $bencoder->bdecode_file($v);
			foreach (array_key_exists('files', $aTorrent['info']) ? $aTorrent['info']['files'] : [
				$aTorrent
			] as $aFile) {
				$aFile['info']['pieces'] = '--';
				$aFiles[] = array_key_exists('name', $aFile['info']) ? $aFile['info']['name'] : $aFile['path'][0];
			}
		}
		file_put_contents('/mnt/###dwn/torrents.json', json_encode($aFiles));
	}

	public function svc_test()
	{
		/*
		 * $aFiles = array_map(function($p){
		 * return '/mnt/dwn/'.$p;
		 * },json_decode(file_get_contents('/mnt/###dwn/torrents.json'),true));
		 */
		$aFiles = glob('/medias/downloads/completed/series/*.avi');
		$this->svc_test2($aFiles);
	}

	private function getSeriesAvailablePaths($aDestRootPaths)
	{
		$aPaths = [];
		foreach ($aDestRootPaths as $sRootPath) {
			$dh = glob($sRootPath . '/*', GLOB_ONLYDIR);
			foreach ($dh as $k => $v) {
				$seriePath = $this->getSeriePath($v);
				if (file_exists($seriePath . '/tvdb.xml') || file_exists($seriePath . '/tvshow.nfo')) {
					$xpath = $this->getXmlDocFromSeriePath($seriePath);
					$sSerieId = $this->extractXQuery($xpath, "/Data/Series/id", true);
					$sSerieName = $this->extractXQuery($xpath, "/Data/Series/SeriesName", true);
					$this->addIfNotPresent($aPaths, $sSerieName, $v);
					$this->addIfNotPresent($aPaths, serieTools::cleanupSaisonTitleForMatch($sSerieName), $v);
					foreach (serieTools::crossedCleanupSaisonTitleForMatch($sSerieName) as $s) {
						$this->addIfNotPresent($aPaths, $sSerieName, $s);
					}
					if ($sSerieId) {
						$urlen = sprintf('http://www.thetvdb.com/api/%s/series/%s/en.xml', $this->thetvdbkey, $sSerieId);
						$xpathen = $this->getXpathFromXmlDoc($this->QDNet->getCacheURL($urlen, 'seriesDetail', $this->cacheminutes, $this->cache));
						$sSerieName = $this->extractXQuery($xpathen, "/Data/Series/SeriesName", true);
						$this->addIfNotPresent($aPaths, $sSerieName, $v);
						$this->addIfNotPresent($aPaths, serieTools::cleanupSaisonTitleForMatch($sSerieName), $v);
						foreach (serieTools::crossedCleanupSaisonTitleForMatch($sSerieName) as $s) {
							$this->addIfNotPresent($aPaths, $sSerieName, $s);
						}
					}
				}
			}
		}
		uksort($aPaths, function ($a, $b) {
			return strlen($b) - strlen($a);
		});
		return $aPaths;
	}

	public function addIfNotPresent(&$aPaths, $k, $v)
	{
		$k = strtolower($k);
		if ($k != '' && ! array_key_exists($k, $aPaths)) {
			$aPaths[$k] = $v;
		}
	}

	private function findSeriesPath($aSeriesPaths, $serieFilename)
	{
		$serieFilename = serieTools::cleanupSaisonTitleForMatch($serieFilename);
		foreach ($aSeriesPaths as $k => $v) {
			if (strcasecmp($serieFilename, serieTools::cleanupSaisonTitleForMatch($k)) == 0) {
				return $v;
			}
		}
		return false;
	}

	private function addIfIsMovie(&$aList, $file)
	{
		$d = ToolsFiles::pathinfo_utf($file);
		if (in_array(strtolower($d['extension']), $this->movieExt)) {
			$aList[] = $file;
			return true;
		}
		return false;
	}

	private function getIncomingFiles($path)
	{
		$aResult = [];
		$aFiles = glob($path . '/*');
		foreach ($aFiles as $file) {
			if (! is_dir($file)) {
				$this->addIfIsMovie($aResult, $file);
			} else {
				foreach (glob($file . '/*') as $file) {
					$this->addIfIsMovie($aResult, $file);
				}
			}
		}
		return $aResult;
	}

	private function pri_getRenamedFile($aSeriesPaths, $v)
	{
		$d = ToolsFiles::pathinfo_utf($v);
		if (in_array(strtolower($d['extension']), $this->movieExt)) {
			// is a video file
			$d['filename'] = $this->cleanFilename($d['filename']);
			$res = $this->extractSeriesFilenameStruct($d['filename']);
			if ($res['found']) {
				// series found
				$seriePath = $this->findSeriesPath($aSeriesPaths, $res['serie']);
				if ($seriePath) {
					$xpath = $this->getXmlDocFromSeriePath($seriePath);
					if ($xpath) {
						$tags = serieTools::extractLanguage($d['filename']);
						$serieName = utf8_encode($this->cleanFilename($this->extractXQuery($xpath, "/Data/Series/SeriesName")));
						$episodeName = utf8_encode($this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']/EpisodeName"));

						$renamedPath = sprintf("%s/S%d %s", $seriePath, $res['saison'], $tags['short_language']);
						$renamedFile = sprintf("%s [%dx%02d] %s", $serieName, $res['saison'], $res['episode'], $episodeName);

						// db(sprintf("%-80s :: %s :: %s",$d['filename'],$renamedPath,$renamedFile));
						return array(
							'success' => true,
							'originalFile' => $d['file'],
							'renamedPath' => $renamedPath,
							'renamedFile' => $renamedFile,
							'renamedExt' => strtolower($d['extension'])
						);
					}
				} else {
					$msg = sprintf('Error : Can\'t find a path for [%s]', $this->mb_str_replace('.', ' ', $res['serie']));
					return array(
						'success' => false,
						'error' => $msg
					);
				}
			}
		}
	}

	public function renameIncoming(/*string*/ $seriePaths,/*string*/ $path,/*boolean*/ $bDryRun)
	{
		$aError = [];
		if ($path) {
			$dh = $this->getIncomingFiles($path);
		} else {
			$dh = array_map(function ($p) {
				return '/mnt/dwn/' . $p;
			}, json_decode(file_get_contents('/mnt/###dwn/torrents.json'), true));
		}

		print_r($dh);
		$aSeriesPaths = $this->getSeriesAvailablePaths(explode(',', $seriePaths));
		print_r($aSeriesPaths);

		$arrResult = [];
		foreach ($dh as $k => $v) {
			$aRenamedFile = $this->pri_getRenamedFile($aSeriesPaths, $v);
			if ($aRenamedFile['success']) {
				$arrResult[] = $aRenamedFile;
			}
		}

		asort($arrResult);
		$arrResult = array_values($arrResult);
		foreach ($arrResult as $file) {
			if ($bDryRun) {
				print(sprintf("%-80s :: %s :: %s\n", $file['originalFile'], $file['renamedPath'], $file['renamedFile'] . '.' . $file['renamedExt']));
			} else {
				$this->renameSerieEpisode($file['originalFile'], $file['renamedPath'] . '/' . $file['renamedFile'], $file['renamedExt']);
			}
		}
	}

	public function renameExisting()
	{
		/*$fName = '/tmp/seriesa.json';
		if (file_exists($fName) && filesize($fName)) {
			$aRoots = unserialize(file_get_contents($fName));
		} else {
			$aRoots = $this->svc_getSeriesTree();
			file_put_contents($fName, serialize($aRoots));
		}*/
		$aRoots = $this->getSeriesTree();

		$aFiles = [];
		foreach ($aRoots as $aRoot) {
			if (is_array($aRoot) && array_key_exists('children', $aRoot)) {
				foreach ($aRoot['children'] as $aSerie) {
					if (is_array($aSerie) && array_key_exists('children', $aSerie)) {
						foreach ($aSerie['children'] as $aFolder) {
							if ($aFolder['numbertorename']) {
								$arrToRename = $this->pri_getFiles($aFolder['fullname'], true);
								if (array_key_exists('results', $arrToRename) && is_array($arrToRename['results']) && count($arrToRename['results']) > 0) {
									foreach ($arrToRename['results'] as $aFile) {
										if ($aFile['formattedfilename'] != '' && $aFile['originalFile'] != $aFile['renamedFile'].'.'.$aFile['renamedExt']) {
											$aFile = array_merge($aFile, [
												'originalFile' => $aFolder['fullname'] . '/' . $aFile['filename'],
												'renamedPath' => $aFile['pathName'],
												'renamedFile' => $aFile['pathName'] . '/' . $aFile['formattedfilename'],
												'renamedExt' => strtolower($aFile['ext'])
											]);
											$aFiles[] = $aFile;
											print sprintf("%s => %s.%s \n",$aFile['originalFile'], $aFile['renamedFile'], $aFile['renamedExt']);
											$a = $this->renameSerieEpisode($aFile['originalFile'], $aFile['renamedFile'], $aFile['renamedExt'],false);
											if(!$a['ok']){
												print $a['error']."\n";
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		//db($aFiles);
	}
}
