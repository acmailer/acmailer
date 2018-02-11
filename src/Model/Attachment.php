<?php
declare(strict_types=1);

namespace AcMailer\Model;

final class Attachment
{
    /**
     * @var string
     */
    private $parserName;
    /**
     * @var mixed
     */
    private $value;

    public function __construct(string $parserName, $value)
    {
        $this->parserName = $parserName;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getParserName(): string
    {
        return $this->parserName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
