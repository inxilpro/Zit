<?php

namespace Zit;

class Container
{
	protected $_objects = array();
	protected $_callbacks = array();
	
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
	
	public function has($name)
	{
		return isset($this->_callbacks[$name]);
	}
	
	public function get($name)
	{
		// Return object if it's already instantiated
		if (isset($this->_objects[$name])) {
			return $this->_objects[$name];
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
		$this->_objects[$name] = call_user_func_array($this->_callbacks[$name], $arguments);
		return $this->_objects[$name];
	}
	
	public function delete($name)
	{
		if (isset($this->_objects[$name])) {
			unset($this->_objects[$name]);
			return true;
		}
		
		return false;
	}
}


