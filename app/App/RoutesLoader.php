<?php

namespace App;

use SM\SilexRestApi\RoutesLoader as AppRoutesLoader;
use Symfony\Component\HttpFoundation\Response;

class RoutesLoader extends AppRoutesLoader{
	protected function instantiateControllers()
	{
	}
	protected function extendsRoutes(){
		$this->app->get('/', function () {
			return new Response(file_get_contents(__DIR__."/../../static/index.html"),200);
		});
	}
}