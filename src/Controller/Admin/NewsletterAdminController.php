<?php

namespace WebEtDesign\NewsletterBundle\Controller\Admin;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Strategy\ORM\Nested;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use WebEtDesign\CmsBundle\Entity\CmsPage;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
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
        /** @var Newsletter $old */
        $old = $this->admin->getObject($id);

        if (!$old) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s',
                $id));
        }

        /** @var Newsletter $new */
        $new = new Newsletter();
        $new
            ->setTitle($old->getTitle() . ' - Copie')
            ->setModel($old->getModel())
            ->setSender($old->getSender())
            ->setEmail($old->getEmail())
            ->setEmailsMore($old->getEmailsMore())
            ->setIsSent(false)
        ;
        $this->em->persist($new);
        $this->em->flush();
        foreach ($new->getContents() as $item) {
            $new->removeContent($item);
            $this->em->remove($item);
        }
        /** @var Content $content */
        foreach ($old->getContents() as $old_content) {
            /** @var Content $new_content */
            $new_content = new Content();
            $new_content
                ->setNewsletter($new)
                ->setType($old_content->getType())
                ->setMedia($old_content->getMedia())
                ->setLabel($old_content->getLabel())
                ->setHelp($old_content->getHelp())
                ->setCode($old_content->getCode())
                ->setCanTranslate($old_content->getCanTranslate())
                ->setTranslations(new ArrayCollection())
            ;
            $this->em->persist($new_content);

            /** @var ContentTranslation $old_translation */
            foreach ($old_content->getTranslations() as $old_translation) {
                $new_translation = new ContentTranslation();
                $new_translation->setTranslatable($new_content);
                $new_translation->setValue($old_translation->getValue());
                $new_translation->setLocale($old_translation->getLocale());
                $this->em->persist($new_translation);
                $new_content->addTranslation($new_translation);
            }
            $new->addContent($new_content);
        }

        $this->em->flush();

        $this->addFlash('success', "La newsletter a été copiée");
        return $this->redirect($this->admin->generateObjectUrl('edit', $new,
            ["id" => $new->getId()]));

    }


}
