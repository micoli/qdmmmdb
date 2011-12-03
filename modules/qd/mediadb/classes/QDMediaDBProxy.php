<?
include  QD_PATH_3RD_PHP."simple_html_dom.php";
class QDMediaDBProxy {
	var $QDNet;
	
	function __construct() {
		$this->QDNet		= new QDNet();
		$this->cacheminutes = 123*59+59;
		$this->cache		= true;

		$this->thetvdbkey					= $GLOBALS['conf']['qdmediadb']['thetvdbkey'];
		$this->xbmcPath						= $GLOBALS['conf']['qdmediadb_xbmc']['xbmcPath'];
		$this->xbmcDB						= $GLOBALS['conf']['qdmediadb_xbmc']['xbmcDB'];

		$this->allowedExt 					= $GLOBALS['conf']['qdmediadb']['allowedExt'];;
		$this->movieExt						= $GLOBALS['conf']['qdmediadb']['movieExt'];
		$this->arrCleanupMoviesRegexStrict 	= $GLOBALS['conf']['qdmediadb']['arrCleanupMoviesRegexStrict'];
		$this->arrCleanupMoviesRegex		= $GLOBALS['conf']['qdmediadb']['arrCleanupMoviesRegex'];
		$this->arrRegex						= $GLOBALS['conf']['qdmediadb']['arrRegex'];
		$this->testFilenames				= $GLOBALS['conf']['qdmediadb']['testFilenames'];
		$this->episodeFormats				= $GLOBALS['conf']['qdmediadb']['episodeFormats'];

		$this->folderSeriesList				= $GLOBALS['conf']['qdmediadb_serie']['folderSeriesList'];
		$arrTmp								= $GLOBALS['conf']['qdmediadb_movie']['folderMoviesList'];
		$this->folderMoviesList				= array();
		foreach($arrTmp as $k=>$v){
			$this->folderMoviesList[$v['name']]=$v;
		}

	}
	/**
	 * Calculate new image dimensions to new constraints
	 * http://www.php.net/manual/fr/function.imagick-thumbnailimage.php
	 *
	 * @param Original X size in pixels
	 * @param Original Y size in pixels
	 * @return New X maximum size in pixels
	 * @return New Y maximum size in pixels
	 */
	function scaleImage($x,$y,$cx,$cy) {
		//Set the default NEW values to be the old, in case it doesn't even need scaling
		list($nx,$ny)=array($x,$y);

		//If image is generally smaller, don't even bother
		if ($x>=$cx || $y>=$cx) {

			//Work out ratios
			if ($x>0) $rx=$cx/$x;
			if ($y>0) $ry=$cy/$y;

			//Use the lowest ratio, to ensure we don't go over the wanted image size
			if ($rx>$ry) {
				$r=$ry;
			} else {
				$r=$rx;
			}

			//Calculate the new size based on the chosen ratio
			$nx=intval($x*$r);
			$ny=intval($y*$r);
		}

		//Return the results
		return array($nx,$ny);
	}
	function svc_proxyImg(){
		$tmp = $this->QDNet->getCacheURL($_REQUEST['u'], 'imgs', 60*24*365*20,true);
		header('Content-type: '.$this->QDNet->lastMimeType);
		if(array_key_exists('c',$_REQUEST) && $this->QDNet->lastCacheFile){
			$cacheFolder = $GLOBALS['conf']['qdnet']['cacheroot'].'/cacheImg/';
			$cacheFile = $cacheFolder.md5($_REQUEST['u'])."-".$_REQUEST['c'].'.jpg';
			if(file_exists($cacheFile)){
				die(file_get_contents($cacheFile));
			}else{
				$sizes=split('x',$_REQUEST['c']);
				$thumb=new Imagick($this->QDNet->lastCacheFile);
				list($newX,$newY)=$this->scaleImage(
						$thumb->getImageWidth(),
						$thumb->getImageHeight(),
						$sizes[0],
						$sizes[0]);
				$thumb->thumbnailImage($newX,$newY);
				//print "ee".$thumb->getImageWidth();
				//echo($this->QDNet->lastCacheFile);
				if(!file_exists($cacheFolder)) mkdir($cacheFolder);
				$thumb->writeImage($cacheFile);
				//Write the new image to a file
				die($thumb);
			}
		}else{
			die($tmp);
		}
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