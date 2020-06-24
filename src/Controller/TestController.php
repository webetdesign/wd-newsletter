<?php

namespace WebEtDesign\NewsletterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * TestController constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/news-config")
     * @param Request $request
     * @param $tag
     */
    public function indexAction(Request $request, $tag = null){
        dump($this->parameterBag->get('wd_newsletter.models'));
        die;
    }
}
