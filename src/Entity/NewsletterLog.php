<?php

namespace WebEtDesign\NewsletterBundle\Entity;

use WebEtDesign\NewsletterBundle\Repository\NewsletterLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JetBrains\PhpStorm\Pure;
use App\Entity\User\User;
/**
 * @ORM\Entity(repositoryClass=NewsletterLogRepository::class)
 * @ORM\Table(name="newsletter__log")
 */
class NewsletterLog
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $title = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $body = '';

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $viewed = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $clicked = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $token = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $newsletterId = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $receiver = '';
    
    #[Pure] public function __toString(): string
    {
        return $this->getTitle() ?? '';
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return NewsletterLog
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getViewed(): ?bool
    {
        return $this->viewed;
    }

    public function setViewed(?bool $viewed): self
    {
        $this->viewed = $viewed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClicked(): bool
    {
        return $this->clicked;
    }

    /**
     * @param bool $clicked
     * @return NewsletterLog
     */
    public function setClicked(bool $clicked): self
    {
        $this->clicked = $clicked;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     * @return NewsletterLog
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNewsletterId(): ?int
    {
        return $this->newsletterId;
    }

    /**
     * @param int|null $newsletterId
     * @return NewsletterLog
     */
    public function setNewsletterId(?int $newsletterId): self
    {
        $this->newsletterId = $newsletterId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiver(): ?string
    {
        return $this->receiver;
    }

    /**
     * @param string|null $receiver
     * @return NewsletterLog
     */
    public function setReceiver(?string $receiver): NewsletterLog
    {
        $this->receiver = $receiver;
        return $this;
    }
    
}
