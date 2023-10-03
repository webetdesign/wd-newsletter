<?php

namespace WebEtDesign\NewsletterBundle\Entity;


use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessengerMessageRepository;

/**
 * @ORM\Entity(repositoryClass=MessengerMessageRepository::class)
 * @ORM\Table(name="messenger_messages__wd_newsletter")
 */
class MessengerMessage
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
    private ?string $body;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $headers;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $queue_name;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTime $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTime $available_at;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string|null
     */
    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    /**
     * @param string|null $headers
     */
    public function setHeaders(?string $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return string|null
     */
    public function getQueueName(): ?string
    {
        return $this->queue_name;
    }

    /**
     * @param string|null $queue_name
     */
    public function setQueueName(?string $queue_name): void
    {
        $this->queue_name = $queue_name;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->created_at;
    }

    /**
     * @param DateTime|null $created_at
     */
    public function setCreatedAt(?DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return DateTime|null
     */
    public function getAvailableAt(): ?DateTime
    {
        return $this->available_at;
    }

    /**
     * @param DateTime|null $available_at
     */
    public function setAvailableAt(?DateTime $available_at): void
    {
        $this->available_at = $available_at;
    }


}