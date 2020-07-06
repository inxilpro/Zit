<?php

namespace Zit;

use PHPUnit\Framework\TestCase;
use Zit\Exception\MissingArgument;
use Zit\Exception\NotFoundException;

class ResolverTest extends TestCase
{
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Definition
     */
    protected $definition;

    public function setUp(): void
    {
        $this->definition = (new Definition('test', TestObj::class))->setParameter('name', 'changeme');
        $this->resolver   = new Resolver($this->container = new Container);
        $this->container->set('ref', 'resolved');
    }

    public function testReferenceReturnsProperFormat()
    {
        self::assertEquals('@@test@@', Resolver::reference('test'));
    }

    public function provideResolvedTests()
    {
        return [
            ['resolved', 'resolved'],
            ['resolved', '@@ref@@']
        ];
    }

    /**
     * @dataProvider provideResolvedTests
     */
    public function testResolve($expected, $payload)
    {
        $this->definition->setParameter('name', $payload);
        self::assertInstanceOf(TestObj::class, $obj = $this->resolver->resolve($this->definition));
        self::assertEquals($expected, $obj->name);
    }

    public function testResolveWithMethodCalls()
    {
        $this->definition->setMethodCall('setName', ['name' => 'method_call']);
        self::assertInstanceOf(TestObj::class, $obj = $this->resolver->resolve($this->definition));
        self::assertEquals('method_call', $obj->name);
    }

    public function testResolveThrowsExceptionWhenReferenceNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->resolver->resolve((new Definition('test', TestObj::class))->setParameter('name', '@@resolved@@'));
    }

    public function testResolveWithoutParams()
    {
        self::assertInstanceOf(
            TestObjNoConstructor::class,
            $this->resolver->resolve(new Definition('test', TestObjNoConstructor::class))
        );
    }

    public function testResolveWithAutoRegister()
    {
        $this->definition
            ->setParameter('name', Resolver::reference(TestObjNoConstructor::class));
        self::assertInstanceOf(TestObj::class, $obj = $this->resolver->resolve($this->definition));
        self::assertInstanceOf(TestObjNoConstructor::class, $obj->name);
    }

    public function testResolveWithTypedParameters()
    {
        $def = (new Definition('test', TestTypedObj::class));
        self::assertInstanceOf(TestTypedObj::class, $obj = $this->resolver->resolve($def));
        self::assertInstanceOf(TestObjNoConstructor::class, $obj->instance);
    }

    public function testResolveWithTypedParametersThrowsExceptionOnInvalidReference()
    {
        $def = (new Definition('test', TestObjNoConstructor::class))
            ->setMethodCall('setBadClass');
        $this->expectException(NotFoundException::class);
        $this->resolver->resolve($def);
    }

    public function testResolveWithTypedParametersSetsNull()
    {
        $def = (new Definition('test', TestObjNoConstructor::class))
            ->setMethodCall('setNullTypedInstance');
        self::assertInstanceOf(TestObjNoConstructor::class, $obj = $this->resolver->resolve($def));
        self::assertNull($obj->instance);
    }

    public function testResolveWithTypedMethodCall()
    {
        $def = (new Definition('test', TestObjNoConstructor::class))
            ->setMethodCall('setTypedInstance');
        self::assertInstanceOf(TestObjNoConstructor::class, $obj = $this->resolver->resolve($def));
        self::assertInstanceOf(TestObjNoConstructor::class, $obj->instance);
    }

    public function testResolveWithOptionalMethodCall()
    {
        $def = (new Definition('test', TestObjNoConstructor::class))
            ->setMethodCall('setOptionalValue');
        self::assertInstanceOf(TestObjNoConstructor::class, $obj = $this->resolver->resolve($def));
        self::assertEquals('kakaw', $obj->instance);
    }

    public function testResolveWithBuiltInValueReturnsNull()
    {
        $def = (new Definition('test', TestObjNoConstructor::class))
            ->setMethodCall('setBuiltInType');
        self::assertInstanceOf(TestObjNoConstructor::class, $obj = $this->resolver->resolve($def));
        self::assertNull($obj->name);
    }

    public function testResolveWithFactory()
    {
        $def = (new Definition('test', TestObjNoConstructor::class))
            ->setFactoryMethod('staticFactoy');
        self::assertEquals('static data', $obj = $this->resolver->resolve($def));
    }

    public function testResolveWithFactoryReference()
    {
        $this->container->register(TestObjNoConstructor::class);
        $def = (new Definition('test', Resolver::reference(TestObjNoConstructor::class)))
            ->setFactoryMethod('staticFactoy');
        self::assertEquals('static data', $obj = $this->resolver->resolve($def));
    }

    /**
     * NOTE: this is basically providing alias functionality
     */
    public function testResolveWithReference()
    {
        $this->container->register(TestObjNoConstructor::class);
        $this->container->register('test', Resolver::reference(TestObjNoConstructor::class));
        self::assertInstanceOf(TestObjNoConstructor::class, $this->container->get('test'));
    }
}