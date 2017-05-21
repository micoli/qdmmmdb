<?php
class QDNzbRssReaderNG4Y extends  QDNzbRssReader{
	function svc_readAllFeeds(){
		$this->svc_readFeed(101);
		$this->svc_readFeed(102);
		$this->dumpIte2Sql();
	}

	function svc_readFeed($catID){
		$rss = fetch_rss('http://www.ng4you.com/rss/rss_cat_'.$catID.'.xml');
		foreach($rss->items as $k=>$item){
			$id = str_replace('http://www.ng4you.com/?page=newsgroups&post_id=','',$item['guid']);
			$this->AllRes[$id]['feed'		] = $catID;
			$this->AllRes[$id]['unique_id'	] = $catID.'_'.$id;
			$this->AllRes[$id]['date_str'	] = $item['pubdate'];
			$this->AllRes[$id]['date'		] = date('Y-m-d H:i:s',$item['date_timestamp']);
			$this->AllRes[$id]['item_id'	] = $id;
			$this->AllRes[$id]['link'		] = CW_String::pregExtract("!\<strong\>\<a href\=\"(.*?)\"\>URL d!",$item['description'],1);
			$this->AllRes[$id]['title'		] = utf8_encode($item['title']);
			//$this->AllRes[$id]['summary'		] = $item['summary'];
			$this->AllRes[$id]['year'		] = CW_String::pregExtract('!(\(([0-9]{4})\))$!',trim($this->AllRes[$id]['title']),2);
			if ($this->AllRes[$id]['year']!=''){
				$this->AllRes[$id]['title'] = str_replace('('.$this->AllRes[$id]['year'].')','',$this->AllRes[$id]['title']);
			}
			$this->AllRes[$id]['lang'		] = CW_String::pregExtract('!<strong>Langue :</strong> (.*?)(<|,)!'			,utf8_encode($item['summary']),1);
			$this->AllRes[$id]['quality'	] = '';//CW_String::pregExtract('!<strong>Langue :</strong> (.*?)[<br>|,)!'		,utf8_encode($item['summary']),1);
			$this->AllRes[$id]['mask'		] = CW_String::pregExtract('!<strong>Nom de fichier :</strong>(.*?)(<|,)!'	,utf8_encode($item['summary']),1);
			$this->AllRes[$id]['group'		] = CW_String::pregExtract('!<strong>Groupe :</strong> (.*?)(<|,)!'			,utf8_encode($item['summary']),1);
			$this->AllRes[$id]['size'		] = CW_String::pregExtract('!<strong>Taille :</strong> (.*?) <!'				,utf8_encode($item['summary']),1);
			$this->AllRes[$id]['poster'		] = '';
			$this->AllRes[$id]['nfo'		] = '';
			$this->AllRes[$id]['picture'	] = CW_String::pregExtract('!<img( style="margin-right: 5px")*( align="left")* src="(.*?)"!'				,utf8_encode($item['summary']),3);
		}
	}
}
?>