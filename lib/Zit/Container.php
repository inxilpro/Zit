<?php

namespace Zit;

class Container
{
	protected $_objects = array();
	protected $_callbacks = array();
	protected $_factories = array();
	
	public function __call($name, $arguments = array())
	{
		// Parse function name
		preg_match_all('/_?([A-Z][a-z0-9]*|[a-z0-9]+)/', $name, $parts);
		$parts = $parts[1];
		
		// Determine method
		$method = array_shift($parts);		
		if ('new' == $method) {
			$method = 'fresh';
		}

		// Handle 'Factory' alternative
		if ('set' == $method && 'factory' == end($parts)) {
			array_pop($parts);
			$method = 'setFactory';
		}
		
		// Determine object key
		$key = strtolower(implode('_', $parts));
		array_unshift($arguments, $key);
		
		// Call method if exists
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $arguments);
		}
		
		// Throw exception on miss
		throw new \InvalidArgumentException(sprintf('Methood "%s" does not exist.', $method));
	}
	
	public function set($name, \Closure $callable)
	{
		$this->_callbacks[$name] = $callable;
	}
	
	public function setParam($name, $param)
	{
		$this->set($name, function() use ($param) {
			return $param;
		});
	}

	public function setFactory($name, \Closure $callable)
	{
		$this->_factories[$name] = true;
		return $this->set($name, $callable);
	}
	
	public function has($name)
	{
		return isset($this->_callbacks[$name]);
	}
	
	public function get($name)
	{
		// Return object if it's already instantiated
		if (isset($this->_objects[$name])) {
			$args = func_get_args();
			array_shift($args);
			
			$key = $this->_keyForArguments($args);
			if ('_no_arguments' == $key && !isset($this->_objects[$name][$key]) && !empty($this->_objects[$name])) {
				$key = key($this->_objects[$name]);
			}
			
			if (isset($this->_objects[$name][$key])) {
				return $this->_objects[$name][$key];
			}
		}
		
		// Otherwise create a new one
		return call_user_func_array(array($this, 'fresh'), func_get_args());
	}
	
	public function fresh($name)
	{
		if (!isset($this->_callbacks[$name])) {
			throw new \InvalidArgumentException(sprintf('Callback for "%s" does not exist.', $name));
		}
		
		$arguments = func_get_args();
		$arguments[0] = $this;
		$key = $this->_keyForArguments($arguments);
		$obj = call_user_func_array($this->_callbacks[$name], $arguments);

		// Store object if it's not defined as a factory
		if (!isset($this->_factories[$name])) {
			$this->_objects[$name][$key] = $obj;
		}

		return $obj;
	}
	
	public function delete($name)
	{
		// TODO: Should this also delete the callback?
		if (isset($this->_objects[$name])) {
			unset($this->_objects[$name]);
			return true;
		}
		
		return false;
	}
	
	protected function _keyForArguments(Array $arguments)
	{
		if (count($arguments) && $this === $arguments[0]) {
			array_shift($arguments);
		}
		
		if (0 == count($arguments)) {
			return '_no_arguments';
		}
		
		return md5(serialize($arguments));
	}
}


