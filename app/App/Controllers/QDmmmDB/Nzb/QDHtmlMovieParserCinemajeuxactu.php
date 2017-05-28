<?php
namespace App\Controllers\QDmmmDB\Nzb;

class MovieParserCinemajeuxactu extends MovieParser{
	function parse($o){
		//print $o['url'];
		$htmlStr = $this->QDNet->getCacheURL($o['url'], 'rssDetail', 60*60*24*365, true);
		$this->result=array();
		$html = new simple_html_dom();
		$html->load($htmlStr);
		$content = $html->find('#content',0);
		$this->result['type'] = 'movie';
		$this->extractFromTag('title'			,'h1'											,false	,$content);
		$this->result['dateOut'			] = CW_String::pregExtract('!class="title cdefault">Ann&eacute;e</span> : (.*?)<br!m'		,$content->innertext,1);
		$this->result['director'		] = array();//split(',',CW_String::pregExtract('!class="title cdefault">De</span> : (.*?)<br!m'		,$content->innertext,1));
		$this->result['actor'			] = array();//split(',',CW_String::pregExtract('!class="title cdefault">Acteurs</span> : (.*?)<br!m'	,$content->innertext,1));
		$this->result['country'			] = split(',',CW_String::pregExtract('!class="title cdefault">Pays de production</span>(.*?)<br!m'	,$content->innertext,1));
		$this->result['genre'			] = CW_String::pregExtract('!class="title cdefault">Genre</span> : (.*?)<br!m'				,$content->innertext,1);
		$this->result['originalTitle'	] = CW_String::pregExtract('!class="title cdefault">Titre en VO</span> : (.*?)<br!m'		,$content->innertext,1);
		$this->result['length'	] = CW_String::pregExtract('!class="title cdefault">Dur&eacute;e</span> : (.*?)<br!m'		,$content->innertext,1);
		$this->extractFromTag('summary'	,'#liste_elt' ,false ,$content,'innertext');
		//print $this->result['summary'	];
		$this->result['summary'	] = strip_tags(CW_String::pregExtract('!Synopsis (.*?)</span>(.*)!m'		,$this->result['summary'],2));
		if(is_object($html->find('img.imfull',0))){
			$this->result['poster']=$html->find('img.imfull',0)->src;
		}
		foreach($content->find('span.cpeople') as $item){
			$typeP='people';
			if (preg_match('!alisateur!',$item->innertext)){
				$typeP='director';
			}
			if (preg_match('!cteur!',$item->innertext)){
				$typeP='actor';
			}
			if(preg_match('!/(.*)!m',$item->parent()->plaintext,$m)){
				$this->result[$typeP][] = trim($m[1]);
			}
		}

		$html->clear();
		//print $htmlStr;
		//db($this->result);
		return $this->result;
	}
}
