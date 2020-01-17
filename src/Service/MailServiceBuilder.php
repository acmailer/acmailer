<?php

declare(strict_types=1);

namespace AcMailer\Service;

use Laminas\ServiceManager\AbstractPluginManager;

class MailServiceBuilder extends AbstractPluginManager implements MailServiceBuilderInterface
{
    protected $instanceOf = MailServiceInterface::class;
}
