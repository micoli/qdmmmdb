<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use \App\Components\QDmmmDB\Mediadb\Movies\MoviesManager;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;

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
		$qdMovie = new MoviesManager($app);
		$sMovie = $request->get('m','');
		$sPath = $request->get('p','');
		$sEngine = $request->get('e','');
		$res = $qdMovie->chooseMovie($sMovie,$sPath,$sEngine);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="chooseMoviesDetail"),
	 * )
	 */
	public function chooseMoviesDetail(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);

		$sEngine = $request->get('e','');
		$sFilename = $request->get('f','');
		$iID = $request->get('i','');

		$res = $qdMovie->chooseMoviesDetail($sEngine,$sFilename,$iID);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="proxyPosterImg"),
	 * )
	 */
	public function proxyPosterImg(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);
		$i64 = $request->get('i64','');

		$response = new Response();
		$response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, 'poster.jpg'));
		$response->headers->set('Content-Type', 'image/jpeg');
		$response->setContent($qdMovie->proxyPosterImg($i64));
		return $response;
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="checkMoviesPicture"),
	 * )
	 */
	public function checkMoviesPicture(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);

		$res = $qdMovie->checkMoviesPicture();
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getMoviesTree"),
	 * )
	 */
	public function getMoviesTree(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);

		$res = $qdMovie->getMoviesTree();
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getXbmcScraperMovieDetail"),
	 * )
	 */
	public function getXbmcScraperMovieDetail(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);

		$res = $qdMovie->getXbmcScraperMovieDetail();
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getMoviesFiles"),
	 * )
	 */
	public function getMoviesFiles(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);
		$sName = $request->get('name','');

		$res = $qdMovie->getMoviesFiles($sName);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setMoviesFromPath"),
	 * )
	 */
	public function setMoviesFromPath(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);

		$sRef = $request->get('ref','');
		$sRecord = $request->get('record','');
		$sID = $request->get('i','');
		$sPath = $request->get('p','');

		$res = $qdMovie->setMoviesFromPath($sRef,$sRecord,$sID,$sPath);
		return $this->formatResponse($request, $app, $res);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="renameMoviesFiles"),
	 * )
	 */
	public function renameMoviesFiles(Application $app,Request $request){
		$qdMovie = new MoviesManager($app);

		$sModified = $request->get('modified','');
		$sMoveExists = $request->get('moveExists','');

		$res = $qdMovie->renameMoviesFiles($sModified,$sMoveExists);
		return $this->formatResponse($request, $app, $res);
	}
}