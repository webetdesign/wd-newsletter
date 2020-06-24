<?php

namespace WebEtDesign\NewsletterBundle\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Controller\CRUDController;
use WebEtDesign\CmsBundle\Entity\CmsPage;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Entity\Unsubscribe;
use WebEtDesign\NewsletterBundle\Services\EmailService;

class NewsletterAdminController extends CRUDController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var Newsletter|null
     */
    private $newsletter;
    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * NewsletterAdminController constructor.
     * @param EmailService $emailService
     * @param EntityManagerInterface $em
     */
    public function __construct(EmailService $emailService, EntityManagerInterface $em)
    {
        $this->em            = $em;
        $this->emailService = $emailService;
    }

    public function sendAction($id = null){
        $request = $this->getRequest();

        $id = $request->get($this->admin->getIdParameter());
        $this->newsletter = $this->admin->getObject($id);

        if (!$this->newsletter) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $emails =  $this->getEmails();
        try {
            $res = $this->emailService->sendNewsletter($this->newsletter,$emails);
        } catch (\Exception $e) {
            $res = 0;
            $this->addFlash('error', $e->getMessage());
        }

        $nbEmails = is_array($emails) ? count($emails) : 0;

        if ($res){
            $this->addFlash('success', 'La newsletter a été envoyée à ' . $nbEmails . ' email(s)');
        }else{
            $this->addFlash('error', "La newsletter n'a pas été envoyée");
        }

        return $this->redirect($this->admin->generateObjectUrl('list', null, []));
    }

    private function getEmails(){
        $unsubcribe = array_map(function(Unsubscribe $a){
            return $a->getEmail();
        }, $this->em->getRepository(Unsubscribe::class)->findAll());

        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

        foreach ($this->newsletter->getReceiver() as $receiver) {
            $qb->orWhere('u.roles LIKE :role')
                ->setParameter('role', '%"'.$receiver.'"%');
        }

       if (!empty($unsubcribe)){
           $qb->andWhere('u.email NOT IN (:unsub)')
               ->setParameter('unsub', $unsubcribe);
       }

       $users = $qb->getQuery()->getResult();
       $emails = [];

        foreach ($users as $u) {
            $locale = method_exists($u, 'getLocale') ? $u->getLocale() :  'fr';
            $locale = $locale !== '' && $locale !== null ? $locale : 'fr';
            $emails[$locale][] = $u->getEmail();
       }

        $emails = array_merge_recursive($emails, $this->newsletter->getEmailsMoreArray());

        return $emails;
    }
}
