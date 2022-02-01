<?php


namespace WebEtDesign\NewsletterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

trait NewsletterTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $newsletterToken;

    public function getNewsletterToken(): ?string
    {
        return $this->newsletterToken;
    }

    public function setNewsletterToken(?string $newsletterToken): self
    {
        $this->newsletterToken = $newsletterToken;

        return $this;
    }
}
