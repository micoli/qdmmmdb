<?php
namespace App\Controllers\QDmmmDB\Nzb;

class QDHtmlParserBinnews extends MovieParser{
	var $path = "/var/www/scripts/binnews.ok";
	function dumpIte2Sql(){
		mysql_connect("localhost", "root", "xxx") or
		die("Impossible de se connecter : " . mysql_error());
		mysql_set_charset ( 'utf8');
		mysql_select_db("rss");
		foreach($this->AllRes as $v){
			$sql = 'insert ignore into rss.ITE_ITEMS set ';
			$sepa='';
			foreach($v as $k=>$ite){
				$sql .= $sepa ."ITE_".strtoupper($k).'="'.mysql_real_escape_string(( $ite)).'"';
				$sepa = ',';
			}
			print $v['date']."\t".$v['title']."\n";//.$sql."\n";
			//print $sql."\n";
			mysql_query($sql);
			print mysql_error();
		}
	}
	function svc_runHistory(){
		$arr = glob($this->path.'/*');
		$arnew=array();
		foreach($arr as $v){
			preg_match ('!cat_id\=(.*)\&page\=(.*)!',$v,$m);
			$arnews[$m[1]][$m[2]]=$v;
		}
		ksort($arnews[6]);
		ksort($arnews[7]);
		$this->AllRes = array();
		foreach($arnews as $k=>$v){
			foreach($v as $kk=>$file){
				//if($kk>587)
				$this->parse(file_get_contents($file),$k);
			}
		}
		$this->dumpIte2Sql();
	}

	function svc_run(){
		$QDNet = new QDNet();
		$this->AllRes = array();
		foreach(array(
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=6&page=1&pageby=40','idx'=>6),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=6&page=2&pageby=40','idx'=>6),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=6&page=3&pageby=40','idx'=>6),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=6&page=4&pageby=40','idx'=>6),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=7&page=1&pageby=40','idx'=>7),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=7&page=2&pageby=40','idx'=>7),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=7&page=3&pageby=40','idx'=>7),
			array('url'=>'http://binnews.in/_bin/liste.php?country=fr&cat_id=7&page=4&pageby=40','idx'=>7),
			) as $cn){
			$htmlStr =  $QDNet->getCacheURL($cn['url'], 'binnews', 10,true);
			$this->parse($htmlStr,$cn['idx']);
		}
		$this->dumpIte2Sql();
	}

	function dateFR2EN($date){
		$arrDay=array(
			'Monday'	=>'Lundi',
			'Tuesday'	=>'Mardi',
			'Wednesday'	=>'Mercredi',
			'Thursday'	=>'Jeudi',
			'Friday'	=>'Vendredi',
			'Saturday'	=>'Samedi',
			'Sunday'	=>'Dimanche'
		);
		$arrMonth=array(
			'January'	=>'Janvier',
			'February'	=>'F�vrier',
			'March'		=>'Mars',
			'April'		=>'Avril',
			'May'		=>'Mai',
			'June'		=>'Juin',
			'July'		=>'Juillet',
			'August'	=>'Ao�t',
			'September'	=>'Septembre',
			'October'	=>'Octobre',
			'November'	=>'Novembre',
			'December'	=>'D�cembre',
		);
		$date = str_replace(array_values($arrDay),array_keys($arrDay),$date);
		$date = str_replace(array_values($arrMonth),array_keys($arrMonth),$date);
		$date = str_replace("1er ","1 ",$date);
		//print $date."\t";
		return $date;
	}

	function parse($htmlStr,$feed){
		$html = new simple_html_dom();
		$html->load($htmlStr);
		$ob = array_pop( $html->find('[id=tabliste]'));
		foreach($ob->find('tr') as $ligne){
			$finddatepost = $ligne->find('[class=datepost]');
			if (count($finddatepost)==1){
				$dateStr= trim(utf8_encode(html_entity_decode(CW_String::pregExtract('!^(.*?) \|!',$finddatepost[0]->innertext,1))));
				$date = strtotime($this->dateFR2EN($dateStr));
				//print date('Y-m-d',$date)."\n";
			}else{
				if(in_array($ligne->class,array('ligneclaire','lignefoncee'))){
					//$ligne->dump();
					$tds = $ligne->find('td');
					$hrefid1 = $tds[10]->find('a[class=thickbox]');
					if (count($hrefid1)==1){
						$hrefid2 = $tds[3]->find('a[target=_blank]');
						$id = CW_String::pregExtract('!\&i\=(.*?)\&TB!',$hrefid1[0]->href,1);
						$this->AllRes[$id]['feed'		]= $feed;
						$this->AllRes[$id]['date_str'	]= $dateStr;
						$this->AllRes[$id]['date'		]= date('Y-m-d H:i:s',$date);
						$this->AllRes[$id]['item_id'	] = $id;
						$this->AllRes[$id]['unique_id'	] = $feed.'_'.$id;
						//print $id." ";
						$this->AllRes[$id]['title'		] = html_entity_decode((count($hrefid2)==1)?trim($hrefid2[0]->innertext):$tds[3]->innertext,ENT_COMPAT,'UTF-8');
						$this->AllRes[$id]['year'		] = CW_String::pregExtract('!\((.*)\)$!',$this->AllRes[$id]['title'],1);
						$this->AllRes[$id]['link'		] = (count($hrefid2)==1)?$hrefid2[0]->href:'';
						$this->AllRes[$id]['link'		] = str_replace("http://anonym.to/?","",$this->AllRes[$id]['link']);
						$this->AllRes[$id]['quality'	] = (count($hrefid2)==1)?CW_String::pregExtract('!\[(.*)\]!',str_replace($hrefid2[0]->outertext,'',$tds[3]->innertext),1):'';
						$this->AllRes[$id]['mask'		] = $tds[6]->innertext;
						$this->AllRes[$id]['group'		] = CW_String::pregExtract('!ng_id=(.*?)"!',$tds[5]->innertext,1);
						$this->AllRes[$id]['size'		] = str_replace(' + <img src="../_images/sample.png" alt="Sample" />','',$tds[7]->innertext);
						$this->AllRes[$id]['poster'		] = $tds[11]->innertext;
						$this->AllRes[$id]['nfo'		] = CW_String::pregExtract('!/nfo/(.*?)\.!',$tds[9]->innertext,1);
						if(!$this->AllRes[$id]['title']){
							$this->AllRes[$id]['title']=$this->AllRes[$id]['mask'];
						}
						/*foreach($this->AllRes[$id] as $kk=>&$vv){
							if($kk!='mask'){
								$vv=$this->replaceMultiSpace($vv);
							}
						}*/
						$this->AllRes[$id]['title'] = CW_String::replaceMultiSpace($this->AllRes[$id]['title']);
						//print $this->AllRes[$id]['nfo']."\n";
					}
				}
			}
		}
	}
}