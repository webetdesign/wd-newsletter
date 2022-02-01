<?php

namespace WebEtDesign\NewsletterBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity()
 * @ORM\Table(name="newsletter__unsubscribe")
 */
class Unsubscribe
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $email;

    /**
     * @var null|DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Timestampable(on="create", field="createdAt")
     */
    protected ?DateTime $createdAt;

    #[Pure] public function __toString()
    {
        return $this->getEmail() ?? '';
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }
}
