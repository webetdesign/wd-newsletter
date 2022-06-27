<?php

namespace WebEtDesign\NewsletterBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use WebEtDesign\CmsBundle\Factory\TemplateFactoryInterface;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Event\MailSentEvent;
use WebEtDesign\NewsletterBundle\Factory\NewsletterFactory;

class EmailService
{

    public function __construct(
        private MessageBusInterface      $bus,
        private Environment              $templating,
        private NewsletterFactory        $newsletterFactory,
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $eventDispatcher,
        private ?array                   $from,
        private ?array                   $reply,
        private RouterInterface          $router,
        private array                    $locales,
        private string                   $rootDir,
        private bool                     $enableLog,
        private string                   $userClass
    ){}

    /**
     * @param Newsletter $newsletter
     * @param $email_list
     * @param FlashBagInterface|null $flashBag
     * @return int
     * @throws Exception|TransportExceptionInterface
     */
    public function sendNewsletter(Newsletter $newsletter, $email_list, FlashBagInterface $flashBag = null): int
    {
        $log = new Logger('mailer');
        $log->pushHandler(new StreamHandler($this->rootDir . '/var/log/mailer.log', Logger::DEBUG));
        $config = $this->newsletterFactory->get($newsletter->getModel());

        foreach ($email_list as $locale => $emails) {
            foreach ($emails as $token => $email) {
                try {
                    $log->info('Mail to ' . $email . ' created');
                    $link = $token ? $this->router->generate('newsletter_unsub', ['token' => $token], $this->router::ABSOLUTE_URL) : $this->router->generate('newsletter_unsub_auto', [], $this->router::ABSOLUTE_URL);

                    $html = $this->templating->render($config->getTemplate(), [
                        'object' => $newsletter,
                        'locale' => $newsletter->isSendInAllLocales() ? $this->locales : [$locale],
                        'unsub' => $link
                    ]);

                    $trackingToken = md5(uniqid('', true));

                    $html = $this->injectTrackerOpening($html, $trackingToken);
                    $html = $this->injectLinkTracker($html, $trackingToken);

                    $message = (new Email())
                        ->subject($newsletter->getTitle())
                        ->from(new Address($this->from['email'], $this->from['name']))
                        ->to($email)
                        ->html(
                            $html, 'text/html'
                        )
                        ->text(
                            $this->templating->render($config->getTxt(), [
                                'object' => $newsletter,
                                'locale' => $newsletter->isSendInAllLocales() ? $this->locales : [$locale],
                                'unsub' => $link
                            ]), 'text/plain'
                        );

                    if ($this->reply && isset($this->reply['email']) && isset($this->reply['name'])) {
                        $message->replyTo(new Address($this->reply['email'], $this->reply['name']));
                    }

                    if ($this->enableLog) {
                        $message->getHeaders()->addTextHeader('X-Mailer-Hash', $trackingToken);
                        $message->getHeaders()->has('X-No-Track') ? $message->getHeaders()->remove('X-No-Track') : null;
                    }

                    $this->bus->dispatch(new SendEmailMessage($message));
                    $this->eventDispatcher->dispatch(new MailSentEvent($message, $trackingToken, $newsletter->getId()), MailSentEvent::NAME);
                    $res = 1;
                } catch (Exception $e) {
                    $log->error('Mail to ' . $email . ' error');
                    $flashBag?->add('error', "Le mail à l'adresse " . $email . " n'a pas été envoyé suite à une erreur. (" . $e->getMessage() . ')');
                    $res = -1;
                }
            }
        }

        return $res;
    }

    public function getEmails(Newsletter $newsletter): array
    {
        $emails = [];

        if ($newsletter->getGroups()->count() > 0) {
            $qb = $this->em->getRepository($this->userClass)->createQueryBuilder('u');

            $or = '(';
            $cpt = 0;
            foreach ($newsletter->getGroups() as $group) {
                $or .= ':group_' . $cpt . ' MEMBER OF u.groups OR ';
                $qb->setParameter('group_' . $cpt, $group);
                $cpt++;
            }
            $or = substr($or, 0, strlen($or) - 3) . ')';

            if (strlen($or) > 5) {
                $qb->andWhere($or);
            }

            $qb->andWhere("u.newsletter = 1");

            $users = $qb->getQuery()->getResult();

            foreach ($users as $u) {
                if (!$u->getNewsletterToken()) {
                    $u->setNewsletterToken(md5(uniqid()));
                }
                $locale = method_exists($u, 'getLocale') ? $u->getLocale() : 'fr';
                $locale = $locale !== '' && $locale !== null ? $locale : 'fr';
                $emails[$locale][$u->getNewsletterToken()] = $u->getEmail();
            }
            $this->em->flush();
        }

        $more = $newsletter->getEmailsMoreArray();

        $emails = array_merge_recursive($emails, $more);
        foreach ($emails as $locale => $email) {
            $emails[$locale] = array_unique($email);
        }

        return $emails;
    }

    public function countEmails($email_list): int
    {
        $total = 0;
        foreach ($email_list as $emails) {
            $total += empty($emails) ? 0 : count($emails);
        }
        return $total;
    }

    private function injectTrackerOpening(string $html, string $trackingToken): string
    {
        if ($this->enableLog) {
            $tracker = "<img border=0 width=1 alt='' height=1  src='" . $this->router->generate('newsletter_track_opening', ['token' => $trackingToken], UrlGeneratorInterface::ABSOLUTE_URL) . "'>";

            $linebreak = ByteString::fromRandom(32)->toString();
            $html = str_replace("\n", $linebreak, $html);

            if (preg_match("/^(.*<body[^>]*>)(.*)$/", $html, $matches)) {
                $html = $matches[1] . $matches[2] . $tracker;
            } else {
                $html = $html . $tracker;
            }
            $html = str_replace($linebreak, "\n", $html);
        }

        return $html;
    }

    private function injectLinkTracker($html, $hash)
    {
        if ($this->enableLog && isset($_ENV['NEWSLETTER_IV']) && strlen($_ENV['NEWSLETTER_IV']) > 0) {
            $html = preg_replace_callback(
                "/(<a[^>]*href=[\"](.+)[\"])/",
                function ($matches) use ($hash) {
                    $url = "";
                    if (!empty($matches[2]) && strlen($matches[2]) > 0) {
                        $url = str_replace('&amp;', '&', $matches[2]);

                        $link = $this->router->generate('newsletter_track_link', [
                            'url' => openssl_encrypt($url, 'AES-256-CBC', $_ENV['APP_SECRET'], 0, substr(hash('sha256', $_ENV['NEWSLETTER_IV']), 0, 16)),
                            'token' => $hash
                        ], UrlGeneratorInterface::ABSOLUTE_URL);
                        $url = str_replace($matches[2], $link, $matches[1]);
                    }
                    return $url;
                },
                $html
            );
        }
        return $html;
    }

}
