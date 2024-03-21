<?php

namespace WebEtDesign\NewsletterBundle\Subscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use WebEtDesign\NewsletterBundle\Entity\NewsletterLog;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebEtDesign\NewsletterBundle\Event\MailSentEvent;

class NewsletterSentSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private bool $log = false,
        private string $userClass
    ) {
    }

    #[NoReturn] public function onMailSentEvent(MailSentEvent $event)
    {
        if (!$this->log) {
            return;
        }

        $user = $this->em->getRepository($this->userClass)->findOneBy(['email' => array_key_first($event->getMessage()->getTo())]);

        $this->em->persist(
            ($l = new NewsletterLog())
                ->setUser($user)
                ->setReceiver(array_key_first($event->getMessage()->getTo()))
                ->setToken($event->getToken())
                ->setTitle($event->getMessage()->getSubject())
                ->setBody($event->getMessage()->getHtmlBody())
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
