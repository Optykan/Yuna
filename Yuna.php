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