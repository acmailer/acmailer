<?php

declare(strict_types=1);

namespace AcMailer\Exception;

use RuntimeException;

class MailCancelledException extends RuntimeException implements ExceptionInterface
{
}
