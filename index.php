<?php 
require_once 'Yuna.php';

//a basic route
Yuna::Route('/test/', function(Request $request, Response $response){
	$response->setResponse('/test/');
	return $response;
});

//groups
Yuna::Group('/api', function(){
	//nested groups
	Yuna::Group('/bar', function(){
		Yuna::Route('/test/{foo}/', function(Request $request, Response $response){
			//set the response to be the :foo: variable in the url
			$response->setResponse($request->getParam('foo'));
			return $response;
		});
	});
	Yuna::Group('/foo', function(){
		Yuna::Route('/test/{bar}/', function(Request $request, Response $response){

			//change the HTTP response code
			$response->setStatus(301);

			//change some headers
			$response->setHeader('X-Powered-By: Yuna');

			//change many headers
			$response->setHeader(array('X-Test-Header-1: Foo', 'X-Test-Header-2: Bar'));

			//set response
			$response->setResponse($request->getParam('bar'));

			//don't forget this
			return $response;
		});
	});
});

//don't forget to run it
Yuna::Run();
?>