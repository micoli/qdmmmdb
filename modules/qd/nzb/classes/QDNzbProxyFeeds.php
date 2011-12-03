<?
include_once QD_PATH_3RD_PHP."simple_html_dom.php";

class QDNzbProxyFeeds{
	var $QDNet;
	var $QDDb;

	function __construct() {
		$this->QDNet = new QDNet();
		$this->QDDb = new QDDB();

	}

	function svc_testAllocine(){
		print "<pre>";
		//$t = new htmlParserAllocine();
		//print_r($t->parse(array('url'=>'http://www.allocine.fr/film/fichefilm_gen_cfilm=119031.html')));
		//print_r($t->parse(array('url'=>'http://www.allocine.fr/series/ficheserie_gen_cserie=8023.html')));
		$t = new QDhtmlParserCinemotions();
		//print_r($t->parse(array('url'=>'http://www.cinemotions.com/modules/Films/fiche/21273/Medium.html')));
		print_r($t->parse(array('url'=>'http://www.cinemotions.com/modules/Films/fiche/81701/Nowhere-Boy.html')));
	}

	function svc_download() {
		switch($_REQUEST['s']) {
			case 'newzleech':
				$res = $this->svc_download_newzleech();
			break;
			case 'binsearch':
				$res = $this->svc_download_binsearch();
			break;
		}
		if (array_key_exists_assign_default('success',$res,false)=='ok'){
			$this->svc_setTreated();
		}
		return $res;
	}

	function svc_search() {
		switch($_REQUEST['s']) {
			case 'newzleech':
				return $this->svc_search_newzleech();
			break;
			case 'binsearch':
				return $this->svc_search_binsearch();
			break;
		}
	}

	function svc_feedCacheRSS(){
		//@apache_setenv('no-gzip', 1);
		//@ini_set('zlib.output_compression', 0);
		//@ini_set('implicit_flush', 1);
		for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
		ob_implicit_flush(1);
		$arr = $this->QDDb->query2Array('select ITE_ID,ITE_LINK from rss.ITE_ITEMS where ITE_LINK_CACHE=0');
		foreach($arr as $item){
			print $item['ITE_LINK']."<br>\n";
			if ($item['ITE_LINK']){
				$this->QDNet->getCacheURL($item['ITE_LINK'], 'rssDetail', 60*60*24*365, $this->cache);
				$this->QDDB->execute('update rss.ITE_ITEMS set ITE_LINK_CACHE=1 where ITE_ID='.$item['ITE_ID']);
				@ob_flush();
				set_time_limit(30);
			}
		}
	}

	function svc_search_binsearch() {
		$url = 'http://www.binsearch.net/?q='.urlencode($_REQUEST['q']).'&max=250&adv_age=1000&server=';
		$st = $this->QDNet->getURL($url);
		$html = new simple_html_dom();
		$html->load($st);
		$checkboxes = $html->find('input[type=checkbox]');
		$res=array();
		foreach ($checkboxes as $checkbox) {
			$tr = $checkbox->parent()->parent();
			$size='';
			if (preg_match('!size: (.*?),!',$tr->children(2)->innertext,$m)){
				$size = $m[1];
			}
			$res[] = array (
				"id"		=>$checkbox->getAttribute('name'),
				"title"		=>($tr->children(2)->innertext),
				"size"		=>($size),
				"posterId"	=>strip_tags($tr->children(3)->innertext),
				"group"		=>strip_tags($tr->children(4)->innertext),
				"age"		=>strip_tags($tr->children(5)->innertext)
			);
		}
		if (false) {
			print "<pre>";
			print $url."<br>";
			print_r($res);
			die();
		}
		return array('posts'=>$res);
	}

	function svc_download_binsearch() {
		$url = 'http://www.binsearch.net/?action=nzb&q='.urlencode($_REQUEST['q']).'&max=99&adv_age=120&server=';
		$ids = split('!', $_REQUEST['ids']);
		foreach ($ids as $v) {
				$url = $url.'&'.$v.'=on';
		}
		$filename=escapeshellcmd($this->normalizeNZBFilename($_REQUEST['q'])).' '.date('YmdHis').'.nzb';
		if (file_put_contents("/var/nzb/".$filename, $this->QDNet->getURL($url))) {
			return array( 'success'=>'ok','filename'=>$filename);
		} else {
			return array('failure'=>'bad');
		}
	}

	function svc_download_newzleech() {
		
	}

    function normalizeNZBFilename($f){
        //$f = strtr(
        //        '#$&;`|*?~<>^{}$,',
        //        '___________________');
        //  \x0A et \xFF.' ' et "
        return $f;
    }

	function svc_search_newzleech() {
		return  array ('url'=>$url, 'posts'=>$res);
	}

