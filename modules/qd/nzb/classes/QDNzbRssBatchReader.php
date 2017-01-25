<?php
class QDNzbRssBatchReader {
	function svc_run(){
		$qddb = new QDDB();
		$arr = $qddb->query2Array('select * from rss.GRI_GROUP_ITEMS');
        foreach($arr as $feed){
			if (class_exists($feed['GRI_ENGINE'])){
				$cls = new $feed['GRI_ENGINE'];
				$cls->svc_readFeed($feed['GRI_PARAM']);
				$cls->dumpIte2Sql();
				unset($cls);
			}
		}
    }
}
?>