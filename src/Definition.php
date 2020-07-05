<?php

/**
 * @file
 * contains \Zit\Definition
 */

namespace Zit;

/**
 * Defines a service that can be used by Zit to create instances
 *
 * @package Zit
 */
class Definition
{
    /**
     * @var string The id of this definition
     */
    public $id;

    /**
     * @var string The class to use
     */
    public $class;

    /**
     * @var array List of constructor parameters
     */
    public $params = [];

    /**
     * @var array List of methods to call
     */
    public $methodCalls = [];

    public function __construct($id, $class)
    {
        $this->id    = $id;
        $this->class = $class;
    }

    public function setMethodCall(string $method, array $params = []): Definition
    {
        $this->methodCalls[$method] = [$params];

        return $this;
    }

    /**
     * Adds a method call to the list of methods to call after object construction
     *
     * @param string $method
     * @param array  $params
     * @return $this
     */
    public function addMethodCall(string $method, array $params): Definition
    {
        if (!isset($this->methodCalls[$method])) {
            return $this->setMethodCall($method, $params);
        }

        $this->methodCalls[$method][] = $params;

        return $this;
    }

    /**
     * Clears the calls for the given method
     *
     * @param string $method
     * @return $this
     */
    public function clearMethodCalls(string $method): Definition
    {
        unset($this->methodCalls[$method]);

        return $this;
    }

    /**
     * Clears all of the method calls that have been set
     *
     * @return $this
     */
    public function clearAllMethodCalls(): Definition
    {
        $this->methodCalls = [];

        return $this;
    }

    /**
     * Sets a constructor parameter
     *
     * @param string $param
     * @param mixed  $value
     * @return $this
     */
    public function setParameter(string $param, $value): Definition
    {
        $this->params[$param] = $value;

        return $this;
    }

    /**
     * Clears all constructor parameters
     *
     * @return $this
     */
    public function clearParameters(): Definition
    {
        $this->params = [];

        return $this;
    }

    /**
     * Clears the request parameter
     *
     * @param string $param
     * @return $this
     */
    public function clearParameter(string $param): Definition
    {
        unset($this->params[$param]);

        return $this;
    }
}
