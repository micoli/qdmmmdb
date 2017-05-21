<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;

class NzbProxyFeedsController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="aaa"),
	 * )
	 */
	public function aaa(Application $app,Request $request){
	}
}