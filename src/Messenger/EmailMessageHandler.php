<?php

namespace WebEtDesign\NewsletterBundle\Messenger;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Services\EmailService;

class EmailMessageHandler implements MessageHandlerInterface
{

    public function __construct(
        private EmailService $emailService,
        private EntityManagerInterface $em,
    ){}

    public function __invoke(EmailMessage $message)
    {
        $newsletter = $this->em->find(Newsletter::class, $message->getNewsletterId());

        $this->emailService->processNewsletter($newsletter, $message->getEmails());

    }

}