<?php 
class Request{
	private $type;
	private $headers;
	private $params;

	public function __construct($headers, $params=NULL){
		$this->type=$_SERVER['REQUEST_METHOD'];
		$this->header=$headers;
		$this->params=$params;
	}
	public function getType(){
		return $this->type;
	}
	public function getHeaders(){
		return $this->headers;
	}
	public function getParams(){
		return $this->params;
	}
}
?>