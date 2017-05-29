<?php
namespace App\Controllers\QDmmmDB\Mediadb;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;
use SM\SilexRestApi\Controllers\NormalizedResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Components\QDmmmDB\Mediadb\QDMediaDBSystemStatus;

class MediaDBSystemStatusController {
	use NormalizedResponse;

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="status"),
	 * )
	 */
	public function status(Application $app,Request $request){
		$qd = new MediaDBSystemStatus();
		$data = $qd->cpu();
		return $this->formatResponse($request, $app, [
			'processes'	=> array_values($data['processes']),
			'cpu'		=> $data['core'],
			'disks'		=> $qd->get_disks()
		]);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="diskStatus"),
	 * )
	 */
	public function diskStatus(Application $app,Request $request){
		$qd = new MediaDBSystemStatus();
		return $this->formatResponse($request, $app, [$qd->get_disks()]);
	}

	/**
	 * @SLX\Route(
	 *     @SLX\Request(uri="processes"),
	 * )
	 */
	public function processes(Application $app,Request $request){
		$qd = new QDMediaDBSystemStatus();
		$data = $qd->cpu();
		return $this->formatResponse($request, $app, [
			'processes'	=> array_values($data['processes']),
			'cpu'		=> $data['core'],
		]);
	}


}