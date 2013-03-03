<?
class QDSeriesProxy extends QDMediaDBProxy{

	function getPathFromName($name){
		$path=false;
		foreach($this->folderSeriesList as $v){
			if ($v['name']==$name){
				$path=$v['path'];
				break;
			}
		}
		return $path;
	}

	function svc_getSerieFromPath() {
		$path = $_REQUEST['p'];
		$path = $this->getSeriePath($path);
		return( array ('results'=> array ('name'=>basename($path))));
	}

	function svc_getFolderSeriesList(){
		return array('results'=> $this->folderSeriesList);
	}

	function svc_serieBulkRename(){
		header('content-type:text/html');
		$p = json_decode($_REQUEST['d']);
		$prm=array();
		$allOk = true;
		foreach($p as $k=>$v){
			$fullfilename	= base64_decode($v->fullfilename	);
			$folder			= base64_decode($v->folder			);
			$renamed		= base64_decode($v->renamed			);
			$extension		= base64_decode($v->extension		);
			$arr[$k]		= $this->pri_renameSerieEpisode($fullfilename,$folder.'/'.$renamed,$extension);
			$arr[$k]['old']	= $fullfilename;
			$arr[$k]['new']	= $folder.'/'.$renamed;
			$arr[$k]['ext']	= $extension;
			if(!$arr[$k]['ok']){
				$allOk=false;
			}
		}
		return array(
			'ok'		=>$allOk,
			'details'	=>$arr
		);
	}

	function pri_renameSerieEpisode($old,$new,$ext){
		$folder = dirname($new);
		if(!file_exists($old)){
			return array('ok'=>false	,'error'=>utf8_decode(sprintf('File %s does not exists',$old)));
		}
		if(!file_exists($folder)){
			mkdir($folder,0777,true);
		}
		if(is_dir($folder)){
			$destFile = $new.'.'.$ext;
			$idx=0;
			while(file_exists($destFile) && $idx<20){
				$idx++;
				$destFile = sprintf('%s(%s).%s',$new,$idx,$ext);
			}
			if (rename($old,$destFile)){
				return array('ok'=>true		,'error'=>'');
			}else{
				db(sprintf('Rename error %s=>%s',$old,$destFile)."eeee");
				return array('ok'=>false	,'error'=>utf8_decode(sprintf('Rename error %s=>%s',$old,$destFile)));
			}
		}else{
			return array('ok'=>false	,'error'=>utf8_decode(sprintf('Destination "%s" exists and is not a directory',$folder)));
		}
	}

	function svc_getFileSorterList(){
		//header('content-type:text/html');print "<table border=1>";
		$arrResult = array();
		$path = $this->getPathFromName($_REQUEST['name']);
		if($path){
			$this->pri_getFileSorterList($path,$arrResult,false,$path,'');
			$dh = glob($path . '/*', GLOB_ONLYDIR);
			foreach ($dh as $k => $v) {
				$this->pri_getFileSorterList($v,$arrResult,true,$path,str_replace($path.'/','',$v));
			}
			$dh2 = array();
			foreach ($dh as $k => $v) {
				$dh2[]=str_replace($path.'/','',$v);
			}
			//db($dh2);die();
		}
		return array('results'=> $arrResult,'folders'=>$dh2);
	}

	function pri_getFileSorterList($path,&$arrResult,$inFolder,$root,$subPath){
		$dh = glob($path . '/*.*');
		foreach ($dh as $k => $v) {
			$d = CW_Files::pathinfo_utf($v);
			if (in_array(strtolower($d['extension']), $this->movieExt)) {
				$res = $this->extractSeriesFilenameStruct($d['filename']);
				$res['fullfilename'	]=$v;
				$res['extension'	]=$d['extension'];
				$res['folder'		]=$path;
				$res['inFolder'		]=$inFolder;
				$res['renamed'		]='';
				$res['root'			]=$root;
				$res['subPath'		]=$subPath;
				$res['selected'		]=false;
				$arrResult[]=$res;
				if(false){
					print "<tr>";
					print "<td>$v</td>";
					print "<td>".$res['saison']."</td>";
					print "<td><span style=color:".($res['episode']==0?'red':'black').">".($res['episode'])."</span></td>";
					print "<td>".$res['rgxnum']."</td>";
					print "<td>".$res['rgx']."</td>";
					print "</tr>";
				}
			}
		}
	}

