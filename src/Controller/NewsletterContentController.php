<?php

namespace WebEtDesign\NewsletterBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\NewsletterBundle\Repository\ContentCollectionRepositoryInterface;

class NewsletterContentController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

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

}