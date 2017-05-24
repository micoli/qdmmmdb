<?php
namespace App\Components\QDmmmDB\Mediadb\Scrapers;

use App\Components\QDmmmDB\Misc\Tools;
use App\Components\QDmmmDB\Misc\QDNet;

include_once QD_PATH_3RD_PHP."simple_html_dom.php";
class MovieParser{
	var $XbmcMovieObjTemplate=array();

	function __construct(){
		$this->QDNet = new QDNet();
	}
	function getCapabilities(){
		return array(
			'pictureWithDimension'	=> false,
			'pictureSorted'			=> false
		);
	}
	function getCapabilitie($key){
		$t = $this->getCapabilities();
		return $t[$key];
	}


	function initBasicResult(){
		$arr['id'				]='';
		$arr['engine'			]='';
		$arr['type'				]='';
		$arr['title'			]='';
		$arr['originalTitle'	]='';
		$arr['dateOut'			]='';
		$arr['genre'			]='';
		$arr['genres'			]=array();
		$arr['year'				]='';
		$arr['director'			]=array();
		$arr['country'			]=array();
		$arr['actor'			]=array();
		$arr['society'			]='';
		$arr['summary'			]='';
		$arr['ratings_press'	]='';
		$arr['ratings_users'	]='';
		$arr['votes'			]=0;
		$arr['length'			]=0;
		$arr['poster'			]='';
		$arr['posters'			]=array();
		$arr['fanarts'			]=array();
		$arr['backdrops'		]=array();
		$arr['trailer'			]='';
		$arr['certification'	]='';
		return $arr;
	}

	function utf8_decode_recurs($arr){
		foreach($arr as $k=>&$v){
			if(is_string($v)) $v = utf8_decode($v);
			if(is_array ($v)) $v = $this->utf8_decode_recurs($v);
		}
		return $arr;
	}

	function simpleLoadXbmcMovieNfo($file){
		libxml_use_internal_errors(true);
		$sxe = simplexml_load_file($file);
		if (!$sxe) {
			echo "Erreur lors du chargement du XML\n";
			foreach(libxml_get_errors() as $error) {
				echo "\t", $error->message;
			}
			return null;
		}
		return $sxe;

	}
	function convertToXbmcMovieNfo($res){
		//$res = $this->utf8_decode_recurs($res);
		$doc = new \DomDocument('1.0','utf-8');
		$doc->formatOutput = true;
		$root = $doc->createElement('movie');
		$root = $doc->appendChild($root);

		$root->appendChild($doc->createElement('id'				,$res['id']));
		$root->appendChild($doc->createElement('scrapengine'	,$res['engine']));
		$root->appendChild($doc->createElement('title'			,htmlspecialchars($res['title'])));
		$root->appendChild($doc->createElement('originaltitle'	,htmlspecialchars($res['originalTitle'])));
		$root->appendChild($doc->createElement('rating'			,$res['ratings_users']));
		$root->appendChild($doc->createElement('year'			,$res['year']));
		$root->appendChild($doc->createElement('votes'			,$res['votes']));
		$root->appendChild($doc->createElement('outline'		,htmlspecialchars($res['summary'])));
		$root->appendChild($doc->createElement('plot'			,htmlspecialchars($res['summary'])));
		$root->appendChild($doc->createElement('runtime'		,$res['length']));
		$root->appendChild($doc->createElement('trailer'		,$res['trailer']));
		$root->appendChild($doc->createElement('genre'			,htmlspecialchars($res['genre'])));
		$root->appendChild($doc->createElement('director'		,htmlspecialchars(join(' / ',$res['director']))));
		$root->appendChild($doc->createElement('mpaa'			,htmlspecialchars($res['certification'])));
		$root->appendChild($doc->createElement('actors'));
		foreach($res['actor'] as $v){
			$actor = $doc->createElement('actor');
			$root->appendChild($actor);
			$actor->appendChild($doc->createElement('name',htmlspecialchars($v)));
		}
		if(count($res['posters'])){
			foreach($res['posters'] as $v){
				$root->appendChild($doc->createElement('thumb',$v));
			}
		}
		if(count($res['backdrops'])){
			$fanart = $root->appendChild($doc->createElement('fanart'));
			foreach($res['backdrops'] as $v){
				$fanart->appendChild($doc->createElement('thumb',$v));
			}
		}
		return $doc->saveXML();
	}

	function convertFullRecordToCompatibleRecord($rec){
		if(is_array($rec)){
			if(array_key_exists('posters',$rec)){
				$tmp = array();
				foreach($rec['posters'] as $v){
					$tmp[]=$v['url'];
				}
				$rec['posters']=$tmp;
			}
			if(array_key_exists('backdrops',$rec)){
				$tmp = array();
				foreach($rec['backdrops'] as $v){
					$tmp[]=$v['url'];
				}
				$rec['backdrops']=$tmp;
			}
		}
		return $rec;
	}

