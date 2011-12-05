<?php
class QDMoviesProxy extends QDMediaDBProxy{

	function svc_preloadFolder(){
		$this->pri_preloadFolder($this->pri_getMoviesFiles('Q'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('F'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('F1'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('K'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QAct'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QDoc'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QDra'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QEnf'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QHor'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QHum'));
		$this->pri_preloadFolder($this->pri_getMoviesFiles('QSFF'));

	}
	function pri_preloadFolder($arrFiles){
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
					$res2 = $this->pri_chooseMoviesDetail(array('e'=>'themoviedb','i'=>$chMovie['id']));
					print "    ".$res2['data']['title']."\n";
					print "    ".$res2['data']['summary']."\n";
					print "    ".$res2['data']['poster']."--"."\n";
					sleep(3);
				}
				print "-----------------------\n";
			}
		}
	}

	function svc_chooseMovie() {
		return $this->pri_chooseMovie($_REQUEST);
	}
	function pri_chooseMovie($prm) {
		$moviesname = trim($prm['m']);
		$path = $prm['p'];
		$sc = null;
		switch ($prm['e']){
			case 'allocineapi':
				$sc = new scraperAllocineApi;
			break;
			case 'themoviedb':
				$sc = new scrapertheMovieDBApi;
			break;
		}
		$res = array ();
		if($sc){
			$res['results'] = $sc->getList($moviesname);
		}
		return $res;
	}

	function svc_proxyPosterImg(){
		header('Content-type: image/jpeg');
		die(file_get_contents(base64_decode($_REQUEST['i64'])));
	}


	function svc_chooseMoviesDetail() {
		return $this->pri_chooseMoviesDetail($_REQUEST);
	}
	function pri_chooseMoviesDetail($prm) {
		$sc = null;
		switch ($prm['e']){
			case 'allocineapi':
				$sc = new scraperAllocineApi;
			break;
			case 'themoviedb':
				$sc = new scrapertheMovieDBApi;
			break;
		}
		$res = array ('data'=>array());
		if($sc){
			$res['data'] = $sc->getDetail(trim($prm['i']));
			//$res['data']['poster'	]="http://cf2.imgobject.com/t/p/w92/t7qevwjcTsEAXsErVgQXI1ipxTK.jpg";
			//$res['data']['backdrop'	]="http://cf2.imgobject.com/t/p/w300/cLDUrc5zmbsEoMvr3y1mlH2EZtv.jpg";

			$this->pri_cacheImages($res['data']['posters'  ]);
			$this->pri_cacheImages($res['data']['backdrops']);
			if(is_array($res['data']['posters'  ]) && count($res['data']['posters'  ])>0){
				$res['data']['poster']=$res['data']['posters'  ][0]['url'];
			}
			if(is_array($res['data']['backdrops'  ]) && count($res['data']['backdrops'  ])>0){
				$res['data']['backdrop'	]=$res['data']['backdrops'  ][0]['url'];
			}
		}
		return $res;
	}

	function pri_cacheImages(&$arr){
		if(is_array($arr)){
			foreach($arr as $k=>$v){
				$url = $v;
				set_time_limit(20);
				$this->QDNet->getCacheURL($v, 'imgs', 60*24*365*20,true);
				$thumb=new Imagick($this->QDNet->lastCacheFile);
				$arr[$k]=array(
					'url'	=> $url,
					'w'		=> $thumb->getImageWidth(),
					'h'		=> $thumb->getImageHeight()
				);
				if ($k>10){
					break;
				}
			}
			$this->sortBySize='desc';
			uasort($arr,array($this,"sortBySize"));
			$arr=array_values($arr);
			$arr = array_slice($arr,0,10);
		}
	}

	function sortBySize($a,$b) {
		$as = $a['w']*$a['h'];
		$bs = $b['w']*$b['h'];
		if ($as == $bs) {
			return 0;
		}
		return (($this->sortBySize=='asc')?$as < $bs:$as > $bs) ? -1 : 1;
		}

