<?php
namespace App\Components\QDmmmDB\Mediadb\Movies;

use App\Components\QDmmmDB\Misc\ToolsFiles;
use App\Components\QDmmmDB\Misc\Tools;
use App\Components\QDmmmDB\Mediadb\Scrapers\MovieParser;
use App\Components\QDmmmDB\Mediadb\Scrapers\movies\scrapertheMovieDBApi;
use App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scraperAllocineApi;
use App\Components\QDmmmDB\Mediadb\MediaDBManager;

class MoviesManager extends MediaDBManager{
	static $threadArr;

	public function chooseMovie($sMovie,$sPath,$sEngine) {
		$sMovie = trim($sMovie);
		$sc = null;
		switch ($sEngine){
			case 'allocineapi':
				$sc = new \App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scraperAllocineApi();
			break;
			case 'themoviedb':
				$sc = new \App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scrapertheMovieDBApi();
			break;
			case 'senscritique':
				$sc = new \App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scrapersenscritique();
			break;
		}
		$res = array ();
		if($sc){
			$res['results'] = $sc->getList($sMovie);
		}
		return $res;
	}

	public function publish(){
		QDLogger::log('test '.date('Ymd His'));
	}

	public function updateDatabase(){
		header('content-type:text/html');
		$sc = new MovieParser;
		$nb=0;
		foreach($this->folderMoviesList as $movieDrive){
			$arrMovies = $this->getMoviesFiles($movieDrive['name'],'*');
			foreach($arrMovies['results'] as $movieData){
				$nb++;
				$movieNFO=$movieData['fileDetail']['fullPath'].'/movie.nfo';
				if(file_exists($movieNFO)){
					$o = $sc->simpleLoadXbmcMovieNfo($movieNFO);
					if($o){
						QDLogger::log($movieData['fileDetail']['fullPath']);
						set_time_limit(20);
						$o = Tools::object2array($o);
						$o['fileDetail']=$movieData['fileDetail'];
						$this->makeMovieDB($o);
					}
				}
			}
		}
	}

	public function convertXBMCNfoToQdMmmDb(){
		$this->pri_convertXBMCNfoToQdMmmDb($this->getMoviesFiles('G','*'));
	}

	private function pri_convertXBMCNfoToQdMmmDb($arrFiles){
		foreach($arrFiles['results'] as $file){
			if ($file['nfo'] && !$file['qdmmmdb']!=''){
				$nfo = $file['fullpath'].'/movie.nfo';
				$txt = $file['fullpath']. '/info.txt';
				print $nfo."\n";
				if(file_exists($nfo)){
					$sc = new scrapertheMovieDBApi();
					$obj = $sc->simpleLoadXbmcMovieNfo($nfo);
					if($obj){
						$id = (string)$obj->id;
						if (preg_match('/^tt[0-9]*$/',$id)){
							$returnId = $sc->getIdFromImdbId($id);
							if (!is_null($returnId)){
								$fullRecord = $sc->getDetail($returnId);
								file_put_contents($txt	,json_encode($fullRecord));
								print "OK =>".$txt." \n";
							}
						}
					}
				}
			}
		}
	}

	public function preloadFolder(){
		set_time_limit(0);
		$this->pri_preloadFolder($this->getMoviesFiles('F'));
		//$this->pri_preloadFolder($this->getMoviesFiles('Q'));
		//$this->pri_preloadFolder($this->getMoviesFiles('K'));
		/*
		$this->pri_preloadFolder($this->getMoviesFiles('F'));
		$this->pri_preloadFolder($this->getMoviesFiles('F1'));
		$this->pri_preloadFolder($this->getMoviesFiles('K'));
		$this->pri_preloadFolder($this->getMoviesFiles('QAct'));
		$this->pri_preloadFolder($this->getMoviesFiles('QDoc'));
		$this->pri_preloadFolder($this->getMoviesFiles('QDra'));
		$this->pri_preloadFolder($this->getMoviesFiles('QEnf'));
		$this->pri_preloadFolder($this->getMoviesFiles('QHor'));
		$this->pri_preloadFolder($this->getMoviesFiles('QHum'));
		$this->pri_preloadFolder($this->getMoviesFiles('QSFF'));
		*/
	}
	private function pri_preloadFolder($arrFiles){
		foreach($arrFiles['results'] as $file){
			if (!$file['nfo'] && $file['title']!=''){
				print "=>".$file['title']."\n";
				$res=$this->pri_chooseMovie(array('e'=>'themoviedb','m'=>$file['title']));
				if(array_key_exists('results',$res)){
				$cnt = count($res['results']);
				}else{
					$cnt = 0;
				}
				print "=>count : ".$cnt."\n";
				if ($cnt>0){
					$chMovie = $res['results'][0];
					print "  =>".$chMovie['title'].' '.$chMovie['year']."\n";;
					$res2 = $this->chooseMoviesDetail('themoviedb',$chMovie['id']);
					print "    ".$res2['data']['title']."\n";
					print "    ".$res2['data']['summary']."\n";
					print "    ".$res2['data']['poster']."--"."\n";
					sleep(3);
				}
				print "-----------------------\n";
			}
		}
	}

