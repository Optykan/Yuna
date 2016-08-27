<?php 
require_once 'net/Request.php';
class Yuna{
	private static $routes=array();
	private static $version='0.1.0';
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

	private static function MapDepth($string, $array){
		$keys = explode( '][', substr( $string, 1, -1 ) );
		foreach( $keys as $key ) {
			if(!isset($array[$key])){
				return NULL;
			}
			$array = $array[$key];
		}
		return $array;
		# thanks to http://stackoverflow.com/questions/7003559/use-strings-to-access-potentially-large-multidimensional-arrays
	}
	private static function BuildRoute(&$routes, $route, $callback, $depth){
		$node=$route[$depth];

		if(!isset($routes[$node]) || !is_array($routes[$node])){
			$routes[$node]=array();
		}

		if(isset($route[$depth+1])){
			$depth++;
			self::BuildRoute($routes[$node], $route, $callback, $depth);
		}else{
			if(strpos($node, '{')!==false){
				$routes[$node]['yuna_callback']=array('callback'=>$callback, 'name'=>preg_replace('/{|}/', '', $node));
			}else{
				$routes[$node]['yuna_callback']=$callback;
			}	
		}
	}

	public static function Route($route, $callback){
		$route=trim($route, '/');
		$route=preg_split('/\/(?![^\(]*\))/', $route);
		self::BuildRoute(self::$routes, $route, $callback, 0);
	}

	public static function Run(){
		$route=trim($_GET['request_url'], '/');
		$routeAsString='['.preg_replace('/\//', '][', $route).']';
		$routeAsArray=explode('/', $route);

		$endpoint=self::MapDepth($routeAsString, self::$routes);

		if(is_null($endpoint) || !isset($endpoint['yuna_callback'])){ //the endpoint we're looking for was not found
			$var=array_pop($routeAsArray); //pop the last element off in hopes that it's just a variable
			$routeAsString='['.implode('][', $routeAsArray).']'; //implode it into a string
			$endpoint=self::MapDepth($routeAsString, self::$routes); //try to map it again


			if(is_null($endpoint)){
				//still no endpoint found
				self::Warn('Route '.$route.' has no callback');
				self::Response(NULL);
			}

			foreach($endpoint as $node=>$callback){
				if(strpos($node, '{')!==false){
					//we have a node with handlebars
					if(is_array($callback['yuna_callback'])){
						$request=new Request(getallheaders(), array($callback['yuna_callback']['name']=>$var));
						$cResponse=$callback['yuna_callback']['callback']($request);
						if(!isset($cResponse)){
							self::Warn('Route '.$route.' callback returned NULL');
						}
						self::Response($cResponse);
					}else{
						$request=new Request(getallheaders(), NULL);
						$cResponse=$callback['yuna_callback']($request);
						if(!isset($cResponse)){
							self::Warn('Route '.$route.' callback returned NULL');
						}
						self::Response($cResponse);
					}
				}
			}
			self::Warn('Route '.$route.' has no callback');
			self::Response(NULL);
		}
		else{
			$request=new Request(getallheaders(), NULL);
			$cResponse=$endpoint['yuna_callback']($request);
			if(!isset($cResponse)){
				self::Warn('Route '.$route.' callback returned NULL');
			}
			self::Response($cResponse);
		}
	}
}
?>