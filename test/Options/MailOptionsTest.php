<?php
namespace AcMailerTest\Options;

use AcMailer\Options\MailOptions;
use AcMailer\Exception\InvalidArgumentException;
use AcMailer\Options\TemplateOptions;
use Zend\Mail\Transport\Null;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\File;

/**
 * Mail options test case
 * @author Alejandro Celaya Alastrué
 * @link http://www.alejandrocelaya.com
 */
class MailOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailOptions
     */
    private $mailOptions;
    
    public function setUp()
    {
        $this->mailOptions = new MailOptions(array());
    }

    public function testDefaultMailOptionsValues()
    {
        $this->assertInstanceOf('\Zend\Mail\Transport\Sendmail', $this->mailOptions->getMailAdapter());
        $this->assertNull($this->mailOptions->getMailAdapterService());
        $this->assertEquals('localhost', $this->mailOptions->getServer());
        $this->assertEquals('', $this->mailOptions->getFrom());
        $this->assertEquals('', $this->mailOptions->getFromName());
        $this->assertEquals(array(), $this->mailOptions->getTo());
        $this->assertCount(0, $this->mailOptions->getTo());
        $this->assertEquals(array(), $this->mailOptions->getCc());
        $this->assertCount(0, $this->mailOptions->getCc());
        $this->assertEquals(array(), $this->mailOptions->getBcc());
        $this->assertCount(0, $this->mailOptions->getBcc());
        $this->assertEquals('', $this->mailOptions->getSmtpUser());
        $this->assertEquals('', $this->mailOptions->getSmtpPassword());
        $this->assertFalse($this->mailOptions->getSsl());
        $this->assertEquals('login', $this->mailOptions->getConnectionClass());
        $this->assertEquals('', $this->mailOptions->getSubject());
        $this->assertEquals('', $this->mailOptions->getBody());
        $this->assertEquals(25, $this->mailOptions->getPort());
        $this->assertInstanceOf('AcMailer\Options\AttachmentsOptions', $this->mailOptions->getAttachments());
        $this->assertInstanceOf('AcMailer\Options\TemplateOptions', $this->mailOptions->getTemplate());
        $this->assertEquals('data/mail/output', $this->mailOptions->getFilePath());
        $this->assertNull($this->mailOptions->getFileCallback());
    }

    public function testMailAdapterNameConversion()
    {
        $this->mailOptions->setMailAdapter("Sendmail");
        $this->assertTrue($this->mailOptions->getMailAdapter() instanceof Sendmail);
        
        $this->mailOptions->setMailAdapter("smtp");
        $this->assertTrue($this->mailOptions->getMailAdapter() instanceof Smtp);

        $this->mailOptions->setMailAdapter("FILE");
        $this->assertTrue($this->mailOptions->getMailAdapter() instanceof File);

        $this->mailOptions->setMailAdapter("null");
        $this->assertTrue($this->mailOptions->getMailAdapter() instanceof \Zend\Mail\Transport\Null);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testMailAdapterInvalidNameThrowAnException()
    {
        $this->mailOptions->setMailAdapter("foo"); // Foo is not a valid adapter name
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testMailAdapterInvalidInstanceThrowAnException()
    {
        $this->mailOptions->setMailAdapter(new \stdClass());
    }
    
    public function testOneDestinationAddressIsCastToArray()
    {
        $this->mailOptions->setTo("one-address");
        $this->assertTrue(is_array($this->mailOptions->getTo()));
        
        $this->mailOptions->setCc("one-address");
        $this->assertTrue(is_array($this->mailOptions->getCc()));
        
        $this->mailOptions->setBcc("one-address");
        $this->assertTrue(is_array($this->mailOptions->getBcc()));
    }
    
    public function testSettersReturnItself()
    {
        $this->assertEquals($this->mailOptions, $this->mailOptions->setServer("foo-server"));
        
        $this->assertEquals($this->mailOptions, $this->mailOptions->setPort(25));
        
        $this->assertEquals($this->mailOptions, $this->mailOptions->setFromName("foo-name"));
    }
    
    public function testGetSmtpServer()
    {
        $expected = "foo@bar.com";
        $this->mailOptions->setFrom($expected);
        $this->assertEquals($expected, $this->mailOptions->getSmtpUser());
        
        $this->mailOptions->setSmtpUser("user");
        $this->assertNotEquals($expected, $this->mailOptions->getSmtpUser());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSslInvalidValuesThrowException()
    {
        $this->mailOptions->setSsl("foo");
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSslIntValueThrowException()
    {
        $this->mailOptions->setSsl(25);
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSslInvalidBooleanValueThrowException()
    {
        $this->mailOptions->setSsl(true);
    }

    public function testTemplateArrayIsCastToTemplateOptions()
    {
        $this->mailOptions->setTemplate(array());
        $this->assertTrue($this->mailOptions->getTemplate() instanceof TemplateOptions);
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTemplateInvalidValueThrowsException()
    {
        $this->mailOptions->setTemplate("foo");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMailConnectionInvalidValueThrowsAnException()
    {
        $this->mailOptions->setConnectionClass("Foo");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAdapterServiceInvalidValueThrowsAnException()
    {
        $this->mailOptions->setMailAdapterService(45);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFilePathInvalidValueThrowsAnException()
    {
        $this->mailOptions->setFilePath(321);
    }
}
