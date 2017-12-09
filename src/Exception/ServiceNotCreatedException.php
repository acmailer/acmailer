<?php
declare(strict_types=1);

namespace AcMailer\Exception;

use Psr\Container\ContainerExceptionInterface;

class ServiceNotCreatedException extends \RuntimeException implements ExceptionInterface, ContainerExceptionInterface
{
}
