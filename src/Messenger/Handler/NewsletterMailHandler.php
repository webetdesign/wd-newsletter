<?php

namespace WebEtDesign\NewsletterBundle\Messenger\Handler;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Event\MailSentEvent;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;
use WebEtDesign\NewsletterBundle\Messenger\Message\NewsletterMailMessage;
use WebEtDesign\NewsletterBundle\Services\EmailService;

class NewsletterMailHandler implements MessageHandlerInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected Environment $twig,
        protected MailerInterface $mailer,
        protected RouterInterface $router,
        protected NewsletterFactory $newsletterFactory,
        protected EmailService $emailService,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(NewsletterMailMessage $message)
    {
        $log = new Logger('mailer');
        $log->pushHandler(new StreamHandler($this->emailService->rootDir . '/var/log/mailer.log', Logger::DEBUG));
        
        $newsletter = $this->em->find(Newsletter::class, $message->getNewsletterId());
        $config     = $this->newsletterFactory->get($newsletter->getModel());

        $log->info('Mail to ' . $message->getRecipient() . ' created');
        
        $link = $message->getToken() ? 
            $this->router->generate('newsletter_unsub', ['token' => $message->getToken()], RouterInterface::ABSOLUTE_URL) : 
            $this->router->generate('newsletter_unsub_auto', [], RouterInterface::ABSOLUTE_URL);
        
        $html = $this->twig->render($config->getTemplate(), [
            'object' => $newsletter,
            'locale' => $newsletter->isSendInAllLocales() ? $this->emailService->locales : [$message->getLocale()],
            'unsub'  => $link,
        ]);

        $trackingToken = md5(uniqid('', true));

        $html = $this->emailService->injectTrackerOpening($html, $trackingToken);
        $html = $this->emailService->injectLinkTracker($html, $trackingToken);

        $email = (new Email())
            ->subject($newsletter->getTitle())
            ->from(new Address($this->emailService->from['email'], $this->emailService->from['name']))
            ->to(new Address($message->getRecipient()))
            ->html(
                $html, 'text/html'
            )
            ->text(
                $this->twig->render($config->getTxt(), [
                    'object' => $newsletter,
                    'locale' => $newsletter->isSendInAllLocales() ? $this->emailService->locales : [$message->getLocale()],
                    'unsub'  => $link
                ]), 'text/plain'
            );

        if ($this->emailService->reply && isset($this->emailService->reply['email']) && isset($this->emailService->reply['name'])) {
            $email->replyTo(new Address($this->emailService->reply['email'], $this->emailService->reply['name']));
        }

        if ($this->emailService->enableLog) {
            $email->getHeaders()->addTextHeader('X-Mailer-Hash', $trackingToken);
            $email->getHeaders()->has('X-No-Track') ? $email->getHeaders()->remove('X-No-Track') : null;
        }

        $this->mailer->send($email);
        $this->eventDispatcher->dispatch(new MailSentEvent($email, $trackingToken, $newsletter->getId()), MailSentEvent::NAME);
    }
}