	function getSeriePath($path) {
		$pathName = basename($path);
		if (ereg('^S[0-9]{1,} (.*)$', $pathName)) {
			$path = dirname($path);
		}
		if (ereg('^S[0-9]{1,}$', $pathName)) {
			$path = dirname($path);
		}
		return $path;
	}

	function svc_setSerieFromPath() {
		return $this->pri_setSerieFromPath($_REQUEST['p'],$_REQUEST['i']);
	}

	function pri_setSerieFromPath($path,$id) {
		$path	= $this->getSeriePath($path);
		$urlvo	= sprintf('http://www.thetvdb.com/api/%s/series/%s/fr.xml',$this->thetvdbkey,$id);
		$urlen	= sprintf('http://www.thetvdb.com/api/%s/series/%s/fr.xml',$this->thetvdbkey,$id);
		$xml	= $this->QDNet->getCacheURL($urlvo, 'seriesDetail', $this->cacheminutes, $this->cache);
		if ($xml == '') {
			$xml = $this->QDNet->getCacheURL($urlen, 'seriesDetail', $this->cacheminutes, $this->cache);
		}
		$filename = str_replace("\\'", "'", $path).'/tvdb.xml';
		file_put_contents($filename, $xml);

		$urlvo = sprintf('http://www.thetvdb.com/api/%s/series/%s/all/fr.xml',$this->thetvdbkey,$id);
		$urlen = sprintf('http://www.thetvdb.com/api/%s/series/%s/all/en.xml',$this->thetvdbkey,$id);
		$xml = $this->QDNet->getCacheURL($urlvo, 'seriesDetail', $this->cacheminutes, $this->cache);
		if ($xml == '') {
			$xml = $this->QDNet->getCacheURL($urlen, 'seriesDetail', $this->cacheminutes, $this->cache);
		}
		$filename = str_replace("\\'", "'", $path).'/tvdb_all.xml';
		file_put_contents($filename, $xml);

		return ( array ('results'=> array ('name'=>basename($path), 'title'=>basename($path).' <b>TVDB</b>')));
	}

	function svc_chooseSerie() {
		$seriename	= basename($_REQUEST['s']);
		$path		= $_REQUEST['p'];
		$path		= $this->getSeriePath($path);
		$xml		= $this->QDNet->getCacheURL("http://www.thetvdb.com/api/GetSeries.php?seriesname=".urlencode($seriename), 'getSeries', $this->cacheminutes, $this->cache);
		$sdom		= simplexml_load_string($xml);
		$res		= array ();
		$dom		= object2array($sdom);
		$f			= $sdom->xpath('Series');
		$res['results']=array();
		foreach ($f as $v) {
			$att = array ();
			foreach ($v->children() as $a=>$b)$att[$a] = (string)$b;
			$res['results'][] = array (
				'name'		=> $att['SeriesName'],
				'seriesid'	=> $att['seriesid'],
				'lang'		=> $att['language'],
				'Overview'	=> $att['Overview'],
				'year'		=> $att['FirstAired']
			);
		}
		if (file_exists($path.'/tvdb.xml')) {
			$res['seriesid'] = $this->getSeriesIdFromXml($path);
		}
		return ($res);
	}

	function getSeriesIdFromXml($path) {
		if (file_exists($path.'/tvdb.xml')) {
			$xml = file_get_contents($path.'/tvdb.xml');
			$dom = simplexml_load_string($xml);
			return (string)$dom->Series->id;
		} else {
			if (file_exists($path.'/tvshow.nfo')) {
				$xml = file_get_contents($path.'/tvshow.nfo');
				$dom = simplexml_load_string($xml);
				return (string)$dom->id;
			} else {
				return null;
			}
		}
	}

