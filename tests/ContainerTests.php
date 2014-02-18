<?php

require_once __DIR__.'/../lib/Zit/Container.php';
require_once 'TestObj.php';
// No need to require if we're using phpunit.phar
// require_once 'PHPUnit/Framework/TestCase.php';

use Zit\Container;

/**
 * Container test case.
 */
class ContainerTests extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->container = new Container();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		$this->container = null;
		parent::tearDown();
	}
	
	public function testSetAndHas()
	{
		$c = $this->container;
		
		// Explicit Call
		$c->set('test', function() {});
		$this->assertTrue($c->has('test'));
		
		// Magic Call
		$c->setTest2(function() {});
		$this->assertTrue($c->hasTest2());
	}
	
	public function testSetParam()
	{
		$c = $this->container;
		
		// Explicit Call Only
		$c->setParam('test', 'testing');
		$this->assertEquals('testing', $c->get('test'));
	}
	
	public function testGet()
	{
		$c = $this->container;
		
		// Explicit Call
		$c->set('test', function($c, $name) {
			return new \Zit\TestObj($name);
		});
		$obj = $c->get('test', 'testing');
		$this->assertInstanceOf('\\Zit\\TestObj', $obj);
		$this->assertAttributeEquals('testing', 'name', $obj);
		
		// Magic Call
		$c->setAnotherTest(function($c, $name) {
			return new \Zit\TestObj($name);
		});
		$obj2 = $c->getAnotherTest('still testing');
		$this->assertInstanceOf('\\Zit\\TestObj', $obj2);
		$this->assertAttributeEquals('still testing', 'name', $obj2);
		
		// Sanity Check
		$this->assertNotSame($obj, $obj2);
	}
	
	public function testFresh()
	{
		$c = $this->container;
		
		$c->setObj(function($c, $name) {
			return new \Zit\TestObj($name);
		});
		
		$o1 = $c->fresh('obj', 'one');
		$o2 = $c->freshObj('Two');
		$o3 = $c->fresh_obj('Three');
		$o4 = $c->newObj('Four');
		$o5 = $c->new_obj('Five');
		
		$this->assertNotSame($o1, $o2);
		$this->assertNotSame($o1, $o3);
		$this->assertNotSame($o1, $o4);
		$this->assertNotSame($o1, $o5);
		
		$this->assertNotSame($o2, $o3);
		$this->assertNotSame($o2, $o4);
		$this->assertNotSame($o2, $o5);
		
		$this->assertNotSame($o3, $o4);
		$this->assertNotSame($o3, $o5);
		
		$this->assertNotSame($o4, $o5);
	}
	
	/*
	public function testDelete()
	{
		$c = $this->container;
		$c->setObj(function() { return new \stdClass(); });
		$c->deleteObj();
	}
	*/
	
	public function testDependency()
	{
		$c = $this->container;
		
		$c->setParent(function() {
			return new \stdClass();
		});
		
		$c->setChild(function($c) {
			$child = new \stdClass();
			$child->parent = $c->getParent();
			return $child;
		});
		
		$parent = $c->getParent();
		$child = $c->getChild();
		
		$this->assertSame($parent, $child->parent);
	}
	
	public function testConstructorArguments()
	{
		$c = $this->container;
		
		$c->setTestObj(function($c, $name) {
			return new \Zit\TestObj($name);
		});
		
		$o1 = $c->getTestObj('A');
		$o2 = $c->getTestObj();
		$o3 = $c->newTestObj('B');
		$o4 = $c->getTestObj('A');
		
		$this->assertAttributeEquals('A', 'name', $o1);
		$this->assertAttributeEquals('A', 'name', $o2);
		$this->assertAttributeEquals('B', 'name', $o3);
		$this->assertSame($o1, $o4);
	}
	
	public function testAlternateMethodFormat()
	{
		$c = $this->container;
		
		$c->set_with_underscores(function($c, $name) {
			return new \Zit\TestObj($name);
		});
		
		$obj = $c->get_with_underscores('Alternate');
		
		$this->assertInstanceOf('\\Zit\\TestObj', $obj);
		$this->assertAttributeEquals('Alternate', 'name', $obj);
	}
	
	public function testMixedMethodFormat()
	{
		$c = $this->container;
		
		$c->setObjectOne(function() {
			return new \Zit\TestObj('object one');
		});
		
		$obj = $c->get_object_one();
		
		$this->assertInstanceOf('\\Zit\\TestObj', $obj);
		$this->assertAttributeEquals('object one', 'name', $obj);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCallInvalidMethod() {
		$c = $this->container;
		$c->badmethodMe('test');
	}

	public function testSetWithFactory() {
		$c = $this->container;
		$c->set('factory', $c->factory(function () {
			return new \Zit\TestObj('factory');
		}));

		$obj1 = $c->get('factory');
		$this->assertInstanceOf('\\Zit\\TestObj', $obj1);

		$obj2 = $c->getFactory();
		$this->assertInstanceOf('\\Zit\\TestObj', $obj2);
		// Make sure the factory returned two separate instances
		$this->assertNotSame($obj1, $obj2);

		$obj3 = $c->newFactory();
		$this->assertInstanceOf('\\Zit\\TestObj', $obj3);

		$obj4 = $c->fresh('factory');
		$this->assertInstanceOf('\\Zit\\TestObj', $obj4);
		// Make sure the factory returned two separate instances
		$this->assertNotSame($obj2, $obj3);
		$this->assertNotSame($obj3, $obj4);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Callback for "invalid" does not exist.
	 */
	public function testFreshInvalidCallbackName() {
		$c = $this->container;
		$c->fresh('invalid');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Callback for "invalid" does not exist.
	 */
	public function testGetInvalidCallbackName() {
		$c = $this->container;
		$c->get('invalid');
	}

	public function testDelete() {
		$c = $this->container;
		$c->set('delete', function() { return new Zit\TestObj('delete'); });
		$obj1 = $c->get('delete');
		$obj2 = $c->get('delete');
		// Verify that we're getting a cached object
		$this->assertSame($obj1, $obj2);
		$this->assertTrue($c->delete('delete'));
		$obj3 = $c->get('delete');
		// We should get back a new instance
		$this->assertNotSame($obj1, $obj3);
	}

	public function testDeleteNoCache() {
		$c = $this->container;
		$c->set('delete', function() { return new Zit\TestObj('delete'); });
		$this->assertFalse($c->delete('delete'));
	}

}

