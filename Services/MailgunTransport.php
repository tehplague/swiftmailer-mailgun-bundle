<?php

namespace cspoo\Swiftmailer\MailgunBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Mailgun\Mailgun;

use \Swift_Events_EventListener;
use \Swift_Events_SendEvent;
use \Swift_Mime_HeaderSet;
use \Swift_Mime_Message;
use \Swift_Transport;


class MailgunTransport extends ContainerAware implements Swift_Transport
{
	private $mailgun = null;

    /**
     * Not used.
     */
    public function isStarted()
    {
    	return true;
    }

    /**
     * Not used.
     */
    public function start()
    {
    }

    /**
     * Not used.
     */
    public function stop()
    {
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return integer
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
    	if (!$this->mailgun)
    		$this->createMailgun();

    	$fromHeader = $message->getHeaders()->get('From');
    	$toHeader = $message->getHeaders()->get('To');
    	$subjectHeader = $message->getHeaders()->get('Subject');

        if (!$toHeader) {
            throw new Swift_TransportException(
                'Cannot send message without a recipient'
            );
        }

        $from = $fromHeader->getFieldBody();
        $to = $toHeader->getFieldBody();
        $subject = $subjectHeader ? $subjectHeader->getFieldBody() : '';

    	$domain = $this->container->getParameter('mailgun.domain');
    	$this->mailgun->sendMessage($domain, array(
    		'from' => $from,
    		'to' => $to
    	), $message->toString());

        return 0;
    }

    private function createMailgun()
    {
    	$key = $this->container->getParameter('mailgun.key');
    	$this->mailgun = new Mailgun($key);
    }

    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
    }
}