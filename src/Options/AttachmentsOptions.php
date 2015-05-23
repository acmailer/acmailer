<?php
namespace AcMailer\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class AttachmentsOptions
 * @author Alejandro Celaya Alstrué
 * @link http://www.alejandrocelaya.com
 */
class AttachmentsOptions extends AbstractOptions
{
    const DEFAULT_ITERATE   = false;
    const DEFAULT_PATH      = 'data/mail/attachments';
    const DEFAULT_RECURSIVE = false;

    /**
     * @var array
     */
    private $files = [];
    /**
     * @var array
     */
    private $dir = [
        'iterate'   => self::DEFAULT_ITERATE,
        'path'      => self::DEFAULT_PATH,
        'recursive' => self::DEFAULT_RECURSIVE,
    ];

    /**
     * @param array $dir
     * @return $this
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
        return $this->normalizeDirArray();
    }
    /**
     * @return array
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
        return $this;
    }
    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
    /**
     * @param $filePath
     * @return $this
     */
    public function addFile($filePath)
    {
        $this->files[] = $filePath;
        return $this;
    }
    /**
     * @param array $files
     * @return $this
     */
    public function addFiles(array $files)
    {
        return $this->setFiles(array_merge($this->files, $files));
    }

    /**
     * Makes sure dir array has default properties at least
     * @return $this
     */
    protected function normalizeDirArray()
    {
        if (! isset($this->dir['iterate'])) {
            $this->dir['iterate'] = self::DEFAULT_ITERATE;
        }
        if (! isset($this->dir['path'])) {
            $this->dir['path'] = self::DEFAULT_PATH;
        }
        if (! isset($this->dir['recursive'])) {
            $this->dir['recursive'] = self::DEFAULT_RECURSIVE;
        }

        return $this;
    }
}
