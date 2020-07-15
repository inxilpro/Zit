<?php

/**
 * @file
 * contains \Zit\Resolver
 */

namespace Zit;

use Zit\Exception\MissingArgument;
use Zit\Exception\NotFoundException;

/**
 * Dependency Resolver for Zit container
 *
 * @package Zit
 */
class Resolver
{
    public const INVALID = '__INVALID__';

    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $definition
     *
     * @return mixed
     */
    public function resolve(Definition $definition)
    {
        if (isset($definition->factoryMethod)) {
            return $this->resolveFactory($definition);
        }

        $reference = $this->resolveReference($definition->class);
        if ($reference) {
            return $this->container->get($reference);
        }

        $instance = $this->resolveInstance($definition);
        $class    = new \ReflectionClass($instance);
        foreach ($definition->methodCalls as $methodName => $calls) {
            $method = $class->getMethod($methodName);
            foreach ($calls as $arguments) {
                $params = $this->resolveMethodParams($method->getParameters(), $arguments);
                $instance->$methodName(...$params);
            }
        }

        return $instance;
    }

    /**
     * Returns a reference string
     *
     * @param string $id The id to reference
     *
     * @return string The encapsulated reference string
     */
    public static function reference(string $id): string
    {
        return "@@{$id}@@";
    }

    /**
     * Resolves the definition as a factory
     *
     * @param Definition $definition
     * @return mixed
     * @throws \ReflectionException
     */
    protected function resolveFactory(Definition $definition)
    {
        $reference = $this->resolveReference($definition->class);
        $instance  = $definition->class;
        if ($reference) {
            $instance = $this->container->get($reference);
        }

        $class  = new \ReflectionClass($reference ? $instance : $definition->class);
        $method = $class->getMethod($definition->factoryMethod);

        return call_user_func_array(
            [$instance, $definition->factoryMethod],
            $this->resolveMethodParams($method->getParameters(), $definition->params)
        );
    }

    /**
     * Resolves the definition to a concrete instance
     *
     * @param string $name
     * @param array  $args
     */
    protected function resolveInstance(Definition $definition)
    {
        $class = new \ReflectionClass($definition->class);
        if ($constructor = $class->getConstructor()) {
            $params   = $this->resolveMethodParams($constructor->getParameters(), $definition->params);
            $instance = $class->newInstanceArgs($params);
        } else {
            $instance = $class->newInstance();
        }

        return $instance;
    }

    /**
     * Returns the references actual value
     *
     * @param mixed $value The value to resolve a reference for
     *
     * @return string|null The reference or null if not a reference
     */
    protected function resolveReference($value)
    {
        if (is_string($value) && strpos($value, '@@') === 0 && strpos($value, '@@', -3) === (strlen($value) - 2)) {
            return trim($value, '@@');
        }

        return null;
    }

    /**
     * Resolves the constructor parameters, registering any that might be needed
     *
     * @param Definition $definition
     * @param array      $params
     *
     * @return array
     * @throws \ReflectionException
     */

    protected function resolveValue($value)
    {
        $reference = $this->resolveReference($value);
        if ($reference) {
            if ($this->container->has($reference)) {
                return $this->container->get($reference);
            } elseif (class_exists($reference)) {
                $this->container->register($reference);

                return $this->container->get($reference);
            }
        } else {
            return $value;
        }

        throw new NotFoundException($value);
    }

    protected function resolveMethodParams(array $params, array $arguments): array
    {
        $outParams = [];
        /** @var \ReflectionParameter $param */
        foreach ($params as $param) {
            $name = $param->getName();
            if (isset($arguments[$name])) {
                // user defined argument
                $outParams[$param->getPosition()] = $this->resolveValue($arguments[$name]);
                continue;
            }

            if ($param->hasType()) {
                $value = $this->resolveValueByParameterType($name, $param->getType(), $arguments);
                if ($value !== self::INVALID) {
                    $outParams[$param->getPosition()] = $this->resolveValue($value);
                    continue;
                }
            }

            if ($param->isOptional()) {
                if ($param->isDefaultValueAvailable()) {
                    $outParams[$param->getPosition()] = $param->getDefaultValue();
                } elseif ($param->allowsNull()) {
                    $outParams[$param->getPosition()] = null;
                }
                continue;
            }

            // special case
            if ($name === 'container') {
                $outParams[$param->getPosition()] = $this->container;
                continue;
            }

            throw new MissingArgument("Argument not found: {$name}");
        }

        return $outParams;
    }

    /**
     * Resolves the value based on the parameter type
     *
     * @param string          $name
     * @param \ReflectionType $type
     * @param array           $arguments
     *
     * @return mixed|string|null
     */
    protected function resolveValueByParameterType(string $name, \ReflectionType $type, array $arguments)
    {
        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            $name = $type->getName();
            if (!$this->container->has($name) && class_exists($name)) {
                if ($type->allowsNull()) {
                    // Don't autowire nullable types - even if the class exists
                    return null;
                }

                $this->container->register($name);
            }
            elseif ($type->allowsNull()) {
                return null;
            }

            return "@@{$name}@@";
        }

        if ($type->allowsNull()) {
            return null;
        }

        return self::INVALID;
    }
}
