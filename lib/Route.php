<?php

declare(encoding='UTF-8');

class HobokenRoute {

	private $argv = array();
	private $route = NULL;
	private $action = NULL;
	private $view = NULL;

	public function __construct($route, Closure $action, $view) {
		$this->route = $route;
		$this->action = $action;
		$this->view = $view;
	}
	
	public function getArgv() {
		return $this->argv;
	}
	
	public function getRoute() {
		return $this->route;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function getView() {
		return $this->view;
	}
	
	public function canRouteUri($uri) {
		// Remove the beginning / from the URI and route.
		$uri = ltrim($uri, '/');
		$uriChunkList = explode('/', $uri);
		$uriChunkCount = count($uriChunkList);
		
		$route = $this->getRoute();
		$route = ltrim($route, '/');
		$routeChunkList = explode('/', $route);
		$routeChunkCount = count($routeChunkList);

		// If all of the chunks eventually match, we have a matched route.
		$matchedChunkCount = 0;

		// List of arguments to pass to the action method.
		$argv = array();

		if ( $uriChunkCount === $routeChunkCount ) {
			for ( $i=0; $i<$routeChunkCount; $i++ ) {
				// ucv == uri chunk value
				$ucv = $uriChunkList[$i];
				
				// rcv = route chunk value
				$rcv = $routeChunkList[$i];

				if ( $ucv == $rcv ) {
					// If the two are exactly the same, no expansion is needed.
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
						
						// Now that we have the correct $ucv values, let's make sure they're types are correct.
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
							$argv[] = $ucv;
						}
					}
				}
			}
		}
		
		$this->argv = $argv;
	
		return ( $matchedChunkCount === $routeChunkCount );
	}
}