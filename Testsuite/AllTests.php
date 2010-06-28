<?php

declare(encoding='UTF-8');
namespace HobokenTest;

require_once 'MiscTest.php';

class AllTests {
	public static function suite() {
		$suite = new \PHPUnit_Framework_TestSuite('Hoboken Tests');

		$suite->addTestSuite('\HobokenTest\MiscTest');
		
		return $suite;
	}
}
