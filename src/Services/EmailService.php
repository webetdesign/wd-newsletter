<?php

namespace WebEtDesign\NewsletterBundle\Services;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\Unsubscribe;
use WebEtDesign\NewsletterBundle\Event\MailSentEvent;

class EmailService
{

    public function __construct(
        private Swift_Mailer             $mailer,
        private EngineInterface          $templating,
        private ModelProvider            $modelProvider,
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $eventDispatcher,
        private ?string                  $from,
        private RouterInterface          $router,
        private array                    $locales,
        private string                   $rootDir,
        private bool                     $enableLog

    )
    {
    }

    /**
     * @param Newsletter $newsletter
     * @param $email_list
     * @param FlashBagInterface|null $flashBag
     * @return int
     * @throws Exception
     */
    public function sendNewsletter(Newsletter $newsletter, $email_list, FlashBagInterface $flashBag = null): int
    {
        $log = new Logger('mailer');
        $log->pushHandler(new StreamHandler($this->rootDir . '/var/log/mailer.log', Logger::DEBUG));

        $res = -1;

        foreach ($email_list as $locale => $emails) {
            foreach ($emails as $token => $email) {
                try {
                    $log->info('Mail to ' . $email . ' created');
                    $link = $token ? $this->router->generate('newsletter_unsub', ['token' => $token], $this->router::ABSOLUTE_URL) : $this->router->generate('newsletter_unsub_auto', [], $this->router::ABSOLUTE_URL);

                    $html = $this->templating->render($this->modelProvider->getTemplate($newsletter->getModel()), [
                        'object' => $newsletter,
                        'locale' => $newsletter->isSendInAllLocales() ? $this->locales : [$locale],
                        'unsub' => $link
                    ]);

                    $trackingToken = md5(uniqid('', true));

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

                    $message = (new Swift_Message($newsletter->getTitle()))
                        ->setFrom([$this->from ?: $newsletter->getEmail() => $newsletter->getSender()])
                        ->setTo($email)
                        ->setReplyTo($newsletter->getEmail())
                        ->setBody(
                            $html, 'text/html'
                        )
                        ->addPart(
                            $this->templating->render($this->modelProvider->getTxt($newsletter->getModel()), [
                                'object' => $newsletter,
                                'locale' => $newsletter->isSendInAllLocales() ? $this->locales : [$locale],
                                'unsub' => $link
                            ]), 'text/plain'
                        );
                    if ($this->enableLog){
                        $message->getHeaders()->addTextHeader('X-Mailer-Hash', $trackingToken);
                        $message->getHeaders()->has('X-No-Track') ? $message->getHeaders()->remove('X-No-Track') : null;
                    }

                    $res = $this->mailer->send($message);
                    $this->eventDispatcher->dispatch(new MailSentEvent($message, $trackingToken, $newsletter->getId()), MailSentEvent::NAME);
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
        $unsubscribe = array_map(function (Unsubscribe $a) {
            return $a->getEmail();
        }, $this->em->getRepository(Unsubscribe::class)->findAll());

        $emails = [];


        if ($newsletter->getGroups()->count() > 0) {
            $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

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

            if (!empty($unsubscribe)) {
                $qb->andWhere(
                    $qb->expr()->notIn('u.email', ':unsub')
                )
                    ->setParameter('unsub', $unsubscribe);
            }

            $users = $qb->getQuery()->getResult();

            /** @var User $u */
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
        if (!empty($more)) {

            foreach ($more['fr'] as $key => $item) {
                if (in_array($item, $unsubscribe)) {
                    unset($more['fr'][$key]);
                }
            }
        }

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
}
