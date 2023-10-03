<?php

namespace WebEtDesign\NewsletterBundle\Messenger;

class EmailMessage
{
    private $subject;
    private $from;
    private $to;
    private $body;
    private $bodyTxt;
    private $replyTo;
    private $trackingToken;
    private $trackingLink;
    private $newsletterId;

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from): void
    {
        $this->from = $from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to): void
    {
        $this->to = $to;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getBodyTxt()
    {
        return $this->bodyTxt;
    }

    public function setBodyTxt($bodyTxt): void
    {
        $this->bodyTxt = $bodyTxt;
    }

    public function getReplyTo()
    {
        return $this->replyTo;
    }

    public function setReplyTo($replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function getTrackingToken()
    {
        return $this->trackingToken;
    }

    public function setTrackingToken($trackingToken): void
    {
        $this->trackingToken = $trackingToken;
    }

    public function getTrackingLink()
    {
        return $this->trackingLink;
    }

    public function setTrackingLink($trackingLink): void
    {
        $this->trackingLink = $trackingLink;
    }

    public function getNewsletterId()
    {
        return $this->newsletterId;
    }

    public function setNewsletterId($newsletterId): void
    {
        $this->newsletterId = $newsletterId;
    }
}