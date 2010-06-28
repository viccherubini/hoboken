<?php

declare(encoding='UTF-8');

require_once 'Hoboken/Hoboken.php';
require_once 'Hoboken/Exception.php';

$hoboken = new \Hoboken;
$hoboken->layout = 'hoboken';
$hoboken->layoutDirectory = 'public/layout/';
$hoboken->viewDirectory = 'public/view/';

try {

	$hoboken->GET('/', function($self) {
		echo 'hi there';
	}, 'index');


	$hoboken->GET('/hire-me', function() {
		
	}, 'hire-me');

} catch ( HobokenException $e ) {
	
	echo $e->getMessage();
}