	function svc_distinctfeed() {
		return array ('res'=>$this->QDDb->query2Array('select * from GRI_GROUP_ITEMS;'));
	}

	function svc_setStarred() {
		$sql = 'update rss.ITE_ITEMS set ITE_STARRED=? where ITE_ID=?';
		$arr = $this->QDDb->execute($sql,array($_REQUEST['ITE_STARRED'],$_REQUEST['ITE_ID']));
		return array('ok'=>1,'starred'=>$_REQUEST['ITE_STARRED']);
	}

	function svc_setRead() {
		$sql = 'update rss.ITE_ITEMS set ITE_READ=? where ITE_ID=?;';
		$arr = $this->QDDb->execute($sql,array($_REQUEST['ITE_READ'],$_REQUEST['ITE_ID']));
		return array('ok'=>1,'read'=>$_REQUEST['ITE_READ']);
	}

	function svc_setTreated() {
		$sql = 'update rss.ITE_ITEMS set ITE_TREATED=1 where ITE_ID=?;';
		$arr = $this->QDDb->execute($sql,array($_REQUEST['rid']));
		return array('ok'=>1,'treated'=>1);
	}

	function svc_dbfeed() {
		$start = array_key_exists_assign_default('start',$_REQUEST,0);
		$limit = array_key_exists_assign_default('limit',$_REQUEST,20);
		$mode =  array_key_exists_assign_default('mode',$_REQUEST,'feed');
		switch($mode){
			case 'feed':
				$sql = "select *
						from rss.ITE_ITEMS
						where ITE_FEED=".$_REQUEST['is']."
						and ITE_TITLE not like 'A Tous ceux Qui%'
						order by ITE_DATE desc,ITE_ID
						desc limit $start,$limit";
			break;
			case 'id':
				$sql = "select *
						from rss.ITE_ITEMS
						where ITE_ID=".$_REQUEST['id'];
			break;
			case 'fullsearch':
                $arrQ = split(' ',$_REQUEST['q']);
                $likes = " (ITE_TITLE not like 'A Tous ceux Qui %') ";
                foreach($arrQ as $qu){
                    $likes .= " and ( ITE_TITLE like '%".$qu."%') ";
                }
				$sql = "select *
						from rss.ITE_ITEMS
						where $likes
						order by ITE_DATE desc,ITE_ID
                        desc limit $start,$limit";
			break;
		}
		$arr = $this->QDDb->query2array($sql);
		//die($sql);

		foreach($arr as $k=>&$v){
			$v['ITE_DATE']=date('Y-m-d',strtotime($v['ITE_DATE']));
			$v['ITE_TITLE'] = str_replace('('.$v['ITE_YEAR'].')','',utf8_encode($v['ITE_TITLE']));
			$desc=array();
			if ($v['ITE_LINK_CACHE_SERIAL']=='' or $v['ITE_LINK_CACHE_SERIAL']=='[]'){
				$desc = QDHtmlMovieParser::getDescFromLink($v['ITE_LINK']);
				if (count($desc)>0){
					$sql = 'update rss.ITE_ITEMS set ITE_LINK_CACHE_SERIAL=? where ITE_ID=?;';
					$this->QDDb->execute($sql,array(json_encode($desc),$v['ITE_ID']));
				}
			}else{
				$desc = json_decode($v['ITE_LINK_CACHE_SERIAL'],true);
			}
			$desc = array_merge(QDHtmlMovieParser::initBasicResult(),$desc);
			$v['ITE_LINK_CACHE_SERIAL'] = $desc;

			foreach($desc as $desck=>$descv){
				$v['DESC_'.strtoupper($desck)]= htmlentities(utf8_decode(is_array($descv)?join(',',$descv):$descv));
			}
		}
		return array ('feeds'=>$arr,'count'=>10000);
	}

	function svc_pubGetCacheSerial(){
		$sql = "select *
				from rss.ITE_ITEMS
				where ITE_ID=?";
		$arr = $this->QDDb->query2array($sql,array($_REQUEST['i']));
		if (count($arr)==1){
			$t = new htmlParserAllocine();
			$desc = array();
			try{
				$desc = $t->parse(array('url'=>$arr[0]['ITE_LINK']));
			}catch (Exception $e){

			}
			foreach($desc as $desck=>$descv){
				$arr[0]['DESC_'.strtoupper($desck)]= htmlentities(utf8_decode(is_array($descv)?join(',',$descv):$descv));
			}
			$sql = 'update rss.ITE_ITEMS set ITE_LINK_CACHE_SERIAL=? where ITE_ID=?;';
			$this->QDDb->execute($sql,array(json_encode($desc),$_REQUEST['i']));
			$arr[0]['ITE_LINK_CACHE_SERIAL']='--';
			return $arr[0];
		}
	}
}
?>