	function svc_getSeriesTree() {
		QDSession::_unset('cacheFolderTree');
		if (array_key_exists('refresh', $_REQUEST) && $_REQUEST['refresh'] = 1) {
			QDSession::_unset('cacheFolderTreeSeries');
		}
		if (QDSession::_isset('cacheFolderTreeSeries')) {
			$res = QDSession::_get('cacheFolderTreeSeries');
		} else {
			$this->nodeId = 10000;
			$res = array();
			foreach ($this->folderSeriesList as $v) {
				$res[] = array(
					'text'		=> $v['name'],
					'fullname'	=> $v['path'],
					'rootDrive'	=> 1,
					'children'	=> $this->getSeriesDirectory($v['path'])
				);
			}
			QDSession::_set('cacheFolderTreeSeries', $res);
		}
		//DB($res);
		return ($res);
	}

	function getSeriesDirectory($path = '.', $level = 0) {
		set_time_limit(90);
		$arr = array();
		$dh = glob($path . '/*', GLOB_ONLYDIR);
		foreach ($dh as $k => $v) {
			$thisDir = array(
				'text'		=> basename($v),
				'fullname'	=> $v,
				'rootDrive'	=> 0,
				'id'		=> $this->nodeId++,
				'uiProvider'=> 'col',
				'leaf'		=> false,
				'tvdb'		=> '',
				'cls'		=> 'folder'
			);

			$seriePath = $this->getSeriePath($v);

			if (file_exists($seriePath . '/tvdb.xml')||file_exists($seriePath . '/tvshow.nfo') ) {
				$thisDir['tvdb'] = 'serie';
			}
			$parentDir = dirname($v);
			if (($parentDir!=$v) && (file_exists($parentDir . '/tvdb.xml') || file_exists($parentDir . '/tvshow.nfo'))) {
				$xpath = $this->getXmlDocFromSeriePath($parentDir);
				$thisDir['tvdb'				] = 'season';
				$thisDir['numbertorename'	] = 0;
				$thisDir['serieName'		] = ($this->cleanFilename($this->extractXQuery($xpath, "/Data/Series/SeriesName")));
				$arrToRename = $this->pri_getFiles($v, true);
				if (array_key_exists('results',$arrToRename) && is_array($arrToRename['results']) && count($arrToRename['results'])>0){
					$thisDir['numbertorename']=count($arrToRename['results']);
				}
			}
			if(false && preg_match('!wallander!i',$parentDir)){
				die(__FILE__." -> ".__LINE__);
			}
			//$this->makeSerieNFO($v . '/tvshow.nfo');
			$subdir = $this->getSeriesDirectory($v, ($level + 1));
			if (count($subdir) > 0) {
				$thisDir['children'] = $subdir;
			}else{
				$thisDir['leaf'] = true;
			}
			$arr[] = $thisDir;
		}
		return $arr;
	}

	function getUpdateUrlOrXmlFromSeriePath($seriePath, $mode) {
		$seriesid = $this->getSeriesIdFromXml($seriePath);
		if ($seriesid) {
			$urlfr = 'http://www.thetvdb.com/api/' . $this->thetvdbkey . '/series/' . $seriesid . '/all/fr.xml';
			$urlen = 'http://www.thetvdb.com/api/' . $this->thetvdbkey . '/series/' . $seriesid . '/all/en.xml';
			if (url_exists($urlfr)) {
				$url = $urlfr;
			} else {
				$url = $urlen;
			}
			if (file_exists($seriePath . '/tvdb_all.xml')) {
				$xml = file_get_contents($seriePath . '/tvdb_all.xml');
			} else {
				$xml = $this->QDNet->getCacheURL($url, 'seriesDetail', $this->cacheminutes, $this->cache);
				//file_put_contents(str_replace("\\\\'", "'", $seriePath) . '/tvdb_all.xml', $xml);
			}
		}
		switch ($mode) {
			case 'xml':
				return $xml;
			break;
			case 'url':
				return $url;
			break;
		}
	}

	function getXmlDocFromSeriePath($seriePath) {
		$xpath = false;
		if (file_exists($seriePath . '/tvdb.xml')) {
			$xml = $this->getUpdateUrlOrXmlFromSeriePath($seriePath, 'xml');
			$xml = str_replace(array('’'), array('\''), $xml);
			$doc = new DomDocument;
			$doc->loadXML($xml);
			$xpath = new DOMXPath($doc);
		}
		return $xpath;
	}

