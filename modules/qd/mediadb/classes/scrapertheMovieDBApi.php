<?

class scrapertheMovieDBApi extends QDHtmlMovieParser{
	function __construct(){
		$this->QDNet			= new QDNet();
		$this->cacheminutes		= 123*59+59;
		$this->cache			= true;
		$this->themoviedbapikey	= 'cedb34914c5fcf4ae1b1aa430de1db3c';
		$this->scrapengine		= 'themoviedb';
	}

	function getList($movieName){
		$url = 'http://api.themoviedb.org/2.1/Movie.search/fr/json/'.$this->themoviedbapikey.'/'.urlencode($movieName);
		$res = $this->QDNet->getCacheURL($url,'themoviedbapi',$this->cacheminutes,$this->cache);
		//print $res;
		$arrResult = object2array(json_decode($res));
		$searchresults = array();
		foreach($arrResult as $k=>$v){
			if(is_array($v)){
				$searchresults[]=array(
					'id'		=> $v['id'],
					'title'		=> array_key_exists_assign_default('name', $v, '').' '.$v['original_name'],
					'year'		=> substr($v['released'],0,4),
					'overview'	=> $v['overview'],
					'poster'	=> (array_key_exists('posters',$v) && is_array($v['posters']) && array_key_exists(0,$v['posters']))?$v['posters'][0]['image']['url']:'',
					'engine'	=> $this->scrapengine
				);
			}
		}
		//if(count($searchresults)==0) return null;
		return $searchresults;
		
	}

	function getDetail($id){
		$url = 'http://api.themoviedb.org/2.1/Movie.getInfo/fr/json/'.$this->themoviedbapikey.'/'.$id;
		$arr = array_pop(object2array(json_decode($this->QDNet->getCacheURL($url,'themoviedbapi',$this->cacheminutes,$this->cache))));
		$res = $this->initBasicResult();
		$res['engine'			]= $this->scrapengine;
		$res['id'				]= $arr['id'		];
		$res['type'				]= 'movie';
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