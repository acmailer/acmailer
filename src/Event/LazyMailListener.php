<?php

declare(strict_types=1);

namespace AcMailer\Event;

use Psr\Container\ContainerInterface;

class LazyMailListener implements MailListenerInterface
{
    private string $serviceName;
    private ContainerInterface $container;
    private ?MailListenerInterface $wrapped = null;

    public function __construct(string $serviceName, ContainerInterface $container)
    {
        $this->serviceName = $serviceName;
        $this->container = $container;
    }

    /**
     * @return mixed
     */
    public function onPreRender(PreRenderEvent $e)
    {
        return $this->getListenerInstance()->onPreRender($e);
    }

    /**
     * @return mixed
     */
    public function onPreSend(PreSendEvent $e)
    {
        return $this->getListenerInstance()->onPreSend($e);
    }

    /**
     * @return mixed
     */
    public function onPostSend(PostSendEvent $e)
    {
        return $this->getListenerInstance()->onPostSend($e);
    }

    /**
     * @return mixed
     */
    public function onSendError(SendErrorEvent $e)
    {
        return $this->getListenerInstance()->onSendError($e);
    }

    private function getListenerInstance(): MailListenerInterface
    {
        if ($this->wrapped === null) {
            $this->wrapped = $this->container->get($this->serviceName);
        }

        return $this->wrapped;
    }
}
