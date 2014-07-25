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
		throw new \InvalidArgumentException(sprintf('Methood "%s" does not exist.', $method));
	}
	
	public function set($name, $callableOrStatic)
	{
		if (!is_callable($callableOrStatic)) {
			$value = $callableOrStatic;
			$callableOrStatic = function() use ($value) {
				return $value;
			};
		}

		$this->_callbacks[$name] = $callableOrStatic;
	}
	
	public function setParam($name, $param)
	{
		trigger_error('Zit::setParam() as been deprecated.  Use Zit::set() instead.', E_USER_NOTICE);
		return call_user_func_array(array($this, 'set'), func_get_args());
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
		$deleted = false;

		// Delete Objects
		if (isset($this->_objects[$name])) {
			unset($this->_objects[$name]);
			$deleted = true;
		}

		// Delete Callbacks
		if (isset($this->_callbacks[$name])) {
			unset($this->_callbacks[$name]);
			$deleted = true;
		}

		// Delete Factories
		if (isset($this->_factories[$name])) {
			unset($this->_factories[$name]);
			$deleted = true;
		}
		
		return $deleted;
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


