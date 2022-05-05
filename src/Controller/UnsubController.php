<?php

namespace WebEtDesign\NewsletterBundle\Controller;

use App\Entity\User as User;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use WebEtDesign\UserBundle\Entity\WDUser;

class UnsubController extends AbstractController
{
    private ?string $home;

    /**
     * UnsubController constructor.
     * @param EntityManagerInterface $em
     * @param array $routes
     */
    public function __construct(private EntityManagerInterface $em, array $routes)
    {
        $this->home = array_key_exists('home', $routes) ? $routes['home'] : 'index';
    }

    /**
     * @param string $token
     *
     * @Route(path="/newsletter/unsub/{token}", name="newsletter_unsub")
     *
     * @return RedirectResponse
     */
    public function token(string $token): RedirectResponse
    {
        /** @var WDUser $user */
        $user = $this->em->getRepository(WDUser::class)->findOneBy([
            'newsletterToken' => $token
        ]);

        if ($user) {
            $this->makeUnsub($user);
        }else{
            $this->addFlash('error', "Vous n'êtes pas inscrit dans notre liste de diffusion");
        }

        return $this->redirectToRoute($this->home);
    }

    /**
     * @Route(path="/newsletter/unsub/auto", name="newsletter_unsub_auto")
     */
    public function auto(): RedirectResponse
    {
        $this->addFlash('error', "Vous avez ajouté par l'administrateur à cette newsletter, vous n'êtes pas sur notre liste de diffusion.");
        return $this->redirectToRoute($this->home);
    }

    /**
     * @param WDUser $user
     */
    private function makeUnsub(WDUser $user)
    {
        $user
            ->setNewsletterAcceptedAt(null)
            ->setNewsletter(false)
            ->setNewsletterToken(null)
        ;
        $this->em->flush();
        $this->addFlash('success', "Vous avez été supprimé de notre liste de diffusion.");
    }

}
