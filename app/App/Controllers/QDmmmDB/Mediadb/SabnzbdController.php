<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Controllers\QDmmmDB\SabNZBD\QDSabnzbdProxy;

class SabnzbdController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="getSpeed"),
	 * )
	 */
	public function getSpeeds(Application $app,Request $request){
		$qd = new QDSabnzbdProxy($app);
		return $this->formatResponse($request,$app,$qd->getSpeeds());
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="setSpeed"),
	 * )
	 */
	public function setSpeed(Application $app,Request $request){
		$qd = new QDSabnzbdProxy($app);
		$value = $request('value');
		return $this->formatResponse($request,$app,$qd->setSpeed($value));
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="action"),
	 * )
	 */
	public function action(Application $app,Request $request){
		$qd = new QDSabnzbdProxy($app);
		$arr = [];
		foreach($request->request->all() as $k=>$v){
			if (preg_match('!^sab_(.*)!',$k,$m)){
				$arr[$m[1]] = $v;
			}
		}
		$objReturn = $request->get('obj_return',false);
		return $this->formatResponse($request,$app,$qd->action($arr,$objReturn));
	}
}

