<?php
use App\Components\QDmmmDB\Misc\Tools;

class QDMoviesProxy extends QDMediaDBProxy{
	var $QDNet;

	function svc_getSerieFromPath() {
		$path = $_REQUEST['p'];
		$path = $this->getSeriePath($path);
		return( array ('results'=> array ('name'=>basename($path))));
	}

	function getSeriePath($path) {
		if (ereg('S[0-9]{1,} (.*)', $path)) {
			$path = dirname($path);
		}
		return $path;
	}

	function svc_chooseMovies() {
		$moviesname = trim($_REQUEST['m']);
		$path = $_REQUEST['p'];
		$sc = new scraperAllocine;
		$res = array ();
		$res['results'] = $sc->getList($moviesname);
		return $res;
	}

	function svc_chooseMoviesDetail() {
		$id = trim($_REQUEST['id']);
		$sc = new scraperAllocine;
		$res = array ();
		$t = $sc->getDetail('', $id);
		//print_r($t);
		$res['data'] = $t;
		return $res;
	}

	function svc_setSerieFromPath() {
		$path	= $_REQUEST['p'];
		$id		= $_REQUEST['i'];
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
		$dom		= Tools::object2array($sdom);
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
				return (string)$dom->tvshow->id;
			} else {
				return null;
			}
		}
	}

	function svc_checkMoviesPicture() {
		$path = "m:/___films";
		$paths = glob($path . "/*", GLOB_ONLYDIR);
		foreach ($paths as $path) {
			if (file_exists($path . "/info.txt") &&
				!file_exists($path . "/movie.tbn")) {
				$t = glob($path . "/*.nfo");
				if (array_key_exists(0, $t)) {
					$sdom = \simplexml_load_string(file_get_contents($t[0]));
					;
					$f = Tools::object2array($sdom->xpath('/details'));
					$f = $f[0];
					$poster = '';
					if (array_key_exists('thumbs', $f) && array_key_exists('thumb', $f['thumbs'])) {
						set_time_limit(90);
						$poster = is_array($f['thumbs']['thumb']) ? $f['thumbs']['thumb'][0] : $f['thumbs']['thumb'];
						$t = $this->QDNet->getCacheURL($poster, 'seriesDetail', $this->cacheminutes, $this->cache);
						file_put_contents($path . '/movie.tbn', $t);
						file_put_contents($path . '/folder.jpg', $t);
						print $poster . "<br>";
					}
				}
			}
		}
	}

	function svc_download_newzleech() {
		$url = 'http://www.newzleech.com/?m=gen&getnzb=Get%20NZB&p=&mode=usenet&offset=0&type=&b=&group=&age=&get=0&q=' . urlencode($_REQUEST['q']);
		$ids = split('!', $_REQUEST['ids']);
		foreach ($ids as $v) {
			$url = $url . '&binary[]=' . $v;
		}
		//die($url);
		if (@file_put_contents("\\\\192.168.1.109\\_dwn\\__NZB\\" . date('YmdHis') . '.nzb', $this->QDNet->getURL($url))) {
			print "{success:'ok'}";
		} else {
			print "{failure:'bad'}";
		}
	}

	function svc_search_newzleech() {
		$url = 'http://www.newzleech.com/?group=&minage=&age=&min=min&max=max&m=search&adv=&q=' . urlencode($_REQUEST['q']) . '';
		$st = $this->QDNet->getURL($url);
		//print $st;
		//preg_match_all('|\<table class\=\"contentt\"\>(.*?)\<\/table\>|',$this->delmulspace(str_replace("\n","",$st)),$arr);
		//print_r($arr);
		$rgx = 'name="binary\[\]" value="(.*?)"';
		$rgx .= '(.*?)<td class="subject">(.*?)</td>';
		$rgx .= '(.*?)<td class="size">(.*?)</td>';
		$rgx .= '(.*?)<td class="files">(.*?)</td>';
		$rgx .= '(.*?)<td class="complete">(.*?)</td>';
		$rgx .= '(.*?)<td class="age">(.*?)</td>';
		$rgx .= '(.*?)<td class="group" align="right">(.*?)</td>';
		preg_match_all('|' . $rgx . '|', $this->delmulspace(str_replace("\n", "", $st)), $tmpRes);
		//print_r($tmpRes);
		$nb = count($tmpRes);
		for ($i = 0; $i < count($tmpRes[0]) - 1; $i++) {
			$res[] = array(
				"id"		=> $tmpRes[1][$i],
				"title"		=> strip_tags($tmpRes[3][$i]),
				"size"		=> strip_tags($tmpRes[5][$i]),
				"size"		=> strip_tags($tmpRes[5][$i]),
				"percent"	=> strip_tags($tmpRes[9][$i]),
				"group"		=> strip_tags($tmpRes[13][$i]),
				"age"		=> strip_tags($tmpRes[11][$i])
			);
		}
		if (false) {
			print $url;
			print_r(str_replace("<", "\n<", $arr[0]));
			print '<pre>';
			print_r($tmpRes);
		}
		return array('url' => $url, 'posts' => $res);
	}

	function delmulspace($str) {
		do {
			$str = str_replace("  ", " ", $str);
		} while (strpos($str, "  ") > 0);
		return $str;
	}

	function cleanFilename($a) {
		$a = str_replace('?', ' ', $a);
		$a = str_replace(':', ' ', $a);
		$a = str_replace('/', ' ', $a);
		$a = str_replace('\\', ' ', $a);
		return trim($this->delmulspace($a));
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

	function svc_getMoviesTree() {
		//unset($_SESSION['cacheFolderTree']);
		if (array_key_exists('refresh', $_REQUEST) && $_REQUEST['refresh'] = 1) {
			QDSession::_unset('cacheFolderTreeMovies');
		}
		if (QDSession::_isset('cacheFolderTreeMovies')) {
			$res = QDSession::_get('cacheFolderTreeMovies');
		} else {
			$this->nodeId = 10000;
			$res = array();
			foreach ($this->folderMoviesList as $v) {
				$res[] = array(
					'text'		=> $v['name'],
					'fullname'	=> $v['path'],
					'rootDrive'	=> 1,
					'children'	=> $this->getMoviesDirectory($v['path'])
				);
			}
			QDSession::_set('cacheFolderTreeMovies',$res);
		}
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
			if (($parentDir!=$v) && (file_exists($parentDir . '/tvdb.xml')||file_exists($parentDir . '/tvshow.nfo'))) {
				$thisDir['tvdb'] = 'season';
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

	function getMoviesDirectory($path = '.', $level = 0) {
		set_time_limit(90);
		$arr = array();
		$dh = @glob($path . '/*', GLOB_ONLYDIR);
		foreach ($dh as $k => $v) {
			$thisDir = array(
				'text'			=> basename($v),
				'fullname'		=> $v,
				'rootDrive'		=> 0,
				'id'			=> $this->nodeId++,
				'leaf'			=> false,
				'iconCls'		=> 'folder',
				'uiProvider'	=> 'col',
				'tvdb'			=> false,
				'cls'			=> 'folder'
			);

			$seriePath = $this->getSeriePath($v);

			//if (file_exists($seriePath.'/tvdb.xml')){
			//  $thisDir['tvdb']=true;
			//}
			$subdir = $this->getMoviesDirectory($v, ($level + 1));
			if (count($subdir) > 0) {
				$thisDir['children'] = $subdir;
			} else {
				$thisDir['leaf'] = true;
			}
			$arr[] = $thisDir;
		}
		return $arr;
	}

	function extractXQuery($xpath, $xpathQ) {
		$val = '';
		if (!$xpath) {
			return '';
		}
		$arts = $xpath->query($xpathQ);
		foreach ($arts as $k => $art) {
			$val = utf8_decode($art->nodeValue);
			break;
		}
		return $val;
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

	function svc_getXbmcScraperMovieDetail() {
		$sc = new scraperAllocine;
		foreach ($this->folderMoviesList as $path) {
			$tmp = glob($path['path'] . '/*.*');
			//$tmp=array();
			foreach ($tmp as $v) {
				set_time_limit(90);
				$d = pathinfo($v);
				//print_r($d);
				if (in_array(strtolower($d['extension']), $this->allowedExt)) {
					$movieName = htmlentities(utf8_decode(trim($this->cleanMoviesFilename($d['filename'], true))));
					print_r("<b>" . $path['path'] . "/" . $movieName . "</b><br>\n");
					print $sc->getListBatch($movieName);
					print_r("<hr>\n");
					ob_flush();
				}
			}
		}
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
		$arr['bannerImg'] = '';
		$arr['bannerText'] = 'MULTI';
		$arr['serieName'] = 'MULTI';
		return ($arr);
	}

	function svc_getFiles() {
		return $this->pri_getFiles($_REQUEST['fullpath'], $_REQUEST['only2Rename'] == 'true');
	}

	function svc_getMoviesFiles() {
		return $this->pri_getMoviesFiles($_REQUEST['fullpath'], $_REQUEST['only2Rename'] == 'true');
	}

	function cleanMoviesFilename($moviename, $strict = false) {
		foreach ($this->arrCleanupMoviesRegex as $k => $v) {
			$moviename = $this->delmulspace(preg_replace('/' . $v['rgx'] . '/i', array_key_exists('rep', $v) ? $v['rep'] : ' ', ' ' . $moviename . ' '));
		}
		if ($strict) {
			foreach ($this->arrCleanupMoviesRegexStrict as $k => $v) {
				$moviename = $this->delmulspace(preg_replace('/' . $v['rgx'] . '/i', array_key_exists('rep', $v) ? $v['rep'] : ' ', ' ' . $moviename . ' '));
			}
		}
		return ucwords(strtolower($moviename));
	}

	function pri_getMoviesFiles($path, $only2Rename) {
		$str = stripslashes(str_replace('/', '\\\\', utf8_decode($path)));
		$arr = array();
		$tmp = glob($str . '/*.*');
		$arr = array();
		$path = str_replace('\\\\', '\\', $path);
		foreach ($this->folderMoviesList as $v) {
			$p = str_replace('\\\\', '\\', $v['path']);
			if (substr($path, 0, strlen($p)) == $p) {
				$uncPath = str_replace('smb://', 'file:////', str_replace('\\', '/', $v['xbmcpath'] . substr($path, strlen($p))));
				break;
			}
		}

		foreach ($tmp as $v) {
			$d = pathinfo($v);
			//print_r($d);
			if (in_array(strtolower($d['extension']), $this->allowedExt)) {
				$cleanFileName = $this->cleanMoviesFilename($d['filename']);
				if (!$only2Rename && $d['filename'] != $cleanFileName) {
					//if (true){
					$arr['results'][] = array(
						'newfilename'	=> $cleanFileName,
						'title'			=> $this->cleanMoviesFilename($d['filename'], true),
						'oldfilename'	=> $d['filename'],
						'ext'			=> $d['extension'],
						'filesize'		=> size_readable(filesize($v)),
						'pathfilename64'=> base64_encode(realpath($v)),
						'uncfilename64' => base64_encode($uncPath . '/' . $d['filename'] . '.' . $d['extension']),
						'md5'			=> md5(realpath($v))
					);
				}
			}
		}
		return $arr;
	}

	function svc_setMoviesFromPath() {
		//db($_REQUEST);
		$id = 0 + $_REQUEST['i'];
		$path = base64_decode($_REQUEST['p']);
		//print $path;
		$sc = new scraperAllocine;
		$t = $sc->getDetail('', $id);
		$tarray = $sc->getDetail('', $id, 'array');
		//print_r($tarray);
		//print_r($t);
		$d = pathinfo($path);
		//print_r($d['dirname']);
		$newfilename = $this->cleanFilename(utf8_decode($t['title'])); //$d['filename']);
		$newpath = $d['dirname'] . '/' . $newfilename;
		if (true) {//!file_exists($newpath)){
			@mkdir($newpath);
			$newfullfilename = $newpath . '/' . $this->cleanFilename(utf8_decode($t['title']));
			//if(array_key_exists('poster',$tarray) && $tarray['poster']){
			if (false) {
				if (is_array($tarray['poster']))
					$tarray['poster'] = $tarray['poster'][0];
				if (!file_exists($newpath . '/movie.tbn')) {
					file_put_contents($newpath . '/movie.tbn', $this->QDNet->getCacheURL($tarray['poster'], 'seriesDetail', $this->cacheminutes, $this->cache));
				}
				if (!file_exists($newpath . '/folder.jpg')) {
					file_put_contents($newpath . '/folder.jpg', $this->QDNet->getCacheURL($tarray['poster'], 'seriesDetail', $this->cacheminutes, $this->cache));
				}
			}
			file_put_contents($newpath . '/info.txt', 'allocine:' . $id);
			//print_r($d);
			rename($path, $newfullfilename . '.' . strtolower($d['extension']));
			file_put_contents($newfullfilename . '.nfo', $sc->getDetail('', $id, 'xml'));
			//print $newfilename;
			print '{success:true}';
		} else {
			print '{success:false}';
		}
	}

	function pri_getFiles($path, $only2Rename) {
		$str = $path; //stripslashes(str_replace('/', '\\\\', utf8_decode($path)));
		$seriePath = $this->getSeriePath($str);
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

		foreach ($tmp as $v) {
			$d = ToolsFiles::pathinfo_utf($v);
			if (in_array(strtolower($d['extension']), $this->allowedExt)) {
				$episodeName = '';
				$Overview = '';
				$res = $this->extractSeriesFilenameStruct($d['basename']);
				if ($res['found'] && $xpath) {
					$episodeName	= $this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']/EpisodeName");
					$Overview		= $this->extractXQuery($xpath, "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']/Overview");
				}
				if (!$only2Rename || ($arr['serieName'] . ' [' . $res['saison'] . 'x' . sprintf('%02d', $res['episode']) . '] ' . $this->cleanFilename($episodeName) . '.' . $d['extension'] != $d['basename'])) {
					$arr['results'][] = array(
						'filename'		=> $d['basename'],
						'ext'			=> $d['extension'],
						'filesize'		=> size_readable(filesize($v)),
						'saison'		=> $res['found'] ? $res['saison'] : '--',
						'episode'		=> $res['found'] ? $res['episode'] : '--',
						'episodeName'	=> $this->cleanFilename($episodeName),
						'Overview'		=> ($Overview),
						'serieName'		=> $arr['serieName'],
						'pathName'		=> $str,
						'md5'			=> md5(realpath($v))
					);
				}
			}
		}
		//db($arr);
		return $arr;
	}

	function svc_renameMoviesFiles() {
		//print '<pre>';
		$t = utf8_decode(base64_decode($_REQUEST['modified']));
		//print_r($t);
		$arr = json_decode($t, true);
		//db($arr);
		foreach ($arr['modified'] as $MoviesPath => $Modified) {
			$MoviesPath = base64_decode($MoviesPath);
			$tmp = glob($MoviesPath . '/*.*');
			$arrMD5 = array();
			foreach ($tmp as $v) {
				$arrMD5[md5(realpath($v))] = realpath($v);
			}
			;
			$arrResult = array();
			foreach ($Modified as $v) {
				$v['serie'] = base64_decode($v['serie']);
				$new64 = realpath($MoviesPath) . "/" . base64_decode($v['new64']) . '.' . strtolower($v['ext']);
				//print $old.'=>'.$new.'<br />';
				if (array_key_exists($v['md5'], $arrMD5)) {
					//print ($new64);
					if (file_exists($new64)) {
						$resultRename = 'file exists';
						if ($_REQUEST['moveExists'] == 'true') {
							fb('exists');
						}
					} else {
						$resultRename = 'ok';
						$resultRename = rename($arrMD5[$v['md5']], $new64);
					}
					$arrResult[] = array('old' => $arrMD5[$v['md5']], 'new' => $new64, 'result' => $resultRename);
				}
			}
		}
		return array('result' => $arrResult);
	}

	function svc_renameFiles() {
		//print '<pre>';
		$t = utf8_decode(base64_decode($_REQUEST['modified']));
		//print_r($t);
		$arr = json_decode($t, true);
		db($arr);
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
			//print_r($Modified);
			foreach ($Modified['modified'] as $v) {
				$v['serie'] = base64_decode($v['serie']);
				$old = realpath($SeriePath . "/" . $v['old']);
				$new = realpath($SeriePath) . "/" . trim($v['serie']) . " [" . $v['saison'] . 'x' . sprintf('%02d', $v['episode']) . '] ' . $v['new'] . '.' . $v['ext'];
				$new64 = realpath($SeriePath) . "/" . trim($v['serie']) . " [" . $v['saison'] . 'x' . sprintf('%02d', $v['episode']) . '] ' . base64_decode($v['new64']) . '.' . $v['ext'];
				//print $old.'=>'.$new.'<br />';
				if (array_key_exists($v['md5'], $arrMD5)) {
					//print ($new64);
					if (file_exists($new64)) {
						$resultRename = 'file exists';
						if ($_REQUEST['moveExists'] == 'true') {
							fb('exists');
						}
					} else {
						$resultRename = rename($arrMD5[$v['md5']], $new64);
					}
					$d = pathinfo($new64);
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

	function makeSerieNFO($filename) {
		set_time_limit(40);
		$seriePath = $this->getSeriePath(dirname($filename));
		if (file_exists($seriePath . '/tvdb_all.xml')) {
			$xpath = $this->getXmlDocFromSeriePath($seriePath);
			$seriePathD = pathinfo($filename);
			$nfoFilename1 = $seriePathD['dirname'] . '/tvshow.nfo';
			$nfoFilename2 = $seriePath . '/tvshow.nfo';
			$bannerFilename = $seriePath . '/folder.jpg';

			if ($xpath) {
				$xmldocNFO = new DOMDocument("1.0");
				$root = $xmldocNFO->createElement("tvshow");
				$xmldocNFO->appendChild($root);

				$this->addNFOTextNode($xmldocNFO, $root, "title", $this->extractXQuery($xpath, "/Data/Series/SeriesName"));
				$this->addNFOTextNode($xmldocNFO, $root, "plot", $this->extractXQuery($xpath, "/Data/Series/Overview"));
				//print $this->getUpdateUrlOrXmlFromSeriePath($seriePath ,'url')."ee";
				$this->addNFOTextNode($xmldocNFO, $root, "episodeguideurl", str_replace('.xml', '.zip', $this->getUpdateUrlOrXmlFromSeriePath($seriePath, 'url')));
				$this->addNFOTextNode($xmldocNFO, $root, "premiered", $this->extractXQuery($xpath, "/Data/Series/FirstAired"));
				$bannerURL = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, "/Data/Series/banner");
				$this->addNFOTextNode($xmldocNFO, $root, "banner", $bannerURL);
				$fanartURL = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, "/Data/Series/fanart");
				$this->addNFOTextNode($xmldocNFO, $root, "fanart", 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, "/Data/Series/fanart"));
				file_put_contents($nfoFilename1, $xmldocNFO->saveXML());
				file_put_contents($nfoFilename2, $xmldocNFO->saveXML());
				if (!file_exists($bannerFilename)) {
					file_put_contents($bannerFilename, $this->QDNet->getURL($bannerURL));
				}
			}
		}
	}

	function makeEpisodeNFO($filename) {
		$seriePath = $this->getSeriePath(dirname($filename));
		if (file_exists($seriePath . '/tvdb_all.xml')) {
			$xpath = $this->getXmlDocFromSeriePath($seriePath);
			$seriePathD = pathinfo($filename);
			$res = $this->extractSeriesFilenameStruct($seriePathD['basename']);
			$pathEpisode = "/Data/Episode[SeasonNumber='" . $res['saison'] . "' and EpisodeNumber='" . ($res['episode'] * 1) . "']";
			if ($res['found'] && $xpath) {
				$xmldocNFO = new DOMDocument("1.0");
				$root = $xmldocNFO->createElement("episodedetails");
				$xmldocNFO->appendChild($root);
				$o = array();
				$o['seriepath'] = $seriePath;
				$o['filename'] = $filename;
				$o['sfilename'] = $seriePathD['filename'] . '.' . $seriePathD['extension'];
				$o['title'] = $this->extractXQuery($xpath, $pathEpisode . "/EpisodeName");
				$o['season'] = $res['saison'];
				$o['episode'] = $res['episode'] * 1;
				$o['plot'] = $this->extractXQuery($xpath, $pathEpisode . "/Overview");
				$o['credits'] = $this->extractXQuery($xpath, $pathEpisode . "/Writer");
				$o['director'] = $this->extractXQuery($xpath, $pathEpisode . "/Director");
				$o['aired'] = $this->extractXQuery($xpath, $pathEpisode . "/FirstAired");
				$o['thumb'] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, $pathEpisode . "/filename");
				$o['fanart'] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, "/Data/Series/fanart");
				$o['banner'] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, '/Data/Series/banner');
				$o['episodetbn'] = 'http://thetvdb.com/banners/_cache/' . $this->extractXQuery($xpath, '/Data/Series/banner');
				$o['serieName'] = $this->extractXQuery($xpath, '/Data/Series/SeriesName');
				$o['serieOverview'] = $this->extractXQuery($xpath, '/Data/Series/Overview');
				$o['seriePremiered'] = $this->extractXQuery($xpath, '/Data/Series/FirstAired');
				$fanartFilename = $seriePathD['dirname'] . '/season' . sprintf('%02d', $o['season']) . '.tbn';
				$nfoFilename = $seriePathD['dirname'] . '/' . $seriePathD['filename'] . '.nfo';
				$tbnFilename = $seriePathD['dirname'] . '/' . $seriePathD['filename'] . '.tbn';
				$bannerFilename = $seriePathD['dirname'] . '/folder.jpg';


				$this->addNFOTextNode($xmldocNFO, $root, "title", $o['title']);
				$this->addNFOTextNode($xmldocNFO, $root, "season", $o['season']);
				$this->addNFOTextNode($xmldocNFO, $root, "episode", $o['episode']);
				$this->addNFOTextNode($xmldocNFO, $root, "plot", $o['plot']);
				$this->addNFOTextNode($xmldocNFO, $root, "credits", $o['credits']);
				$this->addNFOTextNode($xmldocNFO, $root, "director", $o['director']);
				$this->addNFOTextNode($xmldocNFO, $root, "aired", $o['aired']);
				$this->addNFOTextNode($xmldocNFO, $root, "thumb", $o['thumb']);
				$this->addNFOTextNode($xmldocNFO, $root, "fanart", $o['fanart']);
				file_put_contents($nfoFilename, $xmldocNFO->saveXML());
				unlink($nfoFilename);
				if (!file_exists($fanartFilename)) {
					file_put_contents($fanartFilename, $this->QDNet->getURL($o['fanart']));
				}
				if (!file_exists($bannerFilename)) {
					file_put_contents($bannerFilename, $this->QDNet->getURL($o['banner']));
				}
				if (!file_exists($bannerFilename)) {
					file_put_contents($bannerFilename, $this->QDNet->getURL($o['banner']));
				}
				if (!file_exists($tbnFilename)) {
					file_put_contents($tbnFilename, $this->QDNet->getURL($o['thumb']));
				}

				////////////////$this->makeEpisodeDB($o);
			}
		}
	}

	function sqliteInsertOrUpdate($db, $table, $colId, $datas, $dbg = false) {
		$where = '';
		$swhere = '';
		$scomma = '';
		$istrcol = '';
		$istrval = '';
		$icoma = '';
		$ustr = '';
		$ucomma = '';
		foreach ($datas as $k => $v) {
			$unesc = str_replace("'", "''", $v['val']);
			if ($v['isKey']) {
				if ($dbg)
					print_r($v);
				$where = $where . $swhere . $v['col'] . "='" . $unesc . "'";
				$swhere = ' and ';
			}
			$istrcol = $istrcol . $icomma . $v['col'];
			$istrval = $istrval . $icomma . "'" . $unesc . "'";
			$icomma = ",";
			$ustr = $ustr . $ucomma . $v['col'] . "='" . $unesc . "'";
			$ucomma = ',';
		}
		if ($colId != '') {
			$sqlSearch = "select $colId as searchID from $table where $where";
			$cnt = 0;
			if ($dbg)
				print $sqlSearch;
			foreach ($db->query($sqlSearch) as $row) {
				$cnt++;
				$id = $row['searchID'];
				print "#$cnt -- $id#";
				if ($dbg)
					print_r($row);
			}
		} else {
			$cnt = 0;
		}

		$updatestr = "update $table set $ustr where $colId=" . $id;
		$insertstr = "insert into $table ($istrcol) values($istrval)";
		if ($cnt == 0) {
			$db->exec($insertstr);
			$id = $db->lastInsertId();
			if ($dbg)
				print_r($insertstr);
		} else {
			$db->exec($updatestr);
			if ($dbg)
				print_r($updatestr);
		}

		return $id;
	}

	function makeEpisodeDB($data) {
		print "<pre>" . $data['filename'] . '\n';
		$xbmcpath = $this->getXbmcpath($data['filename']);
		$db = new PDO('sqlite:' . $this->xbmcDB);
		$idPath = $this->sqliteInsertOrUpdate(
			$db, 'path', 'idPath', array(
			array('col' => 'strPath', 'val' => $xbmcpath['path'], 'isKey' => true),
			array('col' => 'strContent', 'val' => 'tvshows', 'isKey' => false),
			array('col' => 'StrScraper', 'val' => 'tvdb.xml', 'isKey' => false),
			array('col' => 'useFolderNames', 'val' => '1', 'isKey' => false),
			//<settings><setting id="dvdorder" value="false" /><setting id="absolutenumber" value="false" /><setting id="fanart" value="true" /><setting id="posters" value="false" /><setting id="override" value="false" /><setting id="language" value="en" /></settings>
			array('col' => 'strSettings', 'val' => '', 'isKey' => false),
			array('col' => 'strHash', 'val' => $this->xbmcHash($xbmcpath['path']), 'isKey' => false)
			)
		);
		$idFile = $this->sqliteInsertOrUpdate(
			$db, 'files', 'idFile', array(
			array('col' => 'idPath', 'val' => $idPath, 'isKey' => true),
			array('col' => 'strFilename', 'val' => $data['sfilename'], 'isKey' => true)
			)
		);
		$idShow = $this->sqliteInsertOrUpdate(
			$db, 'tvshow', 'idShow', array(
			array('col' => 'c00', 'val' => utf8_decode($data['serieName']), 'isKey' => true),
			array('col' => 'c01', 'val' => utf8_decode($data['serieOverview']), 'isKey' => false),
			array('col' => 'c05', 'val' => $data['seriePremiered'], 'isKey' => false)
			)
			, true);
		$idEpisode = $this->sqliteInsertOrUpdate(
			$db, 'episode', 'idEpisode', array(
			array('col' => 'c00', 'val' => $data['title'], 'isKey' => true),
			array('col' => 'c01', 'val' => $data['plot'], 'isKey' => false),
			array('col' => 'c03', 'val' => '0.000000', 'isKey' => false),
			array('col' => 'c05', 'val' => $data['aired'], 'isKey' => false),
			array('col' => 'c06', 'val' => '<thumb>' . $data['thumb'] . '</thumb>', 'isKey' => false),
			array('col' => 'c12', 'val' => $data['season'], 'isKey' => false),
			array('col' => 'c13', 'val' => $data['episode'], 'isKey' => false),
			array('col' => 'c15', 'val' => -1, 'isKey' => false),
			array('col' => 'c16', 'val' => -1, 'isKey' => false),
			array('col' => 'c17', 'val' => -1, 'isKey' => false),
			array('col' => 'idFile', 'val' => $idFile, 'isKey' => true)
			)
		);
		/*
		c06
		<thumbs>
		<thumb>http://images.thetvdb.com/banners/posters/83123-1.jpg</thumb><thumb>http://images.thetvdb.com/banners/posters/83123-2.jpg</thumb>
		<thumb>http://images.thetvdb.com/banners/graphical/83123-g4.jpg</thumb><thumb>http://images.thetvdb.com/banners/graphical/83123-g2.jpg</thumb>
		<thumb>http://images.thetvdb.com/banners/graphical/83123-g3.jpg</thumb><thumb type="season" season="1">http://images.thetvdb.com/banners/seasons/83123-1-2.jpg</thumb>
		<thumb type="season" season="1">http://images.thetvdb.com/banners/seasons/83123-1.jpg</thumb><thumb type="season" season="-1">http://images.thetvdb.com/banners/posters/83123-1.jpg</thumb>
		<thumb type="season" season="-1">http://images.thetvdb.com/banners/posters/83123-2.jpg</thumb>
		</thumbs>
		c11
		<fanart url="http://images.thetvdb.com/banners/">
		<thumb dim="1280x720" colors="" preview="_cache/fanart/original/83123-3.jpg">fanart/original/83123-3.jpg</thumb>
		<thumb dim="1280x720" colors="|172,186,199|186,149,133|232,223,218|" preview="_cache/fanart/original/83123-2.jpg">fanart/original/83123-2.jpg</thumb><thumb dim="1280x720" colors="" preview="_cache/fanart/original/83123-4.jpg">fanart/original/83123-4.jpg</thumb><thumb dim="1280x720" colors="|242,238,227|50,53,60|31,36,32|" preview="_cache/fanart/original/83123-1.jpg">fanart/original/83123-1.jpg</thumb><thumb dim="1920x1080" colors="" preview="_cache/fanart/original/83123-5.jpg">fanart/original/83123-5.jpg</thumb><thumb dim="1920x1080" colors="" preview="_cache/fanart/original/83123-6.jpg">fanart/original/83123-6.jpg</thumb><thumb dim="1920x1080" colors="" preview="_cache/fanart/original/83123-8.jpg">fanart/original/83123-8.jpg</thumb><thumb dim="1920x1080" colors="" preview="_cache/fanart/original/83123-10.jpg">fanart/original/83123-10.jpg</thumb><thumb dim="1920x1080" colors="" preview="_cache/fanart/original/83123-9.jpg">fanart/original/83123-9.jpg</thumb>
		<thumb dim="1920x1080" colors="" preview="_cache/fanart/original/83123-7.jpg">fanart/original/83123-7.jpg</thumb>
		<thumb dim="1280x720" colors="" preview="_cache/fanart/original/83123-11.jpg">fanart/original/83123-11.jpg</thumb></fanart>
		c10
		<episodeguide><url cache="83123.xml">http://www.thetvdb.com/api/1D62F2F90030C444/series/83123/all/fr.zip</url></episodeguide>
		 */
		$this->sqliteInsertOrUpdate(
			$db, 'tvshowlinkepisode', '', array(
			array('col' => 'idEpisode', 'val' => $idEpisode, 'isKey' => true),
			array('col' => 'idShow', 'val' => $idShow, 'isKey' => false)
			)
		);
		$this->sqliteInsertOrUpdate(
			$db, 'tvshowlinkpath', '', array(
			array('col' => 'idShow', 'val' => $idShow, 'isKey' => true),
			array('col' => 'idPath', 'val' => $idPath, 'isKey' => false)
			)
		);
		/*
		select *
		from tvshow
		join tvshowlinkpath on tvshow.idShow=tvshowlinkpath.idShow
		join path on path.idpath=tvshowlinkpath.idPath
		left outer join (
		select tvshow.idShow as idShow,count(1) as totalcount,count(files.playCount) as watchedcount
		from tvshow
		join tvshowlinkepisode on tvshow.idShow = tvshowlinkepisode.idShow
		join episode on episode.idEpisode = tvshowlinkepisode.idEpisode
		join files on files.idFile = episode.idFile
		group by tvshow.idShow)
		counts on tvshow.idShow = counts.idShow;
		 */
		/*
		select *
		from tvshow
		join tvshowlinkpath on tvshow.idShow=tvshowlinkpath.idShow
		join path on path.idpath=tvshowlinkpath.idPath
		join tvshowlinkepisode on tvshow.idShow = tvshowlinkepisode.idShow
		join episode on episode.idEpisode = tvshowlinkepisode.idEpisode
		join files on files.idFile = episode.idFile
		where tvshow.c00 like 'Dead%'
		 */
		//die();
	}

	function getXbmcpath($filename) {
		$res = null;
		foreach ($this->folderSeriesList as $k => $v) {
			//db( $v['path']);
			//db(addslashes($data['filename']));
			if (eregi('^' . addslashes($v['path']), ($filename))) {
				$res = str_ireplace($v['path'], $v['xbmcpath'], $filename);
				$res = str_replace('\\', '/', $res);
				preg_match('|(^.*/)(.*)|', $res, $r);
				$res = array();
				$res['path'] = $r[1];
				$res['filename'] = $r[2];
				break;
			}
		}
		return $res;
	}

	function addNFOTextNode($xmldocNFO, $parent, $title, $value) {
		$item = $xmldocNFO->createElement($title);
		$parent->appendChild($item);
		$text = $xmldocNFO->createTextNode(utf8_encode($value));
		$item->appendChild($text);
	}

	function svc_testExtractRegex() {
		print '<table border=1>';
		foreach ($this->testFilenames as $tst) {
			$res = $this->extractSeriesFilenameStruct($tst);
			//print_r($res);
			if (true) {
				//if (!){
				print '<tr>';
				print '<td><font style="background-color:' . (($res['saison'] == '2' && $res['episode'] = '34') ? 'green' : 'red') . '">OO</font></td>';
				print '<td>' . $res['filename'] . '</td>';
				print '<td>' . $res['saison'] . '&nbsp;</td>';
				print '<td>' . $res['episode'] . '&nbsp;</td>';
				print '<td>' . $res['rgxnum'] . '</td>';
				print '<td>' . $res['rgx'] . '&nbsp;</td>';
				print '<td><table border=1><tr>';
				if (is_array($res['rgx_match'])) {
					foreach ($res['rgx_match'] as $k => $v) {
						print "<td width=\"50\">$k</td>";
						print "<td >$v&nbsp;</td>";
					}
					print '</tr>';
				} else {
					print '<td>&nbsp;</td>';
				}
				print '</table></td>';
				print '</tr>';
			}
		}
		print '</table>';
	}

	function svc_extractSeriesFilenameStruct($intern = false) {
		return $this->extractSeriesFilenameStruct($_REQUEST['filename']);
	}

	function extractSeriesFilenameStruct($filename) {
		$res['found'] = false;
		foreach ($this->arrRegex as $k => $rgx) {
			$res['filename'] = $filename;
			if (preg_match('`' . $rgx['rgx'] . '`i', $filename, $match)) {
				$res['saison'] = ($match[$rgx['s']] * 1);
				$res['episode'] = ($match[$rgx['e']] * 1);
				$res['rgx'] = $rgx['rgx'];
				$res['rgxnum'] = $k;
				$res['rgx_match'] = $match;
				$res['found'] = true;
				break;
			}
		}
		return $res;
	}

	function svc_testXsl() {
		$xml = new DOMDocument;
		$xml->load('modules/nzb/tvdb_all.xml');

		// Chargement de la transformation originale (transfo1)
		$xsl = new DOMDocument;
		$xsl->load('modules/nzb/tvdb_all.xsl');
		// Transformation !
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);
		echo $proc->transformToXML($xml);
	}

	function xbmcHash($hashInput) {
		$dir = dirname(__FILE__);
		return exec("$dir/perl.exe $dir/xbmchash.pl \"$hashInput\"");
	}

	function svc_test2() {
		print $this->xbmcHash('F:\\Videos\\Nosferatu.avi') . '<br>' . '2a6ec78d' . '<br><br>';
		print $this->xbmcHash('123456789') . '<br>' . '0376e6e7' . '<br><br>';
		print $this->xbmcHash('F:\\Videos\\Nosferatu.avi') . '<br>' . '2a6ec78d' . '<br><br>';
		print $this->xbmcHash('smb://user:pass@server/share/directory/') . '<br>' . 'c5559f13' . '<br><br>';
		print $this->xbmcHash('smb://user:pass@server/share/directory/file.ext') . '<br>' . '8ce36055' . '<br><br>';
	}
}
?>