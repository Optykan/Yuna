<?php 
class Yuna{
	private $routes=array();
	private $callables=array();
	private $errors=array();
	private $debug=array();
	private $version='0.0.1';

	public function __construct(){
		header('Content-Type: application/json');
	}
	private function error($message){
		array_push($this->errors, $message);
	}
	private function response($params){
		$yuna_meta=array('time'=>time(), 'yuna_version'=>$this->version, 'count'=>count($params));
		array_push($this->debug, $this->routes);
		$response=array('response'=>$params, 'yuna_meta'=>$yuna_meta, 'errors'=>$this->errors, 'debug'=>$this->debug);
		echo json_encode($response);
		exit(0);
	}

	public function route($route, $callable){
		$route=trim($route, '/');
		$endpoint= preg_split('/\/(?![^\(]*\))/', $route);
		if(isset($this->routes[$endpoint[0]])){
			if($this->routes[$endpoint[0]] == $endpoint[0]){
				
			}
		}
		foreach($endpoint as $value){
			//what am i doing
		}
		array_push($this->debug, $endpoint);


		// array_push($this->routes, $route);
		// array_push($this->callables, $callable);
	}
	public function run(){
		$request=$_GET['request_url'] ?: exit(0);
		if(in_array($request, $this->routes)){
			$index=array_search($request, $this->routes);
			$route=preg_replace('/\/', '/', $this->routes[$index]);
			$matches=array();

			preg_match_all('/'.$route.'/', $request, $matches);

			$result=$this->callables[$index]($matches) ?: NULL;
			if(is_null($result)){
				$this->error('Warning: Route '.$request.' callback did not return any value.');
				$this->response(array());
			}else{
				$this->response($result);
			}
		}else{
			$this->response(array('No Route Found'));
		}
	}
}
?>