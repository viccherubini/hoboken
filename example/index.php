<?php

declare(encoding='UTF-8');

require_once 'Hoboken/Framework.php';

try {
	$hoboken = new \Hoboken;
	$hoboken->setLayout('leftnode.phtml')
		->setLayoutDirectory('public/layout/')
		->setViewDirectory('public/view/')
		->setSiteRoot('http://hoboken.dev')
		->setSiteRootSecure('https://hoboken.dev');

	$hoboken->GET('/', function($self) {
		$self->blah = "some value";
	}, 'index');
	
	$hoboken->GET('/page.html', function($self) {
		echo 'viewing a page';
	});

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