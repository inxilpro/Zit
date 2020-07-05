<?php

/**
 * @file
 * contains \Zit\Exception\NotFoundException
 */

namespace Zit\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * NotFound Exception
 *
 * @package Zit\Exception
 */
class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{
}
