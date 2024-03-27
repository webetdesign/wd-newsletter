<?php


namespace WebEtDesign\NewsletterBundle\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait NewsletterFields
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
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
