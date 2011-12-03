<?

///// mathias 06 46 00 89 73
class XbmcScraper {
	function __construct($scraperName){
		$this->QDNet = new QDNet();
		$this->cacheminutes = 123*59+59;
		$this->cache        = true;
		$this->scraperName = $scraperName;
		$this->scraperXml = new DomDocument;
		$this->scraperXml->loadXML(file_get_contents(dirname(__FILE__).'/scraper/'.$this->scraperName.'.xml'));
		$this->scraperXPATH = new DOMXPath($this->scraperXml);

	}
	function extractXQuery($xpathQ,$key=null){
		$val='';
		if (!$this->scraperXPATH){
			return '';

		}
		$arts = $this->scraperXPATH->query($xpathQ);
		foreach ($arts as $k=>$art){
			if ($key){
				$val= utf8_decode($art->getAttribute($key));
			}else{
				$val= utf8_decode($art->nodeValue);
			}
			break;
		}
		return $val;
	}

	function dbgXML($node){
		$doc = new DOMDocument('1.0');
		$doc->formatOutput = true;
		$root = $doc->createElement('book');
		$root = $doc->appendChild($root);
		$node = $doc->importNode($node, true);
		$doc->documentElement->appendChild($node);
		print $doc->saveXML();
	}

	function clearDest($arr){
		/*foreach($arr as $k=>$v){
		 unset($arr[$k]);
		 }*/
		unset($arr);
	}
	
	function replaceTagInDest($str){
		return $str;
	}

	function replaceBuffers(&$str,$buffers){
		for($h=20;$h>=0;$h--){
			//db($input);
			if (ereg('$$'.$h,$str) && !array_key_exists($h,$buffers)){
				$hh='';
			}else if (array_key_exists($h,$buffers)){
				$hh=$buffers[$h];
			}else{
				$hh='';
			}
			$str = str_replace('$$'.$h,$hh,$str);
		}
		/*foreach (array_reverse($buffers,true) as $i=>$ii){
			$str = str_replace('\\'.$i,$ii,$str);
		}*/
	}

	function useRegex($node,&$arrDest){
		$xp = new DOMXPath($this->scraperXml);

		$arNodes =array();
		foreach ($xp->query('RegExp',$node) as $art){
			//$arNodes[]=$art;
			$this->useRegex($art,$arrDest);
		}
		foreach (array_reverse($arNodes,true) as $k=>$art){
			//$this->useRegex($art);
		}
		$expressionNode   = $xp->query('expression',$node)->item(0);
		$expression       = $expressionNode->nodeValue.'';
		$expressionRepeat = $expressionNode->getAttribute('repeat')=='yes';
		$expressionTrim   = $expressionNode->getAttribute('trim')==1;
		$expressionClear  = $expressionNode->getAttribute('clear')=='yes';
		$arrNoClean       = ($expressionNode->getAttribute('noclean')=='')?array():explode(',',$expressionNode->getAttribute('noclean'));


		$input            = $node->getAttribute('input');
		//db(array_keys(array_reverse($arrDest,true)));

		$this->replaceBuffers($input,$arrDest);
		//$input          = $arrDest[str_replace('$$','',$node->getAttribute('input'))];
		$outputMask       = $node->getAttribute('output');
		//if (ereg("function=",$output)) {return;}
		$rgx = '/'.str_replace('/','\/',$expression).'/';
		if ($expressionRepeat){
			$rgx.='m';
		}
		$matches=array();
		if ($expression==''){
			$matches=array();
			$matches[0][1]=$input;
			$regexResult=true;
		}else{
			$regexResult = preg_match_all($rgx,$input,$matches,PREG_SET_ORDER);
			//print_r($matches);
		}


		foreach ($matches as $k=>$row){
			$output = $outputMask;
			foreach (array_reverse($row,true) as $valKey=>$valValue){
				$valValue = utf8_decode($valValue);
				if (!in_array($valKey,$arrNoClean)){
					$valValue=strip_tags($valValue);
				}

				if (ereg('url(.*)function\=',$output)){
					$output           = str_replace('function="',' function="',$output);
					//$url    = str_replace('\\'.$valKey,$valValue,$output);
					if (!ereg('GetFanart',$output)){ //!ereg('GetFanart',$output) ereg('cache',$output)){
						$output="";
					}else{
						$functionXml = new DomDocument();
						$functionXml->formatOutput = true;
						$functionXml->loadXML('<?xml version="1.0" ?><scraper>'.($output).'</scraper>');
						//print $functionXml->saveXML();

						$funcNode = $functionXml->getElementsByTagName('url')->item(0);
						$url = $funcNode->nodeValue;
						$funcInScrapperNode= $this->scraperXPATH->query('/scraper/'.$funcNode->getAttribute('function').'/RegExp')->item(0);
						$clearBuffers = ($funcInScrapperNode->getAttribute('clearbuffers')!="no");
						$keyDst =  microtime();
						$funcArrDest=array();
						if (!$clearBuffers){
							foreach($arrDest as $l=>$ll){
								$funcArrDest[$l]=$ll;
							}
						}
							
						$funcArrDest[1] = $this->QDNet->getCacheURL($url,'scraper_'.$this->scraperName,$this->cacheminutes,$this->cache);
						$this->useRegex($funcInScrapperNode,$funcArrDest);
						//print $this->_getDomPath($funcInScrapperNode).htmlspecialchars_decode($url).'<br>';

						$output = '<'.$funcNode->getAttribute('function').'>'.$funcArrDest[$funcInScrapperNode->getAttribute('dest')].'</'.$funcNode->getAttribute('function').'>';

					}
					//$output = str_replace('\\'.$valKey,$valValue,$output);
				}else{
		            //$output = str_replace('\\'.$valKey,$valValue,$output);
				}
				$output = str_replace('\\'.$valKey,$valValue,$output);
			}
			//$this->replaceBuffers($output,$arrDest);
			if ($expressionTrim){
				$output = trim($output);
			}
			$destIndex = str_replace('+','',$node->getAttribute('dest'));
			if (!$regexResult && $expressionClear){
				$arrDest[$destIndex] = '';
			}else{
				if (substr($node->getAttribute('dest'),-1,1)=='+'){
					$arrDest[$destIndex] =$arrDest[$destIndex]. $output;
				}elseif (substr($node->getAttribute('dest'),1,1)=='+'){
					$arrDest[$destIndex] = $arrDest[$destIndex];
				}else{
					//print "$destIndex $output $valKey /////";
					$arrDest[$destIndex] = $output;
				}
			}
		}
		return $node->getAttribute('dest');
	}
	function cleanDest(){
		foreach($this->dest as $k=>$v){
			$this->dest[$k]=null;
		}
	}
	function getXmlFunc($funcName){
		return $this->scraperXPATH->query('/scraper/'.$funcName.'/RegExp')->item(0);
	}
		
