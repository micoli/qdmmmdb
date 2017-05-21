<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use App\Components\QDmmmDB\Mediadb\QDSeriesProxy;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;

class SeriesController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getSeriesTree"),
	 * )
	 */
	public function getSeriesTree(Application $app,Request $request){
		$this->app = $app;

		$bRefresh = $request->get('refresh',0);
		$iId = $request->get('id',0);

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_getSeriesTree($iId,$bRefresh));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getSerieFromPath"),
	 * )
	 */
	public function getSerieFromPath(Application $app,Request $request){
		$this->app = $app;

		$sPath = $request->get('p',0);

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_getSerieFromPath($sPath));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getFolderSeriesList"),
	 * )
	 */
	public function getFolderSeriesList(Application $app,Request $request){
		$this->app = $app;

		$sPath = $request->get('p',0);

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_getFolderSeriesList($sPath));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="serieBulkRename"),
	 * )
	 */
	public function serieBulkRename(Application $app,Request $request){
		$this->app = $app;

		$sPath = $request->get('d',0);

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_serieBulkRename($sPath));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getFileSorterList"),
	 * )
	 */
	public function getFileSorterList(Application $app,Request $request){
		$this->app = $app;

		$sName = $request->get('name','');

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_getFileSorterList($sName));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setSerieFromPath"),
	 * )
	 */
	public function setSerieFromPath(Application $app,Request $request){
		$this->app = $app;

		$sMode = $request->get('m','');
		$sPath = $request->get('p','');
		$sId = $request->get('i','');

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_setSerieFromPath($sMode,$sPath,$sId));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="chooseSerie"),
	 * )
	 */
	public function chooseSerie(Application $app,Request $request){
		$this->app = $app;

		$sSerieName = $request->get('s','');
		$sPath = $request->get('p','');

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_chooseSerie($sSerieName,$sPath));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="renameFiles"),
	 * )
	 */
	public function renameFiles(Application $app,Request $request){
		$this->app = $app;

		$sModified = $request->get('modified','');
		$sModified = utf8_decode(base64_decode($sModified));
		$sMoveExists = $request->get('moveExists','');

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_renameFiles($sModified,$sMoveExists));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getFilesMulti"),
	 * )
	 */
	public function getFilesMulti(Application $app,Request $request){
		$this->app = $app;

		$sFullPath = $request->get('fullpath','');
		$sOnly2Rename = $request->get('only2Rename','true');

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_getFilesMulti($sFullPath,$sOnly2Rename));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getFiles"),
	 * )
	 */
	public function getFiles(Application $app,Request $request){
		$this->app = $app;

		$sFullPath = $request->get('fullpath','');
		$bOnly2Rename = $request->get('only2Rename','false')=='true';

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_getFiles($sFullPath,$bOnly2Rename));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="updateAllXml"),
	 * )
	 */
	public function updateAllXml(Application $app,Request $request){
		$this->app = $app;

		$bForceRefresh = $request->get('forceRefresh','true');

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_updateAllXml($bForceRefresh));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="updateDatabase"),
	 * )
	 */
	public function updateDatabase(Application $app,Request $request){
		$this->app = $app;

		$sPathShow = $request->get('pathshow',false);
		$sCurrent = $request->get('curent',false);
		$sKey = $request->get('key',false);

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_updateDatabase($sPathShow,$sCurrent,$sKey));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="updateFanartCache"),
	 * )
	 */
	public function updateFanartCache(Application $app,Request $request){
		$this->app = $app;

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_updateFanartCache($sPathShow));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="extractSeriesFilenameStruct"),
	 * )
	 */
	public function extractSeriesFilenameStruct(Application $app,Request $request){
		$this->app = $app;

		$sFilename = $request->get('filename',false);

		$qd = new QDSeriesProxy($app);
		return $this->formatResponse($request, $app, $qd->svc_extractSeriesFilenameStruct($sFilename));
	}

/*

function svc_extractSeriesFilenameStruct($intern = false) {
	return $this->extractSeriesFilenameStruct($_REQUEST['filename']);
*/

}