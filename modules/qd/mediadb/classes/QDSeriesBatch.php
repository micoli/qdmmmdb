<?php
include "BEncode.php";
use Bhutanio\BEncode\BEncode;

class QDSeriesBatch extends QDSeriesProxy{
	static $aAllTags=array(
		'quality'	=> ['BluRay 720p','BluRay 1080p','DVDSCR','DVDRIP','HDRIP','BRRIP','WEBRIP','x264','HDTV','PROPER','720p','WEB-DL','DD5 1','H264','1080p','H265','WEBRip','X264','XviD','DVDR','Multi','MPEG-2','AC3','AVC','mkv','AAC2','TVripHD','HEVC','x265','Avc','AAC','mp4','TVRip','H 264-BS','DD5 1-PSA','BluRay','x264-PopHD','MULTI','VFQ','DTS','J0D','DVD','MKV','LD'],
		'language'	=> ['VOSTFR','TRUEFRENCH','FRENCH','french','jmt','FASTSUB','SUBFRENCH','Vostfr','TVRip'],
		'year'		=> []
	);

	public function svc_testTorrent(){
		$aFiles = [];
		$bencoder = new BEncode();
		$path = '/mnt/###dwn/torrents';
		$dh = glob($path . '/*.added');
		foreach ($dh as $k => $v) {
			$aTorrent=$bencoder->bdecode_file($v);
			foreach(array_key_exists('files',$aTorrent['info'])?$aTorrent['info']['files']:[$aTorrent] as $aFile){
				$aFile['info']['pieces']='--';
				$aFiles[]=array_key_exists('name',$aFile['info'])?$aFile['info']['name']:$aFile['path'][0];
			}
		}
		file_put_contents('/mnt/###dwn/torrents.json',json_encode($aFiles));
	}

	public function svc_test(){
		/*
 		$aFiles = array_map(function($p){
			return '/mnt/dwn/'.$p;
		},json_decode(file_get_contents('/mnt/###dwn/torrents.json'),true));
		 */
		$aFiles = glob('/medias/downloads/completed/series/*.avi');
		$this->svc_test2($aFiles);
	}

	private function getSeriesAvailablePaths($aDestRootPaths){
		$aPaths=[];
		foreach($aDestRootPaths as $sRootPath){
			$dh = glob($sRootPath . '/*', GLOB_ONLYDIR);
			foreach ($dh as $k => $v) {
				$seriePath = $this->getSeriePath($v);
				if (file_exists($seriePath . '/tvdb.xml') || file_exists($seriePath . '/tvshow.nfo') ) {
					$xpath = $this->getXmlDocFromSeriePath($seriePath);
					$sSerieId = $this->extractXQuery($xpath, "/Data/Series/id",true);
					$sSerieName = $this->extractXQuery($xpath, "/Data/Series/SeriesName",true);
					$this->addIfNotPresent($aPaths,$sSerieName,$v);
					$this->addIfNotPresent($aPaths,$this->cleanupSaisonTitleForMatch($sSerieName),$v);
					$aFilters=['removeTrailingYear','removeLongTitle','removeUS','removeApo','removeProvider'];
					foreach($aFilters as $sFilterName){
						$this->addIfNotPresent($aPaths,$this->$sFilterName($sSerieName),$v);
						foreach($aFilters as $sFilterName2){
							if($sFilterName!= $sFilterName2){
								$this->addIfNotPresent($aPaths,$this->$sFilterName2($this->$sFilterName($sSerieName)),$v);
							}
						}
					}
					if($sSerieId){
						$urlen	= sprintf('http://www.thetvdb.com/api/%s/series/%s/en.xml',$this->thetvdbkey,$sSerieId);
						$xpathen = $this->getXpathFromXmlDoc($this->QDNet->getCacheURL($urlen, 'seriesDetail', $this->cacheminutes, $this->cache));
						$sSerieName = $this->extractXQuery($xpathen, "/Data/Series/SeriesName",true);
						$this->addIfNotPresent($aPaths,$sSerieName,$v);
						foreach($aFilters as $sFilterName){
							$this->addIfNotPresent($aPaths,$this->$sFilterName($sSerieName),$v);
							foreach($aFilters as $sFilterName2){
								if($sFilterName!= $sFilterName2){
									$this->addIfNotPresent($aPaths,$this->$sFilterName2($this->$sFilterName($sSerieName)),$v);
								}
							}
						}
					}
				}
			}
		}
		uksort($aPaths,function($a,$b){
			return strlen($b)-strlen($a);
		});
		return $aPaths;
	}

	public function removeApo($s){
		return str_replace ('\'','',$s);
	}

	public function removeProvider($s){
		$m = [];
		if(preg_match('!\[(.*?)\] (.*)!',$s,$m)){
			print trim($m[2])."\n";
			return trim($m[2]);
		}
		return $s;
	}

	public function removeUS($s){
		$m = [];
		if(preg_match('!(.*?) us$!i',$s,$m)){
			return $m[1];
		}
		if(preg_match('!(.*?) \(us\)$!i',$s,$m)){
			return $m[1];
		}
		return $s;
	}

	public function removeLongTitle($s){
		$m = [];
		if(preg_match('!(.*?) : (.*)!',$s,$m)){
			return $m[1];
		}
		return $s;
	}

	public function addIfNotPresent(&$aPaths,$k,$v){
		if($k!='' && !array_key_exists($k,$aPaths)){
			$aPaths[$k]=$v;
		}
	}