	function svc_checkMoviesPicture() {
		$path = "m:/___films";
		$paths = glob($path . "/*", GLOB_ONLYDIR);
		foreach ($paths as $path) {
			if (file_exists($path . "/info.txt") &&
				!file_exists($path . "/movie.tbn")) {
				$t = glob($path . "/*.nfo");
				if (array_key_exists(0, $t)) {
					$sdom = simplexml_load_string(file_get_contents($t[0]));
					;
					$f = object2array($sdom->xpath('/details'));
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

	function svc_getMoviesTree() {
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

		/*
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
		 */
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

	function svc_getXbmcScraperMovieDetail() {
		$sc = new scraperAllocineApi;
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

	function svc_getMoviesFiles() {
		//foreach ($this->folderMoviesList as $path) {
		//db($this->pri_getMoviesFiles($_REQUEST['name']));die();
		return $this->pri_getMoviesFiles($_REQUEST['name']);//$_REQUEST['fullpath'], $_REQUEST['only2Rename'] == 'true'
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
		return trim(ucwords(strtolower($moviename)));
	}

	function pri_addFileToGetMoviesFiles($prm){
		//db($prm);
		$tmp = array(
			'rootPath'		=> $prm['rootPath'],
			'fullpath'		=> $prm['fileDetail']['fullPath'],
			'title'			=> $this->cleanMoviesFilename($prm['fileDetail']['filename'], true),
			'folder'		=> array_key_exists_assign_default('inFolder', $prm, 'inFolder')=='inFolder'?basename(dirname($prm['fileDetail']['file'])):$prm['fileDetail']['filename'],
			'newfilename'	=> $this->cleanMoviesFilename($prm['fileDetail']['filename']),
			'filename'		=> array_key_exists_assign_default('inFolder', $prm, 'inFolder')=='inFolder'?$prm['fileDetail']['filename']:'',
			'ext'			=> $prm['fileDetail']['extension'],
			'filesize'		=> size_readable(filesize($prm['fileDetail']['file'])),
			'pathfilename64'=> base64_encode(realpath($prm['fileDetail']['file'])),
			'md5'			=> md5(realpath($prm['fileDetail']['file'])),
			'srt'			=> file_exists($prm['fileDetail']['fullPath'].'/'.$details['filename'].'.srt'),
			'poster'		=> file_exists($prm['fileDetail']['fullPath'].'/folder.jpg'),
			'fanart'		=> file_exists($prm['fileDetail']['fullPath'].'/fanart.jpg'),
			'nfo'			=> file_exists($prm['fileDetail']['fullPath'].'/movie.nfo'),
			'extrathumbs'	=> file_exists($prm['fileDetail']['fullPath'].'/extrathumbs'),
			'backdrop'		=> file_exists($prm['fileDetail']['fullPath'].'/backdrop.jpg')
		);
		$tmp = array_merge($tmp,$prm);
		return $tmp;
	}

	function pri_scanMovieFolder($name,$path){
		$tmp = glob($path . '/*');
		foreach ($tmp as $file) {
			$fileDetail = CW_Files::pathinfo_utf($file);
			if (in_array(strtolower($fileDetail['extension']), $this->movieExt)) {
				$this->arrMovies['results'][] = $this->pri_addFileToGetMoviesFiles(array(
					'rootPath'		=> $name,
					'fileDetail'	=> $fileDetail,
					'inFolder'		=> 'inFolder'
				));
			}
		}
	}

	function pri_getMoviesFiles($name) {
		//$str = stripslashes(str_replace('/', '\\\\', utf8_decode($path)));
		$this->arrMovies = array();
		$p = $this->folderMoviesList[$name]['path'];
		$tmp = glob($p . '/*');
		foreach ($tmp as $file) {
			if(is_dir($file)){
				$this->pri_scanMovieFolder($name,$file);
			}else{
				$fileDetail = CW_Files::pathinfo_utf($file);
				if (in_array(strtolower($fileDetail['extension']), $this->movieExt)) {
					$this->arrMovies['results'][] = $this->pri_addFileToGetMoviesFiles(array(
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

	function svc_setMoviesFromPath() {
		$debug				= false;
		$forceFileWrite		= true;
		$scraperObject		= new QDHtmlMovieParser();
		$ref				= json_decode($_REQUEST['ref'],true);
		$originalFileName	= base64_decode($ref['pathfilename64']);
		$fullRecord			= json_decode($_REQUEST['record'],true);
		$compatRecord		= $scraperObject->convertFullRecordToCompatibleRecord($fullRecord);


		$newFilename = $this->cleanMoviesFilename($compatRecord['title'],true);
		if (in_array(trim($newFilename),array('','.','..'))){
			return array('corrupted'=>true);
		}
		if (array_key_exists_assign_default('movieFolderWithYear',$GLOBALS['conf']['qdmediadb'],false)){
			$newFilename .= (array_key_exists_assign_default('year', $compatRecord, false)?' ('.$compatRecord['year'].')':'');
		}
		$newFolder = $newFilename;
		switch ($ref['inFolder']){
			case 'file':
				if(file_exists($ref['fullpath'].'/'.$newFolder)){
					return array('corrupted'=>'folder already exists');
				}else{
					@mkdir($ref['fullpath'].'/'.$newFolder);
				}
				$movieFolder = $ref['fullpath'].'/'.$newFolder;
			break;
			case 'inFolder':
				if(file_exists(dirname($ref['fullpath']).'/'.$newFolder)){
					return array('corrupted'=>'folder already exists');
				}
				if(!file_exists($ref['fullpath'].'/'.$newFolder)){
					@rename($ref['fullpath'], dirname($ref['fullpath']).'/'.$newFolder);
					$ref['fullpath'] = dirname($ref['fullpath']);
					$movieFolder = $ref['fullpath'].'/'.$newFolder;
					$tmpDet = CW_Files::pathinfo_utf(base64_decode($ref['pathfilename64']));
					$originalFileName	= $movieFolder.'/'.$tmpDet['basename'];
				}
			break;
			default:
				return array('corrupted'=>$ref['inFolder'].'-'.__FILE__.' - '.__LINE__);
			break;
		}

		$newFullFilename = $movieFolder . '/'.$newFilename.'.'.strtolower($ref['ext']);

		if(file_exists($newFullFilename)){
			return array('corrupted'=>'renamed file already exists');
		}else{
			rename($originalFileName,$newFullFilename);
		}

		if (!file_exists($movieFolder		. '/info.txt'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/info.txt'	,json_encode($fullRecord));
		}

		if (!file_exists($movieFolder		. '/movie.nfo'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/movie.nfo'	, $scraperObject->convertToXbmcMovieNfo($compatRecord) );
		}

		if (!file_exists($movieFolder		. '/movie.tbn'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/movie.tbn'	, $this->QDNet->getCacheURL($compatRecord['poster'], 'imgs', 60*24*365*20,true));
		}

		if (!file_exists($movieFolder		. '/folder.jpg'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/folder.jpg'	, $this->QDNet->getCacheURL($compatRecord['poster'], 'imgs', 60*24*365*20,true));
		}

		if (!file_exists($movieFolder		. '/fanart.jpg'	) or $forceFileWrite) {
			file_put_contents($movieFolder	. '/fanart.jpg'	, $this->QDNet->getCacheURL($compatRecord['backdrop'], 'imgs', 60*24*365*20,true));
		}

		$fileDetail = CW_Files::pathinfo_utf($newFullFilename);
		$newRefRecord =  $this->pri_addFileToGetMoviesFiles(array(
			'rootPath'		=> $ref['rootPath'],
			'fileDetail'	=> $fileDetail,
			'inFolder'		=> 'inFolder'
		));

		//// rollback
		//@rename($newFullFilename,$originalFileName);

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




		//db($_REQUEST);
		$id = 0 + $_REQUEST['i'];
		$path = base64_decode($_REQUEST['p']);
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

	function oldpri_getMoviesFiles($path, $only2Rename) {
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
}
?>