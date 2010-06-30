<?php

declare(encoding='UTF-8');

class Hoboken {
	
	public $contentType = 'text/html';
	public $responseCode = 200;
	
	private $ext = '.phtml';
	private $layout = NULL;
	private $layoutDirectory = NULL;
	private $render = NULL;
	private $requestMethods = array();
	private $routes = array();
	private $siteRoot = NULL;
	private $siteRootSecure = NULL;
	private $viewDirectory = NULL;
	private $viewVariables = array();
	private $uriParam = '__u';
	
	
	public function __construct() {
		$sapi = strtolower(php_sapi_name());
		if ( 'cli' == $sapi ) {
			throw new \HobokenException("Hoboken must be run from a webserver.");
		}
		
		$this->requestMethods = array('GET', 'POST', 'PUT', 'DELETE');
	}
	
	public function __destruct() {
		$this->render = NULL;
		$this->routes = array();
		$this->viewVariables = array();
	}
	
	public function __call($method, $argv) {
		$argc = count($argv);
		$method = strtoupper($method);
		
		if ( $argc >= 2 && in_array($method, $this->requestMethods) ) {
			$route = $argv[0];
			$action = $argv[1];
			
			if ( $this->isValidRoute($route) && $this->isValidClosure($action) ) {
				$view = NULL;
				if ( $argc >= 3 ) {
					$view = $argv[2];
				}
		
				$routeObject = new \HobokenRoute($route, $action, $view);
				$this->routes[$method][] = $routeObject;
			}
		}
		
		return $this;
	}
	
	public function __set($k, $v) {
		$this->viewVariables[$k] = $v;
		return true;
	}
	
	public function __get($k) {
		if ( array_key_exists($k, $this->viewVariables) ) {
			return $this->viewVariables[$k];
		}
		return NULL;
	}
	
	public function setLayout($layout) {
		if ( 0 == preg_match("/{$this->ext}$/i", $layout) ) {
			$layout .= $this->ext;
		}
		$this->layout = $layout;
		return $this;
	}
	
	public function setLayoutDirectory($layoutDirectory) {
		$len = strlen($layoutDirectory)-1;
		if ( DIRECTORY_SEPARATOR != $layoutDirectory[$len] ) {
			$layoutDirectory .= DIRECTORY_SEPARATOR;
		}
		$this->layoutDirectory = $layoutDirectory;
		return $this;
	}
	
	public function setViewDirectory($viewDirectory) {
		$len = strlen($viewDirectory)-1;
		if ( DIRECTORY_SEPARATOR != $viewDirectory[$len] ) {
			$viewDirectory .= DIRECTORY_SEPARATOR;
		}
		$this->viewDirectory = $viewDirectory;
		return $this;
	}
	
	public function setSiteRoot($siteRoot) {
		$len = strlen($siteRoot)-1;
		if ( '/' != $siteRoot[$len] ) {
			$siteRoot .= '/';
		}
		$this->siteRoot = $siteRoot;
		return $this;
	}
	
	public function setSiteRootSecure($siteRootSecure) {
		$len = strlen($siteRootSecure)-1;
		if ( '/' != $siteRootSecure[$len] ) {
			$siteRootSecure .= '/';
		}
		$this->siteRootSecure = $siteRootSecure;
		return $this;
	}
	
	public function execute() {
		$requestMethod = NULL;
		if ( isset($_SERVER) && array_key_exists('REQUEST_METHOD', $_SERVER) ) {
			$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
		}
		
		$routes = array();
		if ( array_key_exists($requestMethod, $this->routes) ) {
			$routes = $this->routes[$requestMethod];
		}
		
		$uri = NULL;
		if ( isset($_REQUEST) && array_key_exists($this->uriParam, $_REQUEST) ) {
			$uri = $_REQUEST[$this->uriParam];
		}
		
		$foundRoute = false;
		foreach ( $routes as $routeObject ) {
			if ( $routeObject->canRouteUri($uri) ) {
				$foundRoute = true;
				break;
			}
		}
		
		if ( false === $foundRoute ) {
			$this->responseCode = 404;
		} else {
			$argv = $routeObject->getArgv();
			array_unshift($argv, $this);
			
			ob_start();
				$routeAction = new \ReflectionFunction($routeObject->getAction());
				$routeAction->invokeArgs($argv);
			$this->render = ob_get_clean();
		}
		
		$view = $routeObject->getView();
		if ( $this->isMissingRoute() ) {
			$view = '404';
		}
		
		header("Content-Type: {$this->contentType}", true, $this->responseCode);
		
		$layoutFile = "{$this->layoutDirectory}{$this->layout}";
		$viewFile = "{$this->viewDirectory}{$view}{$this->ext}";
		
		extract($this->viewVariables);
		if ( is_file($viewFile) ) {
			ob_start();
				require $viewFile;
			$this->render = ob_get_clean();
		}
		
		if ( is_file($layoutFile) ) {
			ob_start();
				require $layoutFile;
			$this->render = ob_get_clean();
		}
		
		return $this->render;
	}
	
	public function safe($v) {
		return htmlspecialchars($v, ENT_COMPAT, 'UTF-8');
	}
	
	public function href($url, $text) {
		$text = $this->safe($text);
		$href = '<a href="' . $url . '">' . $text . '</a>';
		return $href;
	}
	
	public function url() {
		$url = $this->createUrl(func_num_args(), func_get_args());
		$url = $this->siteRoot . $url;
		
		return $url;
	}
	
	public function urls() {
		$url = $this->createUrl(func_num_args(), func_get_args());
		$url = $this->siteRootSecure . $url;
		
		return $url;
	}
	
	private function createUrl($argc, $argv) {
		if ( 0 == $argc ) {
			return NULL;
		}
		
		$param = NULL;
		$loc = $argv[0];
		if ( $argc > 1 ) {
			$argv = array_slice($argv, 1);
			$param = '/' . implode('/', $argv);
		}
		
		$url = $loc . $param;
		
		return $url;
	}
	
	private function isMissingRoute() {
		return ( 404 == $this->responseCode );
	}
	
	private function isValidRoute($route) {
		/* Special case of a valid route. */
		if ( '/' == $route ) {
			return true;
		}
		
		/**
		 * Hoboken is more restrictive about routes than the normal Internet RFC
		 * standards. This is to keep them clean, legible and readible.
		 */
		if ( 0 === preg_match('#^/([a-z]+)([a-z0-9_\-/%\.]*)$#i', $route) ) {
			return false;
		}
		
		return true;
	}

	private function isValidClosure($closure) {
		return ( $closure instanceof \Closure );
	}
	
}