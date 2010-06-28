<?php

declare(encoding='UTF-8');

class Hoboken {
	
	private $actionArgv = array();
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
	
	public function GET($route, $action, $view = NULL) {
		$this->addRouteAndAction(self::METHOD_GET, $route, $action, $view);
		return $this;
	}
	
	public function POST($route, $action, $view = NULL) {
		$this->addRouteAndAction(self::METHOD_POST, $route, $action, $view);
		return $this;
	}
	
	
	
	
	
	

	public function isValidRoute($route) {
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

	public function canRouteUri($route, $uri) {
		/* Remove the beginning / from the URI and route. */
		$uri = ltrim($uri, '/');
		$uriChunkList = explode('/', $uri);
		$uriChunkCount = count($uriChunkList);
		
		$route = ltrim($route, '/');
		$routeChunkList = explode('/', $route);
		$routeChunkCount = count($routeChunkList);
		
		/* If all of the chunks eventually match, we have a matched route. */
		$matchedChunkCount = 0;

		/* List of arguments to pass to the action method. */
		$argv = array();

		if ( $uriChunkCount === $routeChunkCount ) {
			for ( $i=0; $i<$routeChunkCount; $i++ ) {
				/* ucv == uri chunk value */
				$ucv = $uriChunkList[$i];
				
				/* rcv = route chunk value */
				$rcv = $routeChunkList[$i];

				if ( $ucv == $rcv ) {
					/* If the two are exactly the same, no expansion is needed. */
					$matchedChunkCount++;
				} else {
					/**
					 * More investigation is required. See if there is a % character followed by a (n|s), and if so, expand it.
					 * A limitation is that only a single % replacement can exist in a chunk at once, for now.
					 * 
					 * @todo Allow multiple %n or %s characters in a chunk at once.
					 */
					$offset = stripos($rcv, '%');
					if ( false !== $offset && true === isset($rcv[$offset+1]) ) {
						$rcvType = $rcv[$offset+1];
						$rcvLength = strlen($rcv);
						
						if ( 0 !== $offset ) {
							$ucv = trim(substr_replace($ucv, NULL, 0, $offset));
						}
						
						if ( ($offset+2) < $rcvLength ) {
							$goto = strlen(substr($rcv, $offset+2));
							$ucv = substr_replace($ucv, NULL, -$goto);
						}
						
						/* Now that we have the correct $ucv values, let's make sure they're types are correct. */
						$matched = false;
						
						switch ( $rcvType ) {
							case 'n': {
								$matched = is_numeric($ucv);
								break;
							}
							
							case 's': {
								$matched = is_string($ucv) && !is_numeric($ucv);
								break;
							}
						}
						
						if ( true === $matched ) {
							$matchedChunkCount++;
							$argv[$rcv] = $ucv;
						}
					}
				}
			}
		}
		
		$this->argv = $argv;
	
		return ( $matchedChunkCount === $routeChunkCount );
	}
	
	private function addRouteAndAction($requestMethod, $route, $action, $view) {
		$this->hasValidRoute($route);
		$this->hasClosure($action);
		
		$routeObject = new \stdClass;
		$routeObject->route = $route;
		$routeObject->action = $action;
		$routeObject->view = $view;
		
		$this->actionList[$requestMethod][] = $routeObject;
		
	}
	
	private function hasValidRoute($route) {
		if ( !$this->isValidRoute($route) ) {
			throw new \Exception("The route {$route} is invalid and can not be properly translated.");
		}
	}
	
	private function isClosure($closure) {
		return ( $closure instanceof \Closure );
	}
	
	private function hasClosure($closure) {
		if ( !$this->isClosure($closure) ) {
			throw new \Exception("The action is invalid because it is not a closure.");
		}
	}
}