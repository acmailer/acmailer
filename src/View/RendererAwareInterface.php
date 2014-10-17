<?php
namespace AcMailer\View;

use Zend\View\Renderer\RendererInterface;

/**
 * Interface RendererAwareInterface
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
interface RendererAwareInterface
{
    /**
     * @param RendererInterface $renderer
     * @return mixed
     */
    public function setRenderer(RendererInterface $renderer);

    /**
     * @return RendererInterface
     */
    public function getRenderer();
}
