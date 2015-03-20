<?php
namespace AcMailerTest\Options;

use AcMailer\Options\AttachmentsOptions;
use AcMailer\Options\MailOptions;
use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Options\TemplateOptions;
use AcMailer\Service\MailServiceInterface;
use Zend\Mail\Transport\Null;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\File;
use PHPUnit_Framework_TestCase as TestCase;

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
        $this->assertInstanceOf('AcMailer\Options\MessageOptions', $this->mailOptions->getMessageOptions());
        $this->assertInstanceOf('Zend\Mail\Transport\SmtpOptions', $this->mailOptions->getSmtpOptions());
        $this->assertInstanceOf('Zend\Mail\Transport\FileOptions', $this->mailOptions->getFileOptions());
    }

    public function testMailAdapterNameConversion()
    {
        $this->mailOptions->setMailAdapter('Sendmail');
        $this->assertEquals('Zend\Mail\Transport\Sendmail', $this->mailOptions->getMailAdapter());
        
        $this->mailOptions->setMailAdapter('smtp');
        $this->assertEquals('Zend\Mail\Transport\Smtp', $this->mailOptions->getMailAdapter());

        $this->mailOptions->setMailAdapter('FILE');
        $this->assertEquals('Zend\Mail\Transport\File', $this->mailOptions->getMailAdapter());

        $this->mailOptions->setMailAdapter('null');
        $this->assertEquals('Zend\Mail\Transport\Null', $this->mailOptions->getMailAdapter());
    }

//    public function testOneDestinationAddressIsCastToArray()
//    {
//        $this->mailOptions->setTo('one-address');
//        $this->assertTrue(is_array($this->mailOptions->getTo()));
//
//        $this->mailOptions->setCc('one-address');
//        $this->assertTrue(is_array($this->mailOptions->getCc()));
//
//        $this->mailOptions->setBcc('one-address');
//        $this->assertTrue(is_array($this->mailOptions->getBcc()));
//    }
//
//    public function testSettersReturnItself()
//    {
//        $this->assertEquals($this->mailOptions, $this->mailOptions->setServer('foo-server'));
//
//        $this->assertEquals($this->mailOptions, $this->mailOptions->setPort(25));
//
//        $this->assertEquals($this->mailOptions, $this->mailOptions->setFromName('foo-name'));
//    }
//
//    public function testGetSmtpServer()
//    {
//        $expected = 'foo@bar.com';
//        $this->mailOptions->setFrom($expected);
//        $this->assertEquals($expected, $this->mailOptions->getSmtpUser());
//
//        $this->mailOptions->setSmtpUser('user');
//        $this->assertNotEquals($expected, $this->mailOptions->getSmtpUser());
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testSslInvalidValuesThrowException()
//    {
//        $this->mailOptions->setSsl('foo');
//    }
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testSslIntValueThrowException()
//    {
//        $this->mailOptions->setSsl(25);
//    }
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testSslInvalidBooleanValueThrowException()
//    {
//        $this->mailOptions->setSsl(true);
//    }
//
//    public function testConnectionClass()
//    {
//        $expected = 'smtp';
//        $this->assertSame($this->mailOptions, $this->mailOptions->setConnectionClass($expected));
//        $this->assertEquals($expected, $this->mailOptions->getConnectionClass());
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testMailConnectionInvalidValueThrowsAnException()
//    {
//        $this->mailOptions->setConnectionClass('Foo');
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testAdapterServiceInvalidValueThrowsAnException()
//    {
//        $this->mailOptions->setMailAdapterService(45);
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testFilePathInvalidValueThrowsAnException()
//    {
//        $this->mailOptions->setFilePath(321);
//    }
//
//    public function testBodyCharset()
//    {
//        $expected = 'Windows-1252';
//        $this->assertSame($this->mailOptions, $this->mailOptions->setBodyCharset($expected));
//        $this->assertEquals($expected, $this->mailOptions->getBodyCharset());
//    }
//
//    public function testTemplate()
//    {
//        $this->assertSame($this->mailOptions, $this->mailOptions->setTemplate([]));
//        $this->assertInstanceof('AcMailer\Options\TemplateOptions', $this->mailOptions->getTemplate());
//
//        $expected = new TemplateOptions();
//        $this->assertSame($this->mailOptions, $this->mailOptions->setTemplate($expected));
//        $this->assertSame($expected, $this->mailOptions->getTemplate());
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testTemplateInvalidValueThrowsException()
//    {
//        $this->mailOptions->setTemplate('foo');
//    }
//
//    public function testAttachments()
//    {
//        $this->assertSame($this->mailOptions, $this->mailOptions->setAttachments([]));
//        $this->assertInstanceof('AcMailer\Options\AttachmentsOptions', $this->mailOptions->getAttachments());
//
//        $expected = new AttachmentsOptions();
//        $this->assertSame($this->mailOptions, $this->mailOptions->setAttachments($expected));
//        $this->assertSame($expected, $this->mailOptions->getAttachments());
//    }
//
//    /**
//     * @expectedException InvalidArgumentException
//     */
//    public function testInvalidAttachmentsThrowException()
//    {
//        $this->mailOptions->setAttachments(null);
//    }
}
