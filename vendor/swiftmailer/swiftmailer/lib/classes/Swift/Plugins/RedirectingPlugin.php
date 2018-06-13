<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_RedirectingPlugin implements Swift_Events_SendListener
{
    
    private $_recipient;

    
    private $_whitelist = array();

    
    public function __construct($recipient, array $whitelist = array())
    {
        $this->_recipient = $recipient;
        $this->_whitelist = $whitelist;
    }

    
    public function setRecipient($recipient)
    {
        $this->_recipient = $recipient;
    }

    
    public function getRecipient()
    {
        return $this->_recipient;
    }

    
    public function setWhitelist(array $whitelist)
    {
        $this->_whitelist = $whitelist;
    }

    
    public function getWhitelist()
    {
        return $this->_whitelist;
    }

    
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();

        // conditionally save current recipients

        if ($headers->has('to')) {
            $headers->addMailboxHeader('X-Swift-To', $message->getTo());
        }

        if ($headers->has('cc')) {
            $headers->addMailboxHeader('X-Swift-Cc', $message->getCc());
        }

        if ($headers->has('bcc')) {
            $headers->addMailboxHeader('X-Swift-Bcc', $message->getBcc());
        }

        // Filter remaining headers against whitelist
        $this->_filterHeaderSet($headers, 'To');
        $this->_filterHeaderSet($headers, 'Cc');
        $this->_filterHeaderSet($headers, 'Bcc');

        // Add each hard coded recipient
        $to = $message->getTo();
        if (null === $to) {
            $to = array();
        }

        foreach ((array) $this->_recipient as $recipient) {
            if (!array_key_exists($recipient, $to)) {
                $message->addTo($recipient);
            }
        }
    }

    
    private function _filterHeaderSet(Swift_Mime_HeaderSet $headerSet, $type)
    {
        foreach ($headerSet->getAll($type) as $headers) {
            $headers->setNameAddresses($this->_filterNameAddresses($headers->getNameAddresses()));
        }
    }

    
    private function _filterNameAddresses(array $recipients)
    {
        $filtered = array();

        foreach ($recipients as $address => $name) {
            if ($this->_isWhitelisted($address)) {
                $filtered[$address] = $name;
            }
        }

        return $filtered;
    }

    
    protected function _isWhitelisted($recipient)
    {
        if (in_array($recipient, (array) $this->_recipient)) {
            return true;
        }

        foreach ($this->_whitelist as $pattern) {
            if (preg_match($pattern, $recipient)) {
                return true;
            }
        }

        return false;
    }

    
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->_restoreMessage($evt->getMessage());
    }

    private function _restoreMessage(Swift_Mime_Message $message)
    {
        // restore original headers
        $headers = $message->getHeaders();

        if ($headers->has('X-Swift-To')) {
            $message->setTo($headers->get('X-Swift-To')->getNameAddresses());
            $headers->removeAll('X-Swift-To');
        } else {
            $message->setTo(null);
        }

        if ($headers->has('X-Swift-Cc')) {
            $message->setCc($headers->get('X-Swift-Cc')->getNameAddresses());
            $headers->removeAll('X-Swift-Cc');
        }

        if ($headers->has('X-Swift-Bcc')) {
            $message->setBcc($headers->get('X-Swift-Bcc')->getNameAddresses());
            $headers->removeAll('X-Swift-Bcc');
        }
    }
}