	function svc_renameFiles() {
		//print '<pre>';
		$debug = false;
		$t = utf8_decode(base64_decode($_REQUEST['modified']));
		$arr = json_decode($t, true);
		foreach ($arr as $SeriePath => $Modified) {
			$SeriePath = base64_decode($SeriePath);
			$tmp = glob($SeriePath . '/*.*');
			$arrMD5 = array();
			foreach ($tmp as $v) {
				$arrMD5[md5(realpath($v))] = realpath($v);
			}
			;
			$arrResult = array();
			$arrOldNFO = glob(realpath($SeriePath) . "/*.nfo");
			foreach ($arrOldNFO as $oldNFO) {
				unlink($oldNFO);
			}
			$first = true;
			foreach ($Modified['modified'] as $v) {
				$v['serie'] = base64_decode($v['serie']);
				$old		= realpath($SeriePath . "/" . $v['old']);
				$new		= realpath($SeriePath) . "/" . trim($v['serie']) . " [" . $v['saison'] . 'x' . sprintf('%02d', $v['episode']) . '] ' . $v['new'] . '.' . $v['ext'];
				$new64		= realpath($SeriePath) . "/" . trim($v['serie']) . " [" . $v['saison'] . 'x' . sprintf('%02d', $v['episode']) . '] ' . utf8_encode(base64_decode($v['new64'])) . '.' . $v['ext'];
				if (array_key_exists($v['md5'], $arrMD5)) {
					if (file_exists($new64)) {
						$resultRename = 'file exists';
						if ($_REQUEST['moveExists'] == 'true') {
							//fb('exists');
						}
					} else {
						if ($debug){
							$resultRename = true;
							db(array($arrMD5[$v['md5']], $new64));
						}else{
							$resultRename = rename($arrMD5[$v['md5']], $new64);
						}
					}
					$d = CW_Files::pathinfo_utf($new64);
					if (in_array(strtolower($d['extension']), $this->movieExt)) {
						$this->makeEpisodeNFO($new64);
					}
					if ($first) {
						$this->makeSerieNFO($new64);
						$first = false;
					}
					$arrResult[] = array('old' => $arrMD5[$v['md5']], 'new' => $new64, 'result' => $resultRename);
				}
			}
		}
		return array('result' => $arrResult);
	}

	function svc_getFilesMulti() {
		$arrSeries = glob($_REQUEST['fullpath'] . '/*', GLOB_ONLYDIR);
		$arrPaths = array();
		foreach ($arrSeries as $seriePath) {
			$t = glob($seriePath . '/*', GLOB_ONLYDIR);
			foreach ($t as $saisonPath) {
				if (ereg('S[0-9]{1,} (.*)', $saisonPath)) {
					$arrPaths[] = $saisonPath;
				}
			}
		}

		//$arrPaths = array('m:\\\\###Series\\\\24\\\\S7 VO','m:\\\\###Series\\\\30 Rock\\\\S3 VO');
		$arr = array('results' => array());
		foreach ($arrPaths as $p) {
			$arr1 = $this->pri_getFiles($p, $_REQUEST['only2Rename'] == 'true');
			//print_r($arr1);
			$arr['results'] = array_merge($arr['results'], $arr1['results']);
		}
		$arr['bannerImg'	] = '';
		$arr['bannerText'	] = 'MULTI';
		$arr['serieName'	] = 'MULTI';
		return ($arr);
	}

	function pri_formatEpisodeFilename($formatName,$serieName,$saison,$episode,$episodeName,$extension){
		$rtn = '';
		if (array_key_exists($formatName, $this->episodeFormats)){
			$rtn = sprintf($this->episodeFormats[$formatName],trim($serieName),trim($saison),trim($episode),$this->cleanFilename($episodeName),trim($extension));
		}else{
			$rtn = '';
		}
		//db($this->episodeFormats[$formatName]);
		//print "->".$rtn."\n";
		return $rtn;
	}

