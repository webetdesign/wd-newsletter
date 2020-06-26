<?php

namespace WebEtDesign\NewsletterBundle\Controller;


use App\Entity\User as User;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\NewsletterBundle\Entity\Unsubscribe;

class UnsubController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var string
     */
    private $home;

    /**
     * UnsubController constructor.
     * @param EntityManagerInterface $em
     * @param array $routes
     */
    public function __construct(EntityManagerInterface $em, array $routes)
    {
        $this->em = $em;
        $this->home = array_key_exists('home', $routes) ? $routes['home'] : 'index';
    }

    /**
     * @param Request $request
     * @param string $token
     * @Route(path="/newsletter/unsub/{token}", name="newsletter_unsub")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function __invoke(Request $request, string $token)
    {
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneBy([
            'newsletterToken' => $token
        ]);

        if (!$user) {
            $this->addFlash('error', "Vous n'êtes pas inscrit dans notre liste de diffusion");
        } else {
            if (!$this->em->getRepository(Unsubscribe::class)->findOneBy(['email' => $user->getEmail()])){
                $unsub = new Unsubscribe();
                $unsub->setEmail($user->getEmail());
                $this->em->persist($unsub);
            }
            $user->setNewsletterToken(null);

//            $this->em->flush();

            $this->addFlash('success', "Vous avez été supprimé de notre liste de diffusion.");
        }

        return $this->redirectToRoute($this->home);
    }

}
