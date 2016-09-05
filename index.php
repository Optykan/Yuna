<?php 
require_once 'Yuna.php';

// Yuna::Config(array('variable_delimiter'=>[':',':']));
Yuna::Route('/test/', function(Request $request, Response $response){
	return '/test/';
});
Yuna::Group('/api/bar', function(){
	Yuna::Route('/test/{foo}/{bar}', function(Request $request, Response $response){
		return $request->getParam('foo');
	});
});

Yuna::Run();
?>