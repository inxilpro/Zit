<?php

namespace Zit\Exception;

use Interop\Container\Exception\NotFoundException as InteropInterface;

class NotFoundException extends \InvalidArgumentException implements InteropInterface
{
}