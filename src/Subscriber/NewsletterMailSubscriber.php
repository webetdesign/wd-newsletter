<?php

namespace WebEtDesign\NewsletterBundle\Subscriber;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use WebEtDesign\NewsletterBundle\Event\SendNewsletterEvent;
use WebEtDesign\NewsletterBundle\Messenger\Message\NewsletterMailMessage;
use WebEtDesign\NewsletterBundle\Services\EmailService;

class NewsletterMailSubscriber implements EventSubscriberInterface
{
    const BATCH_INTERVAL_SECOND = 15;
    const BATCH_SIZE            = 50;

    public function __construct(
        protected readonly MessageBusInterface $bus,
        protected readonly EntityManagerInterface $em,
        protected readonly EmailService $emailService
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SendNewsletterEvent::class => 'send',
        ];
    }

    public function send(SendNewsletterEvent $event)
    {
        $con = $this->em->getConnection();

        $stmt     = $con->prepare('SELECT * FROM messenger_newsletters ORDER BY available_at DESC');
        $result   = $stmt->executeQuery([]);
        $messages = $result->fetchAllAssociative();

        if (count($messages) === 0) {
            $delay = new DelayStamp(self::BATCH_INTERVAL_SECOND);
        } else {
            $availableAt = DateTime::createFromFormat('Y-m-d H:i:s', $messages[0]['available_at']);
            $diff        = $availableAt->getTimestamp() - (new DateTime('now'))->getTimestamp();

            if ($diff < 0) {
                $diff = 0;
            }

            $delay = new DelayStamp($diff * 1000 + self::BATCH_INTERVAL_SECOND * 1000);
        }

        $newsletter = $event->getNewsletter();
        $recipients = $this->emailService->getEmails($newsletter);
        $dest       = 0;

        foreach ($recipients as $locale => $mails) {
            foreach ($mails as $token => $mail) {
                $this->bus->dispatch(new NewsletterMailMessage($newsletter, $mail, $token, $locale), [$delay]);
                if ($dest !== 0 && $dest % self::BATCH_SIZE === 0) {
                    $delay = new DelayStamp($delay->getDelay() + self::BATCH_INTERVAL_SECOND * 1000);
                }
                $dest++;
            }
        }

        $newsletter
//            ->setIsSent(true)
            ->setSentAt(new DateTime());

        $this->em->persist($newsletter);
        $this->em->flush();
    }
}