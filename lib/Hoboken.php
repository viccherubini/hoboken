<?php

declare(encoding='UTF-8');

class Hoboken {
	
	public $argv = array();
	public $contentType = 'text/html';
	public $debug = false;
	public $ext = '.phtml';
	public $layout = NULL;
	public $layoutDirectory = NULL;
	public $render = NULL;
	public $requestMethods = array();
	public $responseCode = 200;
	public $routes = array();
	public $uriParam = '__u';
	public $viewDirectory = NULL;
	public $viewVariables = array();
	
	
	
	
	
	
	
	public function __construct() {
		$this->requestMethods = array('GET', 'POST', 'PUT', 'DELETE');
	}
	
	public function __destruct() {
		
	}
	
	public function __call($method, $argv) {
		$argc = count($argv);
		$method = strtoupper($method);
		
		if ( $argc >= 2 && in_array($method, $this->requestMethods) ) {
			$route = $argv[0];
			$action = $argv[1];
			
			if ( !$this->isValidRoute($route) ) {
				throw new \HobokenException("The route {$route} is invalid.");
			}
			
			if ( !$this->isClosure($action) ) {
				throw new \HobokenException("The action is not a closure.");
			}

			$view = NULL;
			if ( $argc >= 3 ) {
				$view = $argv[2];
			}
		
			$this->routes[$method][$route] = array(
				'action' => $action,
				'view' => $view
			);
		}
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

	
	public function execute() {
		if ( 0 === count($this->routes) ) {
			throw new \HobokenException("The Route List is empty. Please add at least one route.");
		}
		
		if ( !empty($this->layoutDirectory) && !is_dir($this->layoutDirectory) ) {
			throw new \HobokenException("The layout directory {$this->layoutDirectory} can not be found on the filesystem.");
		}
		
		if ( !empty($this->viewDirectory) && !is_dir($this->viewDirectory) ) {
			throw new \HobokenException("The view directory {$this->layoutDirectory} can not be found on the filesystem.");
		}
		
		$requestMethod = NULL;
		if ( isset($_SERVER) && array_key_exists('REQUEST_METHOD', $_SERVER) ) {
			$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
		}
		
		$routes = array();
		if ( array_key_exists($requestMethod, $this->routes) ) {
			$routes = $this->routes[$requestMethod];
		}
		
		if ( 0 === count($routes) ) {
			throw new \HobokenException("There are no routes configured for the {$requestMethod} request method.");
		}
		
		$uri = NULL;
		if ( isset($_REQUEST) && array_key_exists($this->uriParam, $_REQUEST) ) {
			$uri = $_REQUEST[$this->uriParam];
		}
		
		$foundRoute = false;
		
		foreach ( $routes as $route => $actionable ) {
			if ( $this->canRouteUri($route, $uri) ) {
				$foundRoute = true;
				break;
			}
		}
		
		
		if ( false === $foundRoute ) {
			$this->responseCode = 404;
		} else {
			array_unshift($this->argv, $this);
			
			$action = new \ReflectionFunction($actionable['action']);
			$action->invokeArgs($this->argv);
		}
		
		$view = $actionable['view'];
		if ( 404 == $this->responseCode ) {
			$view = '404';
		}
		
		header("Content-Type: {$this->contentType}", true, $this->responseCode);
		
		$layoutFile = "{$this->layoutDirectory}{$this->layout}{$this->ext}";
		$viewFile = "{$this->viewDirectory}{$view}{$this->ext}";
		
		if ( is_file($viewFile) ) {
			extract($this->viewVariables);
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

	private function isClosure($closure) {
		return ( $closure instanceof \Closure );
	}
}