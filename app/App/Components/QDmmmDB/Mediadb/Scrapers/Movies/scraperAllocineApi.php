<?php
namespace App\Components\QDmmmDB\Mediadb\Scrapers\Movies;

use App\Components\QDmmmDB\Mediadb\Scrapers\QDHtmlMovieParser;
use App\Components\QDmmmDB\Misc\Tools;

class scraperAllocineApi extends QDHtmlMovieParser{
	function __construct(){
		$this->QDNet			= new QDNet();
		$this->cacheminutes		= 123*59+59;
		$this->cache			= true;
		$this->allocineapikey	= 'aXBhZC12MQ';
		$this->allocineapikey	= 'YW5kcm9pZC12M3M';
		$this->scrapengine		= 'allocineapi';
	}

	function getList($movieName){
		$url = 'http://api.allocine.fr/rest/v3/search?partner='.$this->allocineapikey.'&filter=movie&count=50&page=1&format=json&q='.urlencode(str_replace("'"," ",$movieName));
		$arrResult = Tools::object2array(json_decode($this->QDNet->getCacheURL($url,'allocineapi',$this->cacheminutes,$this->cache)));
		$searchresults = array();
		//db($arrResult);
		if (is_array($arrResult) && array_key_exists('feed',$arrResult)&& array_key_exists('totalResults',$arrResult['feed']) && $arrResult['feed']['totalResults']>0){
			foreach($arrResult['feed']['movie'] as $k=>$v){
				$searchresults[]=array(
					'id'		=> $v['code'],
					'title'		=> Tools::array_key_exists_assign_default('title', $v, '').' '.$v['originalTitle'],
					'year'		=> $v['productionYear'],
					'actors'	=> array_key_exists('castingShort'	,$v)?$v['castingShort'	]['actors'		]:'',
					'directors'	=> array_key_exists('castingShort'	,$v)?$v['castingShort'	]['directors'	]:'',
					'poster'	=> array_key_exists('poster'			,$v)?$v['poster'		]['href'		]:'',
					'overview'	=> '',
					'engine'	=> $this->scrapengine
				);
			}
		}
		return $searchresults;

	}

	function getDetail($id){
		$url = 'http://api.allocine.fr/rest/v3/movie?partner='.$this->allocineapikey.'&format=json&profile=large&code='.$id;
		//print $url;
		$txt = $this->QDNet->getCacheURL($url,'allocineapi',$this->cacheminutes,$this->cache);
		$arr = Tools::object2array(json_decode($txt));
		$arr = $arr['movie'];
		//db($arr);
		//die($arr);die('---');
		$res = $this->initBasicResult();
		$res['type'				]= 'movie';
		$res['engine'			]= $this->scrapengine;
		$res['id'				]= $arr['code'		];
		$res['title'			]= $arr['title'];
		$res['originalTitle'	]= $arr['originalTitle'];
		$res['year'				]= $arr['productionYear'];
		$res['dateOut'			]= $arr['productionYear'	];
		$res['summary'			]= $arr['synopsis'	];
		$res['length'			]= 0;//$arr['runtime'	];
		$res['ratings_users'	]= $arr['statistics']['userRating'	];
		$res['trailer'			]= $arr['trailer'	]['href'];
		$res['votes'			]= $arr['userReviewCount'];
		$res['certification'	]= '';//$arr['certification'];

		if(array_key_exists('genre',$arr) && is_array($arr['genre'])){
			foreach($arr['genre'] as $v){
				$res['genres'][]=$v['$'];
			}
			$res['genre']=join(' / ',$res['genres']);
		}
		if(array_key_exists('nationality',$arr) && is_array($arr['nationality'])){
			foreach($arr['nationality'] as $v){
				$res['country'][]=$v['$'];
			}
		}
		if(array_key_exists('poster',$arr) && is_array($arr['poster'])){
			//foreach($arr['posters'] as $k=>$v){
				if($k==0)	$res['poster']=$arr['poster']['href'];
				$res['posters'][]=$arr['poster']['href'];
			//}
		}
		if(array_key_exists('media',$arr) && is_array($arr['media'])){
			foreach($arr['media'] as $k=>$v){
				if($v['class']=='picture'){
					$res['backdrops'][]=$v['thumbnail']['href'];
				}
			}
		}

		if(array_key_exists('castMember',$arr) && is_array($arr['castMember'])){
			foreach($arr['castMember'] as $k=>$v){
				switch (strtolower($v['activity']['$'])){
					case 'réalisateur':
						$res['director'	][]=$v['person']['name'];
					break;
					case 'productrice':
					case 'producteur':
						$res['society'	].=($res['society']==''?'':' / ').$v['person']['name'];
					break;
					case 'actrice':
					case 'acteur':
						$res['actors'	][]=$v['person']['name'];
					break;
				}
			}
		}
		//header('charset=UTF-8');die($this->convertToXbmcMovieNfo($res));
		return $res;
	}
}