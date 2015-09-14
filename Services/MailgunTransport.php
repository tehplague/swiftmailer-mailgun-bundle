<?php

namespace cspoo\Swiftmailer\MailgunBundle\Services;

use Mailgun\Mailgun;
use Swift_Events_EventListener;
use Swift_Events_SendEvent;
use Swift_Mime_Message;
use Swift_Transport;

class MailgunTransport implements Swift_Transport
{
    const DOMAIN_HEADER = 'mg:domain';

    /**
     * @var \Mailgun\Mailgun mailgun
     */
    private $mailgun;

    /**
     * @var string domain
     */
    private $domain;

    /**
     * The event dispatcher from the plugin API.
     *
     * @var \Swift_Events_EventDispatcher eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     * @param Mailgun                       $mailgun
     * @param $domain
     */
    public function __construct(\Swift_Events_EventDispatcher $eventDispatcher, Mailgun $mailgun, $domain)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->domain = $domain;
        $this->mailgun = $mailgun;
    }

    /**
     * Set the domain.
     *
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
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
     * @return integer number of mails sent
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
            $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        if (null === $message->getHeaders()->get('To')) {
            throw new \Swift_TransportException(
                'Cannot send message without a recipient'
            );
        }

        $postData = $this->prepareRecipients($message);
        $messageHeaders = $message->getHeaders();
        if ($messageHeaders->has(self::DOMAIN_HEADER)) {
            $domain = $messageHeaders->get(self::DOMAIN_HEADER);
            $messageHeaders->removeAll(self::DOMAIN_HEADER);
        } else {
            $domain = $this->domain;
        }
        $result = $this->mailgun->sendMessage($domain, $postData, $message->toString());

        if ($evt) {
            $evt->setResult($result->http_response_code == 200 ? Swift_Events_SendEvent::RESULT_SUCCESS : Swift_Events_SendEvent::RESULT_FAILED);
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

    /**
     * @param Swift_Mime_Message $message
     *
     * @return array
     */
    protected function prepareRecipients(Swift_Mime_Message $message)
    {
        $headerNames = array('from', 'to', 'bcc', 'cc');
        $messageHeaders = $message->getHeaders();
        $postData = array();
        foreach ($headerNames as $name) {
            /** @var \Swift_Mime_Headers_MailboxHeader $h */
            $h = $messageHeaders->get($name);
            $postData[$name] = $h === null ? array() : $h->getAddresses();
        }

        // Merge 'bcc' and 'cc' into 'to'.
        $postData['to'] = array_merge($postData['to'], $postData['bcc'], $postData['cc']);
        unset($postData['bcc']);
        unset($postData['cc']);

        // Remove Bcc to make sure it is hidden
        $messageHeaders->removeAll('bcc');

        return $postData;
    }
}
