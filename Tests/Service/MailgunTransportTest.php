<?php

namespace cspoo\Swiftmailer\MailgunBundle\Tests\Service;

use cspoo\Swiftmailer\MailgunBundle\Service\MailgunTransport;
use Mailgun\Exception\HttpClientException;
use Mailgun\Exception\UnknownErrorException;
use Mailgun\Model\Message\SendResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class MailgunTransportTest extends TestCase
{
    public function testGetPostData()
    {
        $class = 'cspoo\Swiftmailer\MailgunBundle\Service\MailgunTransport';
        $transport = $this->getMockBuilder($class)
        ->disableOriginalConstructor()
        ->onlyMethods(array('prepareRecipients'))
        ->getMock();

        $transport->expects($this->once())
            ->method('prepareRecipients')
            ->willReturn(array('foo' => 'bar'));

        $method = new \ReflectionMethod($class, 'getPostData');
        $method->setAccessible(true);

        $message = new \Swift_Message();
        $headers = $message->getHeaders();
        $headers->addTextHeader('o:deliverytime', 'tomorrow');

        $result = $method->invoke($transport, $message);

        // Is post data preserved?
        $this->assertTrue(isset($result['foo']));
        $this->assertEquals('bar', $result['foo']);

        $this->assertTrue(isset($result['o:deliverytime']));
        $this->assertEquals('tomorrow', $result['o:deliverytime']);

        // Is the header removed form the message
        $this->assertNull($headers->get('o:deliverytime'), 'Mailgun headers should be removed');
    }

    public function testGetDomain()
    {
        $class = 'cspoo\Swiftmailer\MailgunBundle\Service\MailgunTransport';
        $transport = $this->getTransport();

        $method = new \ReflectionMethod($class, 'getDomain');
        $method->setAccessible(true);

        // Test default domain
        $message = new \Swift_Message();
        $result = $method->invoke($transport, $message);
        $this->assertEquals('default.com', $result, 'Default domain should be returned when no domain header is used');

        // Test with domain header
        $message = new \Swift_Message();
        $headers = $message->getHeaders();
        $headers->addTextHeader('mg:domain', 'example.com');

        $result = $method->invoke($transport, $message);
        $this->assertEquals('example.com', $result);
    }

    public function testPrepareRecipients()
    {
        $class = 'cspoo\Swiftmailer\MailgunBundle\Service\MailgunTransport';
        $transport = $this->getTransport();

        $method = new \ReflectionMethod($class, 'prepareRecipients');
        $method->setAccessible(true);

        // Test with domain header
        $message = (new \Swift_Message('Foobar'))
            ->setFrom('alice@example.com')
            ->setTo('bob@example.com')
            ->setCc('tobias@example.com')
            ->setBcc('eve@example.com')
            ->setBody('Message body');

        $result = $method->invoke($transport, $message);
        $this->assertTrue(isset($result['to']), 'PostData should always have a "to" field');
        $this->assertFalse(isset($result['cc']), 'PostData should never have a "cc" field');
        $this->assertFalse(isset($result['bcc']), 'PostData should never have a "bcc" field');

        // Make sure $result['to'] have all the recipients
        $this->assertTrue(in_array('bob@example.com', $result['to']));
        $this->assertTrue(in_array('tobias@example.com', $result['to']));
        $this->assertTrue(in_array('eve@example.com', $result['to']));
        $this->assertFalse(in_array('alice@example.com', $result['to']));

        // Make sure we got a from
        $this->assertTrue(in_array('alice@example.com', $result['from']));

        // Make sure we remove BCC from the message headers.
        $messageHeaders = $message->getHeaders();
        $this->assertNull($messageHeaders->get('bcc'));
    }

    public function testSendMessageOk()
    {
        $transport = $this->getTransport();

        $message = (new \Swift_Message('Foobar'))
            ->setFrom('alice@example.com')
            ->setTo('bob@example.com')
            ->setCc('tobias@example.com')
            ->setBcc('eve@example.com')
            ->setBody('Message body');


        $failed = null;
        $sent = $transport->send($message, $failed);

        $this->assertEquals(3, $sent);
        $this->assertEmpty($failed);
    }

    public function testSendMessageWithException()
    {
        $dispatcher = $this->getMockBuilder('Swift_Events_EventDispatcher')->getMock();
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $logger->expects($this->once())
            ->method('error');

        $transport = new MailgunTransport($dispatcher, $mailgun, 'default.com', $logger);

        $messageApi = $this->getMockBuilder('Mailgun\Api\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $messageApi->expects($this->once())
            ->method('sendMime')
            ->will($this->throwException(new UnknownErrorException()));

        $mailgun->expects($this->once())
            ->method('messages')
            ->willReturn($messageApi);


        $message = (new \Swift_Message('Foobar'))
             ->setFrom('alice@example.com')
             ->setTo('bob@example.com')
             ->setCc('tobias@example.com')
             ->setBcc('eve@example.com')
             ->setBody('Message body');

        $failed = null;
        $sent = $transport->send($message, $failed);

        $this->assertEquals(0, $sent);
        $this->assertEquals(['bob@example.com', 'eve@example.com', 'tobias@example.com'], $failed);
    }

    public function testSendMessageWithHttpClientException()
    {
        $dispatcher = $this->getMockBuilder('Swift_Events_EventDispatcher')->getMock();
        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $logger->expects($this->once())
            ->method('error')
            ->with('Your credentials are incorrect.', [
                'mailgun_http_response_code' => 401,
                'mailgun_http_response_reason' => 'UNAUTHORIZED',
                'mailgun_http_response_body' => ['message' => 'Forbidden']
            ]);

        $transport = new MailgunTransport($dispatcher, $mailgun, 'default.com', $logger);

        $responseBody = $this->getMockBuilder(StreamInterface::class)
            ->getMock();

        $responseBody->expects($this->any())
            ->method('__toString')
            ->willReturn('Forbidden');

        $response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn($responseBody);

        $response->method('getHeaderLine')->with('Content-Type')->willReturn('text/plain');

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(401);

        $response->expects($this->any())
            ->method('getReasonPhrase')
            ->willReturn('UNAUTHORIZED');

        $messageApi = $this->getMockBuilder('Mailgun\Api\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $messageApi->expects($this->once())
            ->method('sendMime')
            ->will($this->throwException(HttpClientException::unauthorized($response)));

        $mailgun->expects($this->once())
            ->method('messages')
            ->willReturn($messageApi);


        $message = (new \Swift_Message('Foobar'))
            ->setFrom('alice@example.com')
            ->setTo('bob@example.com')
            ->setCc('tobias@example.com')
            ->setBcc('eve@example.com')
            ->setBody('Message body');

        $failed = null;
        $sent = $transport->send($message, $failed);

        $this->assertEquals(0, $sent);
        $this->assertEquals(['bob@example.com', 'eve@example.com', 'tobias@example.com'], $failed);
    }

    /**
     * @return MailgunTransport
     */
    private function getTransport()
    {
        $dispatcher = $this->getMockBuilder('Swift_Events_EventDispatcher')->getMock();
        $dispatcher->expects($this->any())
            ->method('createSendEvent')
            ->willReturn($this->getMockBuilder('Swift_Events_SendEvent')->disableOriginalConstructor()->getMock());


        $messageApi = $this->getMockBuilder('Mailgun\Api\Message')
            ->disableOriginalConstructor()
            ->getMock();

        $messageApi->expects($this->any())
            ->method('sendMime')
            ->willReturn(SendResponse::create(['id'=>'123', 'message'=>'OK']));

        $mailgun = $this->getMockBuilder('Mailgun\Mailgun')
            ->disableOriginalConstructor()
            ->getMock();

        $mailgun->expects($this->any())
            ->method('messages')
            ->willReturn($messageApi);

        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->getMock();

        return new MailgunTransport($dispatcher, $mailgun, 'default.com', $logger);
    }
}
