<?

class scraperPassionXbmc {
	function __construct(){
		$this->QDNet = new QDNet();
		$this->cacheminutes = 123*59+59;
		$this->cache        = true;
	}

    function cleanupXml($st){
		//<br /><b>Warning</b>:
		$st=str_replace("\n","",$st);
		$st = ereg_replace("<br \/><b>Warning<\/b>:  mysql_fetch_array\(\)\: supplied argument is not a valid MySQL result resource in <b>(.*).php</b> on line <b>([0-9]{1,5})</b><br />",'',$st);
		$st= str_replace('&','&amp;',$st);
		$st = str_replace('&amp;nbsp;',' ',$st);
		return $st;
    }

	function getList($movieName){
		$url = 'http://passion-xbmc.org/scraper/index.php?search='.urlencode($movieName);
		$st = utf8_encode($this->QDNet->getCacheURL($url,'allocine',$this->cacheminutes,$this->cache));
        if (substr($st,0,2)=="-1" )return array();
		$st = '<?xml version="1.0" ?><results>'.$st.'</results>';
        $st=$this->cleanupXml($st);
		$sdom = simplexml_load_string ($st);
		
		$searchresults = object2array($sdom->xpath('entity'));
		return $searchresults;
		
	}

    function getDetail($movieName,$id,$output='array'){
		$url = 'http://passion-xbmc.org/scraper/index.php?id='.$id;
		$st = utf8_encode($this->QDNet->getCacheURL($url,'allocine',$this->cacheminutes,$this->cache));
		if (substr($st,0,2)=="-1" )return array();
		$st = '<?xml version="1.0" ?>'.$st;
		$st=$this->cleanupXml($st);
		$sdom = simplexml_load_string ($st);
		$f = object2array($sdom->xpath('/details'));
		$f = $f[0];
		if (array_key_exists('thumbs',$f) && array_key_exists('thumb',$f['thumbs'])){
			$f['poster'] = $f['thumbs']['thumb'];
		}
		if ($output=='array') return $f;
		if ($output=='xml') return $st;
    }

	function getListBatch($movieName){
		$url = 'http://passion-xbmc.org/scraper/index.php?search='.htmlentities($movieName);
		$st = utf8_encode($this->QDNet->getCacheURL($url,'allocine',$this->cacheminutes,$this->cache));
		$st = '<?xml version="1.0" ?>'.$st;
        if (substr($st,0,2)=="-1" )return array();
		//print $st;
		$st=$this->cleanupXml($st);
        $sdom = simplexml_load_string ($st);
		
		$searchresults = object2array($sdom->xpath('entity'));
		foreach($searchresults as $searchresult){
		    //db($searchresult['id']+0);
			$this->getDetailAllocine('',$searchresult['id']+0);	
		}
		
	}
}
?>