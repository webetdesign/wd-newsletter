<?php

namespace WebEtDesign\NewsletterBundle\Subscriber;

use App\Entity\User;
use WebEtDesign\NewsletterBundle\Entity\NewsletterLog;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebEtDesign\NewsletterBundle\Event\MailSentEvent;

class NewsletterSentSubscriber implements EventSubscriberInterface
{

    public function __construct(private EntityManagerInterface $em, private bool $log = false)
    {
    }

    #[NoReturn] public function onMailSentEvent(MailSentEvent $event)
    {
        if (!$this->log) return;

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => array_key_first($event->getMessage()->getTo())]);

        $this->em->persist(
            (new NewsletterLog())
                ->setUser($user)
                ->setToken($event->getToken())
                ->setTitle($event->getMessage()->getSubject())
                ->setBody($event->getMessage()->getBody())
                ->setViewed(false)
                ->setNewsletterId($event->getNewsletterId())
                ->setClicked(false)
        );

        $this->em->flush();
    }

    #[ArrayShape([MailSentEvent::NAME => "string"])] public static function getSubscribedEvents(): array
   {
       return [
           MailSentEvent::NAME => 'onMailSentEvent',
       ];
   }
}
