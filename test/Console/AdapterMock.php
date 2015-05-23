<?php
namespace AcMailerTest\Console;

use Zend\Console\Adapter\AbstractAdapter;

class AdapterMock extends AbstractAdapter
{
    protected $lines = [];

    public function write($text, $color = null, $bgColor = null)
    {
        $this->lines[] = trim($text);
    }

    public function getLines()
    {
        return $this->lines;
    }
}
