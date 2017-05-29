<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Components\QDmmmDB\Mediadb\MediaDBManager;

class MediaDBController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="proxyImg/"),
	 * )
	 */
	public function proxyImg(Application $app,Request $request){
		$this->app = $app;

		$bRefresh = $request->get('refresh',0);
		$sUrl = $request->get('u',0);
		$sC = $request->get('c',false);

		$qd = new MediaDBManager($app);
		return $this->formatResponse($request, $app, $qd->proxyImg($sUrl,$sC));
	}
}