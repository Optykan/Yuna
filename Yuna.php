<?php 
require_once 'net/Request.php';
require_once 'net/Response.php';
class Yuna{
	private static $routes=array();
	private static $version='0.4.0';
	private static $warnings=array();
	private static $config=array();
	private static $VAR_START;
	private static $VAR_END;

	public static function Init(){
		self::$config=array('variable_delimiter'=>['{', '}'], 'request_url'=>$_GET['request_url'], 'enable_meta'=>true, 'enable_warnings'=>true );
		self::$VAR_START='{';
		self::$VAR_END  ='}';
	}

	public static function Config($config){
		self::$config=array_merge(self::$config, $config);
		self::$VAR_START=self::$config['variable_delimiter'][0];
		self::$VAR_END  =self::$config['variable_delimiter'][1];
	}

	private static function Warn($message){
		array_push(self::$warnings, $message);
	}

	private static function Response($response){
		header('Content-Type: application/json');

		if(self::$config['enable_meta']===true){
			$yuna_meta=array('time'=>time(), 'yuna_version'=>self::$version, 'count'=>$response->getCount());
			$response->setMeta($yuna_meta);
		}
		if(self::$config['enable_warnings']==true){
			$response->setWarnings(self::$warnings);
		}
		
		$response->sendData();
		exit(0);
	}

	private static function MapDepth($keys, $array){
		$encoded=array();

		foreach( $keys as $key ) {
			if(strpos($key, self::$VAR_START) !== false || !isset($array[$key])){
				//this should have stellar runtime
				$length = count($array);
				$i=0;

				foreach($array as $index=>$value){
					if(strpos($index, self::$VAR_START) !== false){
						//find an instance of {bar} or whatever user is using
						$encoded[preg_replace('/'.self::$VAR_START.'|'.self::$VAR_END.'/', '', $index)]=$key;
						$array = $array[$index];
						break;
					}
					$i++;
				}
				if($i==$length){
					return NULL;
				}else{
					$i=0;
				}
			}else{
				$array = $array[$key];
			}
		}
		if(!isset($array['yuna_callback'])){
			self::Warn('No callback found for route /'.implode('/', $keys).'/');
			return NULL;
		}
		return array('yuna_callback'=>$array['yuna_callback'], 'yuna_vars'=>$encoded);
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
			$routes[$node]['yuna_callback']=$callback;
		}
	}

	public static function Route($route, $callback){
		$route=trim($route, '/');

		//split the route along slashes where the slashes aren't in the variable
		$route=preg_split('/\/(?![^'.self::$VAR_START.']*'.self::$VAR_END.')/', $route);
		self::BuildRoute(self::$routes, $route, $callback, 0);
	}

	public static function Run(){
		
		$response=new Response();

		if(!self::$config['request_url']){
			self::Warn('No route passed.');
			self::Response($response);
		}
		$route=trim(self::$config['request_url'], '/');
		$routeAsArray=explode('/', $route);

		$endpoint=self::MapDepth($routeAsArray, self::$routes);

		if(!is_null($endpoint)){
			if($endpoint['yuna_vars']){
				$request=new Request(getallheaders(), $endpoint['yuna_vars']);
			}else{
				$request=new Request(getallheaders(), NULL);
			}

			$cResponse=call_user_func($endpoint['yuna_callback'], $request, $response);
			
			if($cResponse !== false){
				if(!($cResponse instanceof Response)){
					self::Warn('Route /'.$route.'/ did not return a Response object. Yuna will use the return value instead.');
					$response->setResponse($cResponse);
					self::Response($response);
				}
				self::Response($cResponse);
			}else{
				self::Warn('Route /'.$route.'/ callback did not return a Response object');
				self::Response($response);
			}
		}else{
			self::Warn('No route found for /'.$route.'/');
			self::Response($response);
		}
	}
}
Yuna::Init();
?>