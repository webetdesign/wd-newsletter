<?php

namespace WebEtDesign\NewsletterBundle\Entity;

use DateTime;
use JetBrains\PhpStorm\Pure;
use WebEtDesign\NewsletterBundle\Repository\NewsletterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\Group; 
/**
 * @ORM\Entity(repositoryClass="WebEtDesign\NewsletterBundle\Repository\NewsletterRepository", repositoryClass=NewsletterRepository::class)
 * @ORM\Table(name="newsletter__newsletter")
 */
class Newsletter
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
    private ?string $title;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $model = '';

    /**
     * @ORM\Column(type="text")
     */
    private ?string $sender = '';

    /**
     * @ORM\Column(type="text")
     */
    private ?string $email = '';

    /**
     * @ORM\OneToMany(targetEntity=Content::class, mappedBy="newsletter", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private Collection $contents;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $emailsMore;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $isSent = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $sendInAllLocales = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected mixed $sentAt;

    #[Pure] public function __toString()
    {
        return $this->getTitle() ?? '';
    }

    #[Pure] public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
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

    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setNewsletter($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            // set the owning side to null (unless already changed)
            if ($content->getNewsletter() === $this) {
                $content->setNewsletter(null);
            }
        }

        return $this;
    }

    public function getContent($code): ?Content
    {
        foreach ($this->contents as $content) {
            if ($content->getCode() === $code){
                return $content;
            }
        }

        return null;
    }

    public function getEmailsMore(): ?string
    {
        return $this->emailsMore;
    }

    public function setEmailsMore(?string $emailsMore): self
    {
        $this->emailsMore = $emailsMore;

        return $this;
    }

    public function getEmailsMoreArray(): array
    {
        $more = ['fr' => []];
        $cpt = 0;

        $emails = preg_replace('/[\s\r\n]/', ',', $this->getEmailsMore());

        foreach (explode(',', $emails) as $email) {
           if ($email){
               $more['fr'][md5(uniqid())] = $email;
               $cpt++;
           }
        }

        return $cpt != 0 ? $more : [];
    }

    public function getIsSent(): bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): self
    {
        $this->isSent = $isSent;
        return $this;
    }

    public function getSentAt(): ?DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt($sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function sentAtFormatted(){
        return $this->sentAt?->format('d/m/Y H:i:s');
    }

    public function isSendInAllLocales(): bool
    {
        return $this->sendInAllLocales !== null ? $this->sendInAllLocales : false;
    }

    public function setSendInAllLocales(bool $sendInAllLocales): void
    {
        $this->sendInAllLocales = $sendInAllLocales;
    }

}
