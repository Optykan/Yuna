<?php 
require_once 'Yuna.php';

Yuna::Config(array('variable_delimiter'=>[':',':']));
Yuna::Route('/test/', function(Request $request){
	return '/test/';
});
Yuna::Route('/test/:foo:/:bar:/', function(Request $request){
	return $request->getParam('foo');
});

Yuna::Run();
?>