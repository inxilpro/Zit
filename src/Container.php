<?php

/**
 * @file
 * contains \Zit\Container
 */

namespace Zit;

use Interop\Container\ContainerInterface;
use Zit\Exception\NotFoundException;

/**
 * Zit Container
 *
 * @package Zit
 */
class Container implements ContainerInterface
{
    /**
     * @var array Instantiated objects
     */
    protected $objects = array();

    /**
     * @var array Instantiation functions
     */
    protected $callbacks = array();

    /**
     * @var array Keys marked as factories (always return fresh)
     */
    protected $factories = array();

    /**
     * Handles magic methods
     *
     * @param       $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments = array())
    {
        // Parse function name
        preg_match_all('/_?([A-Z][a-z0-9]*|[a-z0-9]+)/', $name, $parts);
        $parts = array_map('strtolower', $parts[1]);

        // Determine method
        $method = array_shift($parts);
        if ('new' == $method) {
            $method = 'fresh';
        }

        // Handle 'Factory' alternatives
        if ('set' == $method && 'factory' == end($parts)) {
            array_pop($parts);
            $method = 'setFactory';
        }
        if ('delete' == $method && 'factory' == end($parts)) {
            array_pop($parts);
        }

        // Determine object key
        $key = implode('_', $parts);
        array_unshift($arguments, $key);

        // Call method if exists
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }

        // Throw exception on miss
        throw new \InvalidArgumentException(sprintf('Method "%s" does not exist.', $method));
    }

    /**
     * Set an item in the container
     *
     * @param string $name             Name of item
     * @param mixed  $callableOrStatic If is callable, this will be set as the instantiation function for this $name,
     *                                 otherwise, it will be used as the value ->get($name) returns
     * @return $this
     */
    public function set($name, $callableOrStatic)
    {
        if (!is_callable($callableOrStatic)) {
            $value            = $callableOrStatic;
            $callableOrStatic = function () use ($value) {
                return $value;
            };
        }

        $this->callbacks[$name] = $callableOrStatic;

        return $this;
    }

    /**
     * Set a factory in the container (always creates a fresh item)
     *
     * @param string    $name
     * @param \callable $callable
     * @return $this
     */
    public function setFactory($name, callable $callable)
    {
        $this->factories[$name] = true;

        return $this->set($name, $callable);
    }

    /**
     * Check to see if an item is set
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->callbacks[$name]);
    }

    /**
     * Get an item
     *
     * On first call, this will defer to fresh()
     * On all subsequent calls, this will return the already instantiated item (unless it was set as a factory)
     * All arguments after the first are passed to the instantiation function
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        // Return object if it's already instantiated
        if (isset($this->objects[$name])) {
            $args = func_get_args();
            array_shift($args);

            $key = $this->keyForArguments($args);
            if ('_no_arguments' == $key && !isset($this->objects[$name][$key]) && !empty($this->objects[$name])) {
                $key = key($this->objects[$name]);
            }

            if (isset($this->objects[$name][$key])) {
                return $this->objects[$name][$key];
            }
        }

        // Otherwise create a new one
        return call_user_func_array(array($this, 'fresh'), func_get_args());
    }

    /**
     * Get a new item (run instantiation function regardless of cache)
     *
     * @param string $name
     * @return mixed
     */
    public function fresh($name)
    {
        if (!isset($this->callbacks[$name])) {
            throw new NotFoundException(sprintf('Callback for "%s" does not exist.', $name));
        }

        $arguments    = func_get_args();
        $arguments[0] = $this;
        $key          = $this->keyForArguments($arguments);
        $obj          = call_user_func_array($this->callbacks[$name], $arguments);

        // Store object if it's not defined as a factory
        if (!isset($this->factories[$name])) {
            $this->objects[$name][$key] = $obj;
        }

        return $obj;
    }

    /**
     * Delete an instantiation function & all associated objects
     *
     * @param string $name
     * @return bool
     */
    public function delete($name)
    {
        $deleted = false;

        // Delete Objects
        if (isset($this->objects[$name])) {
            unset($this->objects[$name]);
            $deleted = true;
        }

        // Delete Callbacks
        if (isset($this->callbacks[$name])) {
            unset($this->callbacks[$name]);
            $deleted = true;
        }

        // Delete Factories
        if (isset($this->factories[$name])) {
            unset($this->factories[$name]);
            $deleted = true;
        }

        return $deleted;
    }

    /**
     * Generate a key for given arguments
     *
     * @param array $arguments
     * @return string
     */
    protected function keyForArguments(array $arguments)
    {
        if (count($arguments) && $this === $arguments[0]) {
            array_shift($arguments);
        }

        if (0 == count($arguments)) {
            return '_no_arguments';
        }

        // md4 is slightly faster than md5
        return hash('md4', serialize($arguments));
    }
}