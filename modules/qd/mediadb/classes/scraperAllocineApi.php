<?
class scraperAllocineApi extends QDHtmlMovieParser{
	function __construct(){
		$this->QDNet			= new QDNet();
		$this->cacheminutes		= 123*59+59;
		$this->cache			= true;
		$this->allocineapikey	= 'aXBhZC12MQ';
		$this->scrapengine		= 'allocineapi';
	}

	function getList($movieName){
		$url = 'http://api.allocine.fr/rest/v3/search?partner='.$this->allocineapikey.'&filter=movie&count=50&page=1&format=json&q='.urlencode($movieName);
		$arrResult = object2array(json_decode($this->QDNet->getCacheURL($url,'allocineapi',$this->cacheminutes,$this->cache)));
		$searchresults = array();
		if (is_array($arrResult) && array_key_exists('feed',$arrResult)&& array_key_exists('totalResults',$arrResult['feed']) && $arrResult['feed']['totalResults']>0){
			foreach($arrResult['feed']['movie'] as $k=>$v){
				$searchresults[]=array(
					'id'		=> $v['code'],
					'title'		=> array_key_exists_assign_default('title', $v, '').' '.$v['originalTitle'],
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
		print $url;
		$arr = object2array(json_decode($this->QDNet->getCacheURL($url,'allocineapi',$this->cacheminutes,$this->cache)));
		die($arr);die('---');
		$res = $this->initBasicResult();
		$res['type'				]= $arr['movie'];
		$res['engine'			]= $this->scrapengine;
		$res['id'				]= $arr['id'		];
		$res['title'			]= $arr['name'];
		$res['originalTitle'	]= $arr['original_name'];
		$res['year'				]= substr($arr['released'],0,4);
		$res['dateOut'			]= $arr['released'	];
		$res['summary'			]= $arr['overview'	];
		$res['length'			]= $arr['runtime'	];
		$res['ratings_users'	]= $arr['rating'	];
		$res['trailer'			]= $arr['trailer'	];
		$res['votes'			]= $arr['votes'		];
		$res['certification'	]= $arr['certification'];
		if(array_key_exists('genres',$arr) && is_array($arr['genres'])){
			foreach($arr['genres'] as $v){
				$res['genres'][]=$v['name'];
			}
			$res['genre']=join(' / ',$res['genres']);
		}
		if(array_key_exists('countries',$arr) && is_array($arr['countries'])){
			foreach($arr['countries'] as $v){
				$res['country'][]=$v['name'];
			}
		}
		if(array_key_exists('posters',$arr) && is_array($arr['posters'])){
			$str = ''; $sepa = '';
			foreach($arr['posters'] as $k=>$v){
				if($k==0)	$res['poster']=$v['image']['url'];
				$res['posters'][]=$v['image']['url'];
			}
		}
		if(array_key_exists('backdrops',$arr) && is_array($arr['backdrops'])){
			foreach($arr['backdrops'] as $k=>$v){
				$res['backdrops'][]=$v['image']['url'];
			}
		}

		if(array_key_exists('cast',$arr) && is_array($arr['cast'])){
			foreach($arr['cast'] as $k=>$v){
				switch (strtolower($v['job'])){
					case 'director':
						$res['director'	][]=$v['name'];
					break;
					case 'producer':
						$res['society'	].=($res['society']==''?'':' / ').$v['name'];
					break;
					case 'actor':
						$res['actors'	][]=$v['name'];
					break;
				}
			}
		}
		//header('charset=UTF-8');die($this->convertToXbmcMovieNfo($res));
		return $res;
    }

	
}
?>