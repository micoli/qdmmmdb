<?php
namespace App\Components\QDmmmDB\Mediadb;

use App\Components\QDmmmDB\Misc\QDNet;
use Silex\Application;

class MediaDBManager {
	var $QDNet;
	var $app;
	static $XbmcDBPDO=null;

	function initdb(){
		if(is_null(self::$XbmcDBPDO)){
			self::$XbmcDBPDO = new PDO($this->xbmcDB,$this->xbmcDBUser,$this->xbmcDBPass);
		}
	}

	function __construct(Application $app) {
		$this->QDNet	= new QDNet();
		$this->app		= $app;
		$this->cacheminutes = 123*59+59;
		$this->cache		= true;

		$this->xbmcPath						= $GLOBALS['conf']['qdmediadb_xbmc']['xbmcPath'];
		$this->xbmcDB						= $GLOBALS['conf']['qdmediadb_xbmc']['xbmcDB'];
		$this->xbmcDBUser					= $GLOBALS['conf']['qdmediadb_xbmc']['xbmcDBUser'];
		$this->xbmcDBPass					= $GLOBALS['conf']['qdmediadb_xbmc']['xbmcDBPass'];

		$this->thetvdbkey					= \App\Components\QDmmmDB\Configuration\Api::$thetvdbkey;
		$this->fanarttvdbkey				= \App\Components\QDmmmDB\Configuration\Api::$fanarttvdbkey;

		$this->allowedExt 					= \App\Components\QDmmmDB\Configuration\Media::$allowedExt;
		$this->movieExt						= \App\Components\QDmmmDB\Configuration\Media::$movieExt;
		$this->subtitlesExt					= \App\Components\QDmmmDB\Configuration\Media::$subtitlesExt;
		$this->arrCleanupMoviesRegexStrict 	= \App\Components\QDmmmDB\Configuration\Movies::$arrCleanupMoviesRegexStrict;
		$this->arrCleanupMoviesRegex		= \App\Components\QDmmmDB\Configuration\Movies::$arrCleanupMoviesRegex;
		$this->arrHiddenmovieRegex			= \App\Components\QDmmmDB\Configuration\Movies::$arrHiddenmovie;

		$this->testFilenames				= \App\Components\QDmmmDB\Configuration\Series::$testFilenames;
		$episodeFormatsTmp					= \App\Components\QDmmmDB\Configuration\Series::$episodeFormats;
		$this->episodeFormats = array();
		foreach($episodeFormatsTmp as $k=>$v){
			$this->episodeFormats[$v['rgx']]=$v['rep'];
		}
		$this->arrKeepSpecialTag			= \App\Components\QDmmmDB\Configuration\Movies::$arrKeepSpecialTag;

		$this->folderSeriesList				= $GLOBALS['conf']['qdmediadb_serie']['folderSeriesList'];
		$arrTmp								= $GLOBALS['conf']['qdmediadb_movie']['folderMoviesList'];
		$this->folderMoviesList				= array();
		foreach($arrTmp as $k=>$v){
			$this->folderMoviesList[$v['name']]=$v;
		}
	}

