<?php

namespace WebEtDesign\NewsletterBundle\Messenger;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EmailMessageHandler implements MessageHandlerInterface
{
    public function __invoke(EmailMessage $message)
    {
        $message->getSubject();
        $message->getFrom();
        $message->getTo();
        $message->getBody();
        $message->getBodyTxt();

    }

}