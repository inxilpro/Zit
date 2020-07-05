<?php

/**
 * @file
 * contains \Zit\Exception\MissingArgument
 */

namespace Zit\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * MissingArgument Exception
 *
 * @package Zit\Exception
 */
class MissingArgument extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
