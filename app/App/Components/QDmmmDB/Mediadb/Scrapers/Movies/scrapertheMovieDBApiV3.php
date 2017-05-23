<?php
namespace App\Components\QDmmmDB\Mediadb\Scrapers\Movies;

use App\Components\QDmmmDB\Mediadb\Scrapers\QDHtmlMovieParser;
use App\Components\QDmmmDB\Misc\QDNet;
use App\Components\QDmmmDB\Misc\Tools;

class scrapertheMovieDBApiV3 extends QDHtmlMovieParser{
	function __construct(){
		$this->QDNet			= new QDNet();
		$this->cacheminutes		= 123*59+59;
		$this->cache			= true;
		$this->themoviedbapikey	= 'cedb34914c5fcf4ae1b1aa430de1db3c';
		$this->scrapengine		= 'themoviedb';
		$this->getConfiguration();
	}

	function getConfiguration(){
		$url='http://api.themoviedb.org/3/configuration?api_key='.$this->themoviedbapikey;
		$res = $this->QDNet->getCacheURL($url,'themoviedbapiV3',$this->cacheminutes,$this->cache);
		$arrResult = Tools::object2array(json_decode($res));
		$this->baseImageUrl=$arrResult['images']['base_url'];
	}

	function getCapabilities(){
		return array(
			'pictureWithDimension'	=> true,
			'pictureSorted'			=> true
		)+parent::getCapabilities();
	}

	function getList($movieName){
		$url = 'http://api.themoviedb.org/3/search/movie?api_key='.$this->themoviedbapikey.'&query='.urlencode(preg_replace("!'!","",$movieName)).'&language=fr&page=1';
		$res = $this->QDNet->getCacheURL($url,'themoviedbapiV3',$this->cacheminutes,$this->cache);
		$arrResult = Tools::object2array(json_decode($res));
		$searchresults = array();
		if(is_array($arrResult['results'] )){
			foreach($arrResult['results'] as $k=>$v){
				if(is_array($v)){
					$searchresults[]=array(
						'id'		=> $v['id'],
						'title'		=> Tools::array_key_exists_assign_default('title', $v, '').($v['original_title']?' ('.$v['original_title'].')':''),
						'year'		=> substr($v['release_date'],0,4),
						'overview'	=> $v['overview'],
						'poster'	=> $this->baseImageUrl.'/w92'.$v['poster_path'],
						'engine'	=> $this->scrapengine
					);
				}
			}
		}
		//if(count($searchresults)==0) return null;
		return $searchresults;

	}

	function getIdFromImdbId($id){
		if(preg_match('/^tt[0-9]*$/',$id)){
			$url = 'http://api.themoviedb.org/3/Movie.imdbLookup/fr/json/'.$this->themoviedbapikey.'/'.$id;
			$res = $this->QDNet->getCacheURL($url,'themoviedbapiV3',$this->cacheminutes,$this->cache);
			$arrResult = json_decode($res,true);
			//db($arrResult);
			if(is_array($arrResult) && count($arrResult)>0){
				if(is_array($arrResult[0]) && array_key_exists('id',$arrResult[0])){
					return $arrResult[0]['id'];
				}else{
					return null;
				}
			}else{
				return null;
			}
		}
	}

	function getDetail($id){
		$url = 'http://api.themoviedb.org/3/movie/'.$id.'?api_key='.$this->themoviedbapikey.'&language=fr&append_to_response=casts,trailers,keywords';
		$arr = Tools::object2array(json_decode($this->QDNet->getCacheURL($url,'themoviedbapiV3',$this->cacheminutes,$this->cache)));
		//db($arr);die();
		$res = $this->initBasicResult();
		$res['engine'			]= $this->scrapengine;
		$res['id'				]= $arr['id'		];
		$res['type'				]= 'movie';
		$res['title'			]= $arr['title'];
		$res['originalTitle'	]= $arr['original_title'];
		$res['year'				]= substr($arr['release_date'],0,4);
		$res['dateOut'			]= $arr['release_date'	];
		$res['summary'			]= $arr['overview'	];
		$res['length'			]= $arr['runtime'	];
		$res['ratings_users'	]= $arr['vote_average'	];
		$res['trailer'			]= '';
		if(array_key_exists('trailers',$arr) && array_key_exists('youtube',$arr['trailers']) && is_array($arr['trailers']['youtube']) && array_key_exists(0, $arr['trailers']['youtube'])){
			$res['trailer']= 'http://www.youtube.com/watch?v='.$arr['trailers']['youtube'][0]['source'];
		}
		$res['votes'			]= $arr['votes'		];
		$res['certification'	]= $arr['certification'];
		if(array_key_exists('genres',$arr) && is_array($arr['genres'])){
			foreach($arr['genres'] as $v){
				$res['genres'][]=$v['name'];
			}
			$res['genre']=join(' / ',$res['genres']);
		}
		if(array_key_exists('production_countries',$arr) && is_array($arr['production_countries'])){
			foreach($arr['production_countries'] as $v){
				$res['country'][]=$v['name'];
			}
		}
		if(array_key_exists('production_companies',$arr) && is_array($arr['production_companies'])){
			foreach($arr['production_companies'] as $k=>$v){
				$res['society'	].=($res['society']==''?'':' / ').$v['name'];
			}
		}
		if(array_key_exists('casts',$arr) && is_array($arr['casts'])){
			if (array_key_exists('cast',$arr['casts']) && is_array($arr['casts']['cast'])){
				foreach($arr['casts']['cast'] as $k=>$v){
					$res['actors'	][]=$v['name'];
				}
			}
			if(array_key_exists('crew',$arr['casts']) && is_array($arr['casts']['crew'])){
				foreach($arr['casts']['crew'] as $k=>$v){
					switch (strtolower($v['job'])){
						case 'director':
							$res['director'	][]=$v['name'];
						break;
						case 'producer':
							$res['society'	].=($res['society']==''?'':' / ').$v['name'];
						break;
					}
				}
			}
		}
		$this->getImages($id,'fr',$res);
		$this->getImages($id,'',$res);
		//db($res);

		//header('charset=UTF-8');die($this->convertToXbmcMovieNfo($res));
		return $res;
	}

	private function getImages($id,$language,&$res){
		$url = 'http://api.themoviedb.org/3/movie/'.$id.'/images?api_key='.$this->themoviedbapikey;
		if($language!=''){
			$url.= "&language=".$language;
		}
		$arr = Tools::object2array(json_decode($this->QDNet->getCacheURL($url,'themoviedbapiv3',$this->cacheminutes,$this->cache)));
		//db($arr);
		if(is_array($arr) && array_key_exists('posters',$arr) && is_array($arr['posters'])){
			$str = ''; $sepa = '';
			foreach($arr['posters'] as $k=>$v){
				if($k==0)	$res['poster']=$v['image']['url'];
				$res['posters'][]=array(
						'url'		=> 'http://image.tmdb.org/t/p/original'.$v['file_path'],
						'width'		=> $v['width'],
						'height'	=> $v['height']
				);
			}
		}

		if(is_array($arr) && array_key_exists('backdrops',$arr) && is_array($arr['backdrops'])){
			foreach($arr['backdrops'] as $k=>$v){
				$res['backdrops'][]=array(
						'url'		=> 'http://image.tmdb.org/t/p/original'.$v['file_path'],
						'width'		=> $v['width'],
						'height'	=> $v['height']
				);
			}
		}
	}
}
?>
