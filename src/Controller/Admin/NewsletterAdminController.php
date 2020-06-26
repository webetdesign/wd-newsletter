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

        $emails = $this->emailService->getEmails($this->newsletter);

        try {
            $res = $this->emailService->sendNewsletter($this->newsletter,$emails);
        } catch (\Exception $e) {
            $res = 0;
            $this->addFlash('error', $e->getMessage());
        }

        if ($res){
            $this->addFlash('success', 'La newsletter va être envoyée à ' . $this->emailService->countEmails($emails) . ' email(s)');
            $this->newsletter->setIsSent(true);
        }else{
            $this->addFlash('error', "La newsletter n'a pas été envoyée");
            $this->newsletter->setIsSent(false);
        }

        $this->em->flush();

        return $this->redirect($this->admin->generateObjectUrl('list', null, []));
    }


}
