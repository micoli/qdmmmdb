<?php
use Symfony\Component\DomCrawler\Crawler;

class scrapersenscritique extends QDHtmlMovieParser{
	function __construct(){
		$this->QDNet			= new QDNet();
		$this->cacheminutes		= 123*59+59;
		$this->cache			= true;
		$this->scrapengine		= 'senscritiques';
		$this->urlRoot			= 'http://www.senscritique.com/';
	}


	function getCapabilities(){
		return array(
			'pictureWithDimension'	=> true,
			'pictureSorted'			=> true
		)+parent::getCapabilities();
	}

	function getList($movieName){
		$url = $this->urlRoot.'recherche?filter=movies&query='.urlencode(preg_replace("!'!","",$movieName));
		$res = $this->QDNet->getCacheURL($url,'senscritique',$this->cacheminutes,$this->cache);
		$html = str_get_html($res);
		$searchresults=array();
		foreach($html->find('ul.esco-list li.esco-item') as $li){
				$searchresults[]=array(
					//'id'		=> $li->attr['data-sc-product-id'],
					'id'		=> $li->find('.esco-content .elco-title a',0)->href,
					'title'		=> $li->find('.esco-content .elco-title a',0)->innertext,
					'year'		=> $li->find('.esco-content span.elco-date',0)->innertext,
					'overview'	=> $li->find('.esco-content p.elco-description',0)->innertext,
					'poster'	=> $li->find('.esco-cover img',0)->src,
					'engine'	=> $this->scrapengine
				);
		}
		return $searchresults;
	}

	private function cleanStr($s){
		return trim(preg_replace("#\R+#","",$s));
	}

	private function getDomNode($crawler,$filter){
		try{
			return $this->cleanStr($crawler->filter($filter)->text());
		}catch(Exception $e){
			return '-';
		}
	}

	function getDetail($id){
		if(preg_match('!\/([0-9]+)$!',$id,$m)){
			$numericId=$m[1];
		}

		$url = $this->urlRoot.$id;
		$mainCrawler = new Crawler($this->QDNet->getCacheURL($url,'senscritique',$this->cacheminutes,$this->cache));

		$url = $this->urlRoot.'sc/products/storyline/'.$numericId.'.json';
		$storyline = json_decode($this->QDNet->getCacheURL($url,'senscritique',$this->cacheminutes,$this->cache),true);

		$url = $this->urlRoot.$id.'/details';
		$detailCrawler = new Crawler($this->QDNet->getCacheURL($url,'senscritique',$this->cacheminutes,$this->cache));

		$url = $this->urlRoot.$id.'/images';
		$imagesCrawler = new Crawler($this->QDNet->getCacheURL($url,'senscritique',$this->cacheminutes,$this->cache));

		$url = $this->urlRoot.$id.'/videos';
		$videosCrawler = new Crawler($this->QDNet->getCacheURL($url,'senscritique',$this->cacheminutes,$this->cache));

		$res = $this->initBasicResult();

		$res['engine'			]= $this->scrapengine;
		$res['id'				]= $numericId;
		$res['type'				]= 'movie';
		$res['trailer'			]= '';
		$res['trailers'			]= array();
		$res['votes'			]= array();
		$res['certification'	]= array();
		$res['title'			]= $this->getDomNode($mainCrawler,".pvi-hero-overlay h1.pvi-product-title");
		$res['originalTitle'	]= $this->getDomNode($mainCrawler,".pvi-hero-overlay h1.pvi-product-originaltitle");
		$res['length'			]= $this->getDomNode($mainCrawler,".pvi-productDetails-item:nth-of-type(3)");
		$res['year'				]= $this->getDomNode($mainCrawler,".pvi-productDetails-item:nth-of-type(4)");
		$res['ratings_users'	]= $this->getDomNode($mainCrawler,".pvi-product-scrating .pvi-scrating-value");
		$res['summary'			]= trim(strip_tags($storyline['json']['data']));

		$exp=$detailCrawler->filter(".d-heading5:contains('Date de sortie')");
		if($exp->count()){
			$res['dateOut'			]= $this->cleanStr($exp->nextAll()->first()->text());
		}

		$exp=$mainCrawler->filter('.pvi-productDetails-item:nth-of-type(2) a');
		if($exp->count()){
			$exp->each(function($v) use (&$res){
				$res['genres'][]=$v->text();
			});
			$res['genre']=join(' / ',$res['genres']);
		}

		$exp=$detailCrawler->filter(".d-heading2-opt:contains('Acteurs')");
		if($exp->count()){
			$exp->parents()->first()->nextAll()->first()->filter('.ecot-contact-inner .ecot-contact-label')->each(function($v) use (&$res){
				$res['actors'	][]=trim($v->text());
			});
		}

		$exp=$detailCrawler->filter(".d-heading2-opt:contains('Réalisateurs')");
		if($exp->count()){
			$exp->parents()->first()->nextAll()->first()->filter('.ecot-contact-inner')->each(function($v) use (&$res){
				$res['director'	][]=trim($v->text());
			});
		}

		$exp=$detailCrawler->filter(".d-heading2-opt:contains('Distributeurs')");
		if($exp->count()){
			$exp->parents()->first()->nextAll()->first()->filter('.pde-data-label')->each(function($v) use (&$res){
				$res['society'	][]=trim($v->text());
			});
		}

		$exp=$detailCrawler->filter(".d-heading5:contains('Pays')");
		if($exp->count()){
			$exp->nextAll()->first()->filter('.pde-detail')->each(function($v) use (&$res){
				$res['country'	][]=trim($v->text());
			});
		}

		$exp=$imagesCrawler->filter("a.pim-thumbnail.lightview");
		if($exp->count()){
			$exp->each(function($v) use (&$res){
				$res['posters'][]=array(
					'url'	=> $v->attr('href'),
					'width'	=> 0,
					'height'=> 0
				);
			});
		}

		$exp=$imagesCrawler->filter("a.pim-backdrop.lightview");
		if($exp->count()){
			$exp->each(function($v) use (&$res){
				$res['backdrops'][]=array(
					'url'	=> $v->attr('href'),
					'width'	=> 0,
					'height'=> 0
				);
			});
		}

		$exp=$videosCrawler->filter('.d-grid-main.pvid-focus');
		if($exp->count()){
			$exp->each(function($v,$k) use (&$res){
				$res['trailers'][]='https://www.youtube.com/watch?v='.$v->attr('data-sc-video-id');
				if($k==0){
					$res['trailer']=$res['trailers'][0];
				}
			});
		}
		/**
		$res['country'][]=$v['name'];
		$res['director'	][]=$v['name'];
		//$this->getImages($id,'fr',$res);
		//$this->getImages($id,'',$res);
		*/

		//header('charset=UTF-8');die($this->convertToXbmcMovieNfo($res));
		return $res;
	}
}
