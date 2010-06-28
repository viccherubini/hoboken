<?php

declare(encoding='UTF-8');
namespace HobokenTest;

class TestCase extends \PHPUnit_Framework_TestCase {
	
	public static function assertArray($a, $message = '') {
		self::assertThat(is_array($a), self::isTrue(), $message);
	}
	
	public static function assertEmptyArray($a, $message = '') {
		self::assertArray($a);
		self::assertEquals(0, count($a), $message);
	}
	
	public static function assertNotEmptyArray($a, $message = '') {
		self::assertArray($a, $message);
		self::assertGreaterThan(0, count($a), $message);
	}
}
