<?php
namespace App\Components\QDmmmDB\Mediadb\MultimediaSystem;

use JsonRPC\Client;

class KodiActions{
	private $client;
	public function __construct($sServerAdress,$sPort,$sUsername,$sPassword){
		$sUrl = sprintf('http://%s:%s/jsonrpc',$sServerAdress,$sPort);
		$this->client = new Client($sUrl);
		if($sUsername && $sPassword){
			$this->client->authentication($sUsername,$sPassword);
		}
		//$this->client->getHttpClient()->withDebug();
	}

	public function syncVideo($sSource){
		return $this->client->execute('VideoLibrary.Scan', [
			"directory" => $sSource
		]);

	}
}