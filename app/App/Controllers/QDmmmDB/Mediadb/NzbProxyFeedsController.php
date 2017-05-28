<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Controllers\QDmmmDB\Nzb\QDNzbProxyFeeds;
class NzbProxyFeedsController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="testAllocine"),
	 * )
	 */
	public function testAllocine(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		return $this->formatResponse($request,$app,$qd->testAllocine());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="download"),
	 * )
	 */
	public function download(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$search = $request->get('s','');
		return $this->formatResponse($request,$app,$qd->download($search));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="search"),
	 * )
	 */
	public function search(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$search = $request->get('s','');
		return $this->formatResponse($request,$app,$qd->search($search));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="feedCacheRSS"),
	 * )
	 */
	public function feedCacheRSS(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		return $this->formatResponse($request,$app,$qd->feedCacheRSS());
	}
	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="search_binsearch"),
	 * )
	 */
	public function search_binsearch(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$query = $request->get('q','');
		return $this->formatResponse($request,$app,$qd->search_binsearch($query));
	}
	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="download_binsearch"),
	 * )
	 */
	public function download_binsearch(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$query = $request->get('q','');
		return $this->formatResponse($request,$app,$qd->download_binsearch($query));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="download_newzleech"),
	 * )
	 */
	public function download_newzleech(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		return $this->formatResponse($request,$app,$qd->download_newzleech());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="search_newzleech"),
	 * )
	 */
	public function search_newzleech(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		return $this->formatResponse($request,$app,$qd->search_newzleech());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="distinctfeed"),
	 * )
	 */
	public function distinctfeed(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		return $this->formatResponse($request,$app,$qd->distinctfeed());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setStarred"),
	 * )
	 */
	public function setStarred(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$sIteStarred = $request->get('ITE_STARRED');
		$sIteId = $request->get('ITE_ID');
		return $this->formatResponse($request,$app,$qd->setStarred($sIteStarred,$sIteId));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setRead"),
	 * )
	 */
	public function setRead(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$sIteRead = $request->get('ITE_READ');
		$sIteId = $request->get('ITE_ID');
		return $this->formatResponse($request,$app,$qd->setRead($sIteRead,$sIteId));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setTreated"),
	 * )
	 */
	public function setTreated(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$sRid = $request->get('rid');
		return $this->formatResponse($request,$app,$qd->setTreated($sRid));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="dbfeed"),
	 * )
	 */
	public function dbfeed(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$mode =  $request->get('mode','feed');
		$sIs = $request->get('is');
		$sId = $request->get('id');
		$sQ = $request->get('q');
		$start = $request->get('start',0);
		$limit = $request->get('limit',20);
		return $this->formatResponse($request,$app,$qd->dbfeed($mode,$sIs,$sId,$sQ,$start,$limit));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="pubGetCacheSerial"),
	 * )
	 */
	public function pubGetCacheSerial(Application $app,Request $request){
		$qd = new QDNzbProxyFeeds($app);
		$i = $request->get('i');
		return $this->formatResponse($request,$app,$qd->pubGetCacheSerial($i));
	}
}