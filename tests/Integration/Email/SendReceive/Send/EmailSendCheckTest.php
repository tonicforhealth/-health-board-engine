<?php

namespace TonicHealthCheck\Tests\Check\Email\Send;

use DateTime;
use PHPUnit_Framework_MockObject_MockObject;
use Swift_Mailer;
use Swift_Mime_Message;
use Swift_SmtpTransport;
use Swift_TransportException;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Email\Entity\EmailSendReceive;
use TonicHealthCheck\Check\Email\Entity\EmailSendReceiveCollection;
use TonicHealthCheck\Check\Email\Persist\PersistCollectionInterface;
use TonicHealthCheck\Check\Email\Persist\PersistCollectionToFile;
use TonicHealthCheck\Check\Email\Send\EmailSendCheck;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class EmailSendCheckTest.
 */
class EmailSendCheckTest extends AbstractIntegration
{
    /**
     * @var EmailSendCheck;
     */
    private $emailSendCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject;
     */
    private $transport;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject;
     */
    private $mailer;

    /**
     * @var PersistCollectionInterface;
     */
    private $persistCollection;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();
        $this->setTransport($this->getMockBuilder(Swift_SmtpTransport::class)->getMock());

        $this->setMailer(
            $this->getMockBuilder(Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock()
        );

        $this
            ->getMailer()
            ->method('getTransport')
            ->willReturn($this->getTransport());

        $this->setPersistCollection(new PersistCollectionToFile(sys_get_temp_dir()));

        $this->setEmailSendCheck(new EmailSendCheck(
            'testnode',
            $this->getMailer(),
            $this->getPersistCollection(),
            'test@test.com',
            'to_test@test.com',
            600
        ));

        parent::setUp();
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->setUpEntity();

        $this->setUpSendMock();

        $this->getChecksList()->add($this->getEmailSendCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test email is down.
     */
    public function testEmailIsDown()
    {
        $this->setUpEntity();

        $exceptionMsg = 'Connection could not be established with host smtp.mailgun.org [Operation timed out #60]';
        $exceptionCode = 0;

        $swiftSwiftException = new Swift_TransportException($exceptionMsg, $exceptionCode);

        $this
            ->getMailer()
            ->method('send')
            ->willThrowException($swiftSwiftException);

        $this->getChecksList()->add($this->getEmailSendCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(8001, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * Test email is frozen.
     */
    public function testEmailIsFrozen()
    {
        $this->setUpEntity();

        $exceptionMsg = 'Connection to localhost:2526 Timed Out';
        $exceptionCode = 0;

        $swiftSwiftException = new Swift_TransportException($exceptionMsg, $exceptionCode);

        $this
            ->getMailer()
            ->method('send')
            ->willThrowException($swiftSwiftException);

        $this->getChecksList()->add($this->getEmailSendCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(8001, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return EmailSendCheck
     */
    public function getEmailSendCheck()
    {
        return $this->emailSendCheck;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return PersistCollectionInterface
     */
    public function getPersistCollection()
    {
        return $this->persistCollection;
    }

    /**
     * @param EmailSendCheck $EmailSendCheck
     */
    protected function setEmailSendCheck(EmailSendCheck $emailSendCheck)
    {
        $this->emailSendCheck = $emailSendCheck;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $mailer
     */
    protected function setMailer(PHPUnit_Framework_MockObject_MockObject $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $transport
     */
    protected function setTransport(PHPUnit_Framework_MockObject_MockObject $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param PersistCollectionInterface $persistCollection
     */
    protected function setPersistCollection(PersistCollectionInterface $persistCollection)
    {
        $this->persistCollection = $persistCollection;
    }

    /**
     * set up entity.
     */
    private function setUpEntity()
    {
        $emailSendReceive = new EmailSendReceive();

        $emailSendReceive->setSentAt(new DateTime('-1 day'));

        $emailSendReceiveColl = new EmailSendReceiveCollection();
        $emailSendReceiveColl->add($emailSendReceive);

        $this->getPersistCollection()->persist($emailSendReceiveColl);
        $this->getPersistCollection()->flush();
    }

    private function setUpSendMock()
    {
        $this
            ->getMailer()
            ->method('send')
            ->willReturnCallback(
                function (Swift_Mime_Message $message, &$failedRecipients = null) {
                    $failedRecipients = ['test@test.com' => true];

                    return true;
                }
            );
    }
}
