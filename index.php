<?php 
require_once 'Yuna.php';

Yuna::Route('/test/', function(Request $request){
	return '/test/';
});
Yuna::Route('/test/{bar}', function(Request $request){
	return $request->getParams();
});
Yuna::Route('/test/foo/{bar}', function(Request $request){
	return '/test/foo/{bar}';
});
Yuna::Route('/test/foo/bar', function(Request $request){
	return '/test/foo/bar';
});
Yuna::Run();
?>