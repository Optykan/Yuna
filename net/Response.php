<?php
class Response{
	private $response=array();
	private $httpResponse=200;
	private $headers=array();
	private $count=0;

	public function __construct($data=NULL){
		$this->setHeader('Content-Type: application/json');
		$this->setWarnings();
		$this->setMeta();
		$this->setStatus(200);
		$this->setResponse($data);
	}
	public function setHeader($header){
		if(is_array($header)){
			array_merge($this->headers, $header);
		}else{
			array_push($this->headers, $header);
		}
	}
	public function setWarnings($warn=NULL){
		$this->response['yuna_warnings']=$warn;
	}
	public function setMeta($meta=NULL){
		$this->response['yuna_meta']=$meta;
	}
	public function setStatus($status=200){
		$this->http_reponse=$status;
	}
	public function setResponse($data){
		$this->response['response']=$data;
		$this->count=count($data);
	}
	public function getCount(){
		return $this->count;
	}
	public function sendData(){
		http_response_code($this->httpResponse);
		foreach($this->headers as $header){
			header($header);
		}
		echo json_encode($this->response);
	}

}
?>