	public function proxyPosterImg($i64){
		return file_get_contents(base64_decode($i64));
	}

	public function chooseMoviesDetail($sEngine,$sFilename,$iID) {
		$sc = null;
		switch ($sEngine){
			case 'allocineapi':
				$sc = new \App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scraperAllocineApi;
			break;
			case 'themoviedb':
				$sc = new \App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scrapertheMovieDBApi;
			break;
			case 'senscritiques':
				$sc = new \App\Components\QDmmmDB\Mediadb\Scrapers\Movies\scrapersenscritique();
			break;
		}
		$res = array ('data'=>array());

		if($sc){
			$file=false;
			if (file_exists($sFilename.'/info.txt')){
				$tmpJson = json_decode(file_get_contents($sFilename.'/info.txt'),true);
				if($tmpJson['engine']==$sEngine && $tmpJson['id']==$iID){
					$res['data']=$tmpJson;
					$res['data']['alreadyDone']=true;
					$file=true;
				}
			}
			if(!$file){
				$res['data'] = $sc->getDetail(trim($iID));
				$this->pri_cacheImages($sc,$res['data']['posters'  ]);
				$this->pri_cacheImages($sc,$res['data']['backdrops']);
				if(is_array($res['data']['posters'  ]) && count($res['data']['posters'  ])>0){
					$res['data']['poster']=$res['data']['posters'  ][0]['url'];
				}
				if(is_array($res['data']['backdrops'  ]) && count($res['data']['backdrops'  ])>0){
					$res['data']['backdrop'	]=$res['data']['backdrops'  ][0]['url'];
				}
			}
		}
		return $res;
	}

	private function pri_processCacheImage($modulo,$arr){
		foreach($arr as $k=>$v){
			if (($k % 4)==$modulo){
				if(is_array($v)){
					$url = $v['url'];
					//$this->QDNet->getCacheURL($url , 'imgs', 60*24*365*20,true,'');
				}else{
					$url = $v;
					$this->QDNet->getCacheURL($url , 'imgs', 60*24*365*20,true,'');
				}
			}
		}
	}

	private function pri_cacheImages($scrapper,&$arr){
		header('content-type:text/html');
		$withThreads=false;
		$nbPic=18;

		if(is_array($arr)){
			if($withThreads){
				$t1 = new Thread( array($this,'pri_processCacheImage') );
				$t2 = new Thread( array($this,'pri_processCacheImage') );
				$t3 = new Thread( array($this,'pri_processCacheImage') );
				$t4 = new Thread( array($this,'pri_processCacheImage') );

				$t1->start(0,$arr);
				$t2->start(1,$arr);
				$t3->start(2,$arr);
				$t4->start(3,$arr);
				while( $t1->isAlive() && $t2->isAlive() && $t3->isAlive() && $t4->isAlive() ) {
					sleep(1);
				}
			}else{
				$this->pri_processCacheImage(0,$arr);
				$this->pri_processCacheImage(1,$arr);
				$this->pri_processCacheImage(2,$arr);
				$this->pri_processCacheImage(3,$arr);
			}
			foreach($arr as $k=>$v){
				if(is_array($v)){
					$url = $v['url'];
				}else{
					$url = $v;
				}
				if(is_array($v)){
					$arr[$k]=array(
							'url'	=> $v['url'],
							'w'		=> $v['width'],
							'h'		=> $v['height']
					);
				}else{
					set_time_limit(20);
					/*$thumb=new Imagick($this->QDNet->lastCacheFile);
					self::$threadArr[$k]=array(
								'url'	=> $v,
								'w'		=> $thumb->getImageWidth(),
								'h'		=> $thumb->getImageHeight()
					);*/
					$localfile = $this->QDNet->getCacheFileName($url , 'imgs','');
					$thumb=getimagesize($localfile);
					$arr[$k]=array(
							'url'	=> $url,
							'w'		=> $thumb[0],
							'h'		=> $thumb[1]
					);
				}
			}

			//db($arr);
			//self::$threadArr=array();
			if(!$scrapper->getCapabilitie('pictureSorted')){
				$this->sortBySize='desc';
				uasort($arr,array($this,"sortBySize"));
			}
			$arr = array_values($arr);
			$arr = array_slice($arr,0,$nbPic);
		}
	}

