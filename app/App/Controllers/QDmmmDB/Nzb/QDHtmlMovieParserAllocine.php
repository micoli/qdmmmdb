<?php
	class QDHtmlMovieParserAllocine extends QDHtmlMovieParser{
		function parse($o){
			//print $o['url'];
			$htmlStr = $this->QDNet->getCacheURL($o['url'], 'rssDetail', 60*60*24*365, true);
			$this->result=array();
			$html = new simple_html_dom();
			$html->load($htmlStr);
			$colContent = $html->find('div.colcontent',0);
			$this->extractFromTag('id' ,'span[parametername=cmovie]' ,false,$html,'parametervalue');
			$type	= 'movie';
			$subTag	= "/film/tous/";
			if (!$this->result['id']){
				$this->extractFromTag('id' ,'span[parametername=cseries]' ,false,$html,'parametervalue');
				$type	= 'serie';
				$subTag	= "/series/toutes/";
			}
			$this->result['type'] = $type;
			$this->extractFromTag('title'			,'h1[property="v:name"]'						,false	,$html);
			$this->extractFromTag('dateOut'			,'a.underline[href*=agenda]'					,false	,$colContent);
			$this->extractFromTag('director'		,'a.underline[rel="v:directedBy"]'				,false	,$colContent);
			$this->extractFromTag('genre'			,'a.underline[href^="'.$subTag.'genre-"]'		,true	,$colContent);
			$this->extractFromTag('country'			,'a.underline[href^="'.$subTag.'pays-"]'		,true	,$colContent);
			$this->extractFromTag('year'			,'a.underline[href^="'.$subTag.'decennie"]'		,true	,$colContent);
			$this->extractFromTag('actor'			,'a.underline[href^="/personne/fichepersonne"]'	,true	,$colContent);
			$this->extractFromTag('society'			,'a.underline[href^="/societe/fichesociete-"]'	,true	,$colContent);
			$this->extractFromTag('summary'			,'span[property="v:summary"]'					,false	,$colContent);
			$this->extractFromTag('ratings_press'	,'img.n30'										,false	,$html->find('.notationbar',0),'title');
			$this->extractFromTag('ratings_users'	,'img.n'.($type=='serie'?35:25)					,false	,$html->find('.notationbar',0),'title');
			$this->result['length']=CW_String::pregExtract(($type=='movie')?'!Durée : (.*?)min!':'!Format : (.*?)mn!',$colContent->plaintext,1);
			if(is_object($html->find('em.imagecontainer',0))){
				$this->result['poster']=$html->find('em.imagecontainer',0)->find('img[src^=http://images.allocine.fr/r]',0)->src;
			}
			$html->clear();
			return $this->result;
			//print '<hr>'.$o['htmlStr'];
		}
	}
?>
