<?php

namespace Zit;

use PHPUnit\Framework\TestCase;
use Zit\Fixture\TestObj;

class CompilerTest extends TestCase
{
    public function testSerialization()
    {
        $zit = new Container();
        $d   = $zit->register(TestObj::class);
        $d->setParameter('name', 'kakaw');

        $serialized = serialize($zit);
        $unzit      = unserialize($serialized);

        self::assertInstanceOf(TestObj::class, $unzit->get(TestObj::class));
    }
}