	private function sortBySize($a,$b) {
		$as = $a['w']*$a['h'];
		$bs = $b['w']*$b['h'];
		if ($as == $bs) {
			return 0;
		}
		return (($this->sortBySize=='asc')?$as < $bs:$as > $bs) ? -1 : 1;
	}

	public function checkMoviesPicture() {
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
						$t = $this->QDNet->getCacheURL($poster, 'seriesDetail', $this->cacheminutes, $this->cache,'');
						file_put_contents($path . '/movie.tbn', $t);
						file_put_contents($path . '/folder.jpg', $t);
						print $poster . "<br>";
					}
				}
			}
		}
	}

	public function getMoviesTree() {
		//unset($_SESSION['cacheFolderTree']);
		$arr = array();
		foreach($this->folderMoviesList as $k=>$v){
			$arr[] = array(
				'text'		=> $v['name'],
				'fullname'	=> $v['path'],
				'rootDrive'	=> 1,
				'leaf'		=> true
			);
		}
		return $arr;

	}

	private function getMoviesDirectory($path = '.', $level = 0) {
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

	public function getXbmcScraperMovieDetail() {
		$sc = new scraperAllocineApi();
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

	private function cleanMoviesFilename($moviename, $strict = false) {
		if ($strict) {
			foreach ($this->arrCleanupMoviesRegexStrict as $k => $v) {
				if(!$v['multiple'] && preg_match('/' . $v['rgx'] . '/i',$moviename)){
					//db($v);
					$moviename = $this->delmulspace(preg_replace('/' . $v['rgx'] . '/i', array_key_exists('rep', $v) ? $v['rep'] : ' ', ' ' . $moviename . ' '));
				}
			}
		}
		foreach ($this->arrCleanupMoviesRegex as $k => $v) {
			if(preg_match('/' . $v['rgx'] . '/i',$moviename)){
				$moviename = $this->delmulspace(preg_replace('/' . $v['rgx'] . '/i', array_key_exists('rep', $v) ? $v['rep'] : ' ', ' ' . $moviename . ' '));
			}
		}
		return trim(ucwords(strtolower($moviename)));
	}

	private function addFileToGetMoviesFiles($prm){
		$tmp = array(
			'rootPath'		=> $prm['rootPath'],
			'fullpath'		=> $prm['fileDetail']['fullPath'],
			'mtime'			=> $prm['fileDetail']['mtime'],
			'title'			=> $this->cleanMoviesFilename($prm['fileDetail']['filename'], true),
			'folder'		=> Tools::array_key_exists_assign_default('inFolder', $prm, 'inFolder')=='inFolder'?basename(dirname($prm['fileDetail']['file'])):$prm['fileDetail']['filename'],
			'newfilename'	=> $this->cleanMoviesFilename($prm['fileDetail']['filename']),
			'filename'		=> Tools::array_key_exists_assign_default('inFolder', $prm, 'inFolder')=='inFolder'?$prm['fileDetail']['filename']:'',
			'ext'			=> $prm['fileDetail']['extension'],
			'filesize'		=> Tools::size_readable(filesize($prm['fileDetail']['file'])),
			'pathfilename64'=> base64_encode(realpath($prm['fileDetail']['file'])),
			'md5'			=> md5(realpath($prm['fileDetail']['file'])),
			'srt'			=> file_exists($prm['fileDetail']['fullPath'].'/'.$details['filename'].'.srt'),
			'poster'		=> file_exists($prm['fileDetail']['fullPath'].'/folder.jpg'),
			'fanart'		=> file_exists($prm['fileDetail']['fullPath'].'/fanart.jpg'),
			'nfo'			=> file_exists($prm['fileDetail']['fullPath'].'/movie.nfo'),
			'qdmmmdb'		=> file_exists($prm['fileDetail']['fullPath'].'/info.txt'),
			'extrathumbs'	=> file_exists($prm['fileDetail']['fullPath'].'/extrathumbs'),
			'backdrop'		=> file_exists($prm['fileDetail']['fullPath'].'/backdrop.jpg')
		);
		$tmp = array_merge($tmp,$prm);
		return $tmp;
	}

	private function pri_scanMovieFolder($name,$path){
		$tmp = glob($path . '/*');
		foreach ($tmp as $file) {
			$fileDetail = ToolsFiles::pathinfo_utf($file);
			if (in_array(strtolower($fileDetail['extension']), $this->movieExt) && $this->pri_movieFileIsVisible($fileDetail['filename'])) {
				$this->arrMovies['results'][] = $this->addFileToGetMoviesFiles(array(
					'rootPath'		=> $name,
					'fileDetail'	=> $fileDetail,
					'mtime'			=> $fileDetail['mtime'],
					'inFolder'		=> 'inFolder'
				));
			}
		}
	}

	private function pri_movieFileIsVisible($file){
		$hidden = false;
		foreach($this->arrHiddenmovieRegex as $rule){
			if (preg_match('/'.$rule['rgx'].'/',$file)){
				$hidden=true;
			}
		}
		return !$hidden;
	}

	public function getMoviesFiles($name,$mask='*') {
		//$str = stripslashes(str_replace('/', '\\\\', utf8_decode($path)));
		$this->arrMovies = array();
		$p = $this->folderMoviesList[$name]['path'];
		$tmp = glob($p . '/'.$mask);
		foreach ($tmp as $file) {
			if(is_dir($file)){
				$this->pri_scanMovieFolder($name,$file);
			}else{
				$fileDetail = ToolsFiles::pathinfo_utf($file);
				if (in_array(strtolower($fileDetail['extension']), $this->movieExt)) {
					$this->arrMovies['results'][] = $this->addFileToGetMoviesFiles(array(
						'rootPath'		=> $name,
						'fileDetail'	=> $fileDetail,
						'inFolder'		=> 'file'
					));
				}
			}
		}
		//db($this->arrMovies);die();
		return $this->arrMovies;
	}

	public function setMoviesFromPath($sRef,$sRecord,$sID,$sPath) {
		header('content-type:text/html');
		$debug				= false;
		$forceFileWrite		= true;
		$scraperObject		= new MovieParser();
		$ref				= json_decode($sRef,true);
		$originalFileName	= base64_decode($ref['pathfilename64']);
		$fullRecord			= json_decode($sRecord,true);
		$compatRecord		= $scraperObject->convertFullRecordToCompatibleRecord($fullRecord);

		$newFilename = $this->cleanMoviesFilename($compatRecord['title'],true);
		if (in_array(trim($newFilename),array('','.','..'))){
			return array('corrupted'=>true);
		}
		if (Tools::array_key_exists_assign_default('movieFolderWithYear',$GLOBALS['conf']['qdmediadb'],false)){
			$newFilename .= (Tools::array_key_exists_assign_default('year', $compatRecord, false)?' ('.$compatRecord['year'].')':'');
		}
		$newFolder = $newFilename;
		switch ($ref['inFolder']){
			case 'file':
				if(!file_exists($ref['fullpath'].'/'.$newFolder)){
				//	return array('corrupted'=>'folder already exists 1');
				//}else{
					@mkdir($ref['fullpath'].'/'.$newFolder);
				}
				$movieFolder = $ref['fullpath'].'/'.$newFolder;
			break;
			case 'inFolder':
				if(file_exists(dirname($ref['fullpath']).'/'.$newFolder)){
					//return array('corrupted'=>'folder already exists 2');
				}
				if(!file_exists($ref['fullpath'].'/'.$newFolder)){
					@rename($ref['fullpath'], dirname($ref['fullpath']).'/'.$newFolder);
					$ref['fullpath'] = dirname($ref['fullpath']);
					$movieFolder = $ref['fullpath'].'/'.$newFolder;
					$tmpDet = ToolsFiles::pathinfo_utf(base64_decode($ref['pathfilename64']));
					$originalFileName	= $movieFolder.'/'.$tmpDet['basename'];
				}
			break;
			default:
				return array('corrupted'=>$ref['inFolder'].'-'.__FILE__.' - '.__LINE__);
			break;
		}
		$isMultiple = false;

		$addStr='';
		foreach ($this->arrKeepSpecialTag as $tag){
			if(preg_match('/'.$tag['rgx'].'/i',$originalFileName,$m)){
				$addStr = '-'.$tag['rep'];
				if($tag['multiple']){
					$isMultiple = true;
					$rgxMultiple = $m[1];
					$repMultiple = $tag['rep'];
				}
			}
		}
		$multipleToRemove=array();
		if($isMultiple){
			for($num=1;$num<=9;$num++){
				if (preg_match('/'.$rgxMultiple .'/i',$originalFileName)){
					$originalFileNameMultiple= preg_replace('/'.$rgxMultiple . 1 . '/i',$rgxMultiple.$num,$originalFileName);

					$newFullFilenameMultiple = $movieFolder . '/'.$newFilename.$addStr.$num.'.'.strtolower($ref['ext']);
					//print "=====> $originalFileName , $repMultiple , $originalFileNameMultiple , $newFullFilenameMultiple)<br>";
					if(!file_exists($newFullFilenameMultiple) && file_exists($originalFileNameMultiple)){
						//print "rename($originalFileNameMultiple , $newFullFilenameMultiple)<br>";
						rename($originalFileNameMultiple,$newFullFilenameMultiple);
						if($num!=1){
							$multipleToRemove[]=$originalFileNameMultiple;
						}
					}
				}
			}
			$newFullFilename = $movieFolder . '/'.$newFilename.$addStr . 1 .'.'.strtolower($ref['ext']);
		}else{
			$newFullFilename = $movieFolder . '/'.$newFilename.$addStr.'.'.strtolower($ref['ext']);
			if(!file_exists($newFullFilename)){
			//	return array('corrupted'=>'renamed file already exists');
			//}else{
				rename($originalFileName,$newFullFilename);
			}
		}

		if (!file_exists($movieFolder		. '/info.txt'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/info.txt'	,json_encode($fullRecord));
		}

		if (!file_exists($movieFolder		. '/movie.nfo'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/movie.nfo'	, $scraperObject->convertToXbmcMovieNfo($compatRecord) );
		}

		if (!file_exists($movieFolder		. '/movie.tbn'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/movie.tbn'	, $this->QDNet->getCacheURL($compatRecord['poster'], 'imgs', 60*24*365*20,true,''));
		}

		if (!file_exists($movieFolder		. '/folder.jpg'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/folder.jpg'	, $this->QDNet->getCacheURL($compatRecord['poster'], 'imgs', 60*24*365*20,true,''));
		}

		if (!file_exists($movieFolder		. '/fanart.jpg'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/fanart.jpg'	, $this->QDNet->getCacheURL($compatRecord['backdrop'], 'imgs', 60*24*365*20,true,''));
		}

		$fileDetail = ToolsFiles::pathinfo_utf($newFullFilename);
		$newRefRecord =  $this->addFileToGetMoviesFiles(array(
			'rootPath'		=> $ref['rootPath'],
			'fileDetail'	=> $fileDetail,
			'inFolder'		=> 'inFolder'
		));

		if (file_exists($movieFolder	. '/movie.nfo')) {
			$rec2db = Tools::object2array($scraperObject->simpleLoadXbmcMovieNfo($movieFolder	. '/movie.nfo'));
			$rec2db['fileDetail']=$fileDetail;
			$this->makeMovieDB($rec2db);
		}

		if($debug){
			db (array(
				'newFolder'			=> $newFolder,
				'newFilename'		=> $newFilename,
				'originalFileName'	=> $originalFileName,
				'ref'				=> $ref,
				'fullRecord'		=> $fullRecord,
				'xml'				=> $scraperObject->convertToXbmcMovieNfo($compatRecord)
			));
		}

		return ($newRefRecord);

		$id = 0 + $sID;
		$path = base64_decode($sPath);
		//print $path;
		$sc = new scraperAllocineApi;
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
					file_put_contents($newpath . '/movie.tbn', $this->QDNet->getCacheURL($tarray['poster'], 'seriesDetail', $this->cacheminutes, $this->cache,''));
				}
				if (!file_exists($newpath . '/folder.jpg')) {
					file_put_contents($newpath . '/folder.jpg', $this->QDNet->getCacheURL($tarray['poster'], 'seriesDetail', $this->cacheminutes, $this->cache,''));
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

	public function renameMoviesFiles($sModified,$sMoveExists) {
		$t = utf8_decode(base64_decode($sModified));
		$arr = json_decode($t, true);
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
				if (array_key_exists($v['md5'], $arrMD5)) {
					//print ($new64);
					if (file_exists($new64)) {
						$resultRename = 'file exists';
						if ($sMoveExists == 'true') {
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
}
