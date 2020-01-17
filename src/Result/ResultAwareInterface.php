<?php

declare(strict_types=1);

namespace AcMailer\Result;

interface ResultAwareInterface
{
    /**
     * @return $this
     */
    public function setResult(ResultInterface $result);

    public function getResult(): ?ResultInterface;
}
