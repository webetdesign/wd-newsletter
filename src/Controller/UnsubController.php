<?php

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
     * UnsubController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param string $token
     * @Route(path="/newsletter/unsub/{token}")
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
            $unsub = new Unsubscribe();
            $unsub->setEmail($user->getEmail());

            $this->em->persist($unsub);
            $this->em->flush();

            $this->addFlash('success', "Vous avez été supprimé de notre liste de diffusion.");
        }

        return $this->redirect('/');
    }

}
