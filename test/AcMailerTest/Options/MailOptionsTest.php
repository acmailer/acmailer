<?php
namespace AcMailerTest\Options;

use AcMailer\Options\MailOptions;
use AcMailer\Exception\InvalidArgumentException;
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
    
    private $mailOptions;
    
    public function setUp() {
        $this->mailOptions = new MailOptions(array());
    }
    
    public function testMailAdapterNameConversion() {
        $this->mailOptions->setMailAdapter("Sendmail");
        $this->assertTrue($this->mailOptions->getMailAdapter() instanceof Sendmail);
        
        $this->mailOptions->setMailAdapter("smtp");
        $this->assertTrue($this->mailOptions->getMailAdapter() instanceof Smtp);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testMailAdapterInvalidNameThrowAnException() {
        $this->mailOptions->setMailAdapter("foo"); // Foo is not a valid adapter name
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testMailAdapterInvalidInstanceThrowAnException() {
        $this->mailOptions->setMailAdapter(new File()); // File transport is not a valid mail adapter
    }
    
    public function testOneDestinationAddressIsCastToArray() {
        $this->mailOptions->setTo("one-address");
        $this->assertTrue(is_array($this->mailOptions->getTo()));
        
        $this->mailOptions->setCc("one-address");
        $this->assertTrue(is_array($this->mailOptions->getCc()));
        
        $this->mailOptions->setBcc("one-address");
        $this->assertTrue(is_array($this->mailOptions->getBcc()));
    }
    
    public function testSettersReturnItself() {
        $this->assertEquals($this->mailOptions, $this->mailOptions->setServer("foo-server"));
        
        $this->assertEquals($this->mailOptions, $this->mailOptions->setPort(25));
        
        $this->assertEquals($this->mailOptions, $this->mailOptions->setFromName("foo-name"));
    }
    
    public function testGetSmtpServer() {
        $expected = "foo@bar.com";
        $this->mailOptions->setFrom($expected);
        $this->assertEquals($expected, $this->mailOptions->getSmtpUser());
        
        $this->mailOptions->setSmtpUser("user");
        $this->assertNotEquals($expected, $this->mailOptions->getSmtpUser());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSslInvalidValuesThrowException() {
        $this->mailOptions->setSsl("foo");
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSslIntValueThrowException() {
        $this->mailOptions->setSsl(25);
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSslInvalidBooleanValueThrowException() {
        $this->mailOptions->setSsl(true);
    }
    
}