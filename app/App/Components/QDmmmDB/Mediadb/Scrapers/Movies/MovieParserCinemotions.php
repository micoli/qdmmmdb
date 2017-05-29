<?php
namespace App\Components\QDmmmDB\Mediadb\Scrapers\Movies;

class MovieParserCinemotions extends MovieParser{
	function parse($o){
		try{
			$htmlStr = $this->QDNet->getCacheURL($o['url'], 'rssDetail', 60*60*24*365, true);
			if (strpos($htmlStr, 'deblocage_captcha')!=false){
				unlink($this->QDNet->lastCacheFile);
				return array('badParsing'=>true);
			}
			$this->result=array();
			$htmlStr = preg_replace('@<script[^>]*?>.*?</script>@si','',$htmlStr);
			$html = new simple_html_dom();
			$html->load(utf8_encode($htmlStr));
			$colContent = $html->find('table #style_contenu_page_sspub',0);
			if (!is_object($colContent)){
				return array('badParsing'=>true);
			}
			//print $html;
			$header = $colContent->find('table[width=820]',0);
			//$header->dump();
			$headerStr= CW_String::replaceMultiSpace($header->find('tr',3)->plaintext);
			if(preg_match('!(.*?) - (.*?) - (.*?) - (.*?) - (.*?)!',$headerStr,$m)){
				$this->result['dateOut'	] = $m[1];
				$this->result['country'	] = $m[2];
				$this->result['genre'	] = split('/',$m[3]);
				$this->result['length'	] = $m[5];
			}elseif(preg_match('!(.*?) - (.*?) - (.*?) - (.*?)!',$headerStr,$m)){
				$this->result['dateOut'	] = $m[1];
				$this->result['country'	] = $m[2];
				$this->result['genre'	] = split('/',$m[3]);
				$this->result['length'	] = $m[4];
			}
			$blocLists= $header->parent()->parent()->parent()->parent()->find('a.link10[href^=http://www.cinemotions.com/modules/Artistes]',0)->parent()->parent()->parent()->parent();//find('tr',0)->plaintext;
			//$blocLists->find('tr',1)->dump();
			//$header->dump();
			$this->extractFromTag('title'			,'.home_nom_fiche'		,false	,$colContent,'innertext');
			$this->extractFromTag('director'		,'a'					,true	,$blocLists->find('tr',0));
			$this->extractFromTag('actor'			,'a'					,true	,$blocLists->find('tr',2));
			$this->extractFromTag('summary'			,'.textes'				,false	,$html,'innertext');
			$this->extractFromTag('poster'			,'img[src^=http://www.cinemotions.com/data/films/]'		,false	,$html,'src');
			$html->clear();
			//db($this->result);die();
			return $this->result;
		//print '<hr>'.$o['htmlStr'];
		}catch(Exception $exc){
			print $exc->getTraceAsString();
			return array('badParsing'=>true);
		}

	}
}
