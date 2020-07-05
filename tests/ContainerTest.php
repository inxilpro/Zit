<?php

namespace Zit;

use PHPUnit\Framework\TestCase;

/**
 * Container test case.
 */
class ContainerTests extends TestCase
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp(): void
	{
		$this->container = new Container();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown(): void
	{
		$this->container = null;
	}

	public function testSetAndHas()
	{
		$c = $this->container;

		// Explicit Call
		$c->set('test', function() {});
		self::assertTrue($c->has('test'));

		// Magic Call
		$c->setTest2(function() {});
		self::assertTrue($c->hasTest2());
	}

	public function testSetParam()
	{
		$c = $this->container;

		$c->set('test', 'testing');
		self::assertEquals('testing', $c->get('test'));

		$o = new \stdClass();
		$c->set('test2', $o);
		self::assertSame($o, $c->get('test2'));

		$c->setString('test3');
		self::assertEquals('test3', $c->get('string'));
	}

	public function testGet()
	{
		$c = $this->container;

		// Explicit Call
		$c->set('test', function($c, $name) {
			return new TestObj($name);
		});
		$obj = $c->get('test', 'testing');
		self::assertInstanceOf(TestObj::class, $obj);
		self::assertEquals('testing', $obj->name);

		// Magic Call
		$c->setAnotherTest(function($c, $name) {
			return new TestObj($name);
		});
		$obj2 = $c->getAnotherTest('still testing');
		self::assertInstanceOf(TestObj::class, $obj2);
		self::assertEquals('still testing', $obj2->name);

		// Sanity Check
		self::assertNotSame($obj, $obj2);
	}

	public function testFresh()
	{
		$c = $this->container;

		$c->setObj(function($c, $name) {
			return new TestObj($name);
		});

		$o1 = $c->fresh('obj', 'one');
		$o2 = $c->freshObj('Two');
		$o3 = $c->fresh_obj('Three');
		$o4 = $c->newObj('Four');
		$o5 = $c->new_obj('Five');

		self::assertNotSame($o1, $o2);
		self::assertNotSame($o1, $o3);
		self::assertNotSame($o1, $o4);
		self::assertNotSame($o1, $o5);

		self::assertNotSame($o2, $o3);
		self::assertNotSame($o2, $o4);
		self::assertNotSame($o2, $o5);

		self::assertNotSame($o3, $o4);
		self::assertNotSame($o3, $o5);

		self::assertNotSame($o4, $o5);
	}

	public function testFactory()
	{
		$c = $this->container;

		$c->setObjFactory(function($c, $name) {
			return new TestObj($name);
		});

		$o1 = $c->newObj('pizza');
		$o2 = $c->newObj('pizza');

		self::assertNotSame($o1, $o2);

		$c->setFactory('obj2', function($c, $name) {
			return new TestObj($name);
		});

		$o3 = $c->newObj2('pizza');
		$o4 = $c->newObj2('pizza');

		self::assertNotSame($o3, $o4);
	}

	public function testDelete()
	{
		$c = $this->container;
		$c->setObj(function() { return new \stdClass(); });

		self::assertInstanceOf('\\stdClass',$c->getObj());

		$c->deleteObj();

		try {
			$c->getObj();
		} catch (\InvalidArgumentException $e) {
			return;
		}

		$this->fail();
	}

	public function testDeleteFactory()
	{
		$c = $this->container;
		$c->setObjFactory(function() { return new \stdClass(); });

		$a = $c->getObj();
		$b = $c->getObj();

		self::assertNotSame($a, $b);

		$c->deleteObjFactory();
		$c->setObj(function() { return new \stdClass(); });

		$a = $c->getObj();
		$b = $c->getObj();

		self::assertSame($a, $b);
	}

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

		self::assertSame($parent, $child->parent);
	}

	public function testConstructorArguments()
	{
		$c = $this->container;

		$c->setTestObj(function($c, $name) {
			return new TestObj($name);
		});

		$o1 = $c->getTestObj('A');
		$o2 = $c->getTestObj();
		$o3 = $c->newTestObj('B');
		$o4 = $c->getTestObj('A');

		self::assertEquals('A', $o1->name);
		self::assertEquals('A', $o2->name);
		self::assertEquals('B', $o3->name);
		self::assertSame($o1, $o4);
	}

	public function testAlternateMethodFormat()
	{
		$c = $this->container;

		$c->set_with_underscores(function($c, $name) {
			return new TestObj($name);
		});

		$obj = $c->get_with_underscores('Alternate');

		self::assertInstanceOf(TestObj::class, $obj);
		self::assertEquals('Alternate', $obj->name);
	}

	public function testMixedMethodFormat()
	{
		$c = $this->container;

		$c->setObjectOne(function() {
			return new TestObj('object one');
		});

		$obj = $c->get_object_one();

		self::assertInstanceOf(TestObj::class, $obj);
		self::assertEquals('object one', $obj->name);
	}

	public function testInvalidArgumentMethodDoesNotExist()
	{
		$c = $this->container;

        $this->expectException(\InvalidArgumentException::class);
		$c->somethingThatDoesNotExist();
	}

	public function testRegister()
    {
        $this->container->register(TestObj::class)->params['name'] = 'registration';

        self::assertTrue($this->container->has(TestObj::class));
        self::assertInstanceOf(TestObj::class, $obj = $this->container->get(TestObj::class));
        self::assertEquals('registration', $obj->name);
    }

    public function testMagicRegisterThrowsException()
    {
        $this->expectException(Exception\Container::class);
        $this->container->registerTestObj();
    }
}
