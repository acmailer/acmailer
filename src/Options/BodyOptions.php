<?php
namespace AcMailer\Options;

use AcMailer\Service\MailServiceInterface;
use Zend\Stdlib\AbstractOptions;
use AcMailer\Exception\InvalidArgumentException;

/**
 * Class BodyOptions
 * @author Alejandro Celaya Alastrué
 * @link http://www.wonnova.com
 */
class BodyOptions extends AbstractOptions
{
    /**
     * @var bool
     */
    private $useTemplate = false;
    /**
     * @var string
     */
    private $content = '';
    /**
     * @var string
     */
    private $charset = MailServiceInterface::DEFAULT_CHARSET;
    /**
     * @var TemplateOptions
     */
    private $template;

    /**
     * @return boolean
     */
    public function getUseTemplate()
    {
        return $this->useTemplate;
    }

    /**
     * @param boolean $useTemplate
     * @return $this
     */
    public function setUseTemplate($useTemplate)
    {
        $this->useTemplate = (bool) $useTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @return TemplateOptions
     */
    public function getTemplate()
    {
        if (! isset($this->template)) {
            $this->setTemplate([]);
        }

        return $this->template;
    }

    /**
     * @param array|TemplateOptions $template
     * @return $this
     * @throws \AcMailer\Exception\InvalidArgumentException
     */
    public function setTemplate($template)
    {
        if (is_array($template)) {
            $this->template = new TemplateOptions($template);
        } elseif ($template instanceof TemplateOptions) {
            $this->template = $template;
        } else {
            throw new InvalidArgumentException(sprintf(
                'Template should be an array or an AcMailer\Options\TemplateOptions object. %s provided.',
                is_object($template) ? get_class($template) : gettype($template)
            ));
        }

        return $this;
    }
}
