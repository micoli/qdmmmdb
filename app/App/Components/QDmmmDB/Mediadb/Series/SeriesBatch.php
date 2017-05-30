<?php
namespace App\Components\QDmmmDB\Mediadb\Series;

use Bhutanio\BEncode\BEncode;
use App\Components\QDmmmDB\Misc\ToolsFiles;
use App\Components\QDmmmDB\Mediadb\serieTools;

class SeriesBatch extends SeriesManager
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

	public function renameIncoming($seriePaths,$path,$bDryRun=false)
	{
		$aIncomingFiles=[];
		$aError = [];

		if ($path) {
			$aIncomingFiles = $this->getIncomingFiles($path);
		} else {
			$aIncomingFiles = array_map(function ($p) {
				return '/mnt/dwn/' . $p;
			}, json_decode(file_get_contents('/mnt/###dwn/torrents.json'), true));
		}
db($aIncomingFiles);
		$aSeriesPaths = $this->getSeriesAvailablePaths(explode(',', $seriePaths));

		foreach ($aIncomingFiles as $k => $originalFilename) {
			$aIncomingFiles[$k]=[
				'originalFilename'=>$originalFilename
			];
			$aRenamedFile = $this->getIncomingRenamedFile($aSeriesPaths, $originalFilename);
			if ($aRenamedFile['success']) {
				$aIncomingFiles[$k]['renamedFile'] = $aRenamedFile;
				if ($bDryRun) {
					$aIncomingFiles[$k]['result'] = sprintf("%-80s :: %s/%s\n", $aRenamedFile['originalFile'], $aRenamedFile['renamedPath'], $aRenamedFile['renamedFile'] . '.' . $aRenamedFile['renamedExt']);
				} else {
					$aIncomingFiles[$k]['result'] = $this->renameSerieEpisode($aRenamedFile['originalFile'], $aRenamedFile['renamedPath'] . '/' . $aRenamedFile['renamedFile'], $aRenamedFile['renamedExt']);
				}
			}else{
				$aIncomingFiles[$k]['error'] = $aRenamedFile['error'];
			}
		}
		return $aIncomingFiles;
	}

	public function renameExisting($bDryRun=false,$bRefresh=true)
	{
		$aRoots = $this->getSeriesTree(false,$bRefresh);
		$aRoots = [$aRoots[0]];
		$aFiles = [];

		foreach ($aRoots as $aRoot) {
			if (is_array($aRoot) && array_key_exists('children', $aRoot)) {
				foreach ($aRoot['children'] as $aSerie) {
					if (is_array($aSerie) && array_key_exists('children', $aSerie)) {
						foreach ($aSerie['children'] as $aFolder) {
							if ($aFolder['numbertorename']) {
								$arrToRename = $this->getFiles($aFolder['fullname'], true);
								foreach ($arrToRename['results'] as $aFile) {
									if ($aFile['formattedfilename'] != '' && !$aFile['formatOK'] ) {
										array_push($aFiles,array_merge($aFile, [
											'originalFile' => $aFolder['fullname'] . '/' . $aFile['filename'],
											'renamedPath' => $aFile['pathName'],
											'renamedFile' => $aFile['pathName'] . '/' . $aFile['formattedfilename'],
											'renamedExt' => strtolower($aFile['ext'])
										]));
									}
								}
							}
						}
					}
				}
			}
		}

		foreach($aFiles as $aFile){
			print sprintf("%s => %s.%s \n", $aFile['originalFile'], $aFile['renamedFile'], $aFile['renamedExt']);
			if (!$bDryRun) {
				$a = $this->renameSerieEpisode($aFile['originalFile'], $aFile['renamedFile'], $aFile['renamedExt'], false);
				if (! $a['ok']) {
					print $a['error'] . "\n";
				}
			}
		}
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

	private function getIncomingRenamedFile($aSeriesPaths, $sFileName)
	{
		$oSerie = new SerieFile($sFileName);
		if ($oSerie->found) {

			$seriePath = $this->findSeriesPath($aSeriesPaths, $oSerie->serie);

			if ($seriePath) {
				$xpath = $this->getXmlDocFromSeriePath($seriePath);

				if ($xpath) {
					$oSerie->filename = $this->cleanFilename($oSerie->filename);
					$tags = serieTools::extractLanguage($oSerie->filename);
					$serieName = utf8_encode($this->cleanFilename($this->extractXQuery($xpath, "/Data/Series/SeriesName")));
					$episodeName = utf8_encode($this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $oSerie->saison . "' and EpisodeNumber='" . ($oSerie->episode * 1) . "']/EpisodeName"));

					$renamedPath = sprintf("%s/S%d %s", $seriePath, $oSerie->saison, $tags['short_language']);
					$renamedFile = sprintf("%s [%dx%02d] %s", $serieName, $oSerie->saison, $oSerie->episode, $episodeName);

					// db(sprintf("%-80s :: %s :: %s",$oSerie->filename,$renamedPath,$renamedFile));
					return array(
						'success' => true,
						'originalFile' => $sFileName,
						'renamedPath' => $renamedPath,
						'renamedFile' => $renamedFile,
						'renamedExt' => strtolower($oSerie->extension)
					);
				}
			} else {
				$msg = sprintf('Error : Can\'t find a path for [%s]', $this->mb_str_replace('.', ' ', $oSerie->serie));
				return array(
					'success' => false,
					'error' => $msg
				);
			}
		}
	}
}