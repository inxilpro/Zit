<?php

namespace Zit;

class TestTypedObj
{
    public $instance;
    public $methodInstance;

    public function __construct(TestObjNoConstructor $instance)
    {
        $this->instance = $instance;
    }

    public function setInstance(TestObjNoConstructor $instance)
    {
        $this->methodInstance = $instance;
    }
}