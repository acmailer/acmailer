<?php
declare(strict_types=1);

namespace AcMailer\Result;

interface ResultAwareInterface
{
    /**
     * @param ResultInterface $result
     * @return $this
     */
    public function setResult(ResultInterface $result);

    /**
     * @return ResultInterface|null
     */
    public function getResult();
}
