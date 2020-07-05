<?php

namespace Zit;

use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    /**
     * @var Definition
     */
    protected $definition;

    public function setUp(): void
    {
        $this->definition              = new Definition('test', 'test');
        $this->definition->methodCalls = [
            'one' => [
                ['one' => 1]
            ],
            'two' => [
                ['two' => 2]
            ]
        ];
    }

    public function testSetAndClearMethodCall()
    {

        $expected = $this->definition->methodCalls;
        $expected['test'][] = ['three' => 3];

        self::assertSame($this->definition, $this->definition->setMethodCall('test', ['three' => 3]));
        self::assertEquals($expected, $this->definition->methodCalls);

        unset($expected['test']);
        self::assertSame($this->definition, $this->definition->clearMethodCalls('test'));
        self::assertEquals($expected, $this->definition->methodCalls);
    }
    public function testAddMethodCall()
    {
        $expected = $this->definition->methodCalls;
        $expected['one'][] = ['three' => 3];

        self::assertSame($this->definition, $this->definition->addMethodCall('one', ['three' => 3]));
        self::assertEquals($expected, $this->definition->methodCalls);
    }

    public function testAddMethodCallFirstTime()
    {
        $expected = $this->definition->methodCalls;
        $expected['test'][] = ['three' => 3];

        self::assertSame($this->definition, $this->definition->addMethodCall('test', ['three' => 3]));
        self::assertEquals($expected, $this->definition->methodCalls);
    }

    public function testClearAllMethodCalls()
    {
        self::assertSame($this->definition, $this->definition->clearAllMethodCalls());
        self::assertEmpty($this->definition->methodCalls);
    }

    public function testSetAndClearParameter()
    {
        self::assertSame($this->definition, $this->definition->setParameter('param', 'test'));
        self::assertEquals(['param' => 'test'], $this->definition->params);

        self::assertSame($this->definition, $this->definition->clearParameter('param'));
        self::assertEmpty($this->definition->params);
    }

    public function testClearParameters()
    {
        $this->definition->params['test'] = 'test';
        self::assertSame($this->definition, $this->definition->clearParameters());
        self::assertEmpty($this->definition->params);
    }
}