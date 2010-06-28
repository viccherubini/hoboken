<?php

declare(encoding='UTF-8');

require_once 'Hoboken/Hoboken.php';
require_once 'Hoboken/Exception.php';

$hoboken = new \Hoboken;
$hoboken->layout = 'leftnode';
$hoboken->layoutDirectory = 'public/layout/';
$hoboken->viewDirectory = 'public/view/';

try {

	$hoboken->GET('/', function($self) {
		$self->blah = "some value";
	}, 'index');

	$hoboken->GET('/hire-me/%n', function($self, $id) {
		$self->id = $id;
	}, 'hire-me');
	
	$hoboken->GET('/no-layout', function($self) {
		$self->layout = NULL;
	}, 'no-layout');
	
	$hoboken->POST('/hire-me', function($self) {
		$self->layout = NULL;
		echo 'you want to hire me by posting me';
	});

	echo $hoboken->execute();
	
} catch ( HobokenException $e ) {
	echo $e->getMessage();
}