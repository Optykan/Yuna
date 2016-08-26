<?php 
require_once 'Yuna.php';

// $yuna=new Yuna();
// $yuna->route('/users/([0-9].*\/)', function($matches){ return $matches; });
// $yuna->run();


Yuna::Route('/test/', function(Request $request){
	return 'test';
});
Yuna::Route('/test/{bar}', function(Request $request){
	return $request->getParams();
});
Yuna::Run();
?>