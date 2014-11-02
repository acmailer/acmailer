<?php
namespace AcMailer\Result;

/**
 * Interface ResultAwareInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface ResultAwareInterface
{
    /**
     * @param ResultInterface $result
     * @return $this
     */
    public function setResult(ResultInterface $result);

    /**
     * @return ResultInterface
     */
    public function getResult();
}
