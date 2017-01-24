<?php
define ('MAGPIE_CACHE_DIR','/var/www/cache/magpie');
define ('MAGPIE_CACHE_ON',true);
define ('MAGPIE_CACHE_AGE',60*15);
include QDBASE."/3rd_php/magpierss/rss_fetch.inc";
class QDNzbRssReader {
	function __construct(){
		$this->AllRes = array();
	}
	function dumpIte2Sql(){
		$this->QDDb = new QDDB();
		mysql_connect("localhost", $GLOBALS['conf']['qddb']['username'],$GLOBALS['conf']['qddb']['password'] ) or
		die("Impossible de se connecter : " . mysql_error());
		mysql_set_charset ( 'utf8');
		mysql_select_db("rss");
		foreach($this->AllRes as $v){
			if(!$v['title']){
				$v['title']=$v['mask'];
			}
			$v['title'		] = CW_String::replaceMultiSpace($v['title']);
			$v['link'		] = str_replace("http://anonym.to/?","",$v['link']);

			$sql = 'insert ignore into rss.ITE_ITEMS set ';
			$sepa='';
			foreach($v as $k=>$ite){
				$sql .= $sepa ."ITE_".strtoupper($k).'="'.mysql_real_escape_string(( $ite)).'"';
				$sepa = ',';
			}
			print $v['date']."\t".$v['title']."\n";//.$sql."\n";
			//if ($v['ITE_LINK_CACHE_SERIAL']=='' or $v['ITE_LINK_CACHE_SERIAL']=='[]'){
			//print $sql."\n";
			mysql_query($sql);
			print mysql_error();


			$sql = 'select * from rss.ITE_ITEMS where ITE_UNIQUE_ID=?';
			$arr = $this->QDDb->query2array($sql,array($v['unique_id']));
			foreach($arr as $h){
				if ($h['ITE_LINK_CACHE_SERIAL']=='' or $h['ITE_LINK_CACHE_SERIAL']=='[]'){
					$desc = QDHtmlMovieParser::getDescFromLink($h['ITE_LINK']);
					if (count($desc)>0){
						print_r(join(',',array_keys($desc)));
						$sql = 'update rss.ITE_ITEMS set ITE_LINK_CACHE_SERIAL=? where ITE_ID=?;';
						$this->QDDb->execute($sql,array(json_encode($desc),$h['ITE_ID']));
					}
				}
			}

		}
	}
}
?>