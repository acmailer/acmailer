<?php
namespace AcMailerTest\Options;

use AcMailer\Options\MailOptions;
use AcMailer\Options\MessageOptions;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\FileOptions;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

/**
 * Mail options test case
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsTest extends TestCase
{
    /**
     * @var MailOptions
     */
    private $mailOptions;
    
    public function setUp()
    {
        $this->mailOptions = new MailOptions([]);
    }

    public function testDefaultMailOptionsValues()
    {
        $this->assertEquals('\Zend\Mail\Transport\Sendmail', $this->mailOptions->getMailAdapter());
        $this->assertEquals('\Zend\Mail\Transport\Sendmail', $this->mailOptions->getTransport());
        $this->assertInstanceOf('AcMailer\Options\MessageOptions', $this->mailOptions->getMessageOptions());
        $this->assertInstanceOf('Zend\Mail\Transport\SmtpOptions', $this->mailOptions->getSmtpOptions());
        $this->assertInstanceOf('Zend\Mail\Transport\FileOptions', $this->mailOptions->getFileOptions());
        $this->assertEquals([], $this->mailOptions->getMailListeners());
    }

    public function testMailAdapterNameConversion()
    {
        $this->mailOptions->setMailAdapter('Sendmail');
        $this->assertEquals('\Zend\Mail\Transport\Sendmail', $this->mailOptions->getMailAdapter());
        
        $this->mailOptions->setMailAdapter('smtp');
        $this->assertEquals('\Zend\Mail\Transport\Smtp', $this->mailOptions->getMailAdapter());

        $this->mailOptions->setMailAdapter('FILE');
        $this->assertEquals('\Zend\Mail\Transport\File', $this->mailOptions->getMailAdapter());

        $nullAdapter = class_exists('Zend\Mail\Transport\InMemory')
            ? '\Zend\Mail\Transport\InMemory'
            : '\Zend\Mail\Transport\Null';
        $this->mailOptions->setMailAdapter('null');
        $this->assertEquals($nullAdapter, $this->mailOptions->getMailAdapter());

        $this->mailOptions->setMailAdapter('in_memory');
        $this->assertEquals($nullAdapter, $this->mailOptions->getMailAdapter());
    }

    public function testSetTransport()
    {
        $transport = 'file';
        $this->assertSame($this->mailOptions, $this->mailOptions->setTransport($transport));
        $this->assertEquals('\Zend\Mail\Transport\File', $this->mailOptions->getTransport());
        $this->assertEquals('\Zend\Mail\Transport\File', $this->mailOptions->getMailAdapter());
    }

    public function testSetMessageOptions()
    {
        $expected = new MessageOptions();
        $this->assertSame($this->mailOptions, $this->mailOptions->setMessageOptions($expected));
        $this->assertSame($expected, $this->mailOptions->getMessageOptions());

        $this->mailOptions->setMessageOptions([]);
        $this->assertInstanceOf('AcMailer\Options\MessageOptions', $this->mailOptions->getMessageOptions());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testSetMessageOptionWithInvalidValueThrowsException()
    {
        $this->mailOptions->setMessageOptions(new \stdClass());
    }

    public function testSetSmtpOptions()
    {
        $expected = new SmtpOptions();
        $this->assertSame($this->mailOptions, $this->mailOptions->setSmtpOptions($expected));
        $this->assertSame($expected, $this->mailOptions->getSmtpOptions());

        $this->mailOptions->setSmtpOptions([]);
        $this->assertInstanceOf('Zend\Mail\Transport\SmtpOptions', $this->mailOptions->getSmtpOptions());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testSetSmtpOptionWithInvalidValueThrowsException()
    {
        $this->mailOptions->setSmtpOptions(new \stdClass());
    }

    public function testSetFileOptions()
    {
        $expected = new FileOptions();
        $this->assertSame($this->mailOptions, $this->mailOptions->setFileOptions($expected));
        $this->assertSame($expected, $this->mailOptions->getFileOptions());

        $this->mailOptions->setFileOptions([]);
        $this->assertInstanceOf('Zend\Mail\Transport\FileOptions', $this->mailOptions->getFileOptions());
    }

    /**
     * @expectedException \AcMailer\Exception\InvalidArgumentException
     */
    public function testSetFileOptionWithInvalidValueThrowsException()
    {
        $this->mailOptions->setFileOptions(new \stdClass());
    }

    public function testSetMailListeners()
    {
        $this->assertCount(0, $this->mailOptions->getMailListeners());
        $this->assertSame($this->mailOptions, $this->mailOptions->setMailListeners([1, 2, 3]));
        $this->assertCount(3, $this->mailOptions->getMailListeners());
    }

    public function testSetRenderer()
    {
        $this->assertEquals('mailviewrenderer', $this->mailOptions->getRenderer());
        $this->assertSame($this->mailOptions, $this->mailOptions->setRenderer('foo'));
        $this->assertEquals('foo', $this->mailOptions->getRenderer());
    }
}
