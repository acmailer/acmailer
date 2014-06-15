<?php
namespace AcMailerTest\Options;

use AcMailer\Options\AttachmentsOptions;

/**
 * Class AttachmentsOptionsTest
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class AttachmentsOptionsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AttachmentsOptions
     */
    private $attachmentsOptions;

    public function setUp()
    {
        $this->attachmentsOptions = new AttachmentsOptions();
    }

    public function testDefaultAttachmentsOptionsValues()
    {
        $this->assertTrue(is_array($this->attachmentsOptions->getFiles()));
        $this->assertCount(0, $this->attachmentsOptions->getFiles());
        $this->assertTrue(is_array($this->attachmentsOptions->getDir()));
        $this->assertArrayHasKey('recursive', $this->attachmentsOptions->getDir());
        $this->assertArrayHasKey('iterate', $this->attachmentsOptions->getDir());
        $this->assertArrayHasKey('path', $this->attachmentsOptions->getDir());

        $dir = $this->attachmentsOptions->getDir();
        $this->assertEquals(AttachmentsOptions::DEFAULT_PATH, $dir['path']);
        $this->assertEquals(AttachmentsOptions::DEFAULT_ITERATE, $dir['iterate']);
        $this->assertEquals(AttachmentsOptions::DEFAULT_RECURSIVE, $dir['recursive']);
    }

    public function testDireHasDefaultKeys()
    {
        $this->attachmentsOptions->setDir(array('recursive' => true));
        $this->assertArrayHasKey('recursive', $this->attachmentsOptions->getDir());
        $this->assertArrayHasKey('iterate', $this->attachmentsOptions->getDir());
        $this->assertArrayHasKey('path', $this->attachmentsOptions->getDir());
        $dir = $this->attachmentsOptions->getDir();
        $this->assertEquals(AttachmentsOptions::DEFAULT_PATH, $dir['path']);
        $this->assertEquals(AttachmentsOptions::DEFAULT_ITERATE, $dir['iterate']);
        $this->assertEquals(true, $dir['recursive']);
    }

} 