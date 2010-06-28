<?php

declare(encoding='UTF-8');
namespace HobokenTest;

use \Hoboken;

require_once 'Hoboken/Hoboken.php';

class HobokenTest extends TestCase {

	public function testMagicSetter_AddsVariableToExtractList() {
		$value = "some value";
		
		$hoboken = new Hoboken;
		$hoboken->some_key = $value;
		
		$this->assertEquals($value, $hoboken->some_key);
	}
	
	public function testMagicGetter_ReturnsNullForUnsetVariable() {
		$hoboken = new Hoboken;
		
		$this->assertEquals(NULL, $hoboken->invalid_key);
	}
	
	public function testSetLayout_CanSetTheLayoutName() {
		$hoboken = new Hoboken;
		
		$this->assertHoboken($hoboken->setLayout('leftnode'));
	}
	
	public function testSetLayoutDirectory_CanSetTheLayoutDirectory() {
		$hoboken = new Hoboken;
		
		$this->assertHoboken($hoboken->setLayoutDirectory('public/layout/'));
	}
	
	public function testSetViewDirectory_CanSetTheViewDirectory() {
		$hoboken = new Hoboken;
		
		$this->assertHoboken($hoboken->setViewDirectory('public/view/'));
	}
	
	/**
	 * @expectedException PHPUnit_Framework_Error
	 * @dataProvider providerInvalidArray
	 */
	public function testSetRouteSource_MustBeArray($invalidSource) {
		$hoboken = new Hoboken;
		
		$hoboken->setRouteSource($invalidSource);
	}
	
	public function testSetRouteSource_CanSetTheRouteSource() {
		$hoboken = new Hoboken;
		
		$this->assertHoboken($hoboken->setRouteSource(array('key' => 'value')));
	}
	
	/**
	 * @expectedException PHPUnit_Framework_Error
	 * @dataProvider providerInvalidArray
	 */
	public function testSetServerParams_MustBeArray($invalidParams) {
		$hoboken = new Hoboken;
		
		$hoboken->setServerParams($invalidParams);
	}
	
	public function testSetServerParams_CanSetTheServerParams() {
		$hoboken = new Hoboken;
		
		$this->assertHoboken($hoboken->setServerParams(array('key' => 'value')));
	}
	
	
	
	
	public function providerInvalidArray() {
		return array(
			array('abc'),
			array(10),
			array(10.45),
			array(new \stdClass)
		);
	}
}