	private function cleanupSaisonTitleForMatch($serieFilename){
		$aFilters=['removeTrailingYear','removeLongTitle','removeUS','removeApo','removeProvider'];
		$serieFilename = str_replace('.'	,' ',$serieFilename);
		$serieFilename = str_replace('\''	,' ',$serieFilename);
		$serieFilename = str_replace('"'	,' ',$serieFilename);
		$serieFilename = str_replace(':'	,' ',$serieFilename);
		$serieFilename = strtolower($serieFilename);
		foreach($aFilters as $sFilterName){
			$serieFilename= $this->$sFilterName($serieFilename);
		}
		$serieFilename = strtolower($serieFilename);
		return $serieFilename;
	}

	private function findSeriesPath($aSeriesPaths,$serieFilename){
		$serieFilename = $this->cleanupSaisonTitleForMatch($serieFilename);
		#print $serieFilename."\n";
		foreach($aSeriesPaths as $k=>$v){
			if(strcasecmp($serieFilename,$this->cleanupSaisonTitleForMatch($k))==0){
				return $v;
			}
		}
		$serieFilenameWithoutYear = preg_replace('! [0-9]{4}$!','',$serieFilename);
		if ($serieFilename!=$serieFilenameWithoutYear){
			$serieFilename=$serieFilenameWithoutYear;
			foreach($aSeriesPaths as $k=>$v){
				if(strcasecmp($serieFilename,$this->cleanupSaisonTitleForMatch($k))==0){
					return $v;
				}
			}
		}
		return false;
	}

	private function extractLanguage($sTitle){
		$tags=[];

		if(!array_key_exists('year', self::$aAllTags)){
			for($i=1990;$i<=2050;$i++){
				self::$aAllTags['year'][]=$i;
			}
		}

		$sTitle	= ' '.preg_replace('!\.!',' ',$sTitle).' ';

		foreach(self::$aAllTags as $sTagType=>$aTag){
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

	private function getIncomingFiles($path){
		$aResult = [];
		$aFiles = glob($path.'/*');
		foreach($aFiles as $file){
			if(!is_dir($file)){
				$d = CW_Files::pathinfo_utf($file);
				if (in_array(strtolower($d['extension']), $this->movieExt)) {
					$aResult[]=$file;
				}
			}else{
				foreach(glob($file.'/*') as $file){
					$d = CW_Files::pathinfo_utf($file);
					if (in_array(strtolower($d['extension']), $this->movieExt)) {
						$aResult[]=$file;
					}
				}
			}
		}
		return $aResult;
	}

	public function svc_renameIncoming(){
		$aError = [];

		if(array_key_exists('path',$_REQUEST) && $_REQUEST['path']!='test'){
			$dh = $this->getIncomingFiles($_REQUEST['path']);
		}else{
			$dh = array_map(function($p){
				return '/mnt/dwn/'.$p;
			},json_decode(file_get_contents('/mnt/###dwn/torrents.json'),true));
		}

		if(!array_key_exists('seriePaths',$_REQUEST)){
			return array(
				'success'=>false,
				'error'=>'no path specified'
			);
		}

		print_r( $dh);
		$aSeriesPaths = $this->getSeriesAvailablePaths(explode(',',$_REQUEST['seriePaths']));
		print_r( $aSeriesPaths);
		$arrResult = [];
		foreach ($dh as $k => $v) {
			$d = CW_Files::pathinfo_utf($v);
			if (in_array(strtolower($d['extension']), $this->movieExt)) {
				//is a video file
				$d['filename'] = $this->cleanFilename($d['filename']);
				$res = $this->extractSeriesFilenameStruct($d['filename']);
				if ($res['found']) {
					//series found
					$seriePath = $this->findSeriesPath($aSeriesPaths, $res['serie']);
					if($seriePath){
						$xpath = $this->getXmlDocFromSeriePath($seriePath);
						if($xpath){
							$tags			= $this->extractLanguage($d['filename']);
							$serieName		= utf8_encode($this->cleanFilename($this->extractXQuery($xpath, "/Data/Series/SeriesName")));
							$episodeName	= utf8_encode($this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']/EpisodeName"));
							$renamedPath	= sprintf("%s/S%d %s", $seriePath, $res['saison'], $tags['short_language']);
							$renamedFile	= sprintf("%s [%dx%02d] %s", $serieName, $res['saison'], $res['episode'], $episodeName);
							//db(sprintf("%-80s :: %s :: %s",$d['filename'],$renamedPath,$renamedFile));
							$arrResult[] = array(
								'originalFile'	=> $d['file'],
								'renamedPath'	=> $renamedPath,
								'renamedFile'	=> $renamedFile,
								'renamedExt'	=> strtolower($d['extension'])
							);
						}
					}else{
						$msg = sprintf('Error : Can\'t find a path for [%s]',$this->mb_str_replace('.', ' ', $res['serie']));
						if(!in_array($msg,$aError)){
							$aError[]=$msg;
					}
				}
			}
		}

		asort($arrResult);
		$arrResult = array_values($arrResult);
		foreach($arrResult as $file){
			if(array_key_exists('dryRun',$_REQUEST) && $_REQUEST['dryRun']){
				print (sprintf("%-80s :: %s :: %s\n",$file['originalFile'],$file['renamedPath'],$file['renamedFile'].'.'.$file['renamedExt']));
			}else{
				$this->pri_renameSerieEpisode($file['originalFile'],$file['renamedPath'].'/'.$file['renamedFile'],$file['renamedExt']);
			}
		}
	}
	}
}
