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


class MailgunTransport implements Swift_Transport
{
    /**
     * @var \Mailgun\Mailgun mailgun
     */
    private $mailgun;

    /**
     * @var string domain
     */
    private $domain;

    /**
     * The event dispatcher from the plugin API
     *
     * @var \Swift_Events_EventDispatcher eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     * @param Mailgun $mailgun
     * @param $domain
     */
    public function __construct(\Swift_Events_EventDispatcher $eventDispatcher, Mailgun $mailgun, $domain)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->domain = $domain;
        $this->mailgun = $mailgun;
    }

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
        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

    	$fromHeader = $message->getHeaders()->get('From');
    	$toHeader = $message->getHeaders()->get('To');

        if (!$toHeader) {
            throw new Swift_TransportException(
                'Cannot send message without a recipient'
            );
        }

        $from = $fromHeader->getFieldBody();
        $to = $toHeader->getFieldBody();

    	$result = $this->mailgun->sendMessage($this->domain, array(
    		'from' => $from,
    		'to' => $to
    	), $message->toString());

        $success = $result->http_response_code == 200;

        if ($evt) {
            $evt->setResult($success ? Swift_Events_SendEvent::RESULT_SUCCESS : Swift_Events_SendEvent::RESULT_FAILED);
            $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 1;
    }

    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->eventDispatcher->bindEventListener($plugin);
    }
}