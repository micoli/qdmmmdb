<?php
namespace App\Controllers\QDmmmDB\Nzb;

class QDNzbRssReaderBinnews extends  QDNzbRssReader{
	function svc_readAllFeeds(){
		$this->svc_readFeed(6);
		$this->svc_readFeed(7);
		$this->dumpIte2Sql();
	}

	function svc_readFeed($catID){
		$rss = fetch_rss('http://www.binnews.in/rss/rss.php?cat_id='.$catID);
		foreach($rss->items as $k=>$item){
			if (preg_match('!333276-les-posts-avec-mot-de-pass!',$item['link'])) continue;
			$id = str_replace('bzf_','',$item['guid']);
			$this->AllRes[$id]['feed'		] = $catID;
			$this->AllRes[$id]['unique_id'	] = $catID.'_'.$id;
			$this->AllRes[$id]['date_str'	] = $item['pubdate'];
			$this->AllRes[$id]['date'		] = date('Y-m-d H:i:s',$item['date_timestamp']);
			$this->AllRes[$id]['item_id'	] = $id;
			$this->AllRes[$id]['link'		] = $item['link'];
			$this->AllRes[$id]['title'		] = utf8_encode($item['title']);
			//$this->AllRes[$id]['summary'		] = $item['summary'];
			$this->AllRes[$id]['year'		] = CW_String::pregExtract('!\((.*)\)$!',$this->AllRes[$id]['title'],1);
			if ($this->AllRes[$id]['year']!=''){
				$this->AllRes[$id]['title'] = str_replace('('.$this->AllRes[$id]['year'].')','',$this->AllRes[$id]['title']);
			}
			if(preg_match('!Langue \: (.*?) \<br\> Newsgroup \: (.*?) \- (.*?) \- (.*?) \<br\> Nom du fichier \: (.*?) \<br\> Taille \: (.*?) \<br\> (\<a href=\"http\:\/\/www\.binnews\.in\/nfo\/(.*?)\.html\"> Fichier Nfo \<\/a\>)*!',utf8_encode($item['summary']),$ma)){
				$this->AllRes[$id]['quality'	] = $ma['4'].' '.$ma['3'];
				$this->AllRes[$id]['mask'		] = $ma['5'];
				$this->AllRes[$id]['group'		] = $ma['2'];
				$this->AllRes[$id]['size'		] = CW_String::replaceMultiSpace($ma['6']);
				$this->AllRes[$id]['poster'		] = '';
				$this->AllRes[$id]['nfo'		] = array_key_exists_assign_default(8,$ma,'');
			}
		}
	}
}