	function isEpisodeFileNameOK($currentFileName,$serieName,$saison,$episode,$episodeName,$extension){
		$isOk = false;
		foreach($this->episodeFormats as $formatName=>$formatString){
			$currentReformatedFilename = $this->pri_formatEpisodeFilename($formatName, $serieName, $saison, $episode, $episodeName, $extension);
			//print "A1";db(($currentReformatedFilename));
			//print "A2";db(utf8_decode($currentFileName));
			if (($currentReformatedFilename == $currentFileName)||($currentReformatedFilename== utf8_decode($currentFileName))){
				$isOk = true;
				break;
			}
		}
		return $isOk;
	}

	function svc_getFiles() {
		return $this->pri_getFiles($_REQUEST['fullpath'], $_REQUEST['only2Rename'] == 'true');
	}

	function pri_getFiles($path, $only2Rename) {
		$str = $path; //stripslashes(str_replace('/', '\\\\', utf8_decode($path)));
		$seriePath = $this->getSeriePath($str);
		if (file_exists($seriePath . '/tvshow.nfo') && !file_exists($seriePath . '/tvdb_all.xml')) {
			$idSerie = $this->getSeriesIdFromXml($seriePath);
			//return($seriePath."/".$idSerie);
			return array('results'=>array(),'arrSerie'=>array());
			if($idSerie){
				print "autodetection serie";
				$this->pri_setSerieFromPath($seriePath,$idSerie);
			}
		}
		$xpath = $this->getXmlDocFromSeriePath($seriePath);
		$arr = array();
		$tmp = glob($str . '/*.*');
		$arr = array();
		$arr['bannerImg'	] = ($this->extractXQuery($xpath, "/Data/Series/banner"));
		$arr['bannerText'	] = ($this->extractXQuery($xpath, "/Data/Series/Overview"));
		$arr['serieName'	] = ($this->cleanFilename($this->extractXQuery($xpath, "/Data/Series/SeriesName")));
		$arr['results'		] = array();
		if (file_exists($seriePath . '/tvdb_all.xml')) {
			$xml = new DOMDocument;
			$xml->load($seriePath . '/tvdb_all.xml');
			$xsl = new DOMDocument;
			$xsl->load(dirname(__FILE__) . '/data/tvdb_all.xsl');
			// Transformation !
			$proc = new XSLTProcessor;
			$proc->importStyleSheet($xsl);
			//$arr['serieHTML'] = base64_encode(utf8_decode($proc->transformToXML($xml)));
			$arr['arrSerie' ] = simpleXMLToArray(simplexml_load_file($seriePath . '/tvdb_all.xml'));
			/*if(array_key_exists('Episode', $arr['arrSerie'])){
				foreach($arr['arrSerie']['Episode'] as &$v){
					foreach($v as $f=>$val){
						$v[$f]=is_array($val)?join('|',$val):$val;
					}
				}
			}*/
			//db($arr['arrSerie']['Episode']);
		}
		header('content-type:text/html');
		foreach ($tmp as $v) {
			$d = CW_Files::pathinfo_utf($v);
			if (in_array(strtolower($d['extension']), $this->allowedExt)) {
				$episodeName = '';
				$Overview = '';
				$res = $this->extractSeriesFilenameStruct($d['basename']);
				if ($res['found'] && $xpath) {
					$episodeName	= $this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']/EpisodeName");
					$Overview		= $this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']/Overview");
				}
				$formatOK = $this->isEpisodeFileNameOK($d['basename'],$arr['serieName'],$res['saison'],$res['episode'],$episodeName,$d['extension']);
				if (!$only2Rename ||  !$formatOK) {
					$arr['results'][] = array(
						'filename'		=> $d['basename'],
						'ext'			=> $d['extension'],
						'filesize'		=> size_readable(filesize($v)),
						'saison'		=> $res['found'] ? $res['saison'] : '--',
						'episode'		=> $res['found'] ? $res['episode'] : '--',
						'episodeName'	=> utf8_encode($this->cleanFilename($episodeName)),
						'Overview'		=> $Overview,
						'serieName'		=> $arr['serieName'],
						'pathName'		=> $str,
						'formatOK'		=> $formatOK,
						'md5'			=> md5(realpath($v))
					);
				}
			}
		}
		//db($arr);
		return $arr;
	}

