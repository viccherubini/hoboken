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
	
	/**
	 * @dataProvider providerValidRoute
	 */
	public function testIsValidRoute_ValidRouteReturnsTrue($routeName) {
		$hoboken = new Hoboken;
		
		$this->assertTrue($hoboken->isValidRoute($routeName));
	}
	
	/**
	 * @dataProvider providerInvalidRoute
	 */
	public function testIsValidRoute_InvalidRouteReturnsFalse($routeName) {
		$hoboken = new Hoboken;
		
		$this->assertFalse($hoboken->isValidRoute($routeName));
	}
	
	/**
	 * @dataProvider providerValidRouteAndUri
	 */
	public function testCanRouteUri_WithValidRoute($routeName, $uri) {
		$hoboken = new Hoboken;
		
		$this->assertTrue($hoboken->canRouteUri($routeName, $uri));
	}
	
	/**
	 * @dataProvider providerInvalidRouteAndUri
	 */
	public function testCanRouteUri_FailsWithInvalidRoute($routeName, $uri) {
		$hoboken = new Hoboken;
	
		$this->assertFalse($hoboken->canRouteUri($routeName, $uri));
	}
	
	/**
	 * @dataProvider providerInvalidRoute
	 * @expectedException \Exception
	 */
	public function testGET_CanOnlyAddValidRoute($routeName) {
		$hoboken = new Hoboken;
		$hoboken->GET($routeName, function() {});
	}
	
	/**
	 * @expectedException \Exception
	 */
	public function testGET_CanOnlyAddClosuresAsActions() {
		$hoboken = new Hoboken;
		$hoboken->GET('/', 'abc');
	}
	
	public function testGET_CanAddValidRouteAndAction() {
		$hoboken = new Hoboken;
		$hoboken->GET('/', function() { echo 'hi'; });
	}
	//public function testExecute
	
	
	public function providerValidRoute() {
		return array(
			array('/'),
			array('/abc'),
			array('/abc9'),
			array('/long_route'),
			array('/long-route'),
			array('/abc99'),
			array('/long_route/'),
			array('/abc/'),
			array('/abc0/'),
			array('/abc/def'),
			array('/abc/def/'),
			array('/abc/%n/'),
			array('/abc/def/efg/%n'),
			array('/abc/def/%s/%n'),
			array('/abc.def/%s/%n'),
			array('/abc/usr/%n/blah/%s'),
			array('/tutorial/%s.html'),
			array('/search/result-%n.html'),
			array('/abc./'),
			array('/abc.')
		);
	}
	
	public function providerInvalidRoute() {
		return array(
			array('//'),
			array('///'),
			array('/abc/*'),
			array('/abc/*/'),
			array('/abc/*/d'),
			array('/abc/*/d/'),
			array('abc')
		);
	}
	
	public function providerInvalidArray() {
		return array(
			array('abc'),
			array(10),
			array(10.45),
			array(new \stdClass)
		);
	}
	
	public function providerValidRouteAndUri() {
		return array(
			array('/abc', '/abc'),
			array('/user/view', '/user/view'),
			array('/user-long-route', '/user-long-route'),
			array('/abc/usr/%n/blah/%s', '/abc/usr/10/blah/hello'),
			array('/abc/usr/%n/blah/%s', '/abc/usr/1/blah/hello-world'),
			array('/abc/usr/%n/blah/%s', '/abc/usr/1/blah/hello world'),
			array('/tutorial/%s.html', '/tutorial/opengl-tutorial.html'),
			array('/tutorial/%s.html', '/tutorial/the-#named#-tutorial.html'),
			array('/tutorial/%s.html', "/tutorial/a tutorial about %s's.html"),
			array('/user/%n.html', '/user/10.html'),
			array('/user/%n.html', '/user/1.html'),
			array('/search/result-%n.html', '/search/result-10.html'),
			array('/search/result-%n.html', '/search/result-101345.html'),
			array('/add/balance/%n', '/add/balance/10.45')
		);
	}
	
	public function providerInvalidRouteAndUri() {
		return array(
			array('/abc', '/def'),
			array('/user/view', '/usr/view'),
			array('/user-long-route', '/user_long_route'),
			array('/abc/usr/%n/blah/%s', '/abc/usr/hello/blah/10'),
			array('/abc/usr/%n/blah/%s', '/abc/usr/hello-world/blah/10'),
			array('/abc/usr/%n/blah/%s', '/abc/usr/hello world/blah/1'),
			array('/tutorial/%s.html', '/tutorial/10.html'),
			array('/user/%n.html', '/user/user-vic.html'),
			array('/user/%s.html', '/user/1.html'),
			array('/search/result-%n.html', '/search/result-search-string.html'),
			array('/add/balance/%n', '/add/balance/some-amount')
		);
	}
	
	
}