	function initXbmcMovieObjTemplate($format='small'){
		$this->XbmcMovieObjTemplate = array(
			'movie'				=>array(
				'title'				=>'',
				'originaltitle'		=>'',
				'sorttitle'			=>'',
				'set'				=>'',
				'rating'			=>'',
				'year'				=>'',
				'top250'			=>'',
				'votes'				=>'',
				'outline'			=>'',
				'plot'				=>'',
				'tagline'			=>'',
				'runtime'			=>'',
				'thumb'				=>'',
				'mpaa'				=>'',
				'playcount'			=>'',
				'watched'			=>'',
				'id'				=>'',
				'filenameandpath'	=>'',
				'trailer'			=>'',
				'genre'				=>'',
				'director'			=>'',
				'credits'			=>''
			)
		);
		if ($format!='complete'){
			$this->XbmcMovieObjTemplate['movie'] = array_merge($this->XbmcMovieObjTemplate['movie'],array(
				'fileinfo'			=>array(
					'streamdetails'		=>array(
						'video'				=>array(
							'codec'		=>'',
							'aspect'	=>'',
							'width'		=>'',
							'height'	=>'',
						),
						'audio'				=>array(
							'codec'		=>'',
							'language'	=>'',
							'channels'	=>'',
						),
						'audio'				=>array(
							'codec'		=>'',
							'language'	=>'',
							'channels'	=>'',
						),
						'subtitle'			=>array(
							'language'	=>''
						)
					)
				),
				'actor'		=>array(
					'name'		=>'',
					'role'		=>''
				)
			));
		}
		/*
		<movie>
			<title>Who knows</title>
			<originaltitle>Who knows for real</originaltitle>
			<sorttitle>Who knows 1</sorttitle>
			<set>Who knows trilogy</set>
			<rating>6.100000</rating>
			<year>2008</year>
			<top250>0</top250>
			<votes>50</votes>
			<outline>A look at the role of the Buckeye State in the 2004 Presidential Election.</outline>
			<plot>A look at the role of the Buckeye State in the 2004 Presidential Election.</plot>
			<tagline></tagline>
			<runtime>90 min</runtime>
			<thumb>http://ia.ec.imdb.com/media/imdb/01/I/25/65/31/10f.jpg</thumb>
			<mpaa>Not available</mpaa>
			<playcount>0</playcount>
			<watched>false</watched>
			<id>tt0432337</id>
			<filenameandpath>c:\Dummy_Movie_Files\Movies\...So Goes The Nation.avi</filenameandpath>
			<trailer></trailer>
			<genre></genre>
			<credits></credits>
			<fileinfo>
				<streamdetails>
					<video>
						<codec>h264</codec>
						<aspect>2.35</aspect>
						<width>1920</width>
						<height>816</height>
					</video>
					<audio>
						<codec>ac3</codec>
						<language>eng</language>
						<channels>6</channels>
					</audio>
					<audio>
						<codec>ac3</codec>
						<language>spa</language>
					<channels>2</channels>
					</audio>
					<subtitle>
						<language>spa</language>
					</subtitle>
				</streamdetails>
			</fileinfo>
			<director>Adam Del Deo</director>
			<actor>
				<name>Paul Begala</name>
				<role>Himself</role>
			</actor>
			<actor>
				<name>George W. Bush</name>
				<role>Himself</role>
			</actor>
			<actor>
				<name>Mary Beth Cahill</name>
				<role>Herself</role>
			</actor>
			<actor>
				<name>Ed Gillespie</name>
				<role>Himself</role>
			</actor>
			<actor>
				<name>John Kerry</name>
				<role>Himself</role>
			</actor>
		</movie>
		 */
	}

	function objectXbmcSet($key,$val){
		$this->XbmcMovieObjTemplate['movie'][$key]=$val;
	}

	function getParserClass($link){
		$class = null;
		$arrParsers = array(
				'MovieParserAllocine'			=>'!allocine\.fr!',
				//'MovieParserCinemotions'		=>'!cinemotions\.com!',
				//'MovieParserCinemajeuxactu'	=>'!cinema\.jeuxactu\.com!',
		);
		foreach ($arrParsers as $className=>$rgx){
			if (preg_match($rgx,$link)){
				$class = $className;
			}
		}
		return $class;
	}

	function getDescFromLink($link){
		$desc=array();
		$className = MovieParser::getParserClass($link);
		if($className){
			$t = new $className();
			try{
				set_time_limit(5);
				$desc = $t->parse(array('url'=>trim($link)));
				if (Tools::array_key_exists_assign_default('badParsing', $desc, false)){
					$desc=array();
				}
			}catch (Exception $e){
				//print('exception' . $e->getMessage()." ".$e->getCode()." ".$e->getFile()." ".$e->getTraceAsString());
			}
			unset($t);
		}
		return $desc;
	}

	function findNodePlainText($tag,$rgx,$dom,$idx=null){
		if (!is_object($dom)){
			return ;
		}
		$arr = $dom->find($tag);
		$arrResult = array();
		foreach($arr as $k=>$v){
			if(preg_match($rgx,$v->plaintext)){
				print $v->plaintext;
				$arrResult[]=$v;
			}
		}
		if(is_null($idx)){
			return $arrResult;
		}else{
			print count($arrResult);
			return Tools::array_key_exists_assign_default($idx,$arrResult,null);
		}
	}
	function extractFromTag($key,$tag,$multiple,$dom,$attribute='plaintext'){
		if (!is_object($dom)){
			$this->result[$key]=($multiple)?array():'';
			return;
		}
		$obj=$dom->find($tag);
		if(!is_array($obj) or count($obj)==0){
			$this->result[$key]=($multiple)?array():'';
			return;
		}
		if ($multiple){
			$this->result[$key] =array();
			$result=array();
			foreach($obj as $v){
				if(trim($v->$attribute)!=''){
					$result[$v->$attribute] = $v->$attribute;
				}
			}
			$this->result[$key]=array_values($result);
		}else{
			$this->result[$key] =$obj[0]->$attribute;
		}
	}
}