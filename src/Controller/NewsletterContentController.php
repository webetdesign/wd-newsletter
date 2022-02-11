<?php

namespace WebEtDesign\NewsletterBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\NewsletterBundle\Entity\NewsletterLog;
use WebEtDesign\NewsletterBundle\Http\TransparentPixelResponse;
use WebEtDesign\NewsletterBundle\Repository\ContentCollectionRepositoryInterface;

class NewsletterContentController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em){}

    /**
     * @Route(path="/admin/newsletter/content/collection", name="newsletter_content_collection")
     */
    public function content(Request $request): JsonResponse
    {
        /** @var ContentCollectionRepositoryInterface $er */
        $er = $this->em->getRepository($request->query->get('class'));
        $results = [];

        foreach ($er->findForNewsletter($request->query->get('q'), 25) as $obj) {
            $results[] = [
                'text' => (string)$obj,
                'id' => $obj->getId()
            ];
        }

        return new JsonResponse([
            'results' => $results
        ]);
    }

    /**
     * @Route(path="/newsletter/track/opening/{token}/image.png", name="newsletter_track_opening")
     */
    public function trackOpening (?string $token): Response
    {
        $newsletterLog = $this->em->getRepository(NewsletterLog::class)->findOneBy(['token' => $token]);

        if ($newsletterLog && !$newsletterLog->getViewed()){
            $newsletterLog->setViewed(true);
            $this->em->flush();
        }

        return new TransparentPixelResponse();
    }

    /**
     * @Route(path="/newsletter/track/link/{token}", name="newsletter_track_link")
     */
    public function trackLink (?string $token, Request $request): RedirectResponse
    {
        $newsletterLog = $this->em->getRepository(NewsletterLog::class)->findOneBy(['token' => $token]);

        if ($newsletterLog){
            $newsletterLog->setClicked(true);
            $this->em->flush();
        }

        return new RedirectResponse(openssl_decrypt($request->query->get('url'), 'AES-256-CBC', $_ENV['APP_SECRET'], $options=0, substr(hash('sha256', $_ENV['NEWSLETTER_IV']), 0, 16)));
    }



}