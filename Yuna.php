<?php 
require_once 'net/Request.php';
class Yuna{
	private static $routes=array();

	private static $version='0.0.1';

	private static $warnings=array();

	private static function Warn($message){
		array_push(self::$warnings, $message);
	}

	private static function Response($data){
		header('Content-Type: application/json');
		$yuna_meta=array('time'=>time(), 'yuna_version'=>self::$version, 'count'=>count($data));
		$response=array('response'=>$data, 'yuna_meta'=>$yuna_meta, 'yuna_warnings'=>self::$warnings);
		echo json_encode($response);
		exit(0);
	}

	private static function FindCallback($routes, $route){
		// where $routes is the list of all routes, and
		//       $route  is the requested route
		//in the array, search for the value that matches the path
		//this function does not support putting the handlebars anywhere but at the end
		# /foo/bar/baz => ['foo']['bar']['baz']
		# /foo/bar/1   => ['foo']['bar']['{user}']

		$route=preg_split('/\/(?![^\(]*\))/', $route);
		$depth=count($route);
		$matches=array();
		switch ($depth) {
			case 1:
				# /users/
			if(isset($routes[$route[0]]['callback'])){
				return $routes[$route[0]]['callback'];
			}
			foreach($routes as $node=>$values){
				# where node is something like /foo/
				# there is no exact route, so we're using the handlebars
				preg_match_all('/\{(.*)\}/', $node, $matches);
				if(isset($matches[1][0])){
					//we got handlebars
					return array('callback'=>$routes[$node]['callback'], 'name'=>$matches[1][0], 'value'=>$route[count($route)-1]);
				}
			}
			break;

			case 2:
				# /users/foo/
			if(isset($routes[$route[0]][$route[1]]['callback'])){
				return $routes[$route[0]][$route[1]]['callback'];
			}
			foreach($routes[$route[0]] as $node=>$values){
				# where node is something like /foo/
				# there is no exact route, so we're using the handlebars
				preg_match_all('/\{(.*)\}/', $node, $matches);
				if(isset($matches[1][0])){
					//we got handlebars
					return array('callback'=>$routes[$route[0]][$node]['callback'], 'name'=>$matches[1][0], 'value'=>$route[count($route)-1]);
				}
			}
			break;

			case 3:
				# /users/foo/bar/
			break;

			case 4:
				# /users/foo/bar/baz
			 	# there has to be a better way
			break;
			default:
			throw new Exception("Invalid Route Depth");
			break;
		}
		return NULL;
	}
	private static function BuildRoute(&$routes, $route, $callback, $depth){
		$node=$route[$depth];
		$routes[$node]=array();
		if(isset($route[$depth+1])){
			$depth++;
			self::BuildRoute($routes[$node], $route, $callback, $depth);
		}else{
			$routes[$node]['callback']=$callback;
		}
		//recursion help
	}

	public static function Route($route, $callback){
		$route=trim($route, '/');
		$route=preg_split('/\/(?![^\(]*\))/', $route);
		if(count($route)==1){
			self::$routes[$route[0]]['callback']=$callback;
		}else{
			self::BuildRoute(self::$routes, $route, $callback, 0);
		}
	}

	public static function Run(){
		$route=trim($_GET['request_url'], '/');

		// var_dump(self::$routes);
		// exit(0);
		$endpoint=self::FindCallback(self::$routes, $route);
		if(is_null($endpoint)){
			self::Warn('Route '.$route.' not found');
			self::Response(NULL);
		}
		if(is_array($endpoint)){
			$request=new Request(getallheaders(), array($endpoint['name']=>$endpoint['value']));
			$data=$endpoint['callback']($request) ?: NULL;
		}
		else{
			$request=new Request(getallheaders(), NULL);
			$data=$endpoint($request) ?: NULL;
		}
		// $data=call_user_func_array($endpoint['callback'], array($request)) ?: NULL;
		if(is_null($data)){
			self::Warn('Route '.$route.' did not return a value.');
		}
		self::Response($data);
	}
}
?>