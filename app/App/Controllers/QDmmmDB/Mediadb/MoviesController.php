<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;
use \App\Components\QDmmmDB\Mediadb\QDMoviesProxy;

class MoviesController {
	use NormalizedResponse;
// 	function svc_updateDatabase(){
// 	function svc_convertXBMCNfoToQdMmmDb(){
// 	function svc_preloadFolder(){

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="chooseMovie"),
	 * )
	 */
	public function chooseMovie(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);
		$sMovie = $request->get('m','');
		$sPath = $request->get('p','');
		$sEngine = $request->get('e','');
		$res = $qdMovie->svc_chooseMovie($sMovie,$sPath,$sEngine);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="proxyPosterImg"),
	 * )
	 */
	public function proxyPosterImg(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);
		$i64 = $request->get('i64','');
		$res = $qdMovie->svc_proxyPosterImg($i64);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="chooseMoviesDetail"),
	 * )
	 */
	public function chooseMoviesDetail(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);

		$sEngine = $request->get('e','');
		$sFilename = $request->get('f','');
		$iID = $request->get('i','');

		$res = $qdMovie->svc_chooseMoviesDetail($sEngine,$sFilename,$iID);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="checkMoviesPicture"),
	 * )
	 */
	public function checkMoviesPicture(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);

		$res = $qdMovie->svc_checkMoviesPicture();
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getMoviesTree"),
	 * )
	 */
	public function getMoviesTree(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);

		$res = $qdMovie->svc_getMoviesTree();
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getXbmcScraperMovieDetail"),
	 * )
	 */
	public function getXbmcScraperMovieDetail(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);

		$res = $qdMovie->svc_getXbmcScraperMovieDetail();
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getMoviesFiles"),
	 * )
	 */
	public function getMoviesFiles(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);
		$sName = $request->get('name','');

		$res = $qdMovie->svc_getMoviesFiles($sName);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setMoviesFromPath"),
	 * )
	 */
	public function setMoviesFromPath(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);

		$sRef = $request->get('ref','');
		$sRecord = $request->get('record','');
		$sID = $request->get('i','');
		$sPath = $request->get('p','');

		$res = $qdMovie->svc_setMoviesFromPath($sRef,$sRecord,$sID,$sPath);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="renameMoviesFiles"),
	 * )
	 */
	public function renameMoviesFiles(Application $app,Request $request){
		$qdMovie = new QDMoviesProxy($app);

		$sModified = $request->get('modified','');
		$sMoveExists = $request->get('moveExists','');

		$res = $qdMovie->svc_renameMoviesFiles($sModified,$sMoveExists);
		return $this->formatResponse($request, $app, $res);
	}
}