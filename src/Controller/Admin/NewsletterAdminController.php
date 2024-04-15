<?php

namespace WebEtDesign\NewsletterBundle\Controller\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use WebEtDesign\NewsletterBundle\Entity\Content;
use WebEtDesign\NewsletterBundle\Entity\ContentTranslation;
use WebEtDesign\NewsletterBundle\Entity\Newsletter;
use WebEtDesign\NewsletterBundle\Event\SendNewsletterEvent;
use WebEtDesign\NewsletterBundle\Services\EmailService;

class NewsletterAdminController extends CRUDController
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }


    public function sendAction(Request $request, $id = null): RedirectResponse
    {
        $id         = $request->get($this->admin->getIdParameter());
        $newsletter = $this->admin->getObject($id);

        if (!$newsletter) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s',
                $id));
        }

        if ($newsletter->getIsSent()) {
            $this->addFlash('error', "La newsletter a déjà été envoyée");
            return $this->redirect($this->admin->generateObjectUrl('list', $newsletter, []));
        }

        $event = new SendNewsletterEvent($newsletter);
        $this->eventDispatcher->dispatch($event, SendNewsletterEvent::class);

        $this->addFlash('success', 'La newsletter va être envoyée');

        return $this->redirect($this->admin->generateObjectUrl('list', $newsletter, []));
    }

    public function copyAction(Request $request, $id = null): RedirectResponse
    {
        $id = $request->get($this->admin->getIdParameter());
        /** @var Newsletter $old */
        $old = $this->admin->getObject($id);

        if (!$old) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s',
                $id));
        }

        $new = new Newsletter();
        $new
            ->setTitle($old->getTitle() . ' - Copie')
            ->setModel($old->getModel())
            ->setSender($old->getSender())
            ->setEmail($old->getEmail())
            ->setEmailsMore($old->getEmailsMore())
            ->setIsSent(false);

        $this->em->persist($new);
        $this->em->flush();

        foreach ($new->getContents() as $item) {
            $new->removeContent($item);
            $this->em->remove($item);
        }

        /** @var Content $content */
        foreach ($old->getContents() as $old_content) {
            $new_content = new Content();
            $new_content
                ->setNewsletter($new)
                ->setType($old_content->getType())
                ->setMedia($old_content->getMedia())
                ->setLabel($old_content->getLabel())
                ->setHelp($old_content->getHelp())
                ->setCode($old_content->getCode())
                ->setCanTranslate($old_content->getCanTranslate())
                ->setTranslations(new ArrayCollection());
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

        return $this->redirect($this->admin->generateObjectUrl('edit', $new, [
            "id" => $new->getId()
        ]));
    }
}