	protected function removeTrailingYear($v){
		return preg_replace('! \([1-2][0-9]{3}\)$!','',$v);
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
	function proxyImg($sUrl,$sC){
		$tmp = $this->QDNet->getCacheURL($sUrl, 'imgs', 60*24*365*20,true,'');
		header('Content-type: '.$this->QDNet->lastMimeType);
		if($sC && $this->QDNet->lastCacheFile){
			$cacheFolder = $GLOBALS['conf']['qdnet']['cacheroot'].'/cacheImg/';
			$cacheFile = $cacheFolder.md5($sUrl)."-".$sC.'.jpg';
			if(file_exists($cacheFile)){
				die(file_get_contents($cacheFile));
			}else{
				$sizes=split('x',$sC);
				//die($this->QDNet->lastCacheFile);
				$thumb=new \Imagick($this->QDNet->lastCacheFile);
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

	function mb_trim( $string )
	{
		$string = preg_replace( "/(^\s+)/", "", $string );
		$string = preg_replace( "/(\s+$)/", "", $string );

		return $string;
	}

	function mb_str_replace($needle, $replacement, $haystack)
	{
		$needle_len = mb_strlen($needle);
		$replacement_len = mb_strlen($replacement);
		$pos = mb_strpos($haystack, $needle);
		while ($pos !== false)
		{
			$haystack = mb_substr($haystack, 0, $pos) . $replacement
			. mb_substr($haystack, $pos + $needle_len);
			$pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
		}
		return $haystack;
	}

	function delmulspace($str) {
		do {
			$str = $this->mb_str_replace("  ", " ", $str);
		} while (mb_strpos($str, "  ") > 0);
		return $str;
	}

	function cleanFilename($a) {
		$a = $this->mb_str_replace('?', ' ', $a);
		$a = $this->mb_str_replace(':', ' ', $a);
		$a = $this->mb_str_replace('/', ' ', $a);
		$a = $this->mb_str_replace('\\', ' ', $a);
		$a = preg_replace('!^\[\s*(.+\.)*(.+\..+)\s*\] !','',$a);
		return $this->mb_trim($this->delmulspace($a));
	}

	function extractXQuery($xpath, $xpathQ,$utf8=false) {
		$val = '';
		if (!$xpath) {
			return '';
		}
		$arts = $xpath->query($xpathQ);
		foreach ($arts as $k => $art) {
			$val = ($utf8)?$art->nodeValue:utf8_decode($art->nodeValue);
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

	function xbmcDBSQLInsertOrUpdate($table, $colId, $datas, $dbg = false) {
		$dbg	=false;
		$where = '';
		$swhere = '';
		$scomma = '';
		$istrcol = '';
		$istrval = '';
		$icoma = '';
		$ustr = '';
		$ucomma = '';
		foreach ($datas as $k => $v) {
			$unesc = (str_replace("'", "''", $v['val']));
			$unesc = addslashes($v['val']);
			if ($v['isKey']) {
				if ($dbg){
					print_r($v);
				}
				$where = $where . $swhere . $v['col'] . "='" . $unesc . "'";
				$swhere = ' and ';
			}
			$istrcol = $istrcol . $icomma . $v['col'];
			$istrval = $istrval . $icomma . "'" . $unesc . "'";
			$icomma = ",";
			if(!(array_key_exists('notUpdated',$v) && $v['notUpdated'])){
				$ustr = $ustr . $ucomma . $v['col'] . "='" . $unesc . "'";
			}
			$ucomma = ',';
		}
		if ($colId != '') {
			$sqlSearch = "select $colId as searchID from $table where $where";
			$cnt = 0;
			if ($dbg){
				print "\n".$sqlSearch;
			}
			foreach (self::$XbmcDBPDO->query($sqlSearch) as $row) {
				$cnt++;
				$id = $row['searchID'];
				if ($dbg){
					print "\n#$cnt -- $id#";
					print_r($row);
				}
			}
		} else {
			$cnt = 0;
		}

		if ($cnt == 0) {
			$insertstr = "insert into $table ($istrcol) values($istrval)";
			if ($dbg){
				print "\n";
				print_r($insertstr);
			}
			self::$XbmcDBPDO->exec($insertstr);
			$id = self::$XbmcDBPDO->lastInsertId();
		} else {
			if ($colId != ''){
				$updatestr = "update $table set $ustr where $colId=" . $id;
				if ($dbg){
					print "\n";
					print_r($updatestr);
				}
				self::$XbmcDBPDO->exec($updatestr);
			}
		}

		return $id;
	}

	function makeEpisodeDB($data) {
		//print "" . $data['filename'] . "\n";
		$seriePath = $data['saisonpath'];
		$xbmcPathShow = $this->getXbmcpath($seriePath);
		$xbmcPathFile = $this->getXbmcpath($data['filename']);
		/*
		select pashow.strPath,fi.idFile,sh.c00,sh.c16,epi.c12,epi.c13,epi.c00,pa.strPath,fi.strFilename,group_concat(concat(art_show.type,':',art_show.url)),group_concat(concat(art_epi.type,':',art_epi.url))
		from tvshow sh
		inner join  tvshowlinkpath tvslp on tvslp.idShow=sh.idShow
		inner join  path pashow on pashow.idPath=tvslp.idPath
		inner join  episode epi on epi.idShow=sh.idShow
		inner join  files fi on fi.idFile=epi.idFile
		inner join  path pa on pa.idPath=fi.idPath
		left join   art art_show on art_show.media_type='tvshow' and art_show.media_id=sh.idShow
		left join   art art_epi on art_epi.media_type='episode' and art_epi.media_id=epi.idEpisode
		#where sh.c00 like 'Nikita'
		group by fi.idFile
		order by sh.c00,epi.c12,epi.c13*1;
		 */

		//db($xbmcPathShow);
		//db($xbmcPathFile);
		//db($data);

		/*"xbmcDB" 		: "sqlite://bigone/userdata/Database/MyVideos34.db"*/
		$this->initdb();
		$idPathShow = $this->xbmcDBSQLInsertOrUpdate(
			'path',
			'idPath',
			array(
				array('col' => 'strPath'		, 'isKey'=>true	, 'val'=> utf8_decode($xbmcPathShow['path'])					),
				array('col' => 'strContent'		, 'isKey'=>false, 'val'=> 'tvshows'												),
				array('col' => 'StrScraper'		, 'isKey'=>false, 'val'=> 'tvdb.xml'											),
				array('col' => 'useFolderNames'	, 'isKey'=>false, 'val'=> '1'													),
				array('col' => 'strSettings'	, 'isKey'=>false, 'val'=> ''													),
				array('col' => 'strHash'		, 'isKey'=>false, 'val'=> $this->xbmcHash(utf8_decode($xbmcPathShow['path']))	)
			)
		);
		$idPathSeason = $this->xbmcDBSQLInsertOrUpdate(
			'path',
			'idPath',
			array(
				array('col' => 'strPath'		, 'isKey'=>true	, 'val'=> utf8_decode($xbmcPathFile['path'])					),
				array('col' => 'strContent'		, 'isKey'=>false, 'val'=> ''													),
				array('col' => 'StrScraper'		, 'isKey'=>false, 'val'=> ''													),
				array('col' => 'useFolderNames'	, 'isKey'=>false, 'val'=> null													),
				array('col' => 'strSettings'	, 'isKey'=>false, 'val'=> ''													),
				array('col' => 'strHash'		, 'isKey'=>false, 'val'=> null													)
			)
		);
		//<settings><setting id="dvdorder" value="false" /><setting id="absolutenumber" value="false" /><setting id="fanart" value="true" /><setting id="posters" value="false" /><setting id="override" value="false" /><setting id="language" value="en" /></settings>
		$idFile = $this->xbmcDBSQLInsertOrUpdate(
			'files',
			'idFile',
			array(
				array('col' => 'idPath'			, 'isKey'=>true, 'val'=> $idPathSeason													),
				array('col' => 'strFilename'	, 'isKey'=>true, 'val'=> utf8_decode($data['sfilename'])								),
				array('col' => 'dateAdded'		, 'isKey'=>false,'val'=> date('Y-m-d H:i:s')						,'notUpdated'=>true	)
			)
		);
		$idShow = $this->xbmcDBSQLInsertOrUpdate(
			'tvshow',
			'idShow',
			array(
				array('col' => 'c00'			, 'isKey'=>false	, 'val'=> ($data['serieName'])						),
				array('col' => 'c01'			, 'isKey'=>false	, 'val'=> ($data['serieOverview'])					),
				array('col' => 'c05'			, 'isKey'=>false	, 'val'=> ($data['seriePremiered'])					),
				array('col' => 'c12'			, 'isKey'=>true		, 'val'=> ($data['tvdbidshow'])						),
				array('col' => 'c16'			, 'isKey'=>true		, 'val'=> (utf8_decode($xbmcPathShow['path']))		),
				array('col' => 'c17'			, 'isKey'=>false	, 'val'=> ($idPathShow)								),
				array('col' => 'c06'			, 'isKey'=>false	, 'val'=> ($data['posters'])						),
				array('col' => 'c11'			, 'isKey'=>false	, 'val'=> ($data['backdrops'])						)
			)
		);
		$idSeason = $this->xbmcDBSQLInsertOrUpdate(
			'seasons',
			'idSeason',
			array(
				array('col' => 'idShow'			, 'isKey'=>true	, 'val'=> ($idShow)									),
				array('col' => 'season'			, 'isKey'=>true , 'val'=> ($data['season'])							)
			)
		);
		$idEpisode = $this->xbmcDBSQLInsertOrUpdate(
			'episode',
			'idEpisode',
			array(
				array('col' => 'c00'			, 'isKey'=>true	, 'val'=> $data['title']										),
				array('col' => 'c01'			, 'isKey'=>false, 'val'=> $data['plot']											),
				array('col' => 'c03'			, 'isKey'=>false, 'val'=> '0.000000'											),
				array('col' => 'c05'			, 'isKey'=>false, 'val'=> $data['aired']										),
				array('col' => 'c06'			, 'isKey'=>false, 'val'=> '<thumb>' . $data['thumb'] . '</thumb>'				),
				array('col' => 'c12'			, 'isKey'=>false, 'val'=> $data['season']										),
				array('col' => 'c13'			, 'isKey'=>false, 'val'=> $data['episode']										),
				array('col' => 'c15'			, 'isKey'=>false, 'val'=> -1													),
				array('col' => 'c16'			, 'isKey'=>false, 'val'=> -1													),
				array('col' => 'c17'			, 'isKey'=>false, 'val'=> -1													),
				array('col' => 'c18'			, 'isKey'=>false, 'val'=> utf8_decode($xbmcPathFile['path'].$data['sfilename'])	),
				array('col' => 'c19'			, 'isKey'=>false, 'val'=> $idShow												),
				array('col' => 'idFile'			, 'isKey'=>true	, 'val'=> $idFile												),
				array('col' => 'idShow'			, 'isKey'=>false, 'val'=> $idShow												)
			)
		);
		$this->xbmcDBSQLInsertOrUpdate(
			'tvshowlinkpath',
			'',
			array(
				array('col' => 'idShow'			, 'isKey'=>true	, 'val'=> $idShow									),
				array('col' => 'idPath'			, 'isKey'=>true	, 'val'=> $idPathShow								)
			)
		);
		foreach($data['art'] as $f){
			//db($f);
			switch ($f['table']){
				case 'sho':
					$this->addMediaToMedia($idShow		, 'tvshow'		,$f['art_type'], $this->getXbmcpath(utf8_decode($f['file']),false));
				break;
				case 'sea':
					$this->addMediaToMedia($idSeason	, 'season'		,$f['art_type'], $this->getXbmcpath(utf8_decode($f['file']),false));
				break;
				case 'epi':
					$this->addMediaToMedia($idEpisode	, 'episode'		,$f['art_type'], $this->getXbmcpath(utf8_decode($f['file']),false));
				break;
			}
		}
		/*
		$this->xbmcDBSQLInsertOrUpdate(
			'tvshowlinkepisode',
			'',
			array(
				array('col' => 'idEpisode'		, 'isKey'=>true	, 'val'=> $idEpisode								),
				array('col' => 'idShow'			, 'isKey'=>false, 'val'=> $idShow									)
			)
		);*/
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

	function makeMovieDB(&$data) {
		return;
		$xbmcpath = $this->getXbmcMoviesPath($data['fileDetail']['file']);

		$this->initdb();
		$idPath = $this->xbmcDBSQLInsertOrUpdate(
			'path',
			'idPath',
			array(
				array('col' => 'strPath'		, 'isKey'=>true	, 'val'=> utf8_decode($xbmcpath['path'])			),
				array('col' => 'strContent'		, 'isKey'=>false, 'val'=> 'movies'									),
				array('col' => 'StrScraper'		, 'isKey'=>false, 'val'=> 'tmdb.xml'								),
				array('col' => 'useFolderNames'	, 'isKey'=>false, 'val'=> '1'										),
				array('col' => 'strSettings'	, 'isKey'=>false, 'val'=> ''										),
				array('col' => 'strHash'		, 'isKey'=>false, 'val'=> $this->xbmcHash(utf8_decode($xbmcpath['path']))		)
				//<settings><setting id="dvdorder" value="false" /><setting id="absolutenumber" value="false" /><setting id="fanart" value="true" /><setting id="posters" value="false" /><setting id="override" value="false" /><setting id="language" value="en" /></settings>
			)
		);
		foreach($data as $k=>$v){
			if(is_scalar($v) && is_string($v)){
				$data[$k]=utf8_decode($v);
			}
		}
		$idFile = $this->xbmcDBSQLInsertOrUpdate(
			'files',
			'idFile',
			array(
				array('col' => 'idPath'			, 'isKey'=>true, 'val'=> $idPath													),
				array('col' => 'strFilename'	, 'isKey'=>true, 'val'=> utf8_decode($xbmcpath['filename'])							),
				array('col' => 'dateAdded'		, 'isKey'=>false,'val'=> date('Y-m-d H:i:s',filemtime($data['fileDetail']['file']))	,'notUpdated'=>true)
			)
		);

		$thumbStr = '';
		$thumbOk = false;
		if (is_array($data['thumb']) && count($data['thumb'])>0){
			$thumbStr = sprintf('<thumb>%s</thumb>',$data['thumb'][0]);
		}

		$fanartStr = '<fanart>';
		$fanartOk=false;
		if (is_array($data['fanart']) && array_key_exists('thumb',$data['fanart']) && is_array($data['fanart']['thumb'])){
			foreach($data['fanart']['thumb'] as $thumb){
				$fanartStr .= sprintf('<thumb>%s</thumb>',$thumb);
			}
		}
		$fanartStr.='</fanart>';

		$idMovie = $this->xbmcDBSQLInsertOrUpdate(
			'movie',
			'idMovie',
			array(
				array('col' => 'c00'	, 'isKey'=>false	, 'val'=> $data['title']								),
				array('col' => 'c01'	, 'isKey'=>false	, 'val'=> $data['plot']									),
				array('col' => 'c02'	, 'isKey'=>false	, 'val'=> $data['plot']									),
				array('col' => 'c03'	, 'isKey'=>false	, 'val'=> $data['xMovie Tagline']						),
				array('col' => 'c04'	, 'isKey'=>false	, 'val'=> $data['votes']								),
				array('col' => 'c05'	, 'isKey'=>false	, 'val'=> $data['rating']								),
				//array('col' => 'c06'	, 'isKey'=>false	, 'val'=> $data['xWriters']								),
				array('col' => 'c07'	, 'isKey'=>false	, 'val'=> $data['year']									),
				array('col' => 'c08'	, 'isKey'=>false	, 'val'=> $thumbStr										),
				//array('col' => 'c09'	, 'isKey'=>false	, 'val'=> $data['xIMDB ID']								),
				array('col' => 'c10'	, 'isKey'=>false	, 'val'=> $data['title']								),
				array('col' => 'c11'	, 'isKey'=>false	, 'val'=> $data['runtime']								),
				array('col' => 'c12'	, 'isKey'=>false	, 'val'=> $data['mpaa']									),
				//array('col' => 'c13'	, 'isKey'=>false	, 'val'=> $data['xIMDB Top 250 Ranking']				),
				array('col' => 'c14'	, 'isKey'=>false	, 'val'=> $data['genre']								),
				array('col' => 'c15'	, 'isKey'=>false	, 'val'=> $data['director']								),
				array('col' => 'c16'	, 'isKey'=>false	, 'val'=> $data['originaltitle']						),
				//array('col' => 'c17'	, 'isKey'=>false	, 'val'=> $data['x']									),
				//array('col' => 'c18'	, 'isKey'=>false	, 'val'=> $data['xStudio']								),
				array('col' => 'c19'	, 'isKey'=>false	, 'val'=> $data['trailer']								),
				array('col' => 'c20'	, 'isKey'=>false	, 'val'=> $fanartStr									),
				//array('col' => 'c21'	, 'isKey'=>false	, 'val'=> $data['xCountry']								),
				array('col' => 'c23'	, 'isKey'=>true		, 'val'=> $idPath										),
				array('col' => 'idFile'	, 'isKey'=>true		, 'val'=> $idFile										),
			)
		);
		if(file_exists($data['fileDetail']['fullPath'].'/folder.jpg')){
			$idArt = $this->addMediaToMedia($idMovie, 'movie','poster', utf8_decode($xbmcpath['path'].'folder.jpg'));
		}
		if(file_exists($data['fileDetail']['fullPath'].'/fanart.jpg')){
			$idArt = $this->addMediaToMedia($idMovie, 'movie','fanart', utf8_decode($xbmcpath['path'].'fanart.jpg'));
		}
		$this->addGenreToMedia($idMovie,$data['genre'],'genrelinkmovie','idMovie');
	}

	function addMediaToMedia($idMedia,$mediaType,$type,$picture){
		$idArt = $this->xbmcDBSQLInsertOrUpdate(
			'art',
			'art_id',
			array(
				array('col' => 'media_id'		, 'isKey'=>true, 'val'=> $idMedia			),
				array('col' => 'media_type'		, 'isKey'=>true, 'val'=> $mediaType			),
				array('col' => 'type'			, 'isKey'=>true, 'val'=> $type				),
				array('col' => 'url'			, 'isKey'=>false,'val'=> $picture			)
			)
		);
	}

	function addGenreToMedia($idMedia,$genreStr,$tableLink,$colLink){
		$arrGenre = preg_split('!\/|\|!',$genreStr);
		if (is_array($arrGenre)){
			foreach($arrGenre as $genre){
				$genre = trim($genre);
				if($genre=='') return;
				$idGenre = $this->xbmcDBSQLInsertOrUpdate(
						'genre',
						'idGenre',
						array(
							array('col' => 'strGenre'		, 'isKey'=>true, 'val'=> $genre			)
						)
				);
				$this->xbmcDBSQLInsertOrUpdate(
						$tableLink,
						'',
						array(
							array('col' => 'idGenre'		, 'isKey'=>true, 'val'=> $idGenre		),
							array('col' => $colLink			, 'isKey'=>true, 'val'=> $idMedia		)
						)
				);
			}
		}
	}

	function getXbmcpath($filename,$returnArray=true) {
		$res = null;
		foreach ($this->folderSeriesList as $k => $v) {
			//db( $v['path']);
			//db(addslashes($data['filename']));
			if (eregi('^' . addslashes($v['path']), $filename)) {
				$res = str_ireplace($v['path'], $v['xbmcpath'], $filename);
				$res = str_replace('\\', '/', $res);
				preg_match('|(^.*/)(.*)|', $res, $r);
				$res = array();
				if($returnArray){
					$res['path'		] = $r[1];
					$res['filename'	] = $r[2];
					return $res;
				}else{
					return $r[1].$r[2];
				}
				break;
			}
		}
		return $res;
	}

	function getLocalpath($filename,$returnArray=true) {
		$res = null;
		//db($filename);
		foreach ($this->folderSeriesList+$this->folderMoviesList as $k => $v) {
			//db( $v['path']);
			//db(addslashes($data['filename']));
			//db(addslashes($v['xbmcpath']));
			if (eregi('^' . addslashes($v['xbmcpath']), $filename)) {
				$res = str_ireplace($v['xbmcpath'], $v['path'], $filename);
				$res = str_replace('\\', '/', $res);
				preg_match('|(^.*/)(.*)|', $res, $r);
				$res = array();
				if($returnArray){
					$res['path'		] = $r[1];
					$res['filename'	] = $r[2];
					$res['key'		] = $v['name'];
					return $res;
				}else{
					return $r[1].$r[2];
				}
				break;
			}
		}
		return $res;
	}

	function getXbmcMoviesPath($filename) {
		$res = null;
		foreach ($this->folderMoviesList as $k => $v) {
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
		header('content-type:text/html');
		print '<table border=1 callpadding=0 cellspacing=0 >';
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
				print '<td><table border=1 callpadding=0 cellspacing=0 ><tr>';
				if (is_array($res['rgx_match'])) {
					foreach ($res['rgx_match'] as $k => $v) {
						print "<td><span style=\"color:blue;\">$k</span>&nbsp;";
						print "$v&nbsp;</td>";
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

	private function _get_hash($file_path)
	{
		$chars = strtolower($file_path);
		$crc = 0xffffffff;

		for ($ptr = 0; $ptr < strlen($chars); $ptr++)
		{
			$chr = ord($chars[$ptr]);
			$crc ^= $chr << 24;

			for ((int) $i = 0; $i < 8; $i++)
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

		// Système d'exploitation en 64 bits ?
		if (strpos(php_uname('m'), '_64') !== false)
		{
			//Formatting the output in a 8 character hex
			if ($crc>=0)
			{
				$hash = sprintf("%16s",sprintf("%x",sprintf("%u",$crc)));
			}
			else
			{
				$source = sprintf('%b', $crc);
				$hash = "";
				while ($source <> "")
				{
					$digit = substr($source, -4);
					$hash = dechex(bindec($digit)) . $hash;
					$source = substr($source, 0, -4);
				}
			}
			$hash = substr($hash, 8);
		}
		else
		{
			//Formatting the output in a 8 character hex
			if ($crc>=0)
			{
				$hash = sprintf("%08s",sprintf("%x",sprintf("%u",$crc)));
			}
			else
			{
				$source = sprintf('%b', $crc);
				$hash = "";
				while ($source <> "")
				{
					$digit = substr($source, -4);
					$hash = dechex(bindec($digit)) . $hash;
					$source = substr($source, 0, -4);
				}
			}
		}

		return $hash;
	}

	function xbmcHash($hashInput) {
		$dir = dirname(__FILE__);
		return $this->_get_hash($hashInput);
		//return exec("$dir/perl.exe $dir/xbmchash.pl \"$hashInput\"");
	}

	function svc_cleanupDatabase(){
		$this->initdb();
		$sqlSearch="select distinct concat(strpath,strFilename) as filename,fi.idFile,pa.idPath
					from path pa
					inner join files fi on fi.idPath=pa.idPath
					where
					strFilename regexp '(avi|mkv|ogm|mp4|divx|iso|mpg|srt|sub|idx)$'
					order by filename
					;";
		$allMedias = self::$XbmcDBPDO->query($sqlSearch);
		foreach ($allMedias as $k=>$row) {
			if($k%200==0){
				db(sprintf('%s/%s %s',$k,$nb,$row['filename']));
			}
			$localFilename=$this->getLocalpath($row['filename'],false);
			if(!file_exists(utf8_encode($localFilename))&& !file_exists(utf8_encode($localFilename)) && utf8_encode($localFilename) && $localFilename!=''){
				print sprintf("%s => %s\n",$localFilename,$row['filename']);
				self::$XbmcDBPDO->query("delete from files where idFile=".$row['idFile']);
			}
		}

		$aSqlDel=array(
			//"not to do delete from episode	where idFile in (select idFile from files where strFilename regexp '\\.(srt|sub|idx)\$');",
			//"not to do delete from movie		where idFile in (select idFile from files where strFilename regexp '\\.(srt|sub|idx)\$');",
			//"not to do delete from path		where idPath not in (select distinct idPath from files);"
			"delete from files		where strFilename regexp '\\.(srt|sub|idx)\$');",
		);

		foreach ($aSqlDel as $sqlDel) {
			db($sqlDel);
			db(self::$XbmcDBPDO->query($sqlDel));
		}

	}

	function svc_test2() {
		print $this->xbmcHash('F:\\Videos\\Nosferatu.avi') . '<br>' . '2a6ec78d' . '<br><br>';
		print $this->xbmcHash('123456789') . '<br>' . '0376e6e7' . '<br><br>';
		print $this->xbmcHash('smb://user:pass@server/share/directory/') . '<br>' . 'c5559f13' . '<br><br>';
		print $this->xbmcHash('smb://user:pass@server/share/directory/file.ext') . '<br>' . '8ce36055' . '<br><br>';
	}

	function svc_copyDevTreeStruct(){
		$this->copyStruct('/mnt/I/__Series'			,'/mnt/ztest/U/__Series',0);
		$this->copyStruct('/mnt/J/__Series'			,'/mnt/ztest/V/__Series',0);
		//$this->copyStruct('/volumes/MOVIES_1TOB'	,'/Users/o.michaud/Documents/tmpStruct.qdmmmdb/J',0);
	}
	function copyStruct($from,$to,$level){
		$aFrom = glob($from.'/*');
		foreach($aFrom as $f){
			$f2 = str_replace($from.'/','',$f);
			if(is_dir($f)){
				db(str_repeat("\t", $level+1)."|[D]|".$to.'/'.$f2);
				mkdir($to.'/'.$f2);
				$this->copyStruct($from.'/'.$f2,$to.'/'.$f2,$level+1);
			}else{
				db(str_repeat("\t", $level+1)."|[F]|".$to.'/'.$f2);
				if(preg_match('!(xml|jpg|png|tbn|nfo)$!',$f2)){
					copy($f,$to.'/'.$f2);
				}else{
					file_put_contents($to.'/'.$f2, '');
				}
			}
		}
	}

	function getLangFromPath($path)
	{
		if (substr($path, - 1) == '/') {
			$path = substr($path, 0, - 1);
		}
		$lang = 'WD';
		if (preg_match('! FR$!', $path)) {
			$lang = 'FR';
		}
		if (preg_match('! VF$!', $path)) {
			$lang = 'FR';
		}
		if (preg_match('! VO$!', $path)) {
			$lang = 'EN';
		}
		return $lang;
	}

}
