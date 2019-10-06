<?php

declare(strict_types=1);

namespace AcMailer\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ServiceNotCreatedException extends RuntimeException implements ExceptionInterface, ContainerExceptionInterface
{
}
