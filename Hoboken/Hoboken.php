<?php

declare(encoding='UTF-8');

class Hoboken {
	
	private $actionList = array();
	private $extractList = array();
	private $routeSource = array();
	private $serverParams = array();

	private $layout = NULL;
	private $layoutDirectory = NULL;
	private $viewDirectory = NULL;
	
	
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	
	
	public function __construct() {
		
	}
	
	public function __destruct() {
		$this->actionList = array();
		$this->extractList = array();
	}
	
	public function __set($k, $v) {
		$this->extractList[$k] = $v;
		return true;
	}
	
	public function __get($k) {
		if ( array_key_exists($k, $this->extractList) ) {
			return $this->extractList[$k];
		}
		return NULL;
	}

	public function setLayout($layout) {
		$this->layout = trim($layout);
		return $this;
	}
	
	public function setLayoutDirectory($layoutDirectory) {
		$this->layoutDirectory = $layoutDirectory;
		return $this;
	}
	
	public function setViewDirectory($viewDirectory) {
		$this->viewDirectory = $viewDirectory;
		return $this;
	}
	
	public function setRouteSource(array $source) {
		$this->routeSource = $source;
		return $this;
	}
	
	public function setServerParams(array $server) {
		$this->serverParams = $server;
		return $this;
	}


}