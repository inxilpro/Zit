<?php

namespace Zit\Fixture;

use Zit\Definition;
use Zit\Resolver;

class TestResolver
    extends Resolver
{
    public $calledResolve = false;
    public function resolve(Definition $definition)
    {
        throw new \RuntimeException("not resolving");
    }
}