<?
	class QDNet {
		function getUrl($url){
			$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
			set_time_limit(126);
			if($GLOBALS['conf']['qdnet']['usecurl']){
				$ch = curl_init();
				$timeout = 30; // set to zero for no timeout

				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt ($ch, CURLOPT_USERAGENT, $useragent);
				$f = curl_exec($ch);
				curl_close($ch);
				//usleep(500000);
				return $f;
			}else{
				$opts = array(
						'http'=>array(
						'method'=>"GET",
						'header'=>"Accept-language: fr\r\n"
					)
				);
				ini_set("user_agent", $useragent);
				$context = stream_context_create($opts);
				return @file_get_contents($url,FILE_TEXT,$context);
			}
		}
		function getCacheURL($url,$folder,$cacheminutes,$cache=true,$extension='.xml'){
			//print $url;
			$this->lastMimeType = '';
			$this->lastCacheFile = '';
			$cacheDir = $GLOBALS['conf']['qdnet']['cacheroot'];
			preg_match("/^(http:\/\/)?([^\/]+)\/(.*)/i",$url , $matches);
			if (!file_exists($cacheDir.$matches[2])) @mkdir($cacheDir.$matches[2]);
			if (!file_exists($cacheDir.$matches[2].'/'.$folder)) @mkdir($cacheDir.$matches[2].'/'.$folder);
			$localfile = $cacheDir.$matches[2]."/".$folder."/".urlencode($matches[3].$extension);
			$this->lastCacheFile=$localfile;
			$todownload=false;
			clearstatcache  ();
			if (!file_exists($localfile)){ //if cache file doesn't exist
				//touch($localfile); //create it
				$todownload=true;
			}else{
			if (((time()-filemtime($localfile))/60)>$cacheminutes && $cache && $cacheminutes!=-1) {
				$todownload=true;
			}
			}
			if (!$cache){
				$todownload=true;
			}
			if ($todownload){
				$contents = '';
				$retry = 3;
				while ($contents=='' && $retry>0){
					$contents = $this->getUrl($url);
					$retry --;
					if ($contents==''){
						sleep(rand(0,2));
						//print_r($retry);
					}
					//print "downloaded\n";
					//sleep(1);
				}
				if ($retry==0){
						return "-1 erreur sur $url";
				}
				if ($contents!=''){
					file_put_contents($localfile, $contents);
				}else{
					if (file_exists($localfile)){
						$this->lastMimeType = mime_content_type($localfile);
						$contents =  file_get_contents($localfile);
					}else{
						return '';
					}
				}
			}else{
				$this->lastMimeType = mime_content_type($localfile);
				$contents =  file_get_contents($localfile);
			}
			//print $contents;
			return $contents;
		}
	}
?>