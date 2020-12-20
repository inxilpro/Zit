<?php

/**
 * @file
 * contains \Zit\Exception\ClassNotFound
 */

namespace Zit\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * ClassNotFound Exception
 *
 * @package Zit\Exception
 */
class ClassNotFound extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
