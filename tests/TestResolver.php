<?php

namespace Zit;

class TestResolver
    extends Resolver
{
    public $calledResolve = false;
    public function resolve(Definition $definition)
    {
        throw new \RuntimeException("not resolving");
    }
}