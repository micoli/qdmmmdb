<?php
/*
select media_type,count(*) from art group by  media_type;
delete from art where url like '/mnt/%';
not in ('movie','actor');


delete from tvshowlinkpath where idShow in (
		select idShow from tvshow where c16 like '/mnt/%' order by c00
);

delete from seasons where idShow in (
		select idShow from tvshow where c16 like '/mnt/%' order by c00
);

delete from episode where idShow in (
		select idShow from tvshow where c16 like '/mnt/%' order by c00
);

delete from tvshow where c16 like '/mnt/%';

select * from tvshow  order by c00;

update tvshow set c16=replace(c16,'/mediaserver','/MEDIASERVER');

select replace(c16,'/mediaserver','/MEDIASERVER') from tvshow;

update path set strPath=replace(strPath,'/mediaserver','/MEDIASERVER');

select * from path where strContent not in ('movies');



select * from tvshow where c16 like '%defiance%' or idShow=508;
select * from art
inner join tvshow on media_id=idShow and media_type='tvshow'
where true;



*/
class QDSeriesProxy extends QDMediaDBProxy{
	static $tvdbxpath=array();
	static $tvdbxpathId=array();
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

	function getDriveFromName($name){
		$path=false;
		foreach($this->folderSeriesList as $v){
			if ($v['name']==$name){
				$path=$v;
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
				$this->makeEpisodeNFO($destFile,true,true,false);
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
		$urlen	= sprintf('http://www.thetvdb.com/api/%s/series/%s/en.xml',$this->thetvdbkey,$id);
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
		$this->makeSerieNFO(str_replace("\\'", "'", $path) . '/tvshow.nfo');

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
				'year'		=> $att['FirstAired'],
				'banner'	=> !isset($att['banner'])?"":$att['banner']
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
			$id=array_key_exists_assign_default('id', $_REQUEST, false);
			$res = array();
			if(!$id || $id=='SeriesRoot'){
				$n=0;
				foreach ($this->folderSeriesList as $v) {
					$res[] = array(
						'text'		=> $v['name'],
						'fullname'	=> $v['path'],
						'rootDrive'	=> 1,
						'leaf'		=> false,
						'id'		=> '::'.$v['name'],
						'children'	=> $n==0?$this->getSeriesDirectory($v['path']):array()
						//'children'	=> $this->getSeriesDirectory($v['path'])
					);
					$n++;
				}
				QDSession::_set('cacheFolderTreeSeries', $res);
			}else{
				if(substr($id,0,2)=='::'){
					$rootDrive=1;
					$v = $this->getDriveFromName(substr($id,2));
					$v['id']='::'.$v['name'];
				}else{
					$rootDrive=0;
					$v=array('name'=>basename($id),'path'=>$id,'id'=>$id);
				}
				$res = /*array(
						'text'		=> $v['name'],
						'fullname'	=> $v['path'],
						'rootDrive'	=> $rootDrive,
						'id'		=> $v['id'],
						'children'	=>*/ $this->getSeriesDirectory($v['path']);
				//);
			}
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
				'id'		=> $v,
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
				$arrToRename = $this->pri_getFiles($v, true,$xpath);
				if (array_key_exists('results',$arrToRename) && is_array($arrToRename['results']) && count($arrToRename['results'])>0){
					$thisDir['numbertorename']=count($arrToRename['results']);
				}
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
		uasort($arr,function($a,$b){
			return strcasecmp($a['text'],$b['text']);
		});
		return array_values($arr);
	}

	private function getBannersXml($seriesid) {
		if ($seriesid) {
			$url = 'http://www.thetvdb.com/api/' . $this->thetvdbkey . '/series/' . $seriesid . '/banners.xml';
			if (url_exists($url)) {
				return $this->QDNet->getCacheURL($url, 'seriesBanners', $this->cacheminutes, $this->cache);
			}
		}
		return '';
	}

	function getUpdateUrlOrXmlFromSeriePath($seriePath, $mode) {
		return $this->getUpdateUrlOrXmlFromSerieID($this->getSeriesIdFromXml($seriePath),$mode,$seriePath);
	}

	function getUpdateUrlOrXmlFromSerieID($seriesid, $mode,$seriePath) {
		if ($seriesid) {
			if ($seriePath!='' && file_exists($seriePath . '/tvdb_all.xml') && filesize($seriePath . '/tvdb_all.xml')) {
				$xml = file_get_contents($seriePath . '/tvdb_all.xml');
			} else {
				$urlfr = 'http://www.thetvdb.com/api/' . $this->thetvdbkey . '/series/' . $seriesid . '/all/fr.xml';
				$urlen = 'http://www.thetvdb.com/api/' . $this->thetvdbkey . '/series/' . $seriesid . '/all/en.xml';
				if (url_exists($urlfr)) {
					$url = $urlfr;
				} else {
					$url = $urlen;
				}
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
		if(array_key_exists($seriePath,self::$tvdbxpath)){
			return self::$tvdbxpath[$seriePath];
		}
		if (file_exists($seriePath . '/tvdb.xml')) {
			$xml = $this->getUpdateUrlOrXmlFromSeriePath($seriePath, 'xml');
			$xpath = $this->getXpathFromXmlDoc($xml);
			self::$tvdbxpath[$seriePath]=$xpath;
		}
		return $xpath;
	}

	function getXpathFromXmlDoc($xml){
		$xml = $this->mb_str_replace('’', "\'", $xml);
		$doc = new DomDocument;
		$doc->loadXML($xml);
		$xpath = new DOMXPath($doc);
		return $xpath;
	}

	function getXmlDocFromSerieId($serieId) {
		$xpath = false;
		if(!array_key_exists($serieId,self::$tvdbxpathId)){
			$xml = $this->getUpdateUrlOrXmlFromSerieId($serieId, 'xml','');
			$xml = $this->mb_str_replace('’', "\'", $xml);
			self::$tvdbxpathId[$serieId]=$xml;
		}
		return self::$tvdbxpathId[$serieId];
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
				$new		= realpath($SeriePath) . "/" . utf8_encode($v['serie']) . " [" . $v['saison'] . 'x' . sprintf('%02d', $v['episode']) . '] ' . $v['new'] . '.' . $v['ext'];
				$new64		= realpath($SeriePath) . "/" . utf8_encode($v['serie']) . " [" . $v['saison'] . 'x' . sprintf('%02d', $v['episode']) . '] ' . utf8_encode(base64_decode($v['new64'])) . '.' . $v['ext'];
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
						$this->makeEpisodeNFO($new64,true,true);
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
		//db($formatName);
		//db($this->episodeFormats[$formatName]);
		if (array_key_exists($formatName, $this->episodeFormats)){
			$rtn = sprintf($this->episodeFormats[$formatName],trim($serieName),trim($saison),trim($episode),$this->cleanFilename($episodeName),trim($extension));
		}else{
			$rtn = '';
		}
		//db(func_get_args());
		//db($formatName);
		//db($this->episodeFormats[$formatName]);
		//print "->".$rtn."\n";
		return $rtn;
	}

	function isEpisodeFileNameOK($currentFileName,$serieName,$saison,$episode,$episodeName,$extension){
		$isOk = false;
		//db($this->episodeFormats);
		//print "AAA<br>";
		foreach($this->episodeFormats as $formatName=>$formatString){
			$currentReformatedFilename = $this->pri_formatEpisodeFilename($formatName, $serieName, $saison, $episode, $episodeName, $extension);
			//print "A1 ";db(($currentReformatedFilename));
			//print "A2 ";db(utf8_decode($currentFileName));
			if (($currentReformatedFilename == $currentFileName)||($currentReformatedFilename== utf8_decode($currentFileName))){
				$isOk = true;
				break;
			}
			$currentReformatedFilename = $this->pri_formatEpisodeFilename($formatName, $serieName, $saison, $episode, '', '');
			//print "A1 ";db(($currentReformatedFilename));
			//print "A2 ";db($currentFileName);
			if ((mb_strpos($currentFileName,$currentReformatedFilename)!==false)||(strpos(utf8_decode($currentFileName),$currentReformatedFilename) !==false) ){
				$isOk = true;
				break;
			}
		}
		//var_dump($currentReformatedFilename);
		//var_dump($isOk);
		//die();
		return $isOk;
	}

	function svc_getFiles() {
		return $this->pri_getFiles($_REQUEST['fullpath'], $_REQUEST['only2Rename'] == 'true');
	}

	function pri_getFiles($path, $only2Rename,$xpath=null) {
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
		if(is_null($xpath)){
			$xpath = $this->getXmlDocFromSeriePath($seriePath);
		}
		$arr = array();
		$tmp = glob($str . '/*.*');
		$arr = array();
		$arr['bannerImg'	] = ($this->extractXQuery($xpath, "/Data/Series/banner"));
		$arr['bannerText'	] = utf8_encode($this->extractXQuery($xpath, "/Data/Series/Overview"));
		$arr['serieName'	] = utf8_encode($this->cleanFilename($this->extractXQuery($xpath, "/Data/Series/SeriesName")));
		$arr['results'		] = array();
		//db($arr);print json_encode($arr);die();
		if (file_exists($seriePath . '/tvdb_all.xml') && filesize($seriePath . '/tvdb_all.xml')) {
			/*
			$xml = new DOMDocument;
			$xml->load($seriePath . '/tvdb_all.xml');
			$xsl = new DOMDocument;
			$xsl->load(dirname(__FILE__) . '/data/tvdb_all.xsl');
			$proc = new XSLTProcessor;
			$proc->importStyleSheet($xsl);
			$arr['serieHTML'] = base64_encode(utf8_decode($proc->transformToXML($xml)));
			 */
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
		//header('content-type:text/html');
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

				$bannerURL = 'http://thetvdb.com/banners/'					. $this->extractXQuery($xpath, "/Data/Series/banner");
				$fanartURL = 'http://thetvdb.com/banners/'					. $this->extractXQuery($xpath, "/Data/Series/fanart");
				$this->addNFOTextNode($xmldocNFO, $root, "title"			, $this->extractXQuery($xpath, "/Data/Series/SeriesName"));
				$this->addNFOTextNode($xmldocNFO, $root, "plot"				, $this->extractXQuery($xpath, "/Data/Series/Overview"));
				$this->addNFOTextNode($xmldocNFO, $root, "episodeguideurl"	, str_replace('.xml', '.zip', $this->getUpdateUrlOrXmlFromSeriePath($seriePath, 'url')));
				$this->addNFOTextNode($xmldocNFO, $root, "premiered"		, $this->extractXQuery($xpath, "/Data/Series/FirstAired"));
				$this->addNFOTextNode($xmldocNFO, $root, "banner"			, $bannerURL);
				$this->addNFOTextNode($xmldocNFO, $root, "fanart"			, 'http://thetvdb.com/banners/' . $this->extractXQuery($xpath, "/Data/Series/fanart"));
				file_put_contents($nfoFilename1, $xmldocNFO->saveXML());
				file_put_contents($nfoFilename2, $xmldocNFO->saveXML());
				if (!file_exists($bannerFilename)) {
					//file_put_contents($bannerFilename, $this->QDNet->getURL($bannerURL));
				}
			}
		}
	}
	//from Media-Manager-for-NAS-for-XBMC /application/libraries/scrapers/video/tvshows/tvdb_com.php
	//git clone git://github.com/tamplan/Media-Manager-for-NAS-for-XBMC.git

	private function _get_remote_images($seriesId){
		$remote_images = $this->getBannersXml($seriesId);

		if(!$remote_images) return '';

		$xml = new SimpleXMLElement($remote_images);
		db($xml);die();
	}
	private function _get_remote_imagesXml($seriesId, $type = 'poster'){
		$remote_images = $this->getBannersXml($seriesId);

		if(!$remote_images) return '';
		try{
			$xml = new SimpleXMLElement($remote_images);
		}catch(Exception $e){
			return;
		}

		$posters = '';
		$backdrops = '<fanart url="http://thetvdb.com/banners/">';

		foreach($xml->Banner as $image){
			if ((string) $image->BannerType == 'fanart'){
				$backdrops .= '<thumb dim="'.(string) $image->BannerType2.'" colors="'.(string) $image->Colors.'" preview="'.(string) $image->ThumbnailPath.'">'.(string) $image->BannerPath.'</thumb>';
			}else{
				if ((string) $image->BannerType2 == 'season'){
					$posters .= '<thumb type="season" season="'.(string) $image->Season.'">';
				}else{
					$posters .= '<thumb>';
				}

				$posters .= 'http://thetvdb.com/banners/';
				$posters .= (string) $image->BannerPath;
				$posters .= '</thumb>';
			}
		}

		$backdrops .= '</fanart>';

		if ($type == 'poster'){
			return $posters;
		}else{
			return $backdrops;
		}
	}

	function makeEpisodeNFO($filename,$writeFiles=true,$writeDB=false,$forceFile = false) {
		QDLogger::log($filename);
		$seriePath = $this->getSeriePath(dirname($filename));
		if (file_exists($seriePath . '/tvdb_all.xml')) {
			//die($seriePath . '/tvdb_all.xml');
			$xpath = $this->getXmlDocFromSeriePath($seriePath);
			$seriePathD = pathinfo($filename);
			if(in_array(strtolower($d['extension']), $this->subtitlesExt)){
				return false;
			}
			$res = $this->extractSeriesFilenameStruct($seriePathD['basename']);
			$pathEpisode = "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']";
			if ($res['found'] && $xpath) {

				$xmldocNFO = new DOMDocument("1.0");
				$root = $xmldocNFO->createElement("episodedetails");
				$xmldocNFO->appendChild($root);

				$o = array();

				$o['seriepath'		] = $seriePath;
				$o['saisonpath'		] = dirname($filename);
				$o['filename'		] = $filename;
				$o['sfilename'		] = $seriePathD['filename'] . '.' . $seriePathD['extension'];
				$o['title'			] = $this->extractXQuery($xpath, $pathEpisode . "/EpisodeName");
				$o['season'			] = $res['saison'];
				$o['episode'		] = $res['episode'] * 1;
				$o['plot'			] = $this->extractXQuery($xpath, $pathEpisode . "/Overview");
				$o['tvdbid'			] = $this->extractXQuery($xpath, $pathEpisode . "/id");
				$o['tvdbidshow'		] = $this->extractXQuery($xpath, "/Data/Series/id");
				$o['credits'		] = $this->extractXQuery($xpath, $pathEpisode . "/Writer");
				$o['director'		] = $this->extractXQuery($xpath, $pathEpisode . "/Director");
				$o['aired'			] = $this->extractXQuery($xpath, $pathEpisode . "/FirstAired");
				$o['serieName'		] = $this->extractXQuery($xpath, '/Data/Series/SeriesName');
				$o['serieOverview'	] = $this->extractXQuery($xpath, '/Data/Series/Overview');
				$o['seriePremiered'	] = $this->extractXQuery($xpath, '/Data/Series/FirstAired');

				$o['thumb'			] = 'http://thetvdb.com/banners/' . $this->extractXQuery($xpath, $pathEpisode . "/filename");
				$o['fanart'			] = 'http://thetvdb.com/banners/' . $this->extractXQuery($xpath, "/Data/Series/fanart");
				$o['banner'			] = 'http://thetvdb.com/banners/' . $this->extractXQuery($xpath, '/Data/Series/banner');
				$o['poster'			] = 'http://thetvdb.com/banners/' . $this->extractXQuery($xpath, '/Data/Series/poster');
				$o['episodetbn'		] = 'http://thetvdb.com/banners/' . $this->extractXQuery($xpath, '/Data/Series/banner');
				$o['clearart'		] = '';
				$o['clearartlogo'	] = '';
				$o['seasontbn'		] = '';

				//$aFanartTv = $this->getMediasFanartTv($o['tvdbidshow']);
				$aFanartTv=null;
				if(is_array($aFanartTv)){
					if(array_key_exists('tvlogo',$aFanartTv) && count($aFanartTv['tvlogo'])){
						$o['clearlogo'		]= $aFanartTv['tvlogo'][0]['url'];
					}
					if(array_key_exists('clearlogo',$aFanartTv) && count($aFanartTv['clearlogo'])){
						$o['clearlogo'		]= $aFanartTv['clearlogo'][0]['url'];
					}
					if(array_key_exists('clearart',$aFanartTv) && count($aFanartTv['clearart'])){
						$o['clearart'		]= $aFanartTv['clearart'][0]['url'];
					}
					if(array_key_exists('hdclearart',$aFanartTv) && count($aFanartTv['hdclearart'])){
						$o['clearart'		]= $aFanartTv['hdclearart'][0]['url'];
					}
					if(array_key_exists('tvbanner',$aFanartTv) && count($aFanartTv['tvbanner'])){
						$o['banner'			]= $aFanartTv['tvbanner'][0]['url'];
					}
					if(array_key_exists('tvposter',$aFanartTv) && count($aFanartTv['tvposter'])){
						$o['poster'			]= $aFanartTv['tvposter'][0]['url'];
					}
					if(array_key_exists('tvthumb',$aFanartTv) && count($aFanartTv['tvthumb'])){
						$o['fanart'			]= $aFanartTv['tvthumb'][0]['url'];
					}
					if(array_key_exists('seasonthumb',$aFanartTv) && count($aFanartTv['seasonthumb'])&& count($aFanartTv['seasonthumb'][$res['saison']])){
						$o['seasontbn'	]= $aFanartTv['seasonthumb'][$res['saison']][0]['url'];
					}
				}

				$this->addNFOTextNode($xmldocNFO, $root, "title"	, $o['title'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "season"	, $o['season'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "episode"	, $o['episode'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "plot"		, $o['plot'		]);
				$this->addNFOTextNode($xmldocNFO, $root, "credits"	, $o['credits'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "director"	, $o['director'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "aired"	, $o['aired'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "thumb"	, $o['thumb'	]);
				$this->addNFOTextNode($xmldocNFO, $root, "fanart"	, $o['fanart'	]);

				$saisonNfoFilename	= $seriePathD['dirname'] . '/' . $seriePathD['filename'] . '.nfo';

				$o['files'] = array();
				$o['files']['seasontbn'	] = array('type'=>'sea','art_type'=>'thumb'		,'file'=>$seriePath . '/season' . sprintf('%02d', $o['season']) . '.tbn');
				$o['files']['banner'	] = array('type'=>'sho','art_type'=>'banner'	,'file'=>$seriePath . '/banner.jpg');
				$o['files']['clearart'	] = array('type'=>'sho','art_type'=>'clearart'	,'file'=>$seriePath . '/clearart.png');
				$o['files']['clearlogo'	] = array('type'=>'sho','art_type'=>'clearlogo'	,'file'=>$seriePath . '/clearart.png');
				$o['files']['fanart'	] = array('type'=>'sho','art_type'=>'fanart'	,'file'=>$seriePath . '/fanart.jpg');
				$o['files']['poster'	] = array('type'=>'sho','art_type'=>'poster'	,'file'=>$seriePath . '/poster.jpg');
				$o['files']['thumb'		] = array('type'=>'epi','art_type'=>'thumb'		,'file'=>$seriePathD['dirname'] . '/' . $seriePathD['filename'] . '.tbn');

				$o['posters'		]=$this->_get_remote_imagesXml($this->extractXQuery($xpath, "/Data/Series/id"),'poster');
				$o['backdrops'		]=$this->_get_remote_imagesXml($this->extractXQuery($xpath, "/Data/Series/id"),'backdrop');
				$o['arts'			]=array();

				if($writeFiles && (!file_exists($saisonNfoFilename)|| $forceFile)){
						file_put_contents($saisonNfoFilename, $xmldocNFO->saveXML());
						QDLogger::log($saisonNfoFilename);
				}
				foreach($o['files'] as $k=>$v){
					if(array_key_exists($k,$o) && $o[$k]){
						if ($writeFiles && (!file_exists($v['file'])|| $forceFile)){
							file_put_contents($v['file']	, $this->QDNet->getCacheURL($o[$k],'theMovieDB',60*24*700,true));
							QDLogger::log($v['file']);
						}
						$o['art'][]=array('type'=>$k	,'table'=>$v['type'],'file'=>$v['file'],'art_type'=>$v['art_type']);
					}
				}

				if($writeDB){
					$lang='';
					if(preg_match('! FR$!',dirname($filename))){
						$lang='FR';
					}
					if(preg_match('! VF$!',dirname($filename))){
						$lang='FR';
					}
					if(preg_match('! VO$!',dirname($filename))){
						$lang='VO';
					}
					if($lang){
						$o['title'].=' ('.$lang.')';
					}

					//////////////////$this->makeEpisodeDB($o);
				}
			}
		}
		/*if (file_exists($tbnFilename	)){
			$o['art'][]=array('type'=>'fanart','file'=>$tbnFilename);
		}*/
		/*
		<thumb aspect="banner">http://thetvdb.com/banners/graphical/255326-g5.jpg</thumb>
		<thumb aspect="banner">http://thetvdb.com/banners/graphical/255326-g4.jpg</thumb>
		<thumb aspect="banner">http://thetvdb.com/banners/graphical/255326-g.jpg</thumb>
		<thumb aspect="banner">http://thetvdb.com/banners/graphical/255326-g3.jpg</thumb>
		<thumb aspect="banner">http://thetvdb.com/banners/graphical/255326-g2.jpg</thumb>
		<thumb aspect="banner">http://thetvdb.com/banners/graphical/255326-g6.jpg</thumb>
		<thumb aspect="poster" type="season" season="1">http://thetvdb.com/banners/seasons/255326-1.jpg</thumb>
		<thumb aspect="poster" type="season" season="1">http://thetvdb.com/banners/seasons/255326-1-2.jpg</thumb>
		<thumb aspect="poster">http://thetvdb.com/banners/posters/255326-3.jpg</thumb>
		<thumb aspect="poster">http://thetvdb.com/banners/posters/255326-2.jpg</thumb>
		<thumb aspect="poster">http://thetvdb.com/banners/posters/255326-1.jpg</thumb>
		<thumb aspect="poster" type="season" season="-1">http://thetvdb.com/banners/posters/255326-3.jpg</thumb>
		<thumb aspect="poster" type="season" season="-1">http://thetvdb.com/banners/posters/255326-2.jpg</thumb>
		<thumb aspect="poster" type="season" season="-1">http://thetvdb.com/banners/posters/255326-1.jpg</thumb>
		*/
	}

	function svc_updateAllXml(){
		//$this->folderSeriesList=array($this->folderSeriesList[10],$this->folderSeriesList[11]);
		//db($this->folderSeriesList);die();
		$forceRefresh = ($_REQUEST['forceRefresh']=="true");
		foreach ($this->folderSeriesList as $v) {
			$drivePath = array(
					'text'		=> $v['name'],
					'fullname'	=> $v['path'],
					'rootDrive'	=> 1,
					'leaf'		=> false,
					'id'		=> '::'.$v['name'],
					'children'	=> $this->getSeriesDirectory($v['path'])
			);
			if (is_array($drivePath['children'])){
				foreach($drivePath['children'] as $seriePath){
					if (is_array($seriePath['children'])){
						$path=$seriePath['fullname'].'/';
						if (count(glob($path.'tvshow.nfo'))==1 && (count(glob($path.'tvdb_all.xml'))==0 || $forceRefresh)){
							db("missing ".$path);
							$tvdbid=$this->getTvDbIdFromPath($path);
							if($tvdbid){
								$this->pri_setSerieFromPath($path,$tvdbid);
							}
						}
					}
				}
			}
		}
	}

	function pri_filterPaths(){
		if(array_key_exists("current",$_REQUEST) && $_REQUEST['current']){
			foreach($this->folderSeriesList as $k=>$v){
				if(!$v['current']){
					unset($this->folderSeriesList[$k]);
				}
			}
		}
		if(array_key_exists("key",$_REQUEST) && $_REQUEST['key']){
			foreach($this->folderSeriesList as $k=>$v){
				if($v['name']!=$_REQUEST['key']){
					unset($this->folderSeriesList[$k]);
				}
			}
		}
		$this->folderSeriesList=array_values($this->folderSeriesList);
	}

	function svc_updateDatabase() {
		//$this->folderSeriesList=array($this->folderSeriesList[11]);
		//unset($this->folderSeriesList[0]);
		//db($this->folderSeriesList);die();
		header('Content-Type: text/html;');
		$this->pri_filterPaths();

		if(array_key_exists('pathshow',$_REQUEST)){
			if (substr($_REQUEST['pathshow'],-1)=='/'){
				$_REQUEST['pathshow']=substr($_REQUEST['pathshow'],0,strlen($_REQUEST['pathshow'])-1);
			}
			$folderSeriesList=array(array('path'=>dirname($_REQUEST['pathshow'])));
			$fmask=str_replace(dirname($_REQUEST['pathshow']).'/','',$_REQUEST['pathshow']);
		}else{
			$folderSeriesList=$this->folderSeriesList;
			$fmask='*';
		}

		foreach ($folderSeriesList as $v) {
			$seriePaths=glob($v['path'].'/'.$fmask,GLOB_ONLYDIR);
			if (is_array($seriePaths)){
				foreach($seriePaths as $seriePath){
					QDLogger::log($seriePath);
					if(file_exists($seriePath.'/folder.jpg')){
						rename($seriePath.'/folder.jpg',$seriePath.'/folder.jpg.delete');
						print sprintf("#ren %s\n",$seriePath.'/folder.jpg');
					}
					$saisonPaths=glob($seriePath.'/*',GLOB_ONLYDIR);
					db($saisonPath);
					$saisonPath='';
					if (is_array($saisonPaths)){
						foreach($saisonPaths as $saisonPath){
							QDLogger::log($saisonPath);
							foreach(array('folder.jpg','poster.jpg','season*.tbn') as $mask){
								foreach(glob($saisonPath.'/'.$mask) as $tbn){
									rename($tbn,$tbn.'.delete');
									print sprintf("#ren %s\n",$tbn);
								}
							}

							$files = $this->pri_getFiles($saisonPath,false);
							set_time_limit(45);
							foreach($files['results'] as $file){
								$fullFileName = $file['pathName'].'/'.$file['filename'];
								if($file['formatOK']){
									$this->makeEpisodeNFO($fullFileName,true,true,false);
								}
							}
						}
						//die();//one serie in drive
					}
				}
				//die();//one drive
			}
		}
	}

	function getMediasFanartTv($tvdbId){
		$url = sprintf('http://api.fanart.tv/webservice/series/%s/%s/json/all/1/2',$this->fanarttvdbkey,$tvdbId);
		$res = array();
		if (url_exists($url)) {
			$strJson= $this->QDNet->getCacheURL($url, 'fanartTv', 60*24*150, true);
			$aFanartTv = json_decode($strJson,true);
			if(is_array($aFanartTv)){
				$serie = array_pop($aFanartTv);
				foreach(array(
					'clearart',
					'hdtvlogo',
					'characterart',
					'hdclearart',
					'clearlogo',
					'seasonthumb',
					'tvthumb',
					'tvbanner',
					'tvposter'
				) as $type){
					if(array_key_exists($type,$serie)){
						if($type=="seasonthumb"){
							foreach($serie[$type] as $media){
								$res[$type][$media['season']][]=$media;
							}
						}else{
							$res[$type]=$serie[$type];
						}
					}
				}
			}
		}
		return $res;
	}

	function getTvDbIdFromPath($seriePath){
		if (file_exists($seriePath.'/tvshow.nfo')) {
			$xml = file_get_contents($seriePath.'/tvshow.nfo');
			$struct = simplexml_load_string($xml);
			$url=$struct->episodeguide->url;
			if(!$url){
				$url=$struct->episodeguideurl;
			}
			if(preg_match('!\/series\/([0-9]*?)\/all!',$url,$m)){
				if($m[1] && is_numeric($m[1])){
					return $m[1]*1;
				}
			}
		}
		return false;
	}

	function svc_updateFanartCache() {
		//$this->folderSeriesList=array($this->folderSeriesList[0]);
		//db($this->folderSeriesList);die();
		$fanartPath='/var/www/fanart';
		foreach ($this->folderSeriesList as $v) {
			$seriePaths=glob($v['path'].'/*',GLOB_ONLYDIR);
			if (is_array($seriePaths)){
				foreach($seriePaths as $seriePath){
					db($seriePath);
					$tvdbId=$this->getTvDbIdFromPath($seriePath);
					if($tvdbId){
						$nb=0;
						db("$seriePath => $tvdbId");
						@mkdir($fanartPath.'/'.$tvdbId);
						$allMedias = $this->getMediasFanartTv($tvdbId);
						foreach($allMedias as $type=>$medias){
							@mkdir($fanartPath.'/'.$tvdbId.'/'.$type);
							foreach($medias as $media){
								$pathInfo = pathinfo($media['url']);
								$dstFile = $fanartPath.'/'.$tvdbId.'/'.$type.'/'.$media['id'].'.'.$pathInfo['extension'];
								if(!file_exists($dstFile)){
									db($media['url']." downloading");
									$nb++;
									file_put_contents($dstFile,$this->QDNet->getCacheURL($media['url'], 'fanartTv', 60*24*700, true));
								}else{
									db($dstFile." exists");
								}
							}
						}
					}
					if($nb){
						//sleep(10);
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
				if($rgx['tyear'] && preg_match('!(19|20)[0-9]{2}!',$filename)){
					continue;
				}
				$res['saison'	] = ($match[$rgx['s']] * 1);
				$res['episode'	] = ($match[$rgx['e']] * 1);
				$res['serie'	] = $match[$rgx['n']];
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
