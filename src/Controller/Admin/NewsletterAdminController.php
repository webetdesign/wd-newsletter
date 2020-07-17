<?php

namespace WebEtDesign\NewsletterBundle\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use WebEtDesign\CmsBundle\Entity\CmsPage;
use WebEtDesign\NewsletterBundle\Entity\Content;
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
    public function __construct(EmailService $emailService, EntityManagerInterface $em, FlashBagInterface $flashBag)
    {
        $this->em           = $em;
        $this->emailService = $emailService;
        $this->flashBag = $flashBag;
    }

    public function sendAction($id = null)
    {
        $request = $this->getRequest();

        $id               = $request->get($this->admin->getIdParameter());
        $this->newsletter = $this->admin->getObject($id);

        if (!$this->newsletter) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s',
                $id));
        }

        if ($this->newsletter->getIsSent()) {
            $this->addFlash('error', "La newsletter a déjà été envoyée");
            return $this->redirect($this->admin->generateObjectUrl('list', null, []));
        }

        $emails = $this->emailService->getEmails($this->newsletter);

        try {
            $res = $this->emailService->sendNewsletter($this->newsletter, $emails, $this->flashBag);
        } catch (\Exception $e) {
            $res = 0;
            $this->addFlash('error', $e->getMessage());
        }

        if ($res) {
            $this->addFlash('success',
                'La newsletter va être envoyée');
            $this->newsletter->setIsSent(true);
            $this->newsletter->setSendedAt(new \DateTime('now'));
        } else {
            $this->addFlash('error', "La newsletter n'a pas été envoyée");
            $this->newsletter->setIsSent(false);
        }

        $this->em->flush();

        return $this->redirect($this->admin->generateObjectUrl('list', null, []));
    }

    public function copyAction($id = null)
    {
        $request = $this->getRequest();

        $id         = $request->get($this->admin->getIdParameter());
        $newsletter = $this->admin->getObject($id);

        if (!$newsletter) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s',
                $id));
        }

        /** @var Newsletter $new */
        $new = clone $newsletter;
        foreach ($newsletter->getContents() as $content) {
            /** @var Content $new_content */
            $new_content = clone $content;
            $new_content->setNewsletter($new);
            $this->em->persist($new_content);
        }

        $new->setTitle($new->getTitle() . ' - Copie');
        $new->setIsSent(false);
        $new->setSendedAt(null);
        $this->em->persist($new);
        $this->em->flush();

        $this->addFlash('success', "La newsletter a été copiée");
        return $this->redirect($this->admin->generateObjectUrl('edit', $new,
            ["id" => $new->getId()]));

    }


}