	function getList2($movieName){
		header('Content-type: text/html; charset=ISO-8859-1');
		$dest=array();
		 $dest[1]="Excalibur";
		 $url = $dest[$this->useRegex($this->getXmlFunc('CreateSearchUrl'),$dest)];
		 db($url);

		$url="http://www.allocine.fr/recherche/default.html?motcle=Excalibur&rub=1&page=1";
		$dest=array();
		$dest[1] = $this->QDNet->getCacheURL($url,'scraper_'.$this->scraperName,$this->cacheminutes,$this->cache);
		$searchResults = $dest[$this->useRegex($this->getXmlFunc('GetSearchResults'),$dest)];
		db($searchResults);

		//$this->dumpxml('<?xml version="1.0" encoding="UTF-8"?'.'>'.$r);
		//print $this->dest[2];

		//die("eee".$res);
		$url="http://www.allocine.fr/film/fichefilm_gen_cfilm=39179.html";
		$dest=array();
		$dest[1] = $this->QDNet->getCacheURL($url,'scraper_'.$this->scraperName,$this->cacheminutes,$this->cache);
		$details = $dest[$this->useRegex($this->getXmlFunc('GetDetails'),$dest)];
		print $details;
		$this->dumpxml($details);
	}

	private function _getDomPath(DomNode $node){
		$r = "";
		while ($node) {
			$r=$node->nodeName.'/'.$r;
			$node = $node->parentNode;
		}
		return  $r;
	}
	
	function dumpxml($t){
		//$t= str_replace('</',"\n</",$t);
		//print str_replace("\n","<br>",htmlspecialchars($t));
		//return;
		$doc = new DOMDocument();
		$doc->formatOutput =true;
		$doc->loadXML('<?xml version="1.0" encoding="UTF-8" ?>'.$t);
		print '<pre>';
		print htmlspecialchars($doc->saveXML());
		print '</pre>';
	}

	function getListAllocine($movieName){
		$url = 'http://passion-xbmc.org/scraper/index.php?search='.urlencode($movieName);
		$st = utf8_encode($this->QDNet->getCacheURL($url,'scraper_'.$this->scraperName,$this->cacheminutes,$this->cache));
		$st = '<?xml version="1.0" ?><results>'.str_replace('</',"\n</",$st).'</results>';
		//print $st;
		$st= str_replace('&','&amp;',$st);
		$st = str_replace('&amp;nbsp;',' ',$st);
		$sdom = simplexml_load_string ($st);
		
		$searchresults = object2array($sdom->xpath('entity'));
		return $searchresults;
		
	}
	function getListAllocineBatch($movieName){
		$url = 'http://passion-xbmc.org/scraper/index.php?search='.htmlentities($movieName);
		$st = utf8_encode($this->QDNet->getCacheURL($url,'scraper_'.$this->scraperName,$this->cacheminutes,$this->cache));
		$st = '<?xml version="1.0" ?>'.str_replace('</',"\n</",$st).'';
		//print $st;
		
		$st= str_replace('&','&amp;',$st);
		$st = str_replace('&amp;nbsp;',' ',$st);
		$sdom = simplexml_load_string ($st);
		
		$searchresults = object2array($sdom->xpath('entity'));
		foreach($searchresults as $searchresult){
		    //db($searchresult['id']+0);
			$this->getDetailAllocine('',$searchresult['id']+0);	
		}
		
	}
	function getDetailAllocine($movieName,$id,$output='array'){
		$url = 'http://passion-xbmc.org/scraper/index.php?id='.$id;		
		$st = utf8_encode($this->QDNet->getCacheURL($url,'scraper_'.$this->scraperName,$this->cacheminutes,$this->cache));
        //print $st;
		$st = '<?xml version="1.0" ?>'.str_replace('</',"\n</",$st).'';
		$st= str_replace('&','&amp;',$st);
		$st = str_replace('&amp;nbsp;',' ',$st);
		$sdom = simplexml_load_string ($st);
		$f = object2array($sdom->xpath('/details'));
		$f = $f[0];
        if (array_key_exists('thumbs',$f) && array_key_exists('thumb',$f['thumbs'])){
            $f['poster'] = $f['thumbs']['thumb'];
        }
		if ($output=='array') return $f;     
        if ($output=='xml') return $st;     
	}
}
?>