	function makeSerieNFO($filename) {
		set_time_limit(40);
		$seriePath = $this->getSeriePath(dirname($filename));
		if (file_exists($seriePath . '/tvdb_all.xml')) {
			$xpath			= $this->getXmlDocFromSeriePath($seriePath);
			$seriePathD		= pathinfo($filename);
			$nfoFilename1	= $seriePathD['dirname'] . '/tvshow.nfo';
			$nfoFilename2	= $seriePath . '/tvshow.nfo';
			$bannerFilename	= $seriePath . '/folder.jpg';

			if ($xpath) {
				$xmldocNFO	= new DOMDocument("1.0");
				$root		= $xmldocNFO->createElement("tvshow");
				$xmldocNFO->appendChild($root);

				$bannerURL = 'http://thetvdb.com/banners/_cache/'			. $this->extractXQuery($xpath, "/Data/Series/banner");
				$fanartURL = 'http://thetvdb.com/banners/_cache/'			. $this->extractXQuery($xpath, "/Data/Series/fanart");
				$this->addNFOTextNode($xmldocNFO, $root, "title"			, $this->extractXQuery($xpath, "/Data/Series/SeriesName"));
				$this->addNFOTextNode($xmldocNFO, $root, "plot"				, $this->extractXQuery($xpath, "/Data/Series/Overview"));
				$this->addNFOTextNode($xmldocNFO, $root, "episodeguideurl"	, str_replace('.xml', '.zip', $this->getUpdateUrlOrXmlFromSeriePath($seriePath, 'url')));
				$this->addNFOTextNode($xmldocNFO, $root, "premiered"		, $this->extractXQuery($xpath, "/Data/Series/FirstAired"));
				$this->addNFOTextNode($xmldocNFO, $root, "banner"			, $bannerURL);
				$this->addNFOTextNode($xmldocNFO, $root, "fanart"			, 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, "/Data/Series/fanart"));
				file_put_contents($nfoFilename1, $xmldocNFO->saveXML());
				file_put_contents($nfoFilename2, $xmldocNFO->saveXML());
				if (!file_exists($bannerFilename)) {
					file_put_contents($bannerFilename, $this->QDNet->getURL($bannerURL));
				}
			}
		}
	}

	function makeEpisodeNFO($filename,$writeFiles=true,$writeDB=false) {
		$seriePath = $this->getSeriePath(dirname($filename));
		if (file_exists($seriePath . '/tvdb_all.xml')) {
			//die($seriePath . '/tvdb_all.xml');
			$xpath = $this->getXmlDocFromSeriePath($seriePath);
			$seriePathD = pathinfo($filename);
			$res = $this->extractSeriesFilenameStruct($seriePathD['basename']);
			$pathEpisode = "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']";
			if ($res['found'] && $xpath) {
				$xmldocNFO = new DOMDocument("1.0");
				$root = $xmldocNFO->createElement("episodedetails");
				$xmldocNFO->appendChild($root);
				$o = array();
				$o['seriepath'		] = $seriePath;
				$o['filename'		] = $filename;
				$o['sfilename'		] = $seriePathD['filename'] . '.' . $seriePathD['extension'];
				$o['title'			] = $this->extractXQuery($xpath, $pathEpisode . "/EpisodeName");
				$o['season'			] = $res['saison'];
				$o['episode'		] = $res['episode'] * 1;
				$o['plot'			] = $this->extractXQuery($xpath, $pathEpisode . "/Overview");
				$o['tvdbid'			] = $this->extractXQuery($xpath, $pathEpisode . "/id");
				$o['credits'		] = $this->extractXQuery($xpath, $pathEpisode . "/Writer");
				$o['director'		] = $this->extractXQuery($xpath, $pathEpisode . "/Director");
				$o['aired'			] = $this->extractXQuery($xpath, $pathEpisode . "/FirstAired");
				$o['thumb'			] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, $pathEpisode . "/filename");
				$o['fanart'			] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, "/Data/Series/fanart");
				$o['banner'			] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, '/Data/Series/banner');
				$o['episodetbn'		] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, '/Data/Series/banner');
				$o['serieName'		] = $this->extractXQuery($xpath, '/Data/Series/SeriesName');
				$o['serieOverview'	] = $this->extractXQuery($xpath, '/Data/Series/Overview');
				$o['seriePremiered'	] = $this->extractXQuery($xpath, '/Data/Series/FirstAired');
				$fanartFilename	= $seriePathD['dirname'] . '/season' . sprintf('%02d', $o['season']) . '.tbn';
				$nfoFilename	= $seriePathD['dirname'] . '/' . $seriePathD['filename'] . '.nfo';
				$tbnFilename	= $seriePathD['dirname'] . '/' . $seriePathD['filename'] . '.tbn';
				$bannerFilename = $seriePathD['dirname'] . '/folder.jpg';

				$this->addNFOTextNode($xmldocNFO, $root, "title"	, $o['title'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "season"	, $o['season'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "episode"	, $o['episode'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "plot"		, $o['plot'		]);
				$this->addNFOTextNode($xmldocNFO, $root, "credits"	, $o['credits'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "director"	, $o['director'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "aired"	, $o['aired'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "thumb"	, $o['thumb'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "fanart"	, $o['fanart'	]);
				if($writeFiles){
					file_put_contents($nfoFilename, $xmldocNFO->saveXML());
					//unlink($nfoFilename);
					if (!file_exists($fanartFilename)) 	file_put_contents($fanartFilename	, $this->QDNet->getURL($o['fanart'	]));
					if (!file_exists($bannerFilename)) 	file_put_contents($bannerFilename	, $this->QDNet->getURL($o['banner'	]));
					if (!file_exists($bannerFilename)) 	file_put_contents($bannerFilename	, $this->QDNet->getURL($o['banner'	]));
					if (!file_exists($tbnFilename	))	file_put_contents($tbnFilename		, $this->QDNet->getURL($o['thumb'	]));
				}
				if($writeDB){
					$lang='';
					if(preg_match('! FR$!',dirname($filename))){
						$lang='FR';
					}
					if(preg_match('! VO$!',dirname($filename))){
						$lang='VO';
					}
					if($lang){
						$o['title'].=' ('.$lang.')';
					}
					$this->makeEpisodeDB($o);
				}
			}
		}
	}

	function svc_updateDatabase() {
		header('Content-Type: text/html;');
		$arrDrive = $this->svc_getSeriesTree();
		//db($arrDrive);die();
		$nb=0;
		foreach($arrDrive as $drivePath){
			if (is_array($drivePath['children'])){
				foreach($drivePath['children'] as $seriePath){
					if (is_array($seriePath['children'])){
						foreach($seriePath['children'] as $saisonPath){
							db($seriePath['fullname']);
							db($saisonPath['fullname']);
							$files = $this->pri_getFiles($saisonPath['fullname'],false);
							unset($files['arrSerie']);
							//db($files);
							set_time_limit(20);
							foreach($files['results'] as $file){
								if($file['formatOK']){
									$fullFileName = $file['pathName'].'/'.$file['filename'];
									$this->makeEpisodeNFO($fullFileName,false,true);
								}
							}
						}
					}
				}
				//die();//one drive
			}
		}
	}


	function svc_extractSeriesFilenameStruct($intern = false) {
		return $this->extractSeriesFilenameStruct($_REQUEST['filename']);
	}

	function extractSeriesFilenameStruct($filename) {
		$res['found'] = false;
		foreach ($this->arrRegex as $k => $rgx) {
			$res['filename'] = $filename;
			if (preg_match('`' . $rgx['rgx'] . '`i', $filename, $match)) {
				$res['saison'	] = ($match[$rgx['s']] * 1);
				$res['episode'	] = ($match[$rgx['e']] * 1);
				$res['rgx'		] = $rgx['rgx'];
				$res['rgxnum'	] = $k;
				$res['rgx_match'] = $match;
				$res['found'	] = true;
				$pos = strpos($res['filename'],$res['rgx_match'][0]);
				if($pos!==false){
					$res['root_file']=substr($res['filename'],0,$pos);
					$res['clean_root_file']=preg_replace('! !',' ',ucfirst(strtolower(str_replace(array('.','_'),array(' ',' '),($res['root_file'])))));
				}
				break;
			}
		}
		return $res;
	}

}
?>