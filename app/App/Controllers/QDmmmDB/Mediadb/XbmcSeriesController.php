<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Components\QDmmmDB\Mediadb\MultimediaSystem\QDXbmcSeries;

class XbmcSeriesController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getShows"),
	 * )
	 */
	public function getShows(Application $app,Request $request){
		$qd = new QDXbmcSeries($app);
		return $this->formatResponse($request,$app,$qd->getShows($value));

	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="preGetShowSeasons"),
	 * )
	 */
	public function preGetShowSeasons(Application $app,Request $request){
		$qd = new QDXbmcSeries($app);
		$sTvdbID = $request('tvdbid');
		return $this->formatResponse($request,$app,$qd->preGetShowSeasons($sTvdbID));

	}
	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getShowsSeasons"),
	 * )
	 */
	public function getShowsSeasons(Application $app,Request $request){
		$qd = new QDXbmcSeries($app);
		$sTvdbID = $request('tvdbid');
		return $this->formatResponse($request,$app,$qd->getShowsSeasons($sTvdbID));

	}
	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getEpisodeList"),
	 * )
	 */
	public function getEpisodeList(Application $app,Request $request){
		$qd = new QDXbmcSeries($app);
		$sTvdbID = $request('tvdbid');
		$sSeason = $request('season');
		return $this->formatResponse($request,$app,$qd->getEpisodeList($sTvdbID,$sSeason));
	}
}

/*
select sh.idShow,pashow.strPath,fi.idFile,sh.c00,sh.c16,epi.c12,epi.c13,epi.c00,pa.strPath,fi.strFilename,
group_concat(distinct concat(art_show.type,'|',art_show.url)),
group_concat(distinct concat(art_epi.type,'|',art_epi.url)),
sh.c01
from tvshow sh
inner join  tvshowlinkpath tvslp on tvslp.idShow=sh.idShow
inner join  path pashow on pashow.idPath=tvslp.idPath
inner join  episode epi on epi.idShow=sh.idShow
inner join  files fi on fi.idFile=epi.idFile
inner join  path pa on pa.idPath=fi.idPath
left join   art art_show on art_show.media_type='tvshow' and art_show.media_id=sh.idShow
left join   art art_epi on art_epi.media_type='episode' and art_epi.media_id=epi.idEpisode
where sh.c12=76290
group by fi.idFile
order by epi.idEpisode,sh.c00,epi.c12,epi.c13*1;
 */
