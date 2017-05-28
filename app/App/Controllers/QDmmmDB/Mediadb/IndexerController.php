<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;

class IndexerController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="showDisks"),
	 * )
	 */
	public function showDisks(Application $app,Request $request){
		$qd = new \App\Components\QDmmmDB\Mediadb\QDIndexer($app);
		return $this->formatResponse($request,$app,$qd->svc_showDisks());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="index"),
	 * )
	 */
	public function index(Application $app,Request $request){
		$qd = new \App\Components\QDmmmDB\Mediadb\QDIndexer($app);
		return $this->formatResponse($request,$app,$qd->svc_index());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="indexAll"),
	 * )
	 */
	public function indexAll(Application $app,Request $request){
		$qd = new \App\Components\QDmmmDB\Mediadb\QDIndexer($app);
		return $this->formatResponse($request,$app,$qd->svc_indexAll());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getFiles"),
	 * )
	 */
	public function getFiles(Application $app,Request $request){
		$qd = new \App\Components\QDmmmDB\Mediadb\QDIndexer($app);

		$arr = explode(' ',$request->get('search',''));
		$this->mode = $request->get('mode','flat');
		if($request->get('search','')==''){
			return $this->formatResponse($request,$app,null);
		}
		return $this->formatResponse($request,$app,$qd->svc_getFiles());
	}
}
