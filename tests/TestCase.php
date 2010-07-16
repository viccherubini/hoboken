<?php

declare(encoding='UTF-8');
namespace HobokenTest;

use \Hoboken;

class TestCase extends \PHPUnit_Framework_TestCase {
	
	public static function assertArray($a, $message = NULL) {
		self::assertThat(is_array($a), self::isTrue(), $message);
	}
	
	public static function assertEmptyArray($a, $message = NULL) {
		self::assertArray($a);
		self::assertEquals(0, count($a), $message);
	}
	
	public static function assertNotEmptyArray($a, $message = NULL) {
		self::assertArray($a, $message);
		self::assertGreaterThan(0, count($a), $message);
	}
	
	public static function assertHoboken($obj, $message = NULL) {
		self::assertTrue(is_object($obj));
		self::assertTrue($obj instanceof \Hoboken);
	}
}