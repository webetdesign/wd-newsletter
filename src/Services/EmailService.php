<?php

namespace WebEtDesign\NewsletterBundle\Services;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\Unsubscribe;

class EmailService
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var EngineInterface
     */
    private $templating;
    /**
     * @var ModelProvider
     */
    private $modelProvider;
    /**
     * @var string
     */
    private $from;

    /** @var EntityManagerInterface $em */
    private $em;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var array
     */
    private $locales;

    /**
     * EmailService constructor.
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templating
     * @param ModelProvider $modelProvider
     * @param EntityManagerInterface $em
     * @param string $from
     * @param RouterInterface $router
     * @param bool $allLocales
     * @param array $locales
     */
    public function __construct(
        \Swift_Mailer $mailer,
        EngineInterface $templating,
        ModelProvider $modelProvider,
        EntityManagerInterface $em,
        string $from,
        RouterInterface $router,
        array $locales

    ) {
        $this->mailer       = $mailer;
        $this->templating = $templating;
        $this->modelProvider = $modelProvider;
        $this->from = $from;
        $this->em = $em;
        $this->router = $router;
        $this->locales = $locales;
    }

    /**
     * @param Newsletter $newsletter
     * @param $email_list
     * @return int
     * @throws Exception
     */
    public function sendNewsletter(Newsletter $newsletter,  $email_list, FlashBagInterface $flashBag = null): int
    {
        $res = -1;
        foreach ($email_list as $locale => $emails) {
            foreach ($emails as $token => $email) {
                try{
                    $link = $token ? $this->router->generate('newsletter_unsub', ['token' => $token], $this->router::ABSOLUTE_URL) : $this->router->generate('newsletter_unsub_auto', [], $this->router::ABSOLUTE_URL) ;

                    $message = (new \Swift_Message($newsletter->getTitle()))
                        ->setFrom([$this->from => $newsletter->getSender()])
                        ->setTo($email)
                        ->setReplyTo($newsletter->getEmail())
                        ->setBody(
                            $this->templating->render($this->modelProvider->getTemplate($newsletter->getModel()), [
                                'object' => $newsletter,
                                'locale' => $newsletter->isSendInAllLocales() ? $this->locales : [$locale],
                                'unsub' => $link
                            ]), 'text/html'
                        )
                        ->addPart(
                            $this->templating->render($this->modelProvider->getTxt($newsletter->getModel()), [
                                'object' => $newsletter,
                                'locale' => $newsletter->isSendInAllLocales() ? $this->locales : [$locale],
                                'unsub' => $link
                            ]), 'text/plain'
                        );

                    $res = $this->mailer->send($message);
                }catch (Exception $e){
                    if ($flashBag){
                        $flashBag->add('error', "Le mail à l'adresse " . $email . "n'a pas été envoyé suite à une erreur. (" . $e->getMessage() . ')');
                    }
                    $res = -1;
                }
           }
        }

        return $res;
    }

    public function getEmails(Newsletter $newsletter){
        $unsubcribe = array_map(function(Unsubscribe $a){
            return $a->getEmail();
        }, $this->em->getRepository(Unsubscribe::class)->findAll());

        $emails = [];


        if ($newsletter->getGroups()->count() > 0){
            /** @var QueryBuilder $qb */
            $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

            $or = '(';
            $cpt = 0;
            foreach ($newsletter->getGroups() as $group) {
                $or .= ':group_' . $cpt . ' MEMBER OF u.groups OR ';
                $qb->setParameter('group_' . $cpt, $group);
                $cpt ++;
            }
            $or = substr($or, 0, strlen($or) - 3) . ')';

            if (strlen($or) > 5){
                $qb->andWhere($or);
            }

            if (!empty($unsubcribe)){
                $qb->andWhere(
                    $qb->expr()->notIn('u.email', ':unsub')
                )
                    ->setParameter('unsub', $unsubcribe);
            }

            $users = $qb->getQuery()->getResult();

            /** @var User $u */
            foreach ($users as $u) {
                if (!$u->getNewsletterToken()){
                    $u->setNewsletterToken(md5(uniqid()));
                }
                $locale = method_exists($u, 'getLocale') ? $u->getLocale() :  'fr';
                $locale = $locale !== '' && $locale !== null ? $locale : 'fr';
                $emails[$locale][$u->getNewsletterToken()] = $u->getEmail();
            }
            $this->em->flush();
        }

        $more = $newsletter->getEmailsMoreArray();
        if (!empty($more)){

            foreach ($more['fr'] as $key => $item) {
                if (in_array($item, $unsubcribe)){
                    unset($more['fr'][$key]);
                }
            }
        }

        $emails = array_merge_recursive($emails, $more);
        foreach ($emails as $locale => $email) {
            $emails[$locale] = array_unique($emails[$locale]);
        }

        return $emails;
    }

    public function countEmails($email_list){
        $total = 0;
        foreach ($email_list as $locale => $emails) {
            $total += empty($emails) ? 0 : count($emails);
        }
        return $total